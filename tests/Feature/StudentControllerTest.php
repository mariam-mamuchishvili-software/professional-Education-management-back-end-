<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_students(): void
    {
        Student::factory(5)->create();

        $response = $this->getJson('/api/students');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_show_returns_one_student(): void
    {
        $student = Student::factory()->create();

        $response = $this->getJson("/api/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $student->id);
    }

    public function test_store_creates_a_student(): void
    {
        $data = [
            'first_name' => 'Giorgi',
            'last_name' => 'Kapanadze',
            'email' => 'student@example.com',
            'phone' => '+995 555 123 456',
            'birth_date' => '2000-01-15',
        ];

        $response = $this->postJson('/api/students', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.first_name', 'Giorgi');

        $this->assertDatabaseHas('students', [
            'email' => 'student@example.com',
        ]);
    }

    public function test_update_updates_a_student(): void
    {
        $student = Student::factory()->create();

        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Student',
            'email' => 'updated-student@example.com',
            'phone' => '+995 599 000 000',
            'birth_date' => '1999-05-20',
        ];

        $response = $this->putJson("/api/students/{$student->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Updated');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Updated',
            'email' => 'updated-student@example.com',
        ]);
    }

    public function test_destroy_deletes_a_student(): void
    {
        $student = Student::factory()->create();

        $response = $this->deleteJson("/api/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Student deleted successfully');

        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);
    }
}
