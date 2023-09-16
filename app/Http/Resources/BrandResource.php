<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'products' => ProductResource::collection($this->whenLoaded('products',function(){
                return $this->products->load('images');
            }))
        ];
    }
}
