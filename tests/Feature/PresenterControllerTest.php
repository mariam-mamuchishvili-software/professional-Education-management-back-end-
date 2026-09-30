<?php

namespace Tests\Feature;

use App\Models\Presenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresenterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_paginates_presenters_with_skip_and_limit(): void
    {
        $presenters = Presenter::factory(5)->create();

        $response = $this->getJson('/api/presenters?skip=1&limit=2');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $presenters[1]->id)
            ->assertJsonPath('total', 5)
            ->assertJsonPath('skip', 1)
            ->assertJsonPath('limit', 2);
    }

    public function test_show_returns_one_presenter(): void
    {
        $presenter = Presenter::factory()->create();

        $response = $this->getJson("/api/presenters/{$presenter->id}");

        $response->assertStatus(200)
            ->assertExactJsonStructure(['data' => ['id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at']])
            ->assertJsonPath('data.id', $presenter->id)
            ->assertJsonPath('data.email', $presenter->email);
    }

    public function test_show_returns_404_for_missing_presenter(): void
    {
        $this->getJson('/api/presenters/999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Presenter not found');
    }

    public function test_store_creates_presenter(): void
    {
        $response = $this->postJson('/api/presenters', [
            'first_name' => 'Nino',
            'last_name' => 'Beridze',
            'email' => 'nino@example.com',
            'phone' => '+995 555 123 456',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'nino@example.com');

        $this->assertDatabaseHas('presenters', [
            'first_name' => 'Nino',
            'last_name' => 'Beridze',
            'email' => 'nino@example.com',
        ]);
    }

    public function test_store_rejects_missing_names_and_duplicate_email(): void
    {
        $existing = Presenter::factory()->create();

        $response = $this->postJson('/api/presenters', [
            'email' => $existing->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }

    public function test_update_changes_only_given_fields_and_allows_keeping_own_email(): void
    {
        $presenter = Presenter::factory()->create(['last_name' => 'Original']);

        $response = $this->patchJson("/api/presenters/{$presenter->id}", [
            'first_name' => 'Renamed',
            'email' => $presenter->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Renamed')
            ->assertJsonPath('data.last_name', 'Original');
    }

    public function test_destroy_deletes_presenter(): void
    {
        $presenter = Presenter::factory()->create();

        $this->deleteJson("/api/presenters/{$presenter->id}")
            ->assertStatus(200)
            ->assertJsonPath('message', 'Presenter deleted successfully');

        $this->assertModelMissing($presenter);
    }
}
