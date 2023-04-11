<?php

namespace Database\Factories;

use App\Models\assets;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = assets::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $userAccounts = array('SERVICE-BAR', 'RECEPTION', 'KITCHEN', 'STEAM-SAUNA-MASSAGE', 'STORE', 'ACCOUNTS');
        $key = array_rand($userAccounts);
        return [
            'section' => $userAccounts[$key],
            'category' => $this->faker->realText(20, 2),
            'stock1s_id' => rand(1, 100),
            'stocks' => $this->faker->realText(20, 2),
            'unit' =>  $this->faker->realText(10, 2),
            'inbound' => rand(10, 50),
            'outbound' => rand(1, 10),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
