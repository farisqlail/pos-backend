<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Payment::insert([
            ['name' => 'Cash', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'QRIS', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
