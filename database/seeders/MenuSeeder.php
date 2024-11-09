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
            'name' => 'Nasi Goreng',
            'price' => 25000,
            'stock' => 50,
        ]);

        Menu::create([
            'name' => 'Mie Ayam',
            'price' => 15000,
            'stock' => 30,
        ]);

        Menu::create([
            'name' => 'Sate Ayam',
            'price' => 20000,
            'stock' => 40,
        ]);

        Menu::create([
            'name' => 'Pecel Lele',
            'price' => 18000,
            'stock' => 60,
        ]);

        Menu::create([
            'name' => 'Nasi Campur',
            'price' => 22000,
            'stock' => 35,
        ]);
    }
}
