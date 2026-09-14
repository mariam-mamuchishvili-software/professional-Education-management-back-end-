<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Colleges\Pages\CreateCollege;
use App\Filament\Resources\Colleges\Pages\EditCollege;
use App\Filament\Resources\Colleges\Pages\ListColleges;
use App\Models\College;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollegeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_list_page_displays_colleges(): void
    {
        $colleges = College::factory()->count(3)->create();

        Livewire::test(ListColleges::class)
            ->assertCanSeeTableRecords($colleges);
    }

    public function test_can_create_a_college(): void
    {
        Livewire::test(CreateCollege::class)
            ->fillForm([
                'name' => 'Tbilisi Technical College',
                'address' => 'Tbilisi, Georgia',
                'email' => 'college@example.com',
                'phone' => '+995 555 123 456',
                'website' => 'https://college.ge',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('colleges', [
            'email' => 'college@example.com',
        ]);
    }

    public function test_create_requires_name_address_email_and_phone(): void
    {
        Livewire::test(CreateCollege::class)
            ->fillForm([
                'name' => '',
                'address' => '',
                'email' => '',
                'phone' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'address' => 'required',
                'email' => 'required',
                'phone' => 'required',
            ]);
    }

    public function test_create_rejects_a_duplicate_email(): void
    {
        College::factory()->create(['email' => 'existing@example.com']);

        Livewire::test(CreateCollege::class)
            ->fillForm([
                'name' => 'Another College',
                'address' => 'Batumi, Georgia',
                'email' => 'existing@example.com',
                'phone' => '+995 555 999 999',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    }

    public function test_can_update_a_college(): void
    {
        $college = College::factory()->create();

        Livewire::test(EditCollege::class, ['record' => $college->getRouteKey()])
            ->fillForm([
                'name' => 'Updated College Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('colleges', [
            'id' => $college->id,
            'name' => 'Updated College Name',
        ]);
    }

    public function test_can_delete_a_college(): void
    {
        $college = College::factory()->create();

        Livewire::test(EditCollege::class, ['record' => $college->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($college);
    }
}
