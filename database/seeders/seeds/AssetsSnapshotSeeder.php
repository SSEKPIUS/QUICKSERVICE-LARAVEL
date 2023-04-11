<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AssetsSnapshotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 1000 seeds
        \App\Models\assets_snapshot::factory(1000)->create();
    }
}
