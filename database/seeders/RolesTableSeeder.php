<?php

namespace Database\Seeders;

use App\Constants\Roles;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => Roles::REGISTERED, 'name' => strtolower(Roles::REGISTERED_NAME), 'display_name' => Roles::REGISTERED_NAME],
            ['id' => Roles::ADMIN, 'name' => strtolower(Roles::ADMIN_NAME), 'display_name' => Roles::ADMIN_NAME],
            ['id' => Roles::MODERATOR, 'name' => strtolower(Roles::MODERATOR_NAME), 'display_name' => Roles::MODERATOR_NAME],
            ['id' => Roles::MEMBER, 'name' => strtolower(Roles::MEMBER_NAME), 'display_name' => Roles::MEMBER_NAME],
            ['id' => Roles::SUPERADMIN, 'name' => strtolower(Roles::SUPER_ADMIN_NAME), 'display_name' => Roles::SUPER_ADMIN_NAME],
        ];

        foreach ($items as $item) {
            try {
                Role::updateOrCreate(['id' => $item['id']], $item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
