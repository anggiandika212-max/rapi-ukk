<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'description' => $this->description,

            'status_repair' => $this->status_repair,

            'photo' => $this->photo_path
                ? asset('storage/' . $this->photo_path)
                : null,

            'room' => $this->whenLoaded('room', function () {
                return [
                    'id' => $this->room->id,
                    'room_name' => $this->room->room_name,
                ];
            }),

            'reporter' => $this->whenLoaded('reporter', function () {
                return [
                    'id' => $this->reporter->id,
                    'name' => $this->reporter->name,
                ];
            }),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'can_edit' => (
                $request->user()?->role?->role_name === 'Guru' &&
                (int) $request->user()?->id === (int) $this->reporter_id &&
                $this->status_repair === 'Belum Diperbaiki'
            ),
        ];
    }
}