<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
 
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'firstname' => ['sometimes', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname' => ['sometimes', 'string', 'max:255'],
            'prefixname' => ['nullable', 'string', 'max:50'],
            'photo' => ['nullable', 'string', 'max:255'],
            'email' => [
                'sometimes', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('users')->ignore($this->user)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.string' => 'The first name must be a string.',
            'firstname.max' => 'The first name may not be greater than 255 characters.',
            
            'lastname.string' => 'The last name must be a string.',
            'lastname.max' => 'The last name may not be greater than 255 characters.',
            
            'email.string' => 'The email must be a string.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'This email address is already in use.',
            
            'middlename.string' => 'The middle name must be a string.',
            'middlename.max' => 'The middle name may not be greater than 255 characters.',
            
            'prefixname.string' => 'The prefix name must be a string.',
            'prefixname.max' => 'The prefix name may not be greater than 50 characters.',
            
            'photo.string' => 'The photo must be a string.',
            'photo.max' => 'The photo path may not be greater than 255 characters.',
        ];
    }
}