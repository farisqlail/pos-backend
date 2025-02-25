<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_menu',
        'stock',
        'date'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu', 'id'); // id_menu in stocks relates to id in menus
    }
}
