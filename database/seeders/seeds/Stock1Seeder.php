<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class Stock1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 1000 seeds
        \App\Models\stock1::factory(1000)->create();
    }
}
