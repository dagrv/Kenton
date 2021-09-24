<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficeImageControllerTest extends TestCase {
    
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_should_upload_an_image_and_store_it() {
        Storage::fake('public');

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);
        
        $response = $this->post("/api/offices/{$office->id}/images", [
            'image' => UploadedFile::fake()->image('image.jpg')
        ]);

        $response->assertCreated();
        
        Storage::disk('public')->assertExists(
            $response->json('data.path')
        );
    }


    /**
     * @test
     */
    public function it_should_delete_an_image() {
        Storage::disk('public')->put('office_image.jpg', 'empty');
        
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $office->images()->create([
            'path' => 'image.jpg'
        ]);

        $image = $office->images()->create([
            'path' => 'office_image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");

        $response->assertOk();

        //assertModelMissing leads to an error (need to upgrade laravel)

        Storage::disk('public')->assertMissing('office_image.jpg');
    }


    /**
     * @test
     */
    public function it_should_not_delete_an_image_belonging_to_another_resource() {
        Storage::disk('public')->put('office_image.jpg', 'empty');
        
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        $office2 = Office::factory()->for($user)->create();

        $image = $office2->images()->create([
            'path' => 'office_image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['image' => 'Cannot delete this image']);
    }


    /**
     * @test
     */
    public function it_should_not_delete_only_image_left() {
        Storage::disk('public')->put('office_image.jpg', 'empty');
        
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path' => 'office_image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['image' => 'Cannot delete this image']);
    }


    /**
     * @test
     */
    public function it_should_not_delete_featured_image() {
        Storage::disk('public')->put('office_image.jpg', 'empty');
        
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $office->images()->create([
            'path' => 'image.jpg'
        ]);

        $image = $office->images()->create([
            'path' => 'office_image.jpg'
        ]);

        $office->update(['featured_image_id' => $image->id]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['image' => 'Cannot delete this image (featured image)']);
    }
}
