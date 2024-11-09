<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock'
    ];

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class)
            ->withPivot('quantity', 'price'); // Tambahkan 'quantity' dan 'price' sebagai data pivot
    }
}
