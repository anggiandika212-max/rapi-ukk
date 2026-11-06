<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            
            'title' => $this->title,
            'description' => $this->description,
            
            'event_date' => $this->event_date?->format('Y-m-d'),
            
            'start_time' => $this->start_time
            ? \Carbon\Carbon::parse($this->start_time)->format('H:i')
            : null,
            
            'end_time' => $this->end_time
            ? \Carbon\Carbon::parse($this->end_time)->format('H:i')
            : null,
            
            'status' => $this->status,
            'approved_by' => $this->approved_by,
            'move_reason' => $this->move_reason,
            
            'room' => $this->whenLoaded('room', function () {
                return [
                    'id' => $this->room->id,
                    'room_name' => $this->room->room_name,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'approver' => $this->whenLoaded('approver', function () {
                return [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                    'email' => $this->approver->email,
                ];
            }),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
 
}