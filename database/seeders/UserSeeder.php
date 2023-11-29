<?php

namespace Database\Seeders;

use App\Constants\Roles;
use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'email' => 'client@client.com',
                'name' => 'client',
                'lastname' => 'client',
                'role' => Roles::REGISTERED,
            ],
            [
                'email' => 'admin@admin.com',
                'name' => 'admin',
                'lastname' => 'admin',
                'role' => Roles::ADMIN,
            ],
            [
                'email' => 'moderator@moderator.com',
                'name' => 'moderator',
                'lastname' => 'moderator',
                'role' => Roles::MODERATOR,
            ],
            [
                'email' => 'superadmin@superadmin.com',
                'name' => 'superadmin',
                'lastname' => 'superadmin',
                'role' => Roles::SUPERADMIN,
            ]
        ];

        foreach ($items as $item) {
            try {
                $user = User::updateOrCreate(['email' => $item['email']], [
                    'email' => $item['email'],
                    'name' => $item['name'],
                    'lastname' => $item['lastname'],
                    'password' => Hash::make('qwerty123456')
                ]);

                // Убираем дубликаты из role_user
                $user->detachRoles($item['role']);

                $user->attachRole($item['role']);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
