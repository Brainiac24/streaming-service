<?php

namespace App\Http\Resources\User;

use App\Http\Resources\BaseJsonResource;
use Illuminate\Support\Facades\Auth;

class UserDataResource extends BaseJsonResource
{
    public function __construct($user)
    {
        $this->data = [
            'id' => $user->id,
            'login' => $user->login,
            'name' => $user->name,
            'lastname' => $user->lastname,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'contact_email' => $user->contact_email,
            'email_verified_at' => $user->email_verified_at,
            'is_verified' => $user->is_verified,
            'country_code' => $user->country_code,
            'phone_code' => $user->phone_code,
            'phone' => $user->phone,
            'channel' => $user->channel,
            'config_json' => $user->config_json,
            'balance' => round((float)$user->balance, 2),
            'lang' => $user->lang,
            'avatar_path' => $user->avatar_path,
            'country' => $user->country,
            'region' => $user->region,
            'city' => $user->city,
            'work_scope' => $user->work_scope,
            'work_company' => $user->work_company,
            'work_division' => $user->work_division,
            'work_position' => $user->work_position,
            'education' => $user->education,
            'is_recently_created' => $user->wasRecentlyCreated,
            //'roles' => $user->roles()->get(),
            //'permissions' => $user->permissions()->get(),
            //'getPermissions' => $user->getPermissions(),
            //'getPermissionsFromRoles' => $user->getPermissionsFromRoles(),
            //'getPermissionsByRoles' => $user->getPermissionsByRoles(['role-2']),
            //'getPermissionsWhereHasAccessGroups' => $user->getPermissionsWhereHasAccessGroups(),
            //'getPermissionsFromRolesWhereHasAccessGroups' => $user->getPermissionsFromRolesWhereHasAccessGroups(),
            //'getPermissionsByAccessGroups' => $user->getPermissionsByAccessGroups(['access-group-1']),
            //'getPermissionsFromRolesByAccessGroups' => $user->getPermissionsFromRolesByAccessGroups(['access-group-2']),
            //'getPermissionsByRolesAndByAccessGroups' => $user->getPermissionsByRolesAndByAccessGroups(['role-2'], ['access-group-1']),
            //'getAccessGroups' => $user->getAccessGroups(),
            //'getRolesByAccessGroups' => $user->getRolesByAccessGroups(['access-group-2']),
            //'attachPermissionsToAccessGroups' => $user->attachPermissionsToAccessGroups([1], 1)
        ];
    }
}
