<?php

namespace Database\Factories;

use App\Models\assetsevent;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetseventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = assetsevent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $userAccounts = array('SUPERVISOR', 'SERVICE-BAR', 'RECEPTION', 'KITCHEN', 'STEAM-SAUNA-MASSAGE', 'STORE', 'ACCOUNTS');
        $key = array_rand($userAccounts);

        $usersIDRange = range(1, 500);
        $key2 = array_rand($usersIDRange);

        $key3 = array_rand($userAccounts);

        $events = array('Deleted', 'Added', 'Trasfered', 'Recieved', 'Returned');
        $key4 = array_rand($events);

        return [
            'section' => $userAccounts[$key],
            'user' => $usersIDRange[$key2],
            'event' => $events[ $key4] . " " . $this->faker->realText(50, 2),
            'onrequestof' => $this->faker->name(),
            'department' => $userAccounts[$key3],
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
