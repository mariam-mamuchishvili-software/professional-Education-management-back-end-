<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Slide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlideTest extends TestCase
{
    use RefreshDatabase;

    public function test_slide_belongs_to_a_college(): void
    {
        $college = College::factory()->create();
        $slide = Slide::factory()->for($college)->create();

        $this->assertTrue($slide->college->is($college));
    }

    public function test_college_has_many_slides(): void
    {
        $college = College::factory()->create();
        $slides = Slide::factory()->count(3)->for($college)->create();

        $this->assertCount(3, $college->slides);
        $this->assertTrue($college->slides->pluck('id')->diff($slides->pluck('id'))->isEmpty());
    }

    public function test_deleting_a_college_deletes_its_slides(): void
    {
        $college = College::factory()->create();
        $slide = Slide::factory()->for($college)->create();

        $college->delete();

        $this->assertDatabaseMissing('slides', ['id' => $slide->id]);
    }
}
