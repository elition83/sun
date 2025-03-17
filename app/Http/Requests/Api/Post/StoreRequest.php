<?php

namespace App\Http\Requests\Api\Post;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'title' => 'required|string|max:32|unique:posts,title',
            'content' => 'required|string',
            'profile_id' => 'exists:profiles,id',
            'category_id' => 'exists:categorys,id',
            'image_path' => 'nullable|string|max:1024',
            'views' => 'nullable',
            'likes' => 'nullable',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ];
        
    }
}
