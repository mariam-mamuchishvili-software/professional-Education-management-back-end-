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
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_can_link_a_user_account_to_a_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $user = User::factory()->teacher()->create();

        Livewire::test(EditTeacher::class, ['record' => $teacher->getRouteKey()])
            ->fillForm(['user_id' => $user->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($teacher->fresh()->user->is($user));
    }

    public function test_cannot_link_a_user_already_linked_to_another_teacher(): void
    {
        $user = User::factory()->teacher()->create();
        Teacher::factory()->for($user)->create();
        $teacher = Teacher::factory()->create();

        Livewire::test(EditTeacher::class, ['record' => $teacher->getRouteKey()])
            ->assertFormFieldExists('user_id', fn (Select $field): bool => ! array_key_exists($user->id, $field->getOptions()))
            ->fillForm(['user_id' => $user->id])
            ->call('save')
            ->assertHasFormErrors(['user_id']);

        $this->assertNull($teacher->fresh()->user_id);
    }

    public function test_create_form_offers_only_users_without_a_teacher_profile(): void
    {
        $linkedUser = User::factory()->create();
        Teacher::factory()->for($linkedUser)->create();
        $freeUser = User::factory()->create();

        Livewire::test(CreateTeacher::class)
            ->assertFormFieldExists('user_id', fn (Select $field): bool => array_key_exists($freeUser->id, $field->getOptions())
                && ! array_key_exists($linkedUser->id, $field->getOptions()));
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
