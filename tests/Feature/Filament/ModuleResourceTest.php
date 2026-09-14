<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Modules\Pages\CreateModule;
use App\Filament\Resources\Modules\Pages\EditModule;
use App\Filament\Resources\Modules\Pages\ListModules;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ModuleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_list_page_displays_modules(): void
    {
        $modules = Module::factory()->count(3)->create();

        Livewire::test(ListModules::class)
            ->assertCanSeeTableRecords($modules);
    }

    public function test_can_create_a_module_with_relationships(): void
    {
        $teachers = Teacher::factory()->count(2)->create();
        $professions = Profession::factory()->count(2)->create();
        $students = Student::factory()->count(2)->create();

        Livewire::test(CreateModule::class)
            ->fillForm([
                'name' => 'Database Systems',
                'code' => 'MOD-100',
                'description' => 'Introduction to databases.',
                'duration' => 40,
                'credits' => 5,
                'teachers' => $teachers->pluck('id')->all(),
                'professions' => $professions->pluck('id')->all(),
                'students' => $students->pluck('id')->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $module = Module::where('code', 'MOD-100')->firstOrFail();

        $this->assertCount(2, $module->teachers);
        $this->assertCount(2, $module->professions);
        $this->assertCount(2, $module->students);
    }

    public function test_create_requires_name_code_duration_and_credits(): void
    {
        Livewire::test(CreateModule::class)
            ->fillForm([
                'name' => '',
                'code' => '',
                'duration' => '',
                'credits' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'code' => 'required',
                'duration' => 'required',
                'credits' => 'required',
            ]);
    }

    public function test_create_rejects_a_duplicate_code(): void
    {
        Module::factory()->create(['code' => 'MOD-999']);

        Livewire::test(CreateModule::class)
            ->fillForm([
                'name' => 'Networking',
                'code' => 'MOD-999',
                'duration' => 30,
                'credits' => 4,
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_can_update_a_module(): void
    {
        $module = Module::factory()->create();

        Livewire::test(EditModule::class, ['record' => $module->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Module Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'name' => 'Updated Module Name',
        ]);
    }

    public function test_can_delete_a_module(): void
    {
        $module = Module::factory()->create();

        Livewire::test(EditModule::class, ['record' => $module->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($module);
    }
}
