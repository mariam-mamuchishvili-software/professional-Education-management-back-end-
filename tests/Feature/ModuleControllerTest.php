<?php

namespace Tests\Feature;

use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_modules(): void
    {
        Module::factory(5)->create();

        $response = $this->getJson('/api/modules');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_show_returns_one_module(): void
    {
        $module = Module::factory()->create();

        $response = $this->getJson("/api/modules/{$module->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $module->id);
    }

    public function test_show_returns_404_for_nonexistent_module(): void
    {
        $response = $this->getJson('/api/modules/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Module not found']);
    }

    public function test_store_creates_a_module(): void
    {
        $data = [
            'name' => 'Database Systems',
            'code' => 'MOD-001',
            'description' => 'Introduction to databases.',
            'duration' => 40,
            'credits' => 5,
        ];

        $response = $this->postJson('/api/modules', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Database Systems');

        $this->assertDatabaseHas('modules', [
            'code' => 'MOD-001',
        ]);
    }

    public function test_update_updates_a_module(): void
    {
        $module = Module::factory()->create();

        $data = [
            'name' => 'Updated Module',
            'code' => 'MOD-999',
            'description' => 'Updated description.',
            'duration' => 60,
            'credits' => 8,
        ];

        $response = $this->putJson("/api/modules/{$module->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Module');

        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'name' => 'Updated Module',
            'code' => 'MOD-999',
        ]);
    }

    public function test_destroy_deletes_a_module(): void
    {
        $module = Module::factory()->create();

        $response = $this->deleteJson("/api/modules/{$module->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Module deleted successfully');

        $this->assertDatabaseMissing('modules', [
            'id' => $module->id,
        ]);
    }
}
