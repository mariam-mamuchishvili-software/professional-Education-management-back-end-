<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\TeacherWorkExperience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeacherWorkExperienceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_work_experiences(): void
    {
        $this->getJson('/api/me/work-experiences')->assertUnauthorized();
    }

    public function test_user_without_a_teacher_profile_cannot_list_or_create_work_experiences(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/me/work-experiences')
            ->assertForbidden()
            ->assertJsonPath('message', 'No teacher profile is linked to this account.');

        $this->postJson('/api/me/work-experiences', $this->validPayload())->assertForbidden();

        $this->assertDatabaseCount('teacher_work_experiences', 0);
    }

    public function test_index_lists_only_the_signed_in_teachers_work_experiences(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $own = TeacherWorkExperience::factory()->for($teacher)->create();
        TeacherWorkExperience::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/me/work-experiences')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);
    }

    public function test_store_creates_a_work_experience_for_the_signed_in_teacher_ignoring_a_sent_teacher_id(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $otherTeacher = Teacher::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/me/work-experiences', [
            ...$this->validPayload(),
            'teacher_id' => $otherTeacher->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.teacher_id', $teacher->id)
            ->assertJsonPath('data.organization', 'Ministry of Education')
            ->assertJsonPath('data.start_date', '2019-09-01')
            ->assertJsonPath('data.end_date', '2023-06-30')
            ->assertJsonPath('data.is_current', false);

        $this->assertDatabaseHas('teacher_work_experiences', [
            'teacher_id' => $teacher->id,
            'organization' => 'Ministry of Education',
            'position' => 'Methodologist',
        ]);
        $this->assertDatabaseMissing('teacher_work_experiences', ['teacher_id' => $otherTeacher->id]);
    }

    public function test_store_requires_organization_position_and_start_date(): void
    {
        Sanctum::actingAs($this->teacherUser()[0]);

        $this->postJson('/api/me/work-experiences', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['organization', 'position', 'start_date']);
    }

    public function test_store_rejects_an_end_date_before_the_start_date(): void
    {
        Sanctum::actingAs($this->teacherUser()[0]);

        $this->postJson('/api/me/work-experiences', [...$this->validPayload(), 'end_date' => '2019-01-01'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date' => 'The end date field must be a date after or equal to 2019-09-01.']);
    }

    public function test_store_rejects_an_end_date_for_a_current_position(): void
    {
        Sanctum::actingAs($this->teacherUser()[0]);

        $this->postJson('/api/me/work-experiences', [...$this->validPayload(), 'is_current' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_update_changes_the_teachers_own_work_experience(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $workExperience = TeacherWorkExperience::factory()->for($teacher)->create(['position' => 'Assistant']);

        Sanctum::actingAs($user);

        $this->patchJson("/api/me/work-experiences/{$workExperience->id}", ['position' => 'Head of Department'])
            ->assertOk()
            ->assertJsonPath('data.position', 'Head of Department');

        $this->assertSame('Head of Department', $workExperience->fresh()->position);
    }

    public function test_marking_a_position_as_current_clears_its_end_date(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $workExperience = TeacherWorkExperience::factory()->for($teacher)->create();

        Sanctum::actingAs($user);

        $this->patchJson("/api/me/work-experiences/{$workExperience->id}", ['is_current' => true])
            ->assertOk()
            ->assertJsonPath('data.is_current', true)
            ->assertJsonPath('data.end_date', null);
    }

    public function test_partial_update_compares_the_end_date_with_the_stored_start_date(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $workExperience = TeacherWorkExperience::factory()->for($teacher)->create(['start_date' => '2020-01-01']);

        Sanctum::actingAs($user);

        $this->patchJson("/api/me/work-experiences/{$workExperience->id}", ['end_date' => '2019-12-31'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_destroy_deletes_the_teachers_own_work_experience(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $workExperience = TeacherWorkExperience::factory()->for($teacher)->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/me/work-experiences/{$workExperience->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Work experience deleted successfully');

        $this->assertModelMissing($workExperience);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function recordRequests(): array
    {
        return [
            'show' => ['getJson'],
            'update' => ['patchJson'],
            'destroy' => ['deleteJson'],
        ];
    }

    #[DataProvider('recordRequests')]
    public function test_teacher_gets_not_found_for_another_teachers_work_experience(string $method): void
    {
        $workExperience = TeacherWorkExperience::factory()->create(['position' => 'Original']);

        Sanctum::actingAs($this->teacherUser()[0]);

        $this->{$method}("/api/me/work-experiences/{$workExperience->id}", ['position' => 'Hijacked'])
            ->assertNotFound();

        $this->assertModelExists($workExperience);
        $this->assertSame('Original', $workExperience->fresh()->position);
    }

    public function test_admin_can_view_update_and_delete_any_teachers_work_experience(): void
    {
        $workExperience = TeacherWorkExperience::factory()->create();

        Sanctum::actingAs(User::factory()->collegeAdmin()->create());

        $this->getJson("/api/me/work-experiences/{$workExperience->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $workExperience->id);

        $this->patchJson("/api/me/work-experiences/{$workExperience->id}", ['position' => 'Updated by admin'])
            ->assertOk()
            ->assertJsonPath('data.position', 'Updated by admin');

        $this->deleteJson("/api/me/work-experiences/{$workExperience->id}")->assertOk();

        $this->assertModelMissing($workExperience);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'organization' => 'Ministry of Education',
            'position' => 'Methodologist',
            'start_date' => '2019-09-01',
            'end_date' => '2023-06-30',
            'description' => 'Curriculum development.',
        ];
    }

    /**
     * A user with the teacher role linked to a teacher profile.
     *
     * @return array{User, Teacher}
     */
    private function teacherUser(): array
    {
        $user = User::factory()->teacher()->create();

        return [$user, Teacher::factory()->for($user)->create()];
    }
}
