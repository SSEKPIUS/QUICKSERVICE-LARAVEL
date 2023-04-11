<?php

namespace Database\Factories;

use App\Models\hotel_rooms;
use Illuminate\Database\Eloquent\Factories\Factory;

class hotel_roomsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = hotel_rooms::class;

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
            'type' => $type[$key],
            'roomNo' => strval(rand(1000, 200000)),
            'occupied' => false,
            'beds' => rand(1, 5),
            'fee' => rand(10000, 50000),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
