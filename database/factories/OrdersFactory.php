<?php

namespace Database\Factories;

use App\Models\orders;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrdersFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = orders::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $status = array(5, 10, 15, 20); // 5 pending, 10 new  15 served 20 cancelled
        $key4 = array_rand($status);
        $userAccounts = array('SUPERVISOR', 'SERVICE-BAR', 'RECEPTION', 'KITCHEN', 'STEAM-SAUNA-MASSAGE', 'STORE', 'ACCOUNTS');
        $key = array_rand($userAccounts);
        $key3 = array_rand($userAccounts);
        return [
            'section' => $userAccounts[$key],
            'receipts_id' => rand(1000, 100000),
            'OrderID' => 'B'.strval(rand(1000, 2000)),
            'Category' => $this->faker->name(),
            'dish' => $this->faker->realText(20, 2),
            'Description' => $this->faker->realText(100, 2),
            'cost' => rand(1000, 2000),
            'qty' => rand(1, 5),
            'SentFrom' => $userAccounts[$key3],
            'status' => $status[$key4],
            'reason' => $this->faker->realText(100, 2),
            'uID' => rand(1, 20),
            'Names' => $this->faker->name(),
            'destTbl' => 'TB'.strval(rand(1, 20)),
            'destRmn' => 'RM' . strval(rand(1, 20)),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
