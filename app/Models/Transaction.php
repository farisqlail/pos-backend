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
        'no_nota',
        'status_transaction',
        'status_payment',
        'discount_amount',
        'grand_total',
        'quantity',
        'payment',
        'type_transaction',
        'pay_amount'
    ];

    public function menus()
    {
        return $this->belongsToMany(Menu::class)
            ->withPivot('quantity', 'price');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
