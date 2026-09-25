<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Trainings\Pages\CreateTraining;
use App\Filament\Resources\Trainings\Pages\EditTraining;
use App\Filament\Resources\Trainings\Pages\ListTrainings;
use App\Filament\Resources\Trainings\Pages\ViewTraining;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TrainingResourceTest extends TestCase
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

    public function test_list_page_displays_trainings(): void
    {
        $trainings = Training::factory()->count(3)->create();

        Livewire::test(ListTrainings::class)
            ->assertCanSeeTableRecords($trainings);
    }

    public function test_view_page_renders_a_training(): void
    {
        $training = Training::factory()->create(['video_link' => 'https://www.youtube.com/watch?v=abc123']);

        Livewire::test(ViewTraining::class, ['record' => $training->getRouteKey()])
            ->assertOk()
            ->assertSee($training->title);
    }

    public function test_can_create_a_training_with_a_poster_uploaded_to_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/trainings/abc123.jpg',
                'public_id' => 'eduhub/trainings/abc123',
            ]),
        ]);

        Livewire::test(CreateTraining::class)
            ->fillForm([
                'title' => 'Laravel Basics',
                'video_link' => 'https://www.youtube.com/watch?v=abc123',
                'poster' => UploadedFile::fake()->image('poster.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('trainings', [
            'title' => 'Laravel Basics',
            'video_link' => 'https://www.youtube.com/watch?v=abc123',
            'poster' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/trainings/abc123.jpg',
        ]);
    }

    public function test_create_requires_title_and_a_valid_video_link(): void
    {
        Livewire::test(CreateTraining::class)
            ->fillForm([
                'title' => '',
                'video_link' => 'not-a-url',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'title' => 'required',
                'video_link' => 'url',
            ]);
    }

    public function test_can_edit_a_training(): void
    {
        $training = Training::factory()->create();

        Livewire::test(EditTraining::class, ['record' => $training->getRouteKey()])
            ->fillForm(['title' => 'Updated Title'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Updated Title', $training->refresh()->title);
    }
}
