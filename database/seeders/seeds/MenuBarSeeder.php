<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class MenuBarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 500 seeds
        \App\Models\menuBar::factory(500)->create();
    }
}
