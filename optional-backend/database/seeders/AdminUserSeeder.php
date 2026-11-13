<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'firstname' => config('services.admin.firstname'),
            'lastname' => config('services.admin.lastname'),
            'email' => config('services.admin.email'),
            'password' => config('services.admin.password'),
        ];

        foreach ($attributes as $field => $value) {
            if (! is_string($value) || trim($value) === '') {
                throw new RuntimeException('Set ADMIN_'.strtoupper($field).' before running this seeder.');
            }
        }

        if (! filter_var($attributes['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('ADMIN_EMAIL must be a valid email address.');
        }

        if (strlen($attributes['password']) < 8) {
            throw new RuntimeException('ADMIN_PASSWORD must contain at least 8 characters.');
        }

        User::updateOrCreate(
            ['email' => $attributes['email']],
            [
                'firstname' => $attributes['firstname'],
                'lastname' => $attributes['lastname'],
                'password' => $attributes['password'],
                'role' => 'admin',
            ],
        );
    }
}
