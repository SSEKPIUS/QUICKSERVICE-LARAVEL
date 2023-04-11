<?php

namespace Database\Factories;

use App\Models\menu1;
use Illuminate\Database\Eloquent\Factories\Factory;

class Menu1Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = menu1::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $menusIDRange = range(1, 1000);
        $key = array_rand($menusIDRange);
        return [
            'menus_id' => $menusIDRange[$key],
            'category' => $this->faker->name(),
            'description' => $this->faker->realText(100, 2),
            'price' => rand(1000, 10000),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}