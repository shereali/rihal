<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|integer|exists:tenants,id',
            'name_bn' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|in:super_admin,admin,teacher,staff,student,user',
            'title' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required' => 'টেন্যান্ট প্রয়োজন।',
            'tenant_id.exists' => 'এই টেন্যান্টটি বিদ্যমান নয়।',
            'name_bn.required' => 'নাম (বাংলা) প্রয়োজন।',
            'email.required' => 'ইমেইল প্রয়োজন।',
            'email.unique' => 'এই ইমেইলটি আগে থেকেই ব্যবহার করা হয়েছে।',
            'password.required' => 'পাসওয়ার্ড প্রয়োজন।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষর হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড এবং পাসওয়ার্ড নিশ্চিতকরণ মিলছে না।',
        ];
    }
}
