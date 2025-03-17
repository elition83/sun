<?php

namespace App\Http\Requests\Api\Profile;

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
            'name' => 'sometimes|string|max:32|unique:profiles,name,{$this->id}',
            'user_id' => 'sometimes',
            'phone' => 'sometimes|string|max:32|nullable',
            'address' => 'sometimes|string|max:255|nullable',
            'gender' => 'sometimes|nullable',
        ];
        
    }
}
