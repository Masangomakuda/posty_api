<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'post_id'=> $this->id,
            'post'=>substr($this->post, 0, 50) . '...',
            'image' => $this->image,
            'created' => $this->created_at->diffForHumans(),
            'user'=>new UserResource($this->whenLoaded('user')),
            'likes_count' => $this->whenCounted('likes'),
            
        ];
    }
}
