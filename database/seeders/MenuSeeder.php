<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // Menambahkan 5 menu contoh
        Menu::create([
            'name' => 'Sate Kambing',
            'price' => 25000,
            'stock' => 50,
        ]);

        Menu::create([
            'name' => 'Sate Ayam Kelapa',
            'price' => 15000,
            'stock' => 30,
        ]);

        Menu::create([
            'name' => 'Sate Ayam',
            'price' => 20000,
            'stock' => 40,
        ]);

        Menu::create([
            'name' => 'Es Teh',
            'price' => 5000,
            'stock' => 60,
        ]);

        Menu::create([
            'name' => 'Es jeruk',
            'price' => 6000,
            'stock' => 35,
        ]);
    }
}
