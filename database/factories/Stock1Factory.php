<?php

namespace Database\Factories;

use App\Models\stock1;
use Illuminate\Database\Eloquent\Factories\Factory;

class Stock1Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = stock1::class;

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
            'stocks_id' => $menusIDRange[$key],
            'category' => $this->faker->name(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
