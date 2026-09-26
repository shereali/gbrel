<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    /**
     * Signs the following API requests in as a new user with the given role.
     */
    protected function signInAs(string $role = 'admin', array $attributes = []): User
    {
        $user = User::create(array_merge([
            'name' => ucfirst($role).' user',
            'email' => $role.'-'.Str::random(6).'@example.test',
            'password' => bcrypt('secret-password'),
            'role' => $role,
        ], $attributes));

        $token = 'test-'.Str::random(20);
        Cache::put('gbrel_auth_token_'.$token, $user->id, 600);
        $this->withHeader('Authorization', 'Bearer '.$token);

        return $user;
    }
}
