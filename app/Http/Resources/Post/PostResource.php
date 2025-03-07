<?php

namespace App\Http\Resources\Post;

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
            'title' => $this->title,
            'content' => $this->content,
            'profile_id' => $this->profile_id, // Foreign Key,
            'category_id' => $this->category_id, // Foreign Key,
            'image_path' => $this->image_path,
            'views' => $this->views,
            'likes' => $this->likes,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }


}
