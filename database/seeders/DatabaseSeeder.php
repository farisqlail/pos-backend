<?php

use Database\Seeders\MenuSeeder;
use Database\Seeders\PromoSeeder;
use Database\Seeders\TransactionSeeder;
use Database\Seeders\UsersSeeder;
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
        $this->call(UsersSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(PromoSeeder::class);
        $this->call(TransactionSeeder::class);
        
    }
}
