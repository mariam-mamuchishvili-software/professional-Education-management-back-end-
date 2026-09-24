<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\RelationManagers\CollegesRelationManager;
use App\Models\College;
use App\Models\Group;
use App\Models\Module;
use App\Models\Student;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class StudentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());

        config([
            'cloudinary.cloud_name' => 'demo-cloud',
            'cloudinary.api_key' => 'demo-key',
            'cloudinary.api_secret' => 'demo-secret',
        ]);
    }

    public function test_list_page_displays_students(): void
    {
        $students = Student::factory()->count(3)->create();

        Livewire::test(ListStudents::class)
            ->assertCanSeeTableRecords($students);
    }

    public function test_can_create_a_student_with_groups_modules_and_colleges(): void
    {
        $groups = Group::factory()->count(2)->create();
        $modules = Module::factory()->count(2)->create();
        $colleges = College::factory()->count(2)->create();

        Livewire::test(CreateStudent::class)
            ->fillForm([
                'first_name' => 'Ana',
                'last_name' => 'Lomidze',
                'email' => 'ana@example.com',
                'phone' => '+995 555 777 888',
                'birth_date' => '2005-04-12',
                'groups' => $groups->pluck('id')->all(),
                'modules' => $modules->pluck('id')->all(),
                'colleges' => $colleges->pluck('id')->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $student = Student::where('email', 'ana@example.com')->firstOrFail();

        $this->assertCount(2, $student->groups);
        $this->assertCount(2, $student->modules);
        $this->assertCount(2, $student->colleges);
    }

    public function test_colleges_relation_manager_can_attach_a_college(): void
    {
        $student = Student::factory()->create();
        $college = College::factory()->create();

        Livewire::test(CollegesRelationManager::class, [
            'ownerRecord' => $student,
            'pageClass' => EditStudent::class,
        ])
            ->callTableAction(AttachAction::class, data: ['recordId' => $college->id])
            ->assertHasNoTableActionErrors()
            ->assertCanSeeTableRecords([$college]);

        $this->assertTrue($student->colleges()->whereKey($college->id)->exists());
    }

    public function test_create_requires_first_name_last_name_email_phone_and_birth_date(): void
    {
        Livewire::test(CreateStudent::class)
            ->fillForm([
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'birth_date' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'birth_date' => 'required',
            ]);
    }

    public function test_create_rejects_a_duplicate_email(): void
    {
        Student::factory()->create(['email' => 'existing@example.com']);

        Livewire::test(CreateStudent::class)
            ->fillForm([
                'first_name' => 'Levan',
                'last_name' => 'Tsiklauri',
                'email' => 'existing@example.com',
                'phone' => '+995 555 222 333',
                'birth_date' => '2004-01-01',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    }

    public function test_can_update_a_student(): void
    {
        $student = Student::factory()->create();

        Livewire::test(EditStudent::class, ['record' => $student->getRouteKey()])
            ->fillForm([
                'first_name' => 'Updated',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Updated',
        ]);
    }

    public function test_can_delete_a_student(): void
    {
        $student = Student::factory()->create();

        Livewire::test(EditStudent::class, ['record' => $student->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($student);
    }

    public function test_can_create_a_student_with_a_profile_image_uploaded_to_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/students/abc123.jpg',
                'public_id' => 'eduhub/students/abc123',
            ]),
        ]);

        Livewire::test(CreateStudent::class)
            ->fillForm([
                'first_name' => 'Ana',
                'last_name' => 'Lomidze',
                'email' => 'ana-image@example.com',
                'phone' => '+995 555 777 888',
                'birth_date' => '2005-04-12',
                'image' => UploadedFile::fake()->image('student.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('students', [
            'email' => 'ana-image@example.com',
            'image' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/students/abc123.jpg',
        ]);
    }

    public function test_edit_form_loads_existing_student_image_without_error(): void
    {
        $student = Student::factory()->create([
            'image' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/students/original.jpg',
        ]);

        Livewire::test(EditStudent::class, ['record' => $student->getRouteKey()])
            ->assertFormSet([
                'image' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/students/original.jpg',
            ]);
    }
}
