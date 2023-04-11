<?php

namespace Database\Seeders\seeds;

use Illuminate\Database\Seeder;

class ReceiptsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // migrate factory of 10000 seeds- memory limit
        \App\Models\receipts::factory(10000)->create();
    }
}
