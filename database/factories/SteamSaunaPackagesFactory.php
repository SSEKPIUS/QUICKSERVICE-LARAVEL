<?php

namespace Database\Factories;

use App\Models\SteamSaunaPackages;
use Illuminate\Database\Eloquent\Factories\Factory;

class SteamSaunaPackagesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SteamSaunaPackages::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->realText(10, 2),
            'fee' => rand(100000, 900000),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}