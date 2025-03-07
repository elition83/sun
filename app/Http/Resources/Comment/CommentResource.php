<?php

namespace App\Http\Resources\Comment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'content' => $this->content,
            'post_id' => $this->post_id, // Foreign Key,
            'profile_id' => $this->profile_id, // Foreign Key,
            'comment_id' => $this->comment_id, // Foreign Key,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }


}
