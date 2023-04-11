<?php

namespace Database\Factories;

use App\Models\SteamSaunaMassageGuests;
use Illuminate\Database\Eloquent\Factories\Factory;

class SteamSaunaMassageGuestsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SteamSaunaMassageGuests::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = array('steam-sauna', 'massage');
        $key = array_rand($type);
        return [
            'section' => $type[$key],
            'fullname' => $this->faker->realText(10, 2),
            'service' =>$this->faker->realText(100, 2),
            'fee' => rand(100000, 900000),
            'paid' => false,
            'time' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}