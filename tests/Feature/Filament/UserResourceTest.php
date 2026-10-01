<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($this->superAdmin);
    }

    public function test_list_page_displays_users(): void
    {
        $users = User::factory()->count(2)->teacher()->create();

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords($users);
    }

    public function test_can_create_a_user_with_a_role(): void
    {
        $teacherRole = Role::findOrCreate(UserRole::Teacher->value, 'web');

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Nino Teacher',
                'email' => 'nino@example.com',
                'password' => 'secret-password',
                'roles' => [$teacherRole->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'nino@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole(UserRole::Teacher));
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }

    public function test_create_requires_name_email_and_password(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => '', 'email' => '', 'password' => ''])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'email' => 'required', 'password' => 'required']);
    }

    public function test_saving_without_a_password_keeps_the_current_one(): void
    {
        $user = User::factory()->create();
        $originalHash = $user->password;

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['name' => 'Renamed', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Renamed', $user->fresh()->name);
        $this->assertSame($originalHash, $user->fresh()->password);
    }

    public function test_super_admin_cannot_change_their_own_roles(): void
    {
        Livewire::test(EditUser::class, ['record' => $this->superAdmin->getRouteKey()])
            ->assertFormFieldDisabled('roles')
            ->fillForm(['roles' => []])
            ->call('save');

        $this->assertTrue($this->superAdmin->fresh()->hasRole(UserRole::SuperAdmin));
    }

    public function test_super_admin_cannot_delete_their_own_account(): void
    {
        Livewire::test(EditUser::class, ['record' => $this->superAdmin->getRouteKey()])
            ->assertActionHidden(DeleteAction::class);
    }

    public function test_super_admin_can_delete_another_user(): void
    {
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($user);
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function nonSuperAdminRoles(): array
    {
        return [
            'college admin' => [UserRole::CollegeAdmin],
            'teacher' => [UserRole::Teacher],
        ];
    }

    #[DataProvider('nonSuperAdminRoles')]
    public function test_only_super_admins_can_manage_users(UserRole $role): void
    {
        $this->actingAs(User::factory()->withRole($role)->create())
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();
    }
}
