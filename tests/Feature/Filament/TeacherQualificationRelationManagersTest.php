<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Filament\Resources\Teachers\RelationManagers\EducationsRelationManager;
use App\Filament\Resources\Teachers\RelationManagers\TrainingsRelationManager;
use App\Filament\Resources\Teachers\RelationManagers\WorkExperiencesRelationManager;
use App\Models\Teacher;
use App\Models\TeacherWorkExperience;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeacherQualificationRelationManagersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cloudinary.cloud_name' => 'demo-cloud',
            'cloudinary.api_key' => 'demo-key',
            'cloudinary.api_secret' => 'demo-secret',
        ]);
    }

    public function test_admin_can_add_work_experience_to_a_teacher(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $teacher = Teacher::factory()->create();

        Livewire::test(WorkExperiencesRelationManager::class, ['ownerRecord' => $teacher, 'pageClass' => EditTeacher::class])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'organization' => 'Ministry of Education',
                'position' => 'Expert',
                'start_date' => '2020-01-01',
                'end_date' => '2022-01-01',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('teacher_work_experiences', [
            'teacher_id' => $teacher->id,
            'organization' => 'Ministry of Education',
        ]);
    }

    public function test_work_experience_form_rejects_an_end_date_before_the_start_date(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $teacher = Teacher::factory()->create();

        Livewire::test(WorkExperiencesRelationManager::class, ['ownerRecord' => $teacher, 'pageClass' => EditTeacher::class])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'organization' => 'Ministry of Education',
                'position' => 'Expert',
                'start_date' => '2022-01-01',
                'end_date' => '2020-01-01',
            ])
            ->assertHasFormErrors(['end_date']);

        $this->assertDatabaseCount('teacher_work_experiences', 0);
    }

    public function test_admin_can_edit_and_delete_a_work_experience(): void
    {
        $this->actingAs(User::factory()->collegeAdmin()->create());
        $teacher = Teacher::factory()->create();
        $workExperience = TeacherWorkExperience::factory()->for($teacher)->create();

        $component = Livewire::test(WorkExperiencesRelationManager::class, ['ownerRecord' => $teacher, 'pageClass' => EditTeacher::class])
            ->callAction(TestAction::make(EditAction::class)->table($workExperience), ['position' => 'Director'])
            ->assertHasNoFormErrors();

        $this->assertSame('Director', $workExperience->fresh()->position);

        $component->callAction(TestAction::make(DeleteAction::class)->table($workExperience));

        $this->assertModelMissing($workExperience);
    }

    public function test_admin_can_add_education_to_a_teacher(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $teacher = Teacher::factory()->create();

        Livewire::test(EducationsRelationManager::class, ['ownerRecord' => $teacher, 'pageClass' => EditTeacher::class])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'institution' => 'Georgian Technical University',
                'degree' => 'Bachelor',
                'specialization' => 'Computer Science',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('teacher_educations', [
            'teacher_id' => $teacher->id,
            'institution' => 'Georgian Technical University',
        ]);
    }

    public function test_admin_can_add_a_training_with_a_certificate_uploaded_to_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teacher-certificates/abc123.pdf',
            ]),
        ]);

        $this->actingAs(User::factory()->superAdmin()->create());
        $teacher = Teacher::factory()->create();

        Livewire::test(TrainingsRelationManager::class, ['ownerRecord' => $teacher, 'pageClass' => EditTeacher::class])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'title' => 'Inclusive Education',
                'organizer' => 'Private Training Center',
                'certificate_url' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('teacher_trainings', [
            'teacher_id' => $teacher->id,
            'title' => 'Inclusive Education',
            'certificate_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teacher-certificates/abc123.pdf',
        ]);
    }

    /**
     * @return array<string, array{class-string}>
     */
    public static function relationManagers(): array
    {
        return [
            'work experiences' => [WorkExperiencesRelationManager::class],
            'educations' => [EducationsRelationManager::class],
            'trainings' => [TrainingsRelationManager::class],
        ];
    }

    #[DataProvider('relationManagers')]
    public function test_relation_manager_is_hidden_from_a_user_without_an_admin_role(string $relationManager): void
    {
        $teacher = Teacher::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->assertFalse($relationManager::canViewForRecord($teacher, EditTeacher::class));
    }
}
