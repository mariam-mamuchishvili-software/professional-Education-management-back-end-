<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Colleges\Pages\CreateCollege;
use App\Filament\Resources\Colleges\Pages\EditCollege;
use App\Filament\Resources\Colleges\Pages\ListColleges;
use App\Filament\Resources\Colleges\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\Colleges\RelationManagers\TeachersRelationManager;
use App\Models\College;
use App\Models\Profession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CollegeResourceTest extends TestCase
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

    public function test_create_rejects_a_phone_longer_than_the_api_allows(): void
    {
        Livewire::test(CreateCollege::class)
            ->fillForm([
                'name' => 'Long Phone College',
                'address' => 'Tbilisi, Georgia',
                'email' => 'long-phone@example.com',
                'phone' => str_repeat('5', 21),
            ])
            ->call('create')
            ->assertHasFormErrors(['phone' => 'max']);
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

    public function test_can_attach_professions_to_a_college(): void
    {
        $college = College::factory()->create();
        $professions = Profession::factory(2)->create();

        Livewire::test(EditCollege::class, ['record' => $college->getRouteKey()])
            ->fillForm([
                'professions' => $professions->modelKeys(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEqualsCanonicalizing($professions->modelKeys(), $college->professions()->pluck('professions.id')->all());
    }

    public function test_students_relation_manager_can_attach_and_detach_students(): void
    {
        $college = College::factory()->create();
        $attached = Student::factory()->create();
        $college->students()->attach($attached);
        $student = Student::factory()->create();

        $relationManager = Livewire::test(StudentsRelationManager::class, [
            'ownerRecord' => $college,
            'pageClass' => EditCollege::class,
        ]);

        $relationManager->assertCanSeeTableRecords([$attached])
            ->assertCanNotSeeTableRecords([$student])
            ->callTableAction(AttachAction::class, data: ['recordId' => $student->id])
            ->assertHasNoTableActionErrors();

        $relationManager->callTableAction(DetachAction::class, $attached);

        $this->assertEquals([$student->id], $college->students()->pluck('students.id')->all());
    }

    public function test_can_delete_a_college(): void
    {
        $college = College::factory()->create();

        Livewire::test(EditCollege::class, ['record' => $college->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($college);
    }

    public function test_deleting_a_college_from_the_panel_removes_its_images_from_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/destroy' => Http::response(['result' => 'ok']),
        ]);

        $college = College::factory()->create([
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/poster.jpg',
            'logo' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/logo.jpg',
        ]);

        Livewire::test(EditCollege::class, ['record' => $college->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($college);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/destroy')
            && $request['public_id'] === 'eduhub/colleges/poster');
        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/destroy')
            && $request['public_id'] === 'eduhub/colleges/logo');
    }

    public function test_can_create_a_college_with_a_poster_uploaded_to_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/abc123.jpg',
                'public_id' => 'eduhub/colleges/abc123',
            ]),
        ]);

        Livewire::test(CreateCollege::class)
            ->fillForm([
                'name' => 'College With Poster',
                'address' => 'Tbilisi, Georgia',
                'email' => 'poster-college@example.com',
                'phone' => '+995 555 123 456',
                'poster' => UploadedFile::fake()->image('poster.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('colleges', [
            'email' => 'poster-college@example.com',
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/abc123.jpg',
        ]);
    }

    public function test_teachers_relation_manager_edit_uploads_the_teacher_image_to_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teachers/abc123.jpg',
                'public_id' => 'eduhub/teachers/abc123',
            ]),
        ]);

        $college = College::factory()->create();
        $teacher = Teacher::factory()->create(['image' => null]);
        $college->teachers()->attach($teacher);

        Livewire::test(TeachersRelationManager::class, [
            'ownerRecord' => $college,
            'pageClass' => EditCollege::class,
        ])
            ->callTableAction(EditAction::class, $teacher, data: [
                'image' => UploadedFile::fake()->image('teacher.jpg'),
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame(
            'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teachers/abc123.jpg',
            $teacher->refresh()->image,
        );

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/upload')
            && str_contains($request->body(), 'eduhub/teachers'));
    }

    public function test_edit_form_loads_existing_poster_without_error(): void
    {
        $college = College::factory()->create([
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/original.jpg',
        ]);

        Livewire::test(EditCollege::class, ['record' => $college->getRouteKey()])
            ->assertFormSet([
                'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/original.jpg',
            ]);
    }

    // Replacing an existing single FileUpload's value is intentionally not
    // exercised here via fillForm()/set(): Livewire's test harness appends the
    // new TemporaryUploadedFile to the field's raw state alongside the already
    // -hydrated value instead of replacing it (the real browser's Alpine.js
    // swap-on-select behavior never runs in a headless Livewire test), so the
    // saved value is unreliable in this harness regardless of the underlying
    // upload/replace logic. That logic (ReplacesCloudinaryImageOnUpdate +
    // CloudinaryUploader::delete) is covered end-to-end instead by
    // CloudinaryImageUploadTest::test_college_update_with_new_poster_replaces_old_one_on_cloudinary,
    // which exercises the same model code path via a real HTTP PUT request.
}
