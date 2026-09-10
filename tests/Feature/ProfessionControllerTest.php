<?php

namespace Tests\Feature;

use App\Models\Profession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_professions(): void
    {
        Profession::factory(5)->create();

        $response = $this->getJson('/api/professions');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_show_returns_one_profession(): void
    {
        $profession = Profession::factory()->create();

        $response = $this->getJson("/api/professions/{$profession->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $profession->id);
    }

    public function test_store_creates_a_profession(): void
    {
        $data = [
            'name' => 'Software Engineering',
            'code' => 'PRF-001',
            'description' => 'A profession about building software.',
            'duration' => 2,
            'qualification' => 'Bachelor',
        ];

        $response = $this->postJson('/api/professions', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Software Engineering');

        $this->assertDatabaseHas('professions', [
            'code' => 'PRF-001',
        ]);
    }

    public function test_update_updates_a_profession(): void
    {
        $profession = Profession::factory()->create();

        $data = [
            'name' => 'Updated Profession',
            'code' => 'PRF-999',
            'description' => 'Updated description.',
            'duration' => 3,
            'qualification' => 'Master',
        ];

        $response = $this->putJson("/api/professions/{$profession->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Profession');

        $this->assertDatabaseHas('professions', [
            'id' => $profession->id,
            'name' => 'Updated Profession',
            'code' => 'PRF-999',
        ]);
    }

    public function test_destroy_deletes_a_profession(): void
    {
        $profession = Profession::factory()->create();

        $response = $this->deleteJson("/api/professions/{$profession->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Profession deleted successfully');

        $this->assertDatabaseMissing('professions', [
            'id' => $profession->id,
        ]);
    }
}
