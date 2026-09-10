<?php

namespace Tests\Feature;

use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_teachers(): void
    {
        Teacher::factory(5)->create();

        $response = $this->getJson('/api/teachers');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_show_returns_one_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->getJson("/api/teachers/{$teacher->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $teacher->id);
    }

    public function test_show_returns_404_for_nonexistent_teacher(): void
    {
        $response = $this->getJson('/api/teachers/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Teacher not found']);
    }

    public function test_store_creates_a_teacher(): void
    {
        $data = [
            'first_name' => 'Nino',
            'last_name' => 'Beridze',
            'email' => 'teacher@example.com',
            'phone' => '+995 555 123 456',
            'specialization' => 'Mathematics',
        ];

        $response = $this->postJson('/api/teachers', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.first_name', 'Nino');

        $this->assertDatabaseHas('teachers', [
            'email' => 'teacher@example.com',
        ]);
    }

    public function test_update_updates_a_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Teacher',
            'email' => 'updated-teacher@example.com',
            'phone' => '+995 599 000 000',
            'specialization' => 'Physics',
        ];

        $response = $this->putJson("/api/teachers/{$teacher->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Updated');

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'first_name' => 'Updated',
            'email' => 'updated-teacher@example.com',
        ]);
    }

    public function test_destroy_deletes_a_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->deleteJson("/api/teachers/{$teacher->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Teacher deleted successfully');

        $this->assertDatabaseMissing('teachers', [
            'id' => $teacher->id,
        ]);
    }
}
