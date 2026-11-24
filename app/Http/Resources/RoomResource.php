<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'room_name' => $this->room_name,

            'facilities' => $this->facilities,

            'facility_condition' => $this->facility_condition,

            'status' => $this->status,

            'photo' => $this->photo
                ? asset('storage/' . $this->photo)
                : null,

            'pic' => $this->whenLoaded('pic', function () {

                return [
                    'id' => $this->pic->id,
                    'name' => $this->pic->name,
                    'email' => $this->pic->email,
                ];

            }),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),

            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

        ];
    }
}