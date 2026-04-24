<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender->name ?? null,
            'sender_role' => $this->sender->role ?? null,
            'content' => $this->content,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
