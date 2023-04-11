<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class MassagePackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 100 seeds
        \App\Models\MassagePackages::factory(100)->create();
    }
}
