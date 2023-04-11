<?php

namespace Database\Factories;

use App\Models\MassagePackages;
use Illuminate\Database\Eloquent\Factories\Factory;

class MassagePackagesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MassagePackages::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = array('SINGLE', 'DOUBLE');
        $key = array_rand($type);
        return [
            'name' => $this->faker->name(),
            'fee' => strval(rand(1000, 200000)),
            'time' => strval(rand(1, 5)),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}