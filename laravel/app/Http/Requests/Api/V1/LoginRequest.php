<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'ইমেইল প্রয়োজন।',
            'email.email' => 'ইমেইল ফরম্যাট সঠিক নয়।',
            'password.required' => 'পাসওয়ার্ড প্রয়োজন।',
        ];
    }
}
