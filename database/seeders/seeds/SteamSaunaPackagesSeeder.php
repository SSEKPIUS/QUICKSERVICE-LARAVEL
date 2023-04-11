<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class SteamSaunaPackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 100 seeds
        \App\Models\SteamSaunaPackages::factory(100)->create();
    }
}
