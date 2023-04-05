<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\Family;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoteTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Testing if a citizen can be in charge of a family.
     * @group promote
     */
    public function test_citizen_can_be_in_charge_of_a_family(): void
    {
        $citizen = Citizen::factory()->create();
        $family = Family::factory()->create();
        $citizen->families()->attach($family->id, ['in_charge' => 0, 'role_id' => 2]);
       
        $response = $this->postJson('/api/promote', ['citizen_id' => $citizen->id, 'family_id' => $family->id]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Testing if when a citizen promoted to in charge of a family, the old one lose his title.
     * @group promote
     */
    public function test_old_member_in_charge_lose_his_title(): void
    {
        $oldCitizen = Citizen::factory()->create();
        $citizen = Citizen::factory()->create();
        $family = Family::factory()->create();
        $citizen->families()->attach($family->id, ['in_charge' => 1, 'role_id' => 2]);
        $oldCitizen->families()->attach($family->id, ['in_charge' => 0, 'role_id' => 2]);

        $response = $this->postJson('/api/promote', ['citizen_id' => $citizen->id, 'family_id' => $family->id]);
        $oldMemberInCharge = $oldCitizen->families()->where('family_id', $family->id)->first();

        $this->assertFalse($oldMemberInCharge->in_charge == 1);
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Children can't be in charge.
     * @group promote
     */
    public function test_children_cannot_be_in_charge(): void
    {
        $citizen = Citizen::factory()->create();
        $family = Family::factory()->create();
        $citizen->families()->attach($family->id, ['role_id' => Role::CHILD]);

        $response = $this->postJson('/api/promote', ['citizen_id' => $citizen->id, 'family_id' => $family->id]);

        $response->assertStatus(405);
    }

    /**
     * Parent can't be in charge of more than three families
     * @group promote
     */
    public function test_parent_cannot_be_in_charge_of_more_than_three_families()
    {
        $citizen = Citizen::factory()->create();
        $families = Family::factory()->count(3)->create();
        foreach ($families as $family) {
            $citizen->families()->attach($family->id, ['role_id' => Role::PARENT, 'in_charge' => 1]);
        }
        $actualFamily = Family::factory()->create();
        $citizen->families()->attach($actualFamily->id, ['role_id' => Role::PARENT, 'in_charge' => 0]);

        $response = $this->postJson('/api/promote', ['citizen_id' => $citizen->id, 'family_id' => $family->id]);

        $response->assertStatus(405);
    }

    /**
     * Parent can't be in charge of family with more than three members
     * @group promote
     */
    public function test_parent_cannot_be_in_charge_of_family_with_more_than_six_members()
    {
        $citizen = Citizen::factory()->create();
        $citizens = Citizen::factory()->count(6)->create();
        $family = Family::factory()->create();
        foreach ($citizens as $otherCitizen) {
            $family->citizens()->attach($otherCitizen->id, ['role_id' => Role::CHILD]);
        }
        $citizen->families()->attach($family->id, ['role_id' => Role::PARENT, 'in_charge' => 0]);

        $response = $this->postJson('/api/promote', ['citizen_id' => $citizen->id, 'family_id' => $family->id]);

        $response->assertStatus(405);
    }
}
