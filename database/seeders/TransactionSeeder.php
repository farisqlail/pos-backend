<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Menu;
use App\Models\Promo;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = Menu::first();
        $promo = Promo::first();

        Transaction::create([
            'id_menu' => $menu->id,
            'id_promo' => $promo ? $promo->id : null,
            'no_nota' => 'n0001',
            'user_id' => 2,
            'quantity' => 2,
            'payment' => "tunai",
            'grand_total' => ($menu->price * 2) - (($promo->discount / 100) * $menu->price * 2),
            'status_transaction' => 'pending',
            'status_payment' => 'paid',
            'discount_amount' => ($promo->discount / 100) * $menu->price * 2
        ]);
    }
}
