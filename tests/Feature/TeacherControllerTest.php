<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Module;
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

    public function test_show_does_not_include_relationships_by_default(): void
    {
        $teacher = Teacher::factory()->create();
        $teacher->colleges()->attach(College::factory()->create());
        $teacher->modules()->attach(Module::factory()->create());

        $response = $this->getJson("/api/teachers/{$teacher->id}");

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.colleges')
            ->assertJsonMissingPath('data.modules');
    }

    public function test_show_returns_teacher_with_only_the_requested_relation(): void
    {
        $teacher = Teacher::factory()->create();
        $college = College::factory()->create();
        $teacher->colleges()->attach($college);
        $teacher->modules()->attach(Module::factory()->create());

        $response = $this->getJson("/api/teachers/{$teacher->id}?include=colleges");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.colleges')
            ->assertJsonPath('data.colleges.0.id', $college->id)
            ->assertJsonMissingPath('data.modules');
    }

    public function test_show_returns_teacher_with_colleges_and_modules_when_included(): void
    {
        $teacher = Teacher::factory()->create();
        $college = College::factory()->create();
        $module = Module::factory()->create();
        $teacher->colleges()->attach($college);
        $teacher->modules()->attach($module);

        $response = $this->getJson("/api/teachers/{$teacher->id}?include=colleges,modules");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.colleges')
            ->assertJsonPath('data.colleges.0.id', $college->id)
            ->assertJsonCount(1, 'data.modules')
            ->assertJsonPath('data.modules.0.id', $module->id);
    }

    public function test_index_returns_teachers_with_colleges_and_modules_when_included(): void
    {
        $teacher = Teacher::factory()->create();
        $teacher->colleges()->attach(College::factory()->create());
        $teacher->modules()->attach(Module::factory()->create());

        $response = $this->getJson('/api/teachers?include=colleges,modules');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.0.colleges')
            ->assertJsonCount(1, 'data.0.modules');
    }

    public function test_show_ignores_invalid_include_value(): void
    {
        $teacher = Teacher::factory()->create();
        $teacher->colleges()->attach(College::factory()->create());

        $response = $this->getJson("/api/teachers/{$teacher->id}?include=invalidRelation");

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.colleges')
            ->assertJsonMissingPath('data.modules');
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
