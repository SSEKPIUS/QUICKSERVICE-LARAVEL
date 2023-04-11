<?php

namespace Database\Seeders\seeds;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

use Illuminate\Database\Seeder;

class CashReturnsSeeder extends Seeder
{

    public function run()
    {
        $Dfrom = Carbon::now()->subDays(200);
        for ($x = 0; $x <= 200; $x++) {
            $Dfrom = $Dfrom->addDays(1);
            DB::table('cash_returns')->insert([
                'Rooms' => rand(10000, 200000),
                'Sauna_Masssage' => rand(10000, 200000),
                'Bar_Kitchen' => rand(10000, 200000),
                'created_at' => $Dfrom,
                'updated_at' => $Dfrom
            ]);
        }
        // migrate factory of 200 seeds
        //\App\Models\CashReturns::factory(200)->create();
    }
}
