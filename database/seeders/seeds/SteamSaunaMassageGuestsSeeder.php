<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class SteamSaunaMassageGuestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         // migrate factory of 10000 seeds
         \App\Models\SteamSaunaMassageGuests::factory(10000)->create();
    }
}
