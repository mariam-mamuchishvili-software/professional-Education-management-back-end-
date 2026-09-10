<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Profession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_groups(): void
    {
        Group::factory(5)->create();

        $response = $this->getJson('/api/groups');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_show_returns_one_group(): void
    {
        $group = Group::factory()->create();

        $response = $this->getJson("/api/groups/{$group->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $group->id);
    }

    public function test_show_returns_404_for_nonexistent_group(): void
    {
        $response = $this->getJson('/api/groups/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Group not found']);
    }

    public function test_store_creates_a_group(): void
    {
        $profession = Profession::factory()->create();

        $data = [
            'profession_id' => $profession->id,
            'name' => 'Group 101',
            'code' => 'GRP-101',
            'capacity' => 25,
            'study_shift' => 'morning',
        ];

        $response = $this->postJson('/api/groups', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Group 101');

        $this->assertDatabaseHas('groups', [
            'code' => 'GRP-101',
            'profession_id' => $profession->id,
        ]);
    }

    public function test_update_updates_a_group(): void
    {
        $group = Group::factory()->create();
        $profession = Profession::factory()->create();

        $data = [
            'profession_id' => $profession->id,
            'name' => 'Updated Group',
            'code' => 'GRP-999',
            'capacity' => 30,
            'study_shift' => 'evening',
        ];

        $response = $this->putJson("/api/groups/{$group->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Group');

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Updated Group',
            'code' => 'GRP-999',
            'profession_id' => $profession->id,
        ]);
    }

    public function test_destroy_deletes_a_group(): void
    {
        $group = Group::factory()->create();

        $response = $this->deleteJson("/api/groups/{$group->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Group deleted successfully');

        $this->assertDatabaseMissing('groups', [
            'id' => $group->id,
        ]);
    }
}
