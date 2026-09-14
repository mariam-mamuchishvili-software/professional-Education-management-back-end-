<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Groups\Pages\CreateGroup;
use App\Filament\Resources\Groups\Pages\EditGroup;
use App\Filament\Resources\Groups\Pages\ListGroups;
use App\Models\Group;
use App\Models\Profession;
use App\Models\Student;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GroupResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_list_page_displays_groups(): void
    {
        $groups = Group::factory()->count(3)->create();

        Livewire::test(ListGroups::class)
            ->assertCanSeeTableRecords($groups);
    }

    public function test_can_create_a_group_with_a_profession_and_students(): void
    {
        $profession = Profession::factory()->create();
        $students = Student::factory()->count(2)->create();

        Livewire::test(CreateGroup::class)
            ->fillForm([
                'profession_id' => $profession->id,
                'name' => 'Group A1',
                'code' => 'GRP-100',
                'capacity' => 25,
                'study_shift' => 'morning',
                'students' => $students->pluck('id')->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $group = Group::where('code', 'GRP-100')->firstOrFail();

        $this->assertSame($profession->id, $group->profession_id);
        $this->assertCount(2, $group->students);
    }

    public function test_create_requires_profession_name_code_capacity_and_study_shift(): void
    {
        Livewire::test(CreateGroup::class)
            ->fillForm([
                'profession_id' => '',
                'name' => '',
                'code' => '',
                'capacity' => '',
                'study_shift' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'profession_id' => 'required',
                'name' => 'required',
                'code' => 'required',
                'capacity' => 'required',
                'study_shift' => 'required',
            ]);
    }

    public function test_create_rejects_a_duplicate_code(): void
    {
        Group::factory()->create(['code' => 'GRP-999']);
        $profession = Profession::factory()->create();

        Livewire::test(CreateGroup::class)
            ->fillForm([
                'profession_id' => $profession->id,
                'name' => 'Group B1',
                'code' => 'GRP-999',
                'capacity' => 20,
                'study_shift' => 'evening',
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    public function test_can_update_a_group(): void
    {
        $group = Group::factory()->create();

        Livewire::test(EditGroup::class, ['record' => $group->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Group Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Updated Group Name',
        ]);
    }

    public function test_can_delete_a_group(): void
    {
        $group = Group::factory()->create();

        Livewire::test(EditGroup::class, ['record' => $group->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($group);
    }
}
