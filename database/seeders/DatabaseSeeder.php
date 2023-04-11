<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //1 => seed users - store users data
        //$this->call(seeds\UserTableSeeder::class);
        //$this->command->info('User table seeded!');

        //2 => seed menu - major categories for kitchen menu
        //$this->call(seeds\MenuSeeder::class);
        //$this->command->info('Main menu table seeded!');

        //3 => seed menu1 - monir menu for kitchen menu
        //$this->call(seeds\Menu1Seeder::class);
        //$this->command->info('Sub menu table seeded!');

        //4 => seed stock - major categories for stock
        //$this->call(seeds\StockSeeder::class);
        //$this->command->info('Stock table seeded!');

        //5 => seed stock1 - minor categories for stock
        //$this->call(seeds\Stock1Seeder::class);
        //$this->command->info('Stock1 table seeded!');

        //6 => seed stock2 - stock units of measure
        //$this->call(seeds\Stock2Seeder::class);
        //$this->command->info('Stock2 table seeded!');

        //7 => seed Assets - section stocks
        //$this->call(seeds\AssetsSeeder::class);
        //$this->command->info('Assets table seeded!');

        //8 => seed Assetsevent - assets event logs for all users
        //$this->call(seeds\AssetseventSeeder::class);
        //$this->command->info('Assetsevent table seeded!');

        //8 => seed Receipts - user receipts
        //$this->call(seeds\ReceiptsSeeder::class);
        //$this->command->info('Receipts table seeded!');

        //9 => seed Orders - orders from all sections
        //$this->call(seeds\OrdersSeeder::class);
        //$this->command->info('Orders table seeded!');

        //10 => seed MenuBar - menu of bar.. must me picked from stock thou
        //$this->call(seeds\MenuBarSeeder::class);
        //$this->command->info('MenuBar table seeded!');

        //11 => seed HotelRooms - add a few rooms 
        //$this->call(seeds\HotelRoomsSeeder::class);
        //$this->command->info('HotelRooms table seeded!');

        //12 => seed HotelGuests
        //$this->call(seeds\HotelGuestsSeeder::class);
        //$this->command->info('HotelGuests table seeded!');

        //13 => seed Massage Packages
        //$this->call(seeds\MassagePackagesSeeder::class);
        //$this->command->info('MassagePackages table seeded!');

        //14 = > seed Steam Sauna Massage  guests
        //$this->call(seeds\SteamSaunaMassageGuestsSeeder::class);
        //$this->command->info('SteamSaunaMassageGuests table seeded!');

        //15 = > seed Steam Sauna Packages 
        //$this->call(seeds\SteamSaunaPackagesSeeder::class);
        //$this->command->info('SteamSaunaPackages table seeded!');

        //15 = > seed Asset snapshot
        //$this->call(seeds\AssetsSnapshotSeeder::class);
        //$this->command->info('AssetsSnapshot table seeded!');

        //16 = > seed cash returns
        //$this->call(seeds\CashReturnsSeeder::class);
        //$this->command->info('CashReturns table seeded!');
        
    }
}