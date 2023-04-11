<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class HotelRoomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 100 seeds
        \App\Models\hotel_rooms::factory(100)->create();
    }
}
