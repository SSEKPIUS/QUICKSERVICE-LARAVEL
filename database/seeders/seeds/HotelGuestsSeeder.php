<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class HotelGuestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 10000 seeds
        \App\Models\hotel_guests::factory(10000)->create();
    }
}
