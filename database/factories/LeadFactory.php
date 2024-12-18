<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $interested = $this->faker->randomElement(['interested', 'not_interested', 'unreachable']);

        return [
            'lead_status' => function (array $lead) use ($interested) {
                return $lead['started'] == 1 ? $interested : NULL;
            },
            'time_taken' => function (array $lead) {
                return $lead['started'] == 1 ? $this->faker->numberBetween(180, 300) : 0;
            },

        ];
    }
}
