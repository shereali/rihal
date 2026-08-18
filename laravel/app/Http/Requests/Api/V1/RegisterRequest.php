<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'tenant_id' => 'nullable|exists:tenants,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name_bn.required' => 'নাম (বাংলা) প্রয়োজন।',
            'email.required' => 'ইমেইল প্রয়োজন।',
            'email.email' => 'ইমেইল ফরম্যাট সঠিক নয়।',
            'email.unique' => 'এই ইমেইলটি আগে থেকেই ব্যবহার করা হয়েছে।',
            'password.required' => 'পাসওয়ার্ড প্রয়োজন।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষর হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড এবং পাসওয়ার্ড নিশ্চিতকরণ মিলছে না।',
            'phone.regex' => 'ফোন নম্বর সঠিক ফরম্যাটে হতে হবে (যেমন: +88017XXXXXXXX)।',
        ];
    }
}
