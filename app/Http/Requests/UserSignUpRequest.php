<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserSignUpRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', Password::default()],
            'profile_picture' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // Max: 5MB
            'about' => ['nullable', 'string'],
            'designation' => ['nullable', 'string']
        ];
    }
}
