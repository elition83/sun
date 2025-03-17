<?php

namespace App\Http\Requests\Api\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:32|unique:posts,title,{$this->id}',
            'content' => 'sometimes|string',
            'profile_id' => 'exists:profiles,id',
            'category_id' => 'exists:categorys,id',
            'image_path' => 'sometimes|string|max:1024|nullable',
            'views' => 'sometimes|nullable',
            'likes' => 'sometimes|nullable',
            'is_published' => 'sometimes|boolean|nullable',
            'published_at' => 'sometimes|date|nullable',
        ];
        
    }
}
