<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends FormRequest
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
            'type' => 'required|in:madrasa,school,college,university,organization',
            'registration_number' => 'nullable|string|max:100',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'address_bn' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'principal_name' => 'nullable|string|max:255',
            'principal_email' => 'nullable|email|max:255',
            'subscription_tier' => 'required|in:free,premium,enterprise',
            'modules_enabled' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name_bn.required' => 'নাম (বাংলা) প্রয়োজন।',
            'type.required' => 'ধরন প্রয়োজন।',
            'subscription_tier.required' => 'সাবস্ক্রিপশন লেভেল প্রয়োজন।',
            'established_year.integer' => 'প্রতিষ্ঠার বছর সংখ্যা হতে হবে।',
            'established_year.min' => 'প্রতিষ্ঠার বছর ১৯০০ বা তার পর হতে হবে।',
        ];
    }
}
