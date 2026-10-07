<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\TeacherEducation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeacherEducationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_educations(): void
    {
        $this->getJson('/api/me/educations')->assertUnauthorized();
    }

    public function test_index_lists_only_the_signed_in_teachers_educations(): void
    {
        $teacher = Teacher::factory()->create();
        $own = TeacherEducation::factory()->for($teacher)->create();
        TeacherEducation::factory()->create();

        Sanctum::actingAs($teacher);

        $this->getJson('/api/me/educations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);
    }

    public function test_store_creates_an_education_for_the_signed_in_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        Sanctum::actingAs($teacher);

        $this->postJson('/api/me/educations', [
            'institution' => 'Tbilisi State University',
            'degree' => 'Master',
            'specialization' => 'Applied Mathematics',
            'start_date' => '2012-09-01',
            'end_date' => '2014-06-30',
        ])
            ->assertCreated()
            ->assertJsonPath('data.teacher_id', $teacher->id)
            ->assertJsonPath('data.degree', 'Master')
            ->assertJsonPath('data.end_date', '2014-06-30');

        $this->assertDatabaseHas('teacher_educations', [
            'teacher_id' => $teacher->id,
            'institution' => 'Tbilisi State University',
            'specialization' => 'Applied Mathematics',
        ]);
    }

    public function test_store_accepts_an_education_without_dates(): void
    {
        Sanctum::actingAs(Teacher::factory()->create());

        $this->postJson('/api/me/educations', [
            'institution' => 'Private Academy',
            'degree' => 'Qualification certificate',
            'specialization' => 'Pedagogy',
            'end_date' => '2014-06-30',
        ])
            ->assertCreated()
            ->assertJsonPath('data.start_date', null);
    }

    public function test_store_requires_institution_degree_and_specialization(): void
    {
        Sanctum::actingAs(Teacher::factory()->create());

        $this->postJson('/api/me/educations', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['institution', 'degree', 'specialization']);
    }

    public function test_store_rejects_an_end_date_before_the_start_date(): void
    {
        Sanctum::actingAs(Teacher::factory()->create());

        $this->postJson('/api/me/educations', [
            'institution' => 'Tbilisi State University',
            'degree' => 'Master',
            'specialization' => 'Applied Mathematics',
            'start_date' => '2014-09-01',
            'end_date' => '2012-06-30',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_update_changes_the_teachers_own_education(): void
    {
        $teacher = Teacher::factory()->create();
        $education = TeacherEducation::factory()->for($teacher)->create(['degree' => 'Bachelor']);

        Sanctum::actingAs($teacher);

        $this->patchJson("/api/me/educations/{$education->id}", ['degree' => 'Master'])
            ->assertOk()
            ->assertJsonPath('data.degree', 'Master');

        $this->assertSame('Master', $education->fresh()->degree);
    }

    public function test_destroy_deletes_the_teachers_own_education(): void
    {
        $teacher = Teacher::factory()->create();
        $education = TeacherEducation::factory()->for($teacher)->create();

        Sanctum::actingAs($teacher);

        $this->deleteJson("/api/me/educations/{$education->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Education deleted successfully');

        $this->assertModelMissing($education);
    }

    public function test_teacher_cannot_view_update_or_delete_another_teachers_education(): void
    {
        $education = TeacherEducation::factory()->create(['degree' => 'Original']);

        Sanctum::actingAs(Teacher::factory()->create());

        $this->getJson("/api/me/educations/{$education->id}")->assertNotFound();
        $this->patchJson("/api/me/educations/{$education->id}", ['degree' => 'Hijacked'])->assertNotFound();
        $this->deleteJson("/api/me/educations/{$education->id}")->assertNotFound();

        $this->assertSame('Original', $education->fresh()->degree);
    }

    public function test_admin_can_update_any_teachers_education(): void
    {
        $education = TeacherEducation::factory()->create();

        Sanctum::actingAs(User::factory()->superAdmin()->create());

        $this->patchJson("/api/me/educations/{$education->id}", ['degree' => 'Doctorate'])
            ->assertOk()
            ->assertJsonPath('data.degree', 'Doctorate');
    }
}
