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
        Promo::create([
            'name' => 'Diskon',
            'discount' => 5000
        ]);

        Promo::create([
            'name' => 'Diskon',
            'discount' => 3000
        ]);

        Promo::create([
            'name' => 'Diskon',
            'discount' => 1000
        ]);
    }
}
