<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Module;
use App\Models\Slide;
use App\Models\Student;
use App\Models\Teacher;
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

    public function test_show_does_not_include_teachers_by_default(): void
    {
        $college = College::factory()->create();
        $college->teachers()->attach(Teacher::factory(2)->create());

        $response = $this->getJson("/api/colleges/{$college->id}");

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.teachers');
    }

    public function test_show_returns_college_with_teachers_when_included(): void
    {
        $college = College::factory()->create();
        $teachers = Teacher::factory(2)->create();
        $college->teachers()->attach($teachers);

        $response = $this->getJson("/api/colleges/{$college->id}?include=teachers");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.teachers')
            ->assertJsonPath('data.teachers.0.id', $teachers[0]->id);
    }

    public function test_show_always_includes_slides(): void
    {
        $college = College::factory()->create();
        $slides = Slide::factory(2)->for($college)->create();

        $response = $this->getJson("/api/colleges/{$college->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.slides')
            ->assertJsonPath('data.slides.0.id', $slides[0]->id);
    }

    public function test_index_always_includes_slides(): void
    {
        $college = College::factory()->create();
        $slides = Slide::factory(2)->for($college)->create();

        $response = $this->getJson('/api/colleges');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.0.slides');
    }

    public function test_index_returns_colleges_with_teachers_when_included(): void
    {
        $college = College::factory()->create();
        $teachers = Teacher::factory(2)->create();
        $college->teachers()->attach($teachers);

        $response = $this->getJson('/api/colleges?include=teachers');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.0.teachers');
    }

    public function test_show_returns_college_with_three_level_nested_include(): void
    {
        $college = College::factory()->create();
        $teacher = Teacher::factory()->create();
        $college->teachers()->attach($teacher);
        $module = Module::factory()->create();
        $teacher->modules()->attach($module);
        $student = Student::factory()->create();
        $module->students()->attach($student);

        $response = $this->getJson("/api/colleges/{$college->id}?include=teachers.modules.students");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.teachers.0.modules')
            ->assertJsonCount(1, 'data.teachers.0.modules.0.students')
            ->assertJsonPath('data.teachers.0.modules.0.students.0.id', $student->id);
    }

    public function test_show_ignores_include_beyond_three_levels(): void
    {
        $college = College::factory()->create();
        $teacher = Teacher::factory()->create();
        $college->teachers()->attach($teacher);
        $module = Module::factory()->create();
        $teacher->modules()->attach($module);
        $student = Student::factory()->create();
        $module->students()->attach($student);

        $response = $this->getJson("/api/colleges/{$college->id}?include=teachers.modules.students.groups");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.teachers.0.modules.0.students')
            ->assertJsonMissingPath('data.teachers.0.modules.0.students.0.groups');
    }

    public function test_show_ignores_invalid_include_value(): void
    {
        $college = College::factory()->create();
        $college->teachers()->attach(Teacher::factory()->create());

        $response = $this->getJson("/api/colleges/{$college->id}?include=invalidRelation");

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.teachers');
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
