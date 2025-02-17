<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray($request)
    {
        return [  
            'id' => $this->menu->id,  
            'name' => $this->menu->name,  
            'price' => $this->menu->price,  
            'stock' => [  
                'id' => $this->id, 
                'stock' => $this->stock,  
                'date' => $this->date,  
            ],  
        ];  
    }
}
