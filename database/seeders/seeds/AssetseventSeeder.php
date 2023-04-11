<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class AssetseventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 250000 seeds - memory limit
        \App\Models\assetsevent::factory(250000)->create();
    }
}
