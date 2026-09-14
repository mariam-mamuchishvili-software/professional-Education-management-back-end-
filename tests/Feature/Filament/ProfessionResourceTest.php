<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Professions\Pages\CreateProfession;
use App\Filament\Resources\Professions\Pages\EditProfession;
use App\Filament\Resources\Professions\Pages\ListProfessions;
use App\Models\Module;
use App\Models\Profession;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfessionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_list_page_displays_professions(): void
    {
        $professions = Profession::factory()->count(3)->create();

        Livewire::test(ListProfessions::class)
            ->assertCanSeeTableRecords($professions);
    }

    public function test_can_create_a_profession_with_modules(): void
    {
        $modules = Module::factory()->count(2)->create();

        Livewire::test(CreateProfession::class)
            ->fillForm([
                'name' => 'Software Engineering',
                'code' => 'PRF-100',
                'description' => 'A software engineering profession.',
                'duration' => 3,
                'qualification' => 'Bachelor',
                'modules' => $modules->pluck('id')->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $profession = Profession::where('code', 'PRF-100')->firstOrFail();

        $this->assertCount(2, $profession->modules);
    }

    public function test_create_requires_name_code_duration_and_qualification(): void
    {
        Livewire::test(CreateProfession::class)
            ->fillForm([
                'name' => '',
                'code' => '',
                'duration' => '',
                'qualification' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'code' => 'required',
                'duration' => 'required',
                'qualification' => 'required',
            ]);
    }

    public function test_create_rejects_a_duplicate_code(): void
    {
        Profession::factory()->create(['code' => 'PRF-999']);

        Livewire::test(CreateProfession::class)
            ->fillForm([
                'name' => 'Nursing',
                'code' => 'PRF-999',
                'duration' => 2,
                'qualification' => 'Diploma',
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_can_update_a_profession(): void
    {
        $profession = Profession::factory()->create();

        Livewire::test(EditProfession::class, ['record' => $profession->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Profession Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('professions', [
            'id' => $profession->id,
            'name' => 'Updated Profession Name',
        ]);
    }

    public function test_can_delete_a_profession(): void
    {
        $profession = Profession::factory()->create();

        Livewire::test(EditProfession::class, ['record' => $profession->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($profession);
    }
}
