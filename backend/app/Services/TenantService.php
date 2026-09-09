<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TenantService
{
    public function create(array $data): Tenant
    {
        $slug = $this->generateSlug($data['name_bn']);

        $tenant = Tenant::create([
            'name_bn' => $data['name_bn'],
            'name_en' => $data['name_en'] ?? null,
            'slug' => $slug,
            'type' => $data['type'],
            'registration_number' => $data['registration_number'] ?? null,
            'established_year' => $data['established_year'] ?? null,
            'address_bn' => $data['address_bn'] ?? null,
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'principal_name' => $data['principal_name'] ?? null,
            'principal_email' => $data['principal_email'] ?? null,
            'subscription_tier' => $data['subscription_tier'],
            'subscription_status' => 'pending',
            'modules_enabled' => $data['modules_enabled'] ?? [],
            'settings' => [],
        ]);

        return $tenant;
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $updateData = [];

        if (isset($data['name_bn'])) {
            $updateData['name_bn'] = $data['name_bn'];
            if (!$tenant->name_en && isset($data['name_en'])) {
                $updateData['name_en'] = $data['name_en'];
            }
        }

        if (isset($data['name_en'])) {
            $updateData['name_en'] = $data['name_en'];
        }

        if (isset($data['type'])) {
            $updateData['type'] = $data['type'];
        }

        if (isset($data['registration_number'])) {
            $updateData['registration_number'] = $data['registration_number'];
        }

        if (isset($data['established_year'])) {
            $updateData['established_year'] = $data['established_year'];
        }

        if (isset($data['address_bn'])) {
            $updateData['address_bn'] = $data['address_bn'];
        }

        if (isset($data['city'])) {
            $updateData['city'] = $data['city'];
        }

        if (isset($data['district'])) {
            $updateData['district'] = $data['district'];
        }

        if (isset($data['contact_email'])) {
            $updateData['contact_email'] = $data['contact_email'];
        }

        if (isset($data['contact_phone'])) {
            $updateData['contact_phone'] = $data['contact_phone'];
        }

        if (isset($data['principal_name'])) {
            $updateData['principal_name'] = $data['principal_name'];
        }

        if (isset($data['principal_email'])) {
            $updateData['principal_email'] = $data['principal_email'];
        }

        if (isset($data['subscription_tier'])) {
            $updateData['subscription_tier'] = $data['subscription_tier'];
        }

        if (isset($data['modules_enabled'])) {
            $updateData['modules_enabled'] = $data['modules_enabled'];
        }

        $tenant->update($updateData);
        $tenant->loadMissing([]);

        return $tenant->fresh();
    }

    public function delete(Tenant $tenant): bool
    {
        return $tenant->delete();
    }

    private function generateSlug(string $name): string
    {
        $slug = Str::slug($name . '-' . time());
        return mb_substr($slug, 0, 60, 'UTF-8');
    }
}
