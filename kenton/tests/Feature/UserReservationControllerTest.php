<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\NewHostReservation;
use App\Notifications\NewUserReservation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserReservationControllerTest extends TestCase {

    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function it_lists_reservation_that_belongs_to_a_user() {
        $user =  User::factory()->create();

        [$reservation] = Reservation::factory()->for($user)->count(2)->create();

        $image = $reservation->office->images()->create([
            'path' => 'office_image.jpg'
        ]);

        $reservation->office()->update(['featured_image_id' => $image->id]);

        Reservation::factory()->count(3)->create();

        $this->actingAs($user);

        $response = $this->getJson('/api/reservations');
        $response
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => ['*' => ['id', 'office']]])
            ->assertJsonPath('data.0.office.featured_image.id', $image->id);
    }


    /**
     * @test
     */
    public function it_lists_reservation_and_filters_by_date_range() {
        $user = User::factory()->create();

        $fromDate = '2021-03-03';
        $toDate = '2021-04-04';

        $reservations = Reservation::factory()->for($user)->createMany([
            [
                'start_date' => '2021-03-01',
                'end_date' => '2021-03-15',
            ],[
                'start_date' => '2021-03-25',
                'end_date' => '2021-04-15',
            ],[
                'start_date' => '2021-03-25',
                'end_date' => '2021-03-29',
            ],[
                'start_date' => '2021-03-01',
                'end_date' => '2021-04-15',
            ]
        ]);

        // Within, from a different user
        Reservation::factory()->create([
            'start_date' => '2021-03-25',
            'end_date' => '2021-03-29',
        ]);

        // Outside range
        Reservation::factory()->for($user)->create([
            'start_date' => '2021-02-25',
            'end_date' => '2021-03-01',
        ]);

        Reservation::factory()->for($user)->create([
            'start_date' => '2021-05-01',
            'end_date' => '2021-05-01',
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/reservations?'.http_build_query([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]));

        $response->assertJsonCount(4, 'data');
        $this->assertEquals($reservations->pluck('id')->toArray(), collect($response->json('data'))->pluck('id')->toArray());
    }

    /**
     * @test
     */
    public function it_filters_results_by_status() {
        $user =  User::factory()->create();

        $reservation = Reservation::factory()->for($user)->create([
            'status' => Reservation::STATUS_ACTIVE
        ]);

        $reservation2 = Reservation::factory()->for($user)->cancelled()->create();

        $this->actingAs($user);

        $response = $this->getJson('/api/reservations?'.http_build_query([
            'status' => Reservation::STATUS_ACTIVE,
        ]));

        $response
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $reservation->id);
    }


    /**
     * @test
     */
    public function it_filters_results_by_specific_office() {
        $user =  User::factory()->create();

        $office = Office::factory()->create();

        $reservation = Reservation::factory()->for($office)->for($user)->create();

        $reservation2 = Reservation::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->getJson('/api/reservations?'.http_build_query([
            'office_id' => $office->id,
        ]));

        $response
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $reservation->id);
    }

    /**
     * @test
     */
    public function it_makes_a_reservation() {
        $user =  User::factory()->create();

        $office = Office::factory()->create([
            'price_per_day' => 1_000,
            'monthly_discount' => 10,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'office_id' => $office->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(40),
        ]);

        $response->assertCreated();

        $response->assertJsonPath('data.price', 36000)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.office_id', $office->id)
            ->assertJsonPath('data.status', Reservation::STATUS_ACTIVE);
    }


    /**
     * @test
     */
    public function it_cannot_make_reservation_on_non_existing_office() {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'office_id' => 10000,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(41),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['office_id' => 'Invalid office_id']);
    }



    /**
     * @test
     */
    public function it_cannot_make_reservation_on_pending_office_or_hidden() {
        $user = User::factory()->create();

        $office = Office::factory()->create([
            'approval_status' => Office::APPROVAL_PENDING
        ]);

        $office2 = Office::factory()->create([
            'hidden' => true
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'office_id' => $office->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(41),
        ]);

        $response2 = $this->postJson('/api/reservations', [
            'office_id' => $office2->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(41),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['office_id' => 'Sorry, you cannot reserve an hidden office']);

        $response2->assertUnprocessable()
            ->assertJsonValidationErrors(['office_id' => 'Sorry, you cannot reserve an hidden office']);
    }


    /**
     * @test
     */
    public function user_cannot_reserve_their_own_office() {
        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'office_id' => $office->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(41),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['office_id' => 'Sorry, you cannot reserve your own office']);
    }


    /**
     * @test
     */
    public function it_cannot_reserve_less_than_2_days() {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'office_id' => $office->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDay(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date' => 'The end date must be a date after start date.']);
    }



    /**
     * @test
     */
    public function it_cannot_reservation_on_same_day() {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'office_id' => $office->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['start_date' => 'The start date must be a date after today.']);
    }




    /**
     * @test
     */
    public function it_makes_reservation_for_2_days() {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'office_id' => $office->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(2),
        ]);

        $response->assertCreated();
    }


    /**
     * @test
     */
    public function it_cannot_make_reservation_thats_conflicting() {
        $user = User::factory()->create();

        $fromDate = now()->addDays(2)->toDateString();
        $toDate = now()->addDay(15)->toDateString();

        $office = Office::factory()->create();

        Reservation::factory()->for($office)->create([
            'start_date' => now()->addDay(2),
            'end_date' => $toDate,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'office_id' => $office->id,
            'start_date' => $fromDate,
            'end_date' => $toDate,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['office_id' => 'Sorry, you cannot reserve an office with this date range']);
    }


    /**
     * @test
     */
    public function it_send_notifications_on_new_reservations() {
        Notification::fake();

        $user = User::factory()->create();

        $office = Office::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations', [
            'office_id' => $office->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(2),
        ]);

        Notification::assertSentTo($user, NewUserReservation::class);
        Notification::assertSentTo($office->user, NewHostReservation::class);

        $response->assertCreated();
    }

}
