<?php

namespace Tests\Feature;

use App\Models\College;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollegeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_colleges(): void
    {
        College::factory(5)->create();

        $response = $this->getJson('/api/colleges');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_show_returns_one_college(): void
    {
        $college = College::factory()->create();

        $response = $this->getJson("/api/colleges/{$college->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $college->id);
    }

    public function test_show_returns_404_for_nonexistent_college(): void
    {
        $response = $this->getJson('/api/colleges/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'College not found']);
    }

    public function test_store_creates_a_college(): void
    {
        $data = [
            'name' => 'Tbilisi Technical College',
            'address' => 'Tbilisi, Georgia',
            'email' => 'college@example.com',
            'phone' => '+995 555 123 456',
            'website' => 'https://college.ge',
        ];

        $response = $this->postJson('/api/colleges', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Tbilisi Technical College');

        $this->assertDatabaseHas('colleges', [
            'email' => 'college@example.com',
        ]);
    }

    public function test_update_updates_a_college(): void
    {
        $college = College::factory()->create();

        $data = [
            'name' => 'Updated College',
            'address' => 'Updated Address',
            'email' => 'updated@example.com',
            'phone' => '+995 599 000 000',
            'website' => 'https://updated.ge',
        ];

        $response = $this->putJson("/api/colleges/{$college->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated College');

        $this->assertDatabaseHas('colleges', [
            'id' => $college->id,
            'name' => 'Updated College',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_destroy_deletes_a_college(): void
    {
        $college = College::factory()->create();

        $response = $this->deleteJson("/api/colleges/{$college->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'College deleted successfully');

        $this->assertDatabaseMissing('colleges', [
            'id' => $college->id,
        ]);
    }
}
