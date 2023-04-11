<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 1000 seeds
        \App\Models\menu::factory(1000)->create();
    }
}