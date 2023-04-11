<?php

namespace Database\Factories;

use App\Models\receipts;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceiptsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = receipts::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $status = array(5, 10); // 5 unpaid, 10 paid
        $key4 = array_rand($status);
        $userAccounts = array('SUPERVISOR', 'SERVICE-BAR', 'RECEPTION', 'KITCHEN', 'STEAM-SAUNA-MASSAGE', 'STORE', 'ACCOUNTS');
        $key = array_rand($userAccounts);
        return [
            'uID' => rand(1, 500),
            'name' => $this->faker->realText(10, 2),
            'section' => $userAccounts[$key],
            'status' => $status[$key4],
            'TTotal' => rand(10000, 200000),
            'misc' => $this->faker->realText(50, 2),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
