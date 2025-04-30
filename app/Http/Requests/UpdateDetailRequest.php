<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDetailRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

 
    public function rules(): array
    {
        return [
            'key' => ['sometimes', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
            'icon' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
        ];
    }

  
    public function messages(): array
    {
        return [
            'key.string' => 'The key must be a string.',
            'key.max' => 'The key may not be greater than 255 characters.',
            
            'value.string' => 'The value must be a string.',
            
            'icon.string' => 'The icon must be a string.',
            
            'status.string' => 'The status must be a string.',
            'status.max' => 'The status may not be greater than 255 characters.',
            
            'type.string' => 'The type must be a string.',
            'type.max' => 'The type may not be greater than 255 characters.',
        ];
    }
}