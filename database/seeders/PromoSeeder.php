<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promo;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Menambahkan 3 data promo
        Promo::create([
            'name' => 'Diskon 50%',
            'discount' => 50,
            'quantity' => 100
        ]);

        Promo::create([
            'name' => 'Diskon 30%',
            'discount' => 30,
            'quantity' => 150
        ]);

        Promo::create([
            'name' => 'Diskon 10%',
            'discount' => 10,
            'quantity' => 200
        ]);
    }
}
