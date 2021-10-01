<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserReservationControllerTest extends TestCase {
    
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function it_lists_reservation_that_belongs_to_a_user() {
        $user =  User::factory()->create();
        $reservation = Reservation::factory()->for($user)->create();

        $image = $reservation->office->images()->create([
            'path' => 'office_image.jpg'
        ]);

        $reservation->office()->update(['featured_image_id' => $image->id]);
        
        Reservation::factory()->for($user)->count(2)->create();
        Reservation::factory()->count(3)->create();

        $this->actingAs($user);

        $response = $this->getJson('/api/reservations');
        $response
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => ['*' => ['id', 'office']]])
            ->assertJsonPath('data.0.office.featured_image.id', $image->id);
    }


    /**
     * @test
     */
    public function it_lists_reservation_and_filters_by_date_range() {
        $user =  User::factory()->create();

        $fromDate = '2021-03-03';
        $toDate = '2021-04-04';
        
        // 1
        $reservation1 = Reservation::factory()->for($user)->create([
            'start_date' => '2021-03-01',
            'end_date' => '2021-03-15',
        ]);

        // 2
        $reservation2 = Reservation::factory()->for($user)->create([
            'start_date' => '2021-03-25',
            'end_date' => '2021-04-15',
        ]);

        // 3
        $reservation3 =Reservation::factory()->for($user)->create([
            'start_date' => '2021-03-25',
            'end_date' => '2021-03-29',
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

        $response->assertJsonCount(3, 'data');

        $this->assertEquals(
            [$reservation1->id, $reservation2->id, $reservation3->id],
            collect($response->json('data'))->pluck('id')->toArray());
    }
}
