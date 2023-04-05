<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\Family;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RemoveTest extends TestCase
{
   
    use RefreshDatabase;

    /**
     * Unique children cannot be removed.
     * @group remove
    */
    public function test_unique_children_cannot_be_removed(): void
    {
        $citizen = Citizen::factory()->create();
        $family = Family::factory()->create();
        $citizen->families()->attach($family->id, ['role_id' => Role::CHILD]);
       
        $response = $this->postJson('/api/remove', ['citizen_id' => $citizen->id, 'family_id' => $family->id]);
       
        $response->assertStatus(405);
    }

    /**
     * Citizen in charge cannot be removed.
     * @group remove
    */
    public function test_citizen_in_charge_cannot_be_removed(): void
    {
        $citizen = Citizen::factory()->create();
        $family = Family::factory()->create();
        $citizen->families()->attach($family->id, ['role_id' => Role::TUTOR, 'in_charge' => 1]);
        
        $response = $this->postJson('/api/remove', ['citizen_id' => $citizen->id, 'family_id' => $family->id]);
        
        $response->assertStatus(405);
    }

    /**
     * Citizen can be removed.
     * @group remove
    */
    public function test_citizen_can_be_removed(): void
    {
        $citizen = Citizen::factory()->create();
        $family = Family::factory()->create();
        $citizen->families()->attach($family->id, ['role_id' => Role::TUTOR, 'in_charge' => 0]);
        
        $response = $this->postJson('/api/remove', ['citizen_id' => $citizen->id, 'family_id' => $family->id]);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
        ]);
    }
}
