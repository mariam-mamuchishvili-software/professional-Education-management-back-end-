<?php

namespace Tests\Feature;

use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrainingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cloudinary.cloud_name' => 'demo-cloud',
            'cloudinary.api_key' => 'demo-key',
            'cloudinary.api_secret' => 'demo-secret',
        ]);
    }

    public function test_index_paginates_trainings_with_skip_and_limit(): void
    {
        $trainings = Training::factory(5)->create();

        $response = $this->getJson('/api/trainings?skip=1&limit=2');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $trainings[1]->id)
            ->assertJsonPath('total', 5)
            ->assertJsonPath('skip', 1)
            ->assertJsonPath('limit', 2);
    }

    public function test_show_returns_one_training(): void
    {
        $training = Training::factory()->create();

        $response = $this->getJson("/api/trainings/{$training->id}");

        $response->assertStatus(200)
            ->assertExactJsonStructure(['data' => ['id', 'title', 'description', 'poster', 'video_link', 'created_at', 'updated_at']])
            ->assertJsonPath('data.id', $training->id)
            ->assertJsonPath('data.title', $training->title);
    }

    public function test_show_returns_404_for_missing_training(): void
    {
        $this->getJson('/api/trainings/999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Training not found');
    }

    public function test_store_creates_training_and_uploads_poster_to_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/trainings/abc123.jpg',
                'public_id' => 'eduhub/trainings/abc123',
            ]),
        ]);

        $response = $this->postJson('/api/trainings', [
            'title' => 'Laravel Basics',
            'description' => 'An introduction to Laravel.',
            'video_link' => 'https://www.youtube.com/watch?v=abc123',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.poster', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/trainings/abc123.jpg');

        $this->assertDatabaseHas('trainings', [
            'title' => 'Laravel Basics',
            'video_link' => 'https://www.youtube.com/watch?v=abc123',
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/trainings/abc123.jpg',
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/upload')
            && str_contains($request->body(), 'eduhub/trainings'));
    }

    public function test_store_rejects_missing_title_invalid_video_link_and_non_image_poster(): void
    {
        Http::fake();

        $response = $this->postJson('/api/trainings', [
            'video_link' => 'not-a-url',
            'poster' => UploadedFile::fake()->create('poster.pdf', 10, 'application/pdf'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'video_link', 'poster']);

        Http::assertNothingSent();
    }

    public function test_update_changes_only_given_fields(): void
    {
        $training = Training::factory()->create(['description' => 'Original description']);

        $response = $this->patchJson("/api/trainings/{$training->id}", [
            'title' => 'Renamed Training',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Renamed Training')
            ->assertJsonPath('data.description', 'Original description');
    }

    public function test_destroy_deletes_training_and_its_poster_from_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/destroy' => Http::response(['result' => 'ok']),
        ]);

        $training = Training::factory()->create([
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/trainings/to-delete.jpg',
        ]);

        $this->deleteJson("/api/trainings/{$training->id}")
            ->assertStatus(200)
            ->assertJsonPath('message', 'Training deleted successfully');

        $this->assertModelMissing($training);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/destroy')
            && $request['public_id'] === 'eduhub/trainings/to-delete');
    }
}
