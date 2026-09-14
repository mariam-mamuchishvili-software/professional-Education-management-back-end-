<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Module;
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

    public function test_show_does_not_include_groups_by_default(): void
    {
        $student = Student::factory()->create();
        $student->groups()->attach(Group::factory(2)->create());

        $response = $this->getJson("/api/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.groups');
    }

    public function test_show_returns_student_with_groups_when_included(): void
    {
        $student = Student::factory()->create();
        $groups = Group::factory(2)->create();
        $student->groups()->attach($groups);

        $response = $this->getJson("/api/students/{$student->id}?include=groups");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.groups')
            ->assertJsonPath('data.groups.0.id', $groups[0]->id);
    }

    public function test_index_returns_students_with_groups_when_included(): void
    {
        $student = Student::factory()->create();
        $groups = Group::factory(2)->create();
        $student->groups()->attach($groups);

        $response = $this->getJson('/api/students?include=groups');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.0.groups');
    }

    public function test_show_returns_student_with_nested_group_profession_when_included(): void
    {
        $student = Student::factory()->create();
        $group = Group::factory()->create();
        $student->groups()->attach($group);

        $response = $this->getJson("/api/students/{$student->id}?include=groups.profession");

        $response->assertStatus(200)
            ->assertJsonPath('data.groups.0.profession.id', $group->profession_id);
    }

    public function test_show_returns_student_with_three_level_nested_include(): void
    {
        $student = Student::factory()->create();
        $group = Group::factory()->create();
        $student->groups()->attach($group);
        $module = Module::factory()->create();
        $group->profession->modules()->attach($module);

        $response = $this->getJson("/api/students/{$student->id}?include=groups.profession.modules");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.groups.0.profession.modules')
            ->assertJsonPath('data.groups.0.profession.modules.0.id', $module->id);
    }

    public function test_show_ignores_include_beyond_three_levels(): void
    {
        $student = Student::factory()->create();
        $group = Group::factory()->create();
        $student->groups()->attach($group);
        $module = Module::factory()->create();
        $group->profession->modules()->attach($module);

        $response = $this->getJson("/api/students/{$student->id}?include=groups.profession.modules.teachers");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.groups.0.profession.modules')
            ->assertJsonMissingPath('data.groups.0.profession.modules.0.teachers');
    }

    public function test_show_ignores_invalid_include_value(): void
    {
        $student = Student::factory()->create();
        $student->groups()->attach(Group::factory()->create());

        $response = $this->getJson("/api/students/{$student->id}?include=invalidRelation");

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.groups');
    }

    public function test_show_returns_404_for_nonexistent_student(): void
    {
        $response = $this->getJson('/api/students/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Student not found']);
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
