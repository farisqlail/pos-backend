<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_menu',
        'id_promo',
        'status_transaction',
        'status_payment',
        'discount_amount',
        'grand_total',
        'quantity'
    ];

    public function menus()
    {
        return $this->belongsToMany(Menu::class)
            ->withPivot('quantity', 'price'); // Tambahkan 'quantity' dan 'price' sebagai data pivot
    }
}
