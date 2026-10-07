<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Modules\Pages\ListModules;
use App\Models\Module;
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
            ['super_admin', 'college_admin'],
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
        ];
    }

    #[DataProvider('roles')]
    public function test_users_with_a_role_can_access_the_panel(UserRole $role): void
    {
        $this->actingAs(User::factory()->withRole($role)->create())
            ->get('/admin')
            ->assertOk();
    }

    public function test_user_with_a_role_cannot_access_the_teacher_cabinet(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/teacher')
            ->assertRedirect('/teacher/login');
    }

    public function test_college_admin_sees_all_modules(): void
    {
        $modules = Module::factory()->count(2)->hasAttached(Teacher::factory())->create();

        $this->actingAs(User::factory()->collegeAdmin()->create());

        Livewire::test(ListModules::class)
            ->assertCanSeeTableRecords($modules);
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
        ];
    }

    #[DataProvider('studentAbilities')]
    public function test_student_policy_grants_abilities_by_role(string $role, string $ability, bool $expected): void
    {
        $user = User::factory()->withRole(UserRole::from($role))->create();
        $argument = in_array($ability, ['viewAny', 'create', 'attach'], true) ? Student::class : Student::factory()->create();

        $this->assertSame($expected, $user->can($ability, $argument));
    }
}
