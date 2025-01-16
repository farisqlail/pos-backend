<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_stock',
        'name',
        'price',
    ];

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class)
            ->withPivot('quantity', 'price'); 
    }

    public function stock()  
    {  
        return $this->hasOne(Stock::class, 'id_menu');  
    }  
}
