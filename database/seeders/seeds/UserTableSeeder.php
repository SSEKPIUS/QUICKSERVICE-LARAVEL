<?php

namespace Database\Seeders\seeds;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{

    public function run()
    {
        DB::table('users')->insert([
            'section' => 'administrator',
            'name' => 'Administrator',
            'email' => 'admin@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('G36nu^XF'),
            'permission' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'section' => 'SUPERVISOR',
            'name' => 'supervisor',
            'email' => 'supervisor@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('1234'),
            'permission' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'section' => 'STORE',
            'name' => 'store',
            'email' => 'store@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('1234'),
            'permission' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'section' => 'SERVICE-BAR',
            'name' => 'bar',
            'email' => 'bar@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('1234'),
            'permission' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'section' => 'SERVICE-WAITER-WAITRESS',
            'name' => 'waiter',
            'email' => 'waiter@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('1234'),
            'permission' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'section' => 'RECEPTION',
            'name' => 'reception',
            'email' => 'reception@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('1234'),
            'permission' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'section' => 'KITCHEN',
            'name' => 'kitchen',
            'email' => 'kitchen@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('1234'),
            'permission' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'section' => 'STEAM-SAUNA-MASSAGE',
            'name' => 'Sauna',
            'email' => 'sauna@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('1234'),
            'permission' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'section' => 'ACCOUNTS',
            'name' => 'accounts',
            'email' => 'accounts@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('1234'),
            'permission' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]); 
        // migrate factory of 20 seeds
        \App\Models\User::factory(1000)->create();
    }
}
