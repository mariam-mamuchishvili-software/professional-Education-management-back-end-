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
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->superAdmin()->create());

        config([
            'cloudinary.cloud_name' => 'demo-cloud',
            'cloudinary.api_key' => 'demo-key',
            'cloudinary.api_secret' => 'demo-secret',
        ]);
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
                'password' => 'secret-password',
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

    public function test_create_requires_first_name_last_name_email_password_phone_and_specialization(): void
    {
        Livewire::test(CreateTeacher::class)
            ->fillForm([
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'password' => '',
                'phone' => '',
                'specialization' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                'password' => 'required',
                'phone' => 'required',
                'specialization' => 'required',
            ]);
    }

    public function test_create_generates_a_passphrase_and_verifies_the_email_automatically(): void
    {
        $this->freezeSecond();

        $component = Livewire::test(CreateTeacher::class);
        $passphrase = $component->get('data.password');

        $this->assertMatchesRegularExpression('/^([A-Z][a-z]+-){4}\d{2}$/', $passphrase);

        $component
            ->fillForm([
                'first_name' => 'Nino',
                'last_name' => 'Kapanadze',
                'email' => 'nino@example.com',
                'phone' => '+995 555 111 222',
                'specialization' => 'Mathematics',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $teacher = Teacher::where('email', 'nino@example.com')->firstOrFail();

        $this->assertTrue(Hash::check($passphrase, $teacher->password));
        $this->assertTrue($teacher->email_verified_at->equalTo(now()));
    }

    public function test_generate_action_replaces_the_password_with_a_new_passphrase(): void
    {
        $teacher = Teacher::factory()->create();

        $component = Livewire::test(EditTeacher::class, ['record' => $teacher->getRouteKey()])
            ->assertSchemaStateSet(['password' => null])
            ->callAction(TestAction::make('generatePassphrase')->schemaComponent('password'));

        $passphrase = $component->get('data.password');

        $this->assertMatchesRegularExpression('/^([A-Z][a-z]+-){4}\d{2}$/', $passphrase);

        $component->call('save')->assertHasNoFormErrors();

        $this->assertTrue(Hash::check($passphrase, $teacher->fresh()->password));
    }

    public function test_edit_keeps_the_password_when_left_empty_and_changes_it_when_filled(): void
    {
        $teacher = Teacher::factory()->create();

        Livewire::test(EditTeacher::class, ['record' => $teacher->getRouteKey()])
            ->assertSchemaStateSet(['password' => null])
            ->fillForm(['password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('password', $teacher->fresh()->password));

        Livewire::test(EditTeacher::class, ['record' => $teacher->getRouteKey()])
            ->fillForm(['password' => 'new-secret-password'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('new-secret-password', $teacher->fresh()->password));
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

    public function test_can_create_a_teacher_with_a_profile_image_uploaded_to_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teachers/abc123.jpg',
                'public_id' => 'eduhub/teachers/abc123',
            ]),
        ]);

        Livewire::test(CreateTeacher::class)
            ->fillForm([
                'first_name' => 'Nino',
                'last_name' => 'Kapanadze',
                'email' => 'nino-image@example.com',
                'password' => 'secret-password',
                'phone' => '+995 555 111 222',
                'specialization' => 'Mathematics',
                'image' => UploadedFile::fake()->image('teacher.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('teachers', [
            'email' => 'nino-image@example.com',
            'image' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teachers/abc123.jpg',
        ]);
    }

    public function test_edit_form_loads_existing_teacher_image_without_error(): void
    {
        $teacher = Teacher::factory()->create([
            'image' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teachers/original.jpg',
        ]);

        Livewire::test(EditTeacher::class, ['record' => $teacher->getRouteKey()])
            ->assertFormSet([
                'image' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teachers/original.jpg',
            ]);
    }
}
