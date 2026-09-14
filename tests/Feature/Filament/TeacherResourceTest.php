<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Filament\Resources\Teachers\Pages\ListTeachers;
use App\Models\College;
use App\Models\Module;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_list_page_displays_teachers(): void
    {
        $teachers = Teacher::factory()->count(3)->create();

        Livewire::test(ListTeachers::class)
            ->assertCanSeeTableRecords($teachers);
    }

    public function test_can_create_a_teacher_with_colleges_and_modules(): void
    {
        $colleges = College::factory()->count(2)->create();
        $modules = Module::factory()->count(2)->create();

        Livewire::test(CreateTeacher::class)
            ->fillForm([
                'first_name' => 'Nino',
                'last_name' => 'Kapanadze',
                'email' => 'nino@example.com',
                'phone' => '+995 555 111 222',
                'specialization' => 'Mathematics',
                'colleges' => $colleges->pluck('id')->all(),
                'modules' => $modules->pluck('id')->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $teacher = Teacher::where('email', 'nino@example.com')->firstOrFail();

        $this->assertCount(2, $teacher->colleges);
        $this->assertCount(2, $teacher->modules);
    }

    public function test_create_requires_first_name_last_name_email_phone_and_specialization(): void
    {
        Livewire::test(CreateTeacher::class)
            ->fillForm([
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'specialization' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'specialization' => 'required',
            ]);
    }

    public function test_create_rejects_a_duplicate_email(): void
    {
        Teacher::factory()->create(['email' => 'existing@example.com']);

        Livewire::test(CreateTeacher::class)
            ->fillForm([
                'first_name' => 'Giorgi',
                'last_name' => 'Beridze',
                'email' => 'existing@example.com',
                'phone' => '+995 555 333 444',
                'specialization' => 'Physics',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    }

    public function test_can_update_a_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        Livewire::test(EditTeacher::class, ['record' => $teacher->getRouteKey()])
            ->fillForm([
                'specialization' => 'Updated Specialization',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'specialization' => 'Updated Specialization',
        ]);
    }

    public function test_can_delete_a_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        Livewire::test(EditTeacher::class, ['record' => $teacher->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($teacher);
    }
}
