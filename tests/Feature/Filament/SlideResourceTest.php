<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Slides\Pages\CreateSlide;
use App\Filament\Resources\Slides\Pages\EditSlide;
use App\Filament\Resources\Slides\Pages\ListSlides;
use App\Models\College;
use App\Models\Slide;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SlideResourceTest extends TestCase
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

    public function test_list_page_displays_slides(): void
    {
        $slides = Slide::factory()->count(3)->create();

        Livewire::test(ListSlides::class)
            ->assertCanSeeTableRecords($slides);
    }

    public function test_can_create_a_slide_with_an_image_uploaded_to_cloudinary(): void
    {
        $college = College::factory()->create();

        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/upload' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/slides/abc123.jpg',
                'public_id' => 'eduhub/slides/abc123',
            ]),
        ]);

        Livewire::test(CreateSlide::class)
            ->fillForm([
                'college_id' => $college->id,
                'title' => 'Welcome Slide',
                'image' => UploadedFile::fake()->image('slide.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('slides', [
            'college_id' => $college->id,
            'title' => 'Welcome Slide',
            'image' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/slides/abc123.jpg',
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/upload')
            && str_contains($request->body(), 'eduhub/slides'));
    }

    public function test_create_requires_college_and_title(): void
    {
        Livewire::test(CreateSlide::class)
            ->fillForm([
                'college_id' => null,
                'title' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'college_id' => 'required',
                'title' => 'required',
            ]);
    }

    public function test_deleting_a_slide_removes_its_image_from_cloudinary(): void
    {
        Http::fake([
            'api.cloudinary.com/v1_1/demo-cloud/image/destroy' => Http::response(['result' => 'ok']),
        ]);

        $slide = Slide::factory()->create([
            'image' => 'https://res.cloudinary.com/demo-cloud/image/upload/v1/eduhub/slides/to-delete.jpg',
        ]);

        Livewire::test(EditSlide::class, ['record' => $slide->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($slide);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/image/destroy')
            && $request['public_id'] === 'eduhub/slides/to-delete');
    }
}
