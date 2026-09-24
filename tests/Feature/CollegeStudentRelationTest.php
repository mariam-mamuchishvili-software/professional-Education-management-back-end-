<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Student;
use Database\Seeders\CollegeStudentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollegeStudentRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_and_colleges_are_linked_in_both_directions_with_timestamps(): void
    {
        $college = College::factory()->create();
        $student = Student::factory()->create();

        $college->students()->attach($student);

        $this->assertTrue($student->colleges()->whereKey($college->id)->exists());
        $this->assertNotNull($college->students()->first()->pivot->created_at);
    }

    public function test_deleting_a_college_or_student_removes_their_pivot_rows(): void
    {
        $college = College::factory()->create();
        $students = Student::factory(2)->create();
        $college->students()->attach($students);

        $students[0]->delete();
        $this->assertDatabaseCount('college_student', 1);

        $college->delete();
        $this->assertDatabaseCount('college_student', 0);
    }

    public function test_college_students_index_lists_attached_students(): void
    {
        $college = College::factory()->create();
        $student = Student::factory()->create();
        $college->students()->attach($student);
        Student::factory()->create();

        $response = $this->getJson("/api/colleges/{$college->id}/students");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $student->id);
    }

    public function test_student_colleges_index_lists_attached_colleges(): void
    {
        $student = Student::factory()->create();
        $college = College::factory()->create();
        $student->colleges()->attach($college);
        College::factory()->create();

        $response = $this->getJson("/api/students/{$student->id}/colleges");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $college->id);
    }

    public function test_attach_student_links_a_student_to_a_college_only_once(): void
    {
        $college = College::factory()->create();
        $student = Student::factory()->create();

        $this->postJson("/api/colleges/{$college->id}/students", ['student_id' => $student->id])
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->postJson("/api/colleges/{$college->id}/students", ['student_id' => $student->id])
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseCount('college_student', 1);
    }

    public function test_attach_student_rejects_a_missing_or_nonexistent_student(): void
    {
        $college = College::factory()->create();

        $this->postJson("/api/colleges/{$college->id}/students", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('student_id');

        $this->postJson("/api/colleges/{$college->id}/students", ['student_id' => 999])
            ->assertStatus(422)
            ->assertJsonValidationErrors('student_id');

        $this->assertDatabaseCount('college_student', 0);
    }

    public function test_detach_student_unlinks_only_that_student(): void
    {
        $college = College::factory()->create();
        $students = Student::factory(2)->create();
        $college->students()->attach($students);

        $response = $this->deleteJson("/api/colleges/{$college->id}/students/{$students[0]->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Student detached from college successfully');

        $this->assertEquals([$students[1]->id], $college->students()->pluck('students.id')->all());
        $this->assertModelExists($students[0]);
    }

    public function test_show_college_includes_students_only_when_requested(): void
    {
        $college = College::factory()->create();
        $college->students()->attach(Student::factory(2)->create());

        $this->getJson("/api/colleges/{$college->id}")
            ->assertStatus(200)
            ->assertJsonMissingPath('data.students');

        $this->getJson("/api/colleges/{$college->id}?include=students")
            ->assertStatus(200)
            ->assertJsonCount(2, 'data.students');
    }

    public function test_show_student_includes_colleges_only_when_requested(): void
    {
        $student = Student::factory()->create();
        $college = College::factory()->create();
        $student->colleges()->attach($college);

        $this->getJson("/api/students/{$student->id}")
            ->assertStatus(200)
            ->assertJsonMissingPath('data.colleges');

        $this->getJson("/api/students/{$student->id}?include=colleges")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.colleges')
            ->assertJsonPath('data.colleges.0.id', $college->id);
    }

    public function test_seeder_links_every_student_to_a_college_and_is_safe_to_rerun(): void
    {
        College::factory(2)->create();
        Student::factory(3)->create();

        $this->seed(CollegeStudentSeeder::class);
        $this->seed(CollegeStudentSeeder::class);

        $this->assertDatabaseCount('college_student', 3);
        $this->assertSame(0, Student::query()->doesntHave('colleges')->count());
        $this->assertSame(0, College::query()->doesntHave('students')->count());
    }
}
