<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Teachers\RelationManagers\EducationsRelationManager;
use App\Filament\Teacher\RelationManagers\StudentsRelationManager;
use App\Filament\Teacher\Resources\Colleges\Pages\ListColleges;
use App\Filament\Teacher\Resources\Groups\GroupResource;
use App\Filament\Teacher\Resources\Groups\Pages\ListGroups;
use App\Filament\Teacher\Resources\Groups\Pages\ViewGroup;
use App\Filament\Teacher\Resources\Modules\ModuleResource;
use App\Filament\Teacher\Resources\Modules\Pages\ListModules;
use App\Filament\Teacher\Resources\Profile\Pages\EditProfile;
use App\Models\College;
use App\Models\Group;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherEducation;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherCabinetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('teacher'));
    }

    public function test_teacher_signs_in_to_the_cabinet_with_their_own_email_and_password(): void
    {
        $teacher = Teacher::factory()->create(['email' => 'nino@example.com']);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'nino@example.com', 'password' => 'password'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($teacher, 'teacher');
        $this->assertGuest('web');
    }

    public function test_user_account_credentials_do_not_sign_in_to_the_cabinet(): void
    {
        User::factory()->superAdmin()->create(['email' => 'admin@example.com']);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@example.com', 'password' => 'password'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest('teacher');
    }

    public function test_teacher_without_a_password_cannot_sign_in(): void
    {
        Teacher::factory()->create(['email' => 'nino@example.com', 'password' => null]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'nino@example.com', 'password' => ''])
            ->call('authenticate')
            ->assertHasFormErrors(['password']);

        $this->assertGuest('teacher');
    }

    public function test_guest_is_redirected_to_the_cabinet_login(): void
    {
        $this->get('/teacher')->assertRedirect('/teacher/login');
    }

    public function test_signed_in_teacher_can_open_the_cabinet_but_not_the_admin_panel(): void
    {
        $this->actingAs(Teacher::factory()->create(), 'teacher');

        $this->get('/teacher')->assertOk();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_teacher_sees_only_their_own_modules_colleges_and_groups(): void
    {
        $teacher = $this->signIn();
        $otherTeacher = Teacher::factory()->create();

        $ownModule = Module::factory()->hasAttached($teacher)->create();
        $otherModule = Module::factory()->hasAttached($otherTeacher)->create();
        $ownCollege = College::factory()->hasAttached($teacher)->create();
        $otherCollege = College::factory()->hasAttached($otherTeacher)->create();
        $ownGroup = $this->groupTaughtBy($teacher);
        $otherGroup = $this->groupTaughtBy($otherTeacher);

        Livewire::test(ListModules::class)
            ->assertCanSeeTableRecords([$ownModule])
            ->assertCanNotSeeTableRecords([$otherModule]);

        Livewire::test(ListColleges::class)
            ->assertCanSeeTableRecords([$ownCollege])
            ->assertCanNotSeeTableRecords([$otherCollege]);

        Livewire::test(ListGroups::class)
            ->assertCanSeeTableRecords([$ownGroup])
            ->assertCanNotSeeTableRecords([$otherGroup]);
    }

    public function test_teacher_can_open_their_own_module_and_group_but_not_another_teachers(): void
    {
        $teacher = $this->signIn();
        $otherTeacher = Teacher::factory()->create();

        $this->get(ModuleResource::getUrl('view', ['record' => Module::factory()->hasAttached($teacher)->create()], panel: 'teacher'))->assertOk();
        $this->get(GroupResource::getUrl('view', ['record' => $this->groupTaughtBy($teacher)], panel: 'teacher'))->assertOk();

        $this->get(ModuleResource::getUrl('view', ['record' => Module::factory()->hasAttached($otherTeacher)->create()], panel: 'teacher'))->assertNotFound();
        $this->get(GroupResource::getUrl('view', ['record' => $this->groupTaughtBy($otherTeacher)], panel: 'teacher'))->assertNotFound();
    }

    public function test_teacher_sees_the_students_of_their_group_as_a_read_only_list(): void
    {
        $group = $this->groupTaughtBy($this->signIn());
        $student = Student::factory()->create();
        $group->students()->attach($student);

        Livewire::test(StudentsRelationManager::class, ['ownerRecord' => $group, 'pageClass' => ViewGroup::class])
            ->assertCanSeeTableRecords([$student])
            ->assertTableActionDoesNotExist('attach')
            ->assertTableActionDoesNotExist('detach', record: $student);
    }

    public function test_teacher_updates_their_own_profile_and_password(): void
    {
        $teacher = $this->signIn();

        Livewire::test(EditProfile::class)
            ->assertSchemaStateSet(['email' => $teacher->email, 'password' => null])
            ->fillForm([
                'specialization' => 'Physics',
                'password' => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $teacher->refresh();

        $this->assertSame('Physics', $teacher->specialization);
        $this->assertTrue(Hash::check('new-secret-password', $teacher->password));
    }

    public function test_profile_keeps_the_password_when_left_empty_and_requires_a_matching_confirmation(): void
    {
        $teacher = $this->signIn();

        Livewire::test(EditProfile::class)
            ->fillForm(['specialization' => 'Physics'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('password', $teacher->fresh()->password));

        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'new-secret-password', 'password_confirmation' => 'something-else'])
            ->call('save')
            ->assertHasFormErrors(['password' => 'confirmed']);

        $this->assertTrue(Hash::check('password', $teacher->fresh()->password));
    }

    public function test_teacher_adds_education_to_their_own_profile(): void
    {
        $teacher = $this->signIn();

        Livewire::test(EducationsRelationManager::class, ['ownerRecord' => $teacher, 'pageClass' => EditProfile::class])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'institution' => 'Tbilisi State University',
                'degree' => 'Master',
                'specialization' => 'Applied Mathematics',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('teacher_educations', [
            'teacher_id' => $teacher->id,
            'institution' => 'Tbilisi State University',
        ]);
    }

    public function test_teacher_cannot_edit_another_teachers_education(): void
    {
        $this->signIn();
        $otherTeacher = Teacher::factory()->create();
        $education = TeacherEducation::factory()->for($otherTeacher)->create();

        Livewire::test(EducationsRelationManager::class, ['ownerRecord' => $otherTeacher, 'pageClass' => EditProfile::class])
            ->assertActionHidden(TestAction::make(EditAction::class)->table($education));
    }

    private function signIn(): Teacher
    {
        $teacher = Teacher::factory()->create();

        $this->actingAs($teacher, 'teacher');

        return $teacher;
    }

    private function groupTaughtBy(Teacher $teacher): Group
    {
        $module = Module::factory()->hasAttached($teacher)->create();
        $profession = Profession::factory()->hasAttached($module)->create();

        return Group::factory()->for($profession)->create();
    }
}
