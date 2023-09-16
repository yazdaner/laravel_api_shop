<?php

namespace App\Http\Resources;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'address' => $this->address,
            'cellphone' => $this->cellphone,
            'postal_code' => $this->postal_code,
            'province' =>Province::where('id',$this->province_id)->first()->name,
            'city' => City::where('id',$this->city_id)->where('province_id',$this->province_id)->first()->name
        ];
    }
}
