<?php

namespace Database\Factories;

use App\Models\hotel_guests;
use Illuminate\Database\Eloquent\Factories\Factory;

class hotel_guestsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = hotel_guests::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = array('inactive', 'active');
        $key = array_rand($type);
        $idtype = array('National ID', 'PassPort', 'Drivers Permit');
        $idkey = array_rand($idtype);
        return [
            'fullname' => $this->faker->realText(10, 2),
            'idType' => $idtype[$idkey],
            'idNum' => rand(100000, 900000),
            'email' => $this->faker->unique()->safeEmail(),
            'aob' => $this->faker->realText(100, 2),
            'paid' => false,
            'checkIn' => now(),
            'leaveDate' => now(),
            'status' => $type[$key],
            'roomNo' => rand(2000, 4000),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
