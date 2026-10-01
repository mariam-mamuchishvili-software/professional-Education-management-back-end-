<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Colleges\CollegeResource;
use App\Filament\Resources\Colleges\Pages\ListColleges;
use App\Filament\Resources\Groups\GroupResource;
use App\Filament\Resources\Groups\Pages\EditGroup;
use App\Filament\Resources\Groups\Pages\ListGroups;
use App\Filament\Resources\Groups\Pages\ViewGroup;
use App\Filament\Resources\Groups\RelationManagers\StudentsRelationManager as GroupStudentsRelationManager;
use App\Filament\Resources\Modules\ModuleResource;
use App\Filament\Resources\Modules\Pages\EditModule;
use App\Filament\Resources\Modules\Pages\ListModules;
use App\Filament\Resources\Modules\RelationManagers\StudentsRelationManager as ModuleStudentsRelationManager;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Teachers\TeacherResource;
use App\Filament\Widgets\LatestStudents;
use App\Models\College;
use App\Models\Group;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAndPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seeder_creates_each_role_once_when_run_repeatedly(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertEqualsCanonicalizing(
            ['super_admin', 'college_admin', 'teacher'],
            Role::query()->pluck('name')->all(),
        );
    }

    public function test_role_seeder_does_not_assign_roles_to_existing_users(): void
    {
        $user = User::factory()->create();

        $this->seed(RoleSeeder::class);

        $this->assertCount(0, $user->fresh()->roles);
    }

    public function test_user_without_a_role_cannot_access_the_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function roles(): array
    {
        return [
            'super admin' => [UserRole::SuperAdmin],
            'college admin' => [UserRole::CollegeAdmin],
            'teacher' => [UserRole::Teacher],
        ];
    }

    #[DataProvider('roles')]
    public function test_users_with_a_role_can_access_the_panel(UserRole $role): void
    {
        $this->actingAs(User::factory()->withRole($role)->create())
            ->get('/admin')
            ->assertOk();
    }

    public function test_teacher_sees_only_their_own_modules(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $ownModule = Module::factory()->hasAttached($teacher)->create();
        $otherModule = Module::factory()->hasAttached(Teacher::factory())->create();

        $this->actingAs($user);

        Livewire::test(ListModules::class)
            ->assertCanSeeTableRecords([$ownModule])
            ->assertCanNotSeeTableRecords([$otherModule]);
    }

    public function test_teacher_sees_only_their_own_colleges(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $ownCollege = College::factory()->hasAttached($teacher)->create();
        $otherCollege = College::factory()->hasAttached(Teacher::factory())->create();

        $this->actingAs($user);

        Livewire::test(ListColleges::class)
            ->assertCanSeeTableRecords([$ownCollege])
            ->assertCanNotSeeTableRecords([$otherCollege]);
    }

    public function test_teacher_sees_only_groups_of_professions_containing_their_modules(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $ownGroup = $this->groupTaughtBy($teacher);
        $otherGroup = $this->groupTaughtBy(Teacher::factory()->create());

        $this->actingAs($user);

        Livewire::test(ListGroups::class)
            ->assertCanSeeTableRecords([$ownGroup])
            ->assertCanNotSeeTableRecords([$otherGroup]);
    }

    public function test_teacher_role_without_a_linked_teacher_profile_sees_no_modules(): void
    {
        $module = Module::factory()->hasAttached(Teacher::factory())->create();

        $this->actingAs(User::factory()->teacher()->create());

        Livewire::test(ListModules::class)
            ->assertCanNotSeeTableRecords([$module])
            ->assertCountTableRecords(0);
    }

    public function test_college_admin_sees_all_modules(): void
    {
        $modules = Module::factory()->count(2)->hasAttached(Teacher::factory())->create();

        $this->actingAs(User::factory()->collegeAdmin()->create());

        Livewire::test(ListModules::class)
            ->assertCanSeeTableRecords($modules);
    }

    public function test_teacher_can_open_their_own_module_and_college(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $module = Module::factory()->hasAttached($teacher)->create();
        $college = College::factory()->hasAttached($teacher)->create();

        $this->actingAs($user);

        $this->get(ModuleResource::getUrl('view', ['record' => $module]))->assertOk();
        $this->get(CollegeResource::getUrl('view', ['record' => $college]))->assertOk();
    }

    public function test_teacher_cannot_open_another_teachers_module_college_or_group(): void
    {
        [$user] = $this->teacherUser();
        $otherTeacher = Teacher::factory()->create();
        $module = Module::factory()->hasAttached($otherTeacher)->create();
        $college = College::factory()->hasAttached($otherTeacher)->create();
        $group = $this->groupTaughtBy($otherTeacher);

        $this->actingAs($user);

        $this->get(ModuleResource::getUrl('view', ['record' => $module]))->assertNotFound();
        $this->get(CollegeResource::getUrl('view', ['record' => $college]))->assertNotFound();
        $this->get(GroupResource::getUrl('view', ['record' => $group]))->assertNotFound();
    }

    public function test_teacher_cannot_edit_their_own_module(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $module = Module::factory()->hasAttached($teacher)->create();

        $this->actingAs($user)
            ->get(ModuleResource::getUrl('edit', ['record' => $module]))
            ->assertForbidden();
    }

    public function test_teacher_cannot_open_the_global_student_list(): void
    {
        [$user] = $this->teacherUser();

        $this->actingAs($user)
            ->get(StudentResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_teacher_cannot_open_the_teacher_list(): void
    {
        [$user] = $this->teacherUser();

        $this->actingAs($user)
            ->get(TeacherResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_teacher_does_not_see_institution_wide_dashboard_widgets(): void
    {
        [$user] = $this->teacherUser();

        $this->actingAs($user);

        $this->assertFalse(LatestStudents::canView());
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function studentAbilities(): array
    {
        return [
            'super admin viewAny' => ['super_admin', 'viewAny', true],
            'super admin create' => ['super_admin', 'create', true],
            'super admin update' => ['super_admin', 'update', true],
            'super admin delete' => ['super_admin', 'delete', true],
            'super admin attach' => ['super_admin', 'attach', true],
            'super admin detach' => ['super_admin', 'detach', true],
            'college admin viewAny' => ['college_admin', 'viewAny', true],
            'college admin create' => ['college_admin', 'create', true],
            'college admin update' => ['college_admin', 'update', true],
            'college admin delete' => ['college_admin', 'delete', true],
            'college admin attach' => ['college_admin', 'attach', true],
            'college admin detach' => ['college_admin', 'detach', true],
            'teacher viewAny' => ['teacher', 'viewAny', false],
            'teacher create' => ['teacher', 'create', false],
            'teacher update' => ['teacher', 'update', false],
            'teacher delete' => ['teacher', 'delete', false],
            'teacher attach' => ['teacher', 'attach', false],
            'teacher detach' => ['teacher', 'detach', false],
        ];
    }

    #[DataProvider('studentAbilities')]
    public function test_student_policy_grants_abilities_by_role(string $role, string $ability, bool $expected): void
    {
        $user = User::factory()->withRole(UserRole::from($role))->create();
        $argument = in_array($ability, ['viewAny', 'create', 'attach'], true) ? Student::class : Student::factory()->create();

        $this->assertSame($expected, $user->can($ability, $argument));
    }

    public function test_teacher_sees_students_of_their_module_as_a_read_only_list(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $module = Module::factory()->hasAttached($teacher)->create();
        $student = Student::factory()->create();
        $module->students()->attach($student);

        $this->actingAs($user);

        Livewire::test(ModuleStudentsRelationManager::class, [
            'ownerRecord' => $module,
            'pageClass' => EditModule::class,
        ])
            ->assertCanSeeTableRecords([$student])
            ->assertTableActionHidden('attach')
            ->assertTableActionHidden('detach', $student)
            ->assertTableBulkActionHidden('detach');
    }

    public function test_students_relation_manager_is_visible_to_the_teacher_only_for_their_own_records(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $ownGroup = $this->groupTaughtBy($teacher);
        $otherGroup = $this->groupTaughtBy(Teacher::factory()->create());

        $this->actingAs($user);

        $this->assertTrue(GroupStudentsRelationManager::canViewForRecord($ownGroup, ViewGroup::class));
        $this->assertFalse(GroupStudentsRelationManager::canViewForRecord($otherGroup, ViewGroup::class));
    }

    public function test_teacher_cannot_detach_a_student_from_their_group(): void
    {
        [$user, $teacher] = $this->teacherUser();
        $group = $this->groupTaughtBy($teacher);
        $student = Student::factory()->create();
        $group->students()->attach($student);

        $this->actingAs($user);

        Livewire::test(GroupStudentsRelationManager::class, [
            'ownerRecord' => $group,
            'pageClass' => EditGroup::class,
        ])
            ->call('mountAction', 'detach', [], ['table' => true, 'recordKey' => (string) $student->getKey()])
            ->call('callMountedAction');

        $this->assertTrue($group->students()->whereKey($student->id)->exists());
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

    private function groupTaughtBy(Teacher $teacher): Group
    {
        $module = Module::factory()->hasAttached($teacher)->create();
        $profession = Profession::factory()->hasAttached($module)->create();

        return Group::factory()->for($profession)->create();
    }
}
