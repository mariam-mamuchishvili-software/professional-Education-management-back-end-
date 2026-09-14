<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Student;
use App\Models\Teacher;
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

    public function test_show_does_not_include_relationships_by_default(): void
    {
        $module = Module::factory()->create();
        $module->teachers()->attach(Teacher::factory()->create());
        $module->professions()->attach(Profession::factory()->create());

        $response = $this->getJson("/api/modules/{$module->id}");

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.teachers')
            ->assertJsonMissingPath('data.professions');
    }

    public function test_show_returns_module_with_only_the_requested_relation(): void
    {
        $module = Module::factory()->create();
        $teacher = Teacher::factory()->create();
        $module->teachers()->attach($teacher);
        $module->professions()->attach(Profession::factory()->create());

        $response = $this->getJson("/api/modules/{$module->id}?include=teachers");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.teachers')
            ->assertJsonPath('data.teachers.0.id', $teacher->id)
            ->assertJsonMissingPath('data.professions');
    }

    public function test_show_returns_module_with_teachers_and_professions_when_included(): void
    {
        $module = Module::factory()->create();
        $teacher = Teacher::factory()->create();
        $profession = Profession::factory()->create();
        $module->teachers()->attach($teacher);
        $module->professions()->attach($profession);

        $response = $this->getJson("/api/modules/{$module->id}?include=teachers,professions");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.teachers')
            ->assertJsonPath('data.teachers.0.id', $teacher->id)
            ->assertJsonCount(1, 'data.professions')
            ->assertJsonPath('data.professions.0.id', $profession->id);
    }

    public function test_index_returns_modules_with_teachers_and_professions_when_included(): void
    {
        $module = Module::factory()->create();
        $module->teachers()->attach(Teacher::factory()->create());
        $module->professions()->attach(Profession::factory()->create());

        $response = $this->getJson('/api/modules?include=teachers,professions');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.0.teachers')
            ->assertJsonCount(1, 'data.0.professions');
    }

    public function test_show_returns_module_with_three_level_nested_include(): void
    {
        $module = Module::factory()->create();
        $profession = Profession::factory()->create();
        $module->professions()->attach($profession);
        $group = Group::factory()->create(['profession_id' => $profession->id]);
        $student = Student::factory()->create();
        $group->students()->attach($student);

        $response = $this->getJson("/api/modules/{$module->id}?include=professions.groups.students");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.professions.0.groups')
            ->assertJsonCount(1, 'data.professions.0.groups.0.students')
            ->assertJsonPath('data.professions.0.groups.0.students.0.id', $student->id);
    }

    public function test_show_ignores_include_beyond_three_levels(): void
    {
        $module = Module::factory()->create();
        $profession = Profession::factory()->create();
        $module->professions()->attach($profession);
        $group = Group::factory()->create(['profession_id' => $profession->id]);
        $student = Student::factory()->create();
        $group->students()->attach($student);

        $response = $this->getJson("/api/modules/{$module->id}?include=professions.groups.students.groups");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.professions.0.groups.0.students')
            ->assertJsonMissingPath('data.professions.0.groups.0.students.0.groups');
    }

    public function test_show_ignores_invalid_include_value(): void
    {
        $module = Module::factory()->create();
        $module->teachers()->attach(Teacher::factory()->create());

        $response = $this->getJson("/api/modules/{$module->id}?include=invalidRelation");

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.teachers')
            ->assertJsonMissingPath('data.professions');
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
