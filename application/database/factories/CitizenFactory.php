<?php

namespace Database\Factories;

use App\Models\Citizen;
use App\Models\Citizens;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CitizenFactory extends Factory
{

    protected $model = Citizen::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'surname' => $this->faker->name(),
            'fiscal_code' => $this->faker->regexify('[A-Z0-9]{10}')
        ];
    }
}
