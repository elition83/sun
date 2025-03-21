<?php

namespace App\Http\Resources\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'user_id' => $this->user_id,
            'phone' => $this->phone,
            'address' => $this->address,
            'gender' => $this->gender,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }

}
