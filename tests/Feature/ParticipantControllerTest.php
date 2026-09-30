<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_paginates_participants_with_skip_and_limit(): void
    {
        $participants = Participant::factory(5)->create();

        $response = $this->getJson('/api/participants?skip=1&limit=2');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $participants[1]->id)
            ->assertJsonPath('total', 5)
            ->assertJsonPath('skip', 1)
            ->assertJsonPath('limit', 2);
    }

    public function test_show_returns_one_participant(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->getJson("/api/participants/{$participant->id}");

        $response->assertStatus(200)
            ->assertExactJsonStructure(['data' => ['id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at']])
            ->assertJsonPath('data.id', $participant->id)
            ->assertJsonPath('data.email', $participant->email);
    }

    public function test_show_returns_404_for_missing_participant(): void
    {
        $this->getJson('/api/participants/999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Participant not found');
    }

    public function test_store_creates_participant(): void
    {
        $response = $this->postJson('/api/participants', [
            'first_name' => 'Nino',
            'last_name' => 'Beridze',
            'email' => 'nino@example.com',
            'phone' => '+995 555 123 456',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'nino@example.com');

        $this->assertDatabaseHas('participants', [
            'first_name' => 'Nino',
            'last_name' => 'Beridze',
            'email' => 'nino@example.com',
        ]);
    }

    public function test_store_rejects_missing_names_and_duplicate_email(): void
    {
        $existing = Participant::factory()->create();

        $response = $this->postJson('/api/participants', [
            'email' => $existing->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }

    public function test_update_changes_only_given_fields_and_allows_keeping_own_email(): void
    {
        $participant = Participant::factory()->create(['last_name' => 'Original']);

        $response = $this->patchJson("/api/participants/{$participant->id}", [
            'first_name' => 'Renamed',
            'email' => $participant->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Renamed')
            ->assertJsonPath('data.last_name', 'Original');
    }

    public function test_destroy_deletes_participant(): void
    {
        $participant = Participant::factory()->create();

        $this->deleteJson("/api/participants/{$participant->id}")
            ->assertStatus(200)
            ->assertJsonPath('message', 'Participant deleted successfully');

        $this->assertModelMissing($participant);
    }
}
