<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use App\Http\Resources\V1\PostResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'name' => $this->name,
            'email'=> $this->email,
            'total_posts'=> $this->whenCounted('posts'),
            'posts'=> PostResource::collection($this->whenLoaded('posts')),
            
           
        ];
    }
}
