<?php

namespace Database\Factories;

use App\Models\Salesman;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesmanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Salesman::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' =>  '12345678',
            'user_type' => 'salesman',
            'status' => $this->faker->randomElement(['enabled', 'disabled'])
        ];
    }
}
