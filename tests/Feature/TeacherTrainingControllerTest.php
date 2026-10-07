<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\TeacherTraining;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeacherTrainingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cloudinary.cloud_name' => 'demo-cloud',
            'cloudinary.api_key' => 'demo-key',
            'cloudinary.api_secret' => 'demo-secret',
        ]);
    }

    public function test_guest_cannot_access_trainings(): void
    {
        $this->getJson('/api/me/trainings')->assertUnauthorized();
    }

    public function test_index_lists_only_the_signed_in_teachers_trainings(): void
    {
        $teacher = Teacher::factory()->create();
        $own = TeacherTraining::factory()->for($teacher)->create();
        TeacherTraining::factory()->create();

        Sanctum::actingAs($teacher);

        $this->getJson('/api/me/trainings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);
    }

    public function test_store_creates_a_training_with_a_certificate_link(): void
    {
        $teacher = Teacher::factory()->create();

        Sanctum::actingAs($teacher);

        $this->postJson('/api/me/trainings', [
            'title' => 'Inclusive Education',
            'organizer' => 'Private Training Center',
            'certificate_number' => 'CERT-2024-01',
            'issue_date' => '2024-03-01',
            'expiry_date' => '2027-03-01',
            'certificate_url' => 'https://example.com/certificates/1.pdf',
        ])
            ->assertCreated()
            ->assertJsonPath('data.teacher_id', $teacher->id)
            ->assertJsonPath('data.certificate_url', 'https://example.com/certificates/1.pdf');

        $this->assertDatabaseHas('teacher_trainings', [
            'teacher_id' => $teacher->id,
            'title' => 'Inclusive Education',
            'organizer' => 'Private Training Center',
        ]);
    }

    public function test_store_uploads_a_pdf_certificate_to_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teacher-certificates/abc123.pdf',
            ]),
        ]);

        $teacher = Teacher::factory()->create();

        Sanctum::actingAs($teacher);

        $this->postJson('/api/me/trainings', [
            'title' => 'Digital Skills',
            'organizer' => 'Ministry of Education',
            'certificate' => UploadedFile::fake()->create('certificate.pdf', 200, 'application/pdf'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.certificate_url', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teacher-certificates/abc123.pdf');

        $this->assertDatabaseHas('teacher_trainings', [
            'teacher_id' => $teacher->id,
            'certificate_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teacher-certificates/abc123.pdf',
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/upload')
            && str_contains($request->body(), 'eduhub/teacher-certificates'));
    }

    public function test_store_rejects_a_certificate_of_a_disallowed_type_or_size(): void
    {
        Http::fake();

        Sanctum::actingAs(Teacher::factory()->create());

        $this->postJson('/api/me/trainings', [
            'title' => 'Digital Skills',
            'organizer' => 'Ministry of Education',
            'certificate' => UploadedFile::fake()->create('certificate.exe', 10, 'application/x-msdownload'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['certificate']);

        $this->postJson('/api/me/trainings', [
            'title' => 'Digital Skills',
            'organizer' => 'Ministry of Education',
            'certificate' => UploadedFile::fake()->create('certificate.pdf', 6000, 'application/pdf'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['certificate']);

        Http::assertNothingSent();
        $this->assertDatabaseCount('teacher_trainings', 0);
    }

    public function test_store_rejects_a_certificate_file_and_link_together_and_a_non_http_link(): void
    {
        Http::fake();

        Sanctum::actingAs(Teacher::factory()->create());

        $this->postJson('/api/me/trainings', [
            'title' => 'Digital Skills',
            'organizer' => 'Ministry of Education',
            'certificate' => UploadedFile::fake()->create('certificate.pdf', 10, 'application/pdf'),
            'certificate_url' => 'https://example.com/certificate.pdf',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['certificate_url']);

        $this->postJson('/api/me/trainings', [
            'title' => 'Digital Skills',
            'organizer' => 'Ministry of Education',
            'certificate_url' => 'javascript:alert(1)',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['certificate_url']);

        Http::assertNothingSent();
    }

    public function test_store_requires_title_and_organizer_and_an_expiry_on_or_after_issue(): void
    {
        Sanctum::actingAs(Teacher::factory()->create());

        $this->postJson('/api/me/trainings', [
            'issue_date' => '2024-03-01',
            'expiry_date' => '2024-02-01',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'organizer', 'expiry_date']);
    }

    public function test_update_replaces_the_certificate_and_deletes_the_old_one_from_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teacher-certificates/new.pdf',
            ]),
            'api.cloudinary.com/v1_1/demo-cloud/image/destroy' => Http::response(['result' => 'ok']),
        ]);

        $teacher = Teacher::factory()->create();
        $training = TeacherTraining::factory()->for($teacher)->create([
            'certificate_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teacher-certificates/old.pdf',
        ]);

        Sanctum::actingAs($teacher);

        $this->post("/api/me/trainings/{$training->id}", [
            '_method' => 'PATCH',
            'certificate' => UploadedFile::fake()->create('new.pdf', 50, 'application/pdf'),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.certificate_url', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teacher-certificates/new.pdf');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/destroy')
            && $request['public_id'] === 'eduhub/teacher-certificates/old');
    }

    public function test_destroy_deletes_the_training_and_its_certificate_from_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/destroy' => Http::response(['result' => 'ok']),
        ]);

        $teacher = Teacher::factory()->create();
        $training = TeacherTraining::factory()->for($teacher)->create([
            'certificate_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teacher-certificates/to-delete.pdf',
        ]);

        Sanctum::actingAs($teacher);

        $this->deleteJson("/api/me/trainings/{$training->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Training deleted successfully');

        $this->assertModelMissing($training);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/destroy')
            && $request['public_id'] === 'eduhub/teacher-certificates/to-delete');
    }

    public function test_teacher_cannot_view_update_or_delete_another_teachers_training(): void
    {
        $training = TeacherTraining::factory()->create(['title' => 'Original']);

        Sanctum::actingAs(Teacher::factory()->create());

        $this->getJson("/api/me/trainings/{$training->id}")->assertNotFound();
        $this->patchJson("/api/me/trainings/{$training->id}", ['title' => 'Hijacked'])->assertNotFound();
        $this->deleteJson("/api/me/trainings/{$training->id}")->assertNotFound();

        $this->assertSame('Original', $training->fresh()->title);
    }

    public function test_admin_can_delete_any_teachers_training(): void
    {
        $training = TeacherTraining::factory()->create();

        Sanctum::actingAs(User::factory()->collegeAdmin()->create());

        $this->deleteJson("/api/me/trainings/{$training->id}")->assertOk();

        $this->assertModelMissing($training);
    }
}
