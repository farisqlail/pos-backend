<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray($request)
    {
        return [  
            'id' => $this->id,  
            'name' => $this->name,  
            'price' => $this->price,  
            'stock' => $this->stock ? [  
                'id' => $this->stock->id, 
                'id_menu' => $this->stock->id_menu, 
                'stock' => $this->stock->stock,  
                'date' => $this->stock->date,  
            ] : null,  
        ];  
    }
}
