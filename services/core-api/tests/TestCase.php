<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\ApiKey;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{

    protected function getApiAuthHeaders(array $apiKeyAttributes = []): array
    {
        $keyString = (string) Str::uuid();
        $defaultAttributes = [
            'name'      => 'Test API Key',
            'key'       => $keyString,
            'is_active' => true,
            'role'      => 'test_role'
        ];
        $attributesToCreate = array_merge($defaultAttributes, $apiKeyAttributes);
        $attributesToCreate['key'] = $keyString;
        ApiKey::create($attributesToCreate);
        return [
            'Authorization' => 'Bearer ' . $keyString,
            'Accept'        => 'application/json',
        ];
    }
}