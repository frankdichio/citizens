<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\Family;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoveTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Testing if a citizen can be in charge of a family.
     * @group move
     */
    public function test_citizen_can_moved_from_a_family_to_another(): void
    {
        $citizen = Citizen::factory()->create();
        $family = Family::factory()->create();
        $oldFamily = Family::factory()->create();
        $citizen->families()->attach($oldFamily->id, ['in_charge' => 0, 'role_id' => Role::PARENT]);
        
        $response = $this->postJson('/api/move', ['citizen_id' => $citizen->id, 'family_id' => $family->id, 'old_family_id' => $oldFamily->id]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
