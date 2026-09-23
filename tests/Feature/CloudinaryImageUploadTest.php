<?php

namespace Tests\Feature;

use App\Models\College;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloudinaryImageUploadTest extends TestCase
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

    private function fakeCloudinary(string $uploadedPublicId): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => "https://res.cloudinary.com/demo-cloud/image/upload/v1/{$uploadedPublicId}.jpg",
                'public_id' => $uploadedPublicId,
            ]),
            'api.cloudinary.com/v1_1/demo-cloud/image/destroy' => Http::response(['result' => 'ok']),
        ]);
    }

    public function test_college_store_uploads_poster_to_cloudinary(): void
    {
        $this->fakeCloudinary('eduhub/colleges/abc123');

        $response = $this->postJson('/api/colleges', [
            'name' => 'Tbilisi Technical College',
            'address' => 'Tbilisi, Georgia',
            'email' => 'college-image@example.com',
            'phone' => '+995 555 123 456',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.poster', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/abc123.jpg');

        $this->assertDatabaseHas('colleges', [
            'email' => 'college-image@example.com',
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/abc123.jpg',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/image/upload')
                && $request->hasFile('file')
                && str_contains($request->body(), 'eduhub/colleges');
        });
    }

    public function test_college_store_without_poster_leaves_it_null_and_never_calls_cloudinary(): void
    {
        Http::fake();

        $response = $this->postJson('/api/colleges', [
            'name' => 'No Poster College',
            'address' => 'Batumi, Georgia',
            'email' => 'no-poster@example.com',
            'phone' => '+995 555 000 000',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.poster', null);

        Http::assertNothingSent();
    }

    public function test_college_update_without_touching_poster_keeps_existing_value(): void
    {
        Http::fake();

        $college = College::factory()->create([
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/original.jpg',
        ]);

        $response = $this->putJson("/api/colleges/{$college->id}", [
            'name' => 'Renamed College',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.poster', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/original.jpg');

        Http::assertNothingSent();
    }

    public function test_college_update_with_new_poster_replaces_old_one_on_cloudinary(): void
    {
        $college = College::factory()->create([
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/original.jpg',
        ]);

        $this->fakeCloudinary('eduhub/colleges/new123');

        $response = $this->putJson("/api/colleges/{$college->id}", [
            'poster' => UploadedFile::fake()->image('new-poster.jpg'),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.poster', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/new123.jpg');

        $this->assertDatabaseHas('colleges', [
            'id' => $college->id,
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/new123.jpg',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/image/destroy')
                && $request['public_id'] === 'eduhub/colleges/original';
        });
    }

    public function test_college_store_uploads_logo_to_cloudinary(): void
    {
        $this->fakeCloudinary('eduhub/colleges/logo123');

        $response = $this->postJson('/api/colleges', [
            'name' => 'Logo College',
            'address' => 'Tbilisi, Georgia',
            'email' => 'college-logo@example.com',
            'phone' => '+995 555 123 456',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.logo', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/logo123.jpg')
            ->assertJsonPath('data.poster', null);

        $this->assertDatabaseHas('colleges', [
            'email' => 'college-logo@example.com',
            'logo' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/logo123.jpg',
        ]);
    }

    public function test_college_update_with_new_logo_replaces_old_logo_and_keeps_poster(): void
    {
        $college = College::factory()->create([
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/poster.jpg',
            'logo' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/old-logo.jpg',
        ]);

        $this->fakeCloudinary('eduhub/colleges/new-logo');

        $response = $this->putJson("/api/colleges/{$college->id}", [
            'logo' => UploadedFile::fake()->image('new-logo.png'),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.logo', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/new-logo.jpg')
            ->assertJsonPath('data.poster', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/colleges/poster.jpg');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/destroy')
            && $request['public_id'] === 'eduhub/colleges/old-logo');

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/image/destroy')
            && $request['public_id'] === 'eduhub/colleges/poster');
    }

    public function test_teacher_store_uploads_image_to_cloudinary(): void
    {
        $this->fakeCloudinary('eduhub/teachers/abc123');

        $response = $this->postJson('/api/teachers', [
            'first_name' => 'Nino',
            'last_name' => 'Beridze',
            'email' => 'teacher-image@example.com',
            'phone' => '+995 555 123 456',
            'specialization' => 'Mathematics',
            'image' => UploadedFile::fake()->image('teacher.jpg'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.image', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/teachers/abc123.jpg');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/upload') && str_contains($request->body(), 'eduhub/teachers'));
    }

    public function test_student_store_uploads_image_to_cloudinary(): void
    {
        $this->fakeCloudinary('eduhub/students/abc123');

        $response = $this->postJson('/api/students', [
            'first_name' => 'Giorgi',
            'last_name' => 'Kapanadze',
            'email' => 'student-image@example.com',
            'phone' => '+995 555 987 654',
            'birth_date' => '2000-01-01',
            'image' => UploadedFile::fake()->image('student.jpg'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.image', 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/students/abc123.jpg');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/upload') && str_contains($request->body(), 'eduhub/students'));
    }

    public function test_store_rejects_a_non_image_file(): void
    {
        Http::fake();

        $response = $this->postJson('/api/colleges', [
            'name' => 'Bad Upload College',
            'address' => 'Kutaisi, Georgia',
            'email' => 'bad-upload@example.com',
            'phone' => '+995 555 111 222',
            'poster' => UploadedFile::fake()->create('not-an-image.pdf', 100),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['poster']);

        Http::assertNothingSent();
    }
}
