<?php

namespace App\Traits;

use App\Constants\CacheKeys;
use App\Exceptions\AccessForbiddenException;
use App\Exceptions\BusinessLogicException;
use App\Exceptions\ValidationException;
use App\Models\AccessGroup;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Cache\CacheServiceFacade;
use Auth;
use LogicException;

trait AccessTrait
{

    // ------------------- RELATIONS -------------------
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    // ------------------- ATTACH -------------------
    public function attachPermission(Permission $permission): bool
    {

        $cacheKey = CacheKeys::userPermissionKey($this->id, $permission->name);

        $isPermissionExist = CacheServiceFacade::remember($cacheKey, config('cache.ttl'), function () use ($permission) {
            return $this->permissions()->wherePivot('permission_id', $permission->id)->wherePivot('user_id', $this->id)->wherePivotNull('access_group_id')->exists();
        });

        if ($isPermissionExist) {
            throw new BusinessLogicException('Duplicate permissions!');
        }

        $this->permissions()->attach($permission->id);

        CacheServiceFacade::remember($cacheKey, config('cache.ttl'), fn () => true);

        return true;
    }

    public function attachPermissionsForAccessGroups(Permission $permission, AccessGroup $accessGroup)
    {
        /*$cacheKey = "user:$this->id:permission_id:$permissionIds:access_group_id:$accessGroupId";
    $isPermissionExist = CacheServiceFacade::rememberForever($cacheKey, function () use ($permissionIds, $accessGroupId) {
    return $this->permissions()->wherePivotIn('permission_id', $permissionIds)->wherePivot('user_id', $this->id)->wherePivot('access_group_id', $accessGroupId)->exists();
    });

    if ($isPermissionExist) {
    throw new LogicException('Duplicate permissions!');
    }

    $this->permissions()->attach($permissionIds, ['access_group_id' => $accessGroupId]);

    CacheServiceFacade::rememberForever($cacheKey, fn() => true);

    return true;*/
    }

    public function attachRole($roleId)
    {
        $cacheKey = CacheKeys::userRoleKey($this->id, $roleId);
        $isRoleExist = CacheServiceFacade::remember($cacheKey, config('cache.ttl'), function () use ($roleId) {
            return $this->roles()->wherePivot('role_id', $roleId)->wherePivot('user_id', $this->id)->wherePivotNull('access_group_id')->exists();
        });

        if ($isRoleExist) {
            throw new BusinessLogicException('Duplicate roles!');
        }

        $this->roles()->attach($roleId);

        CacheServiceFacade::remember($cacheKey, config('cache.ttl'), fn () => true);

        return true;
    }

    public function attachRoleToAccessGroupAndFail($roleId, $accessGroupId)
    {

        $roleUser = $this->roles()->wherePivot('role_id', $roleId)->wherePivot('user_id', $this->id)->wherePivot('access_group_id', $accessGroupId)->first();

        if ($roleUser) {
            throw new ValidationException(__('Can not attach role, because provided role is already attached! Duplicated role: ') . $roleUser->name);
        }

        $this->roles()->attach($roleId, ['access_group_id' => $accessGroupId]);
        return true;
    }

    public function attachRoleToAccessGroup($roleId, $accessGroupId)
    {

        $roleUser = $this->roles()->wherePivot('role_id', $roleId)->wherePivot('user_id', $this->id)->wherePivot('access_group_id', $accessGroupId)->first();

        if ($roleUser) {
            return true;
        }

        $this->roles()->attach($roleId, ['access_group_id' => $accessGroupId]);
        return true;
    }

    public function attachRoleAndEventTicketToAccessGroup($roleId, $eventTicketId, $accessGroupId)
    {

        $roleUser = $this->roles()
            ->wherePivot('role_id', $roleId)
            ->wherePivot('user_id', $this->id)
            ->wherePivot('access_group_id', $accessGroupId)
            ->first();

        if ($roleUser) {
            return true;
        }

        $this->roles()->attach(
            $roleId,
            [
                'access_group_id' => $accessGroupId,
                'event_ticket_id' => $eventTicketId
            ]
        );

        CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($accessGroupId))
            ->forget(CacheKeys::rolesByUserIdKey($this->id));

        return true;
    }

    // ------------------- DETACH -------------------

    public function detachPermissions($permissionIds)
    {
        return $this->permissions()->detach($permissionIds);
    }

    public function detachPermissionsForAccessGroups($permissionIds, $accessGroupId)
    {
        return $this->permissions()->wherePivot('access_group_id', $accessGroupId)->detach($permissionIds);
    }

    public function detachRoles($roleIds)
    {
        return $this->roles()->detach($roleIds);
    }

    public function detachRolesForAccessGroups($roleIds, $accessGroupId)
    {
        return $this->roles()->wherePivot('access_group_id', $accessGroupId)->detach($roleIds);
    }

    public function detachRoleByEventTicket($roleId, $eventTicketId)
    {

        $this->roles()
            ->wherePivot('role_id')
            ->wherePivot('event_ticket_id', $eventTicketId)
            ->detach();

        return true;
    }

    // ------------------- HAS -------------------

    public function hasPermission($permissionName): bool
    {
        return !is_null(
            $this->permissions()
                ->where('name', $permissionName)
                ->wherePivotNull('access_group_id')
                ->first()
        );
    }

    public function hasPermissionFromRoles($permissionName): bool
    {
        return !is_null(
            $this->roles()
                ->join('permission_role', 'permission_role.role_id', 'roles.id')
                ->join('permissions', 'permission_role.permission_id', 'permissions.id')
                ->where('permissions.name', $permissionName)
                ->wherePivotNull('access_group_id')
                ->first()
        );
    }

    public function hasPermissionFromAnyRelations($permissionName): bool
    {
        return !is_null(
            $this->roles()
                ->join('permission_role', 'permission_role.role_id', 'roles.id')
                ->join('permissions as permissions_from_roles', 'permission_role.permission_id', 'permissions_from_roles.id')
                ->join('permission_user', 'role_user.id', 'permission_user.user_id')
                ->join('permissions as direct_permissions', 'permission_user.permission_id', 'direct_permissions.id')
                ->where('permissions_from_roles.name', $permissionName)
                ->orWhere('direct_permissions.name', $permissionName)
                ->first(['role_user.id'])
        );
    }

    public function hasPermissionFromAnyRelationsByAccessGroup($permissionName, $accessGroupId): bool
    {
        return !is_null(
            $this->roles()
                ->join('permission_role', 'permission_role.role_id', 'roles.id')
                ->join('permissions as permissions_from_roles', 'permission_role.permission_id', 'permissions_from_roles.id')
                ->join('permission_user', 'role_user.id', 'permission_user.user_id')
                ->join('permissions as direct_permissions', 'permission_user.permission_id', 'direct_permissions.id')
                ->where(function ($query) use ($permissionName) {
                    return $query->where('permissions_from_roles.name', $permissionName)
                        ->orWhere('direct_permissions.name', $permissionName);
                })
                ->where(function ($query) use ($accessGroupId) {
                    return $query->where('permission_user.access_group_id', $accessGroupId)
                        ->odWhere('pivot.access_group_id', $accessGroupId);
                })
                ->first(['role_user.id'])
        );
    }

    public function hasPermissionByRole($permissionName, $roleName)
    {
        return !is_null(
            $this->roles()
                ->join('permission_role', 'permission_role.role_id', 'roles.id')
                ->join('permissions', 'permission_role.permission_id', 'permissions.id')
                ->where('permissions.name', $permissionName)
                ->where('roles.name', $roleName)
                ->wherePivotNull('access_group_id')
                ->first()
        );
    }

    public function hasPermissionsWhereHasAccessGroups($permissionName): bool
    {
        return !is_null(
            $this->permissions()
                ->whereNotNull('access_group_id')
                ->where('permissions.name', $permissionName)
                ->first()
        );
    }

    public function hasPermissionsFromRolesWhereHasAccessGroups($permissions)
    {
        return !is_null(
            $this->roles()
                ->join('permission_role', 'permission_role.role_id', 'roles.id')
                ->join('permissions', 'permission_role.permission_id', 'permissions.id')
                ->whereNotNull('access_group_id')
                ->whereIn('permissions.name', $permissions)
                ->first()
        );
    }

    public function hasPermissionsByAccessGroups($permissions, $accessGroups)
    {
        return !is_null(
            $this->permissions()
                ->join('access_groups', 'permission_user.access_group_id', 'access_groups.id')
                ->whereIn('access_groups.name', $accessGroups)
                ->whereIn('permissions.name', $permissions)
                ->first()
        );
    }

    public function hasPermissionsFromRolesByAccessGroups($permissions, $accessGroups)
    {
        return !is_null(
            $this->roles()->join('permission_role', 'permission_role.role_id', 'roles.id')
                ->join('permissions', 'permission_role.permission_id', 'permissions.id')
                ->join('access_groups', 'role_user.access_group_id', 'access_groups.id')
                ->whereIn('access_groups.name', $accessGroups)
                ->whereIn('permissions.name', $permissions)
                ->first()
        );
    }

    public function hasPermissionsByRolesAndByAccessGroups($permissions, $roles, $accessGroups)
    {
        return !is_null(
            $this->roles()
                ->join('permission_role', 'permission_role.role_id', 'roles.id')
                ->join('permissions', 'permission_role.permission_id', 'permissions.id')
                ->join('access_groups', 'role_user.access_group_id', 'access_groups.id')
                ->whereIn('roles.name', $roles)
                ->whereIn('access_groups.name', $accessGroups)
                ->whereIn('permissions.name', $permissions)
                ->first()
        );
    }

    public function hasRolesByAccessGroupId($accessGroupId, array $roleIds)
    {
        $roles = CacheServiceFacade::get(CacheKeys::rolesByUserIdKey(Auth::id()));

        if (!$roles) {
            $roles = $this
                ->join('role_user', function ($join) use ($accessGroupId) {
                    $join
                        ->on('users.id', '=', 'role_user.user_id')
                        ->where('user_id', '=', Auth::id())
                        ->where('access_group_id', '=', $accessGroupId);
                })
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->get(['roles.*']);

            CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($accessGroupId))
                ->set(CacheKeys::rolesByUserIdKey(Auth::id()), $roles, config('cache.ttl'));
        }

        $hasRoles = !empty(array_intersect($roles->pluck('id')->toArray(), $roleIds));

        return $hasRoles;
    }

    public function hasRolesByAccessGroupIdOrFail($accessGroupId, array $roleIds)
    {
        if (!$this->hasRolesByAccessGroupId($accessGroupId, $roleIds)) {
            throw new AccessForbiddenException();
        }
        return true;
    }

    public function hasRolesByChatId($chatId, array $roleIds)
    {
        $roles = CacheServiceFacade::remember(CacheKeys::rolesByChatIdKey($chatId, Auth::id()), config('cache.ttl'), function () use ($chatId) {
            return $this
                ->join('chats', function ($join) use ($chatId) {
                    $join
                        ->where('chats.id', '=', $chatId);
                })
                ->join('event_sessions', 'event_sessions.id', '=', 'chats.event_session_id')
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
                ->join('role_user', function ($join) {
                    $join
                        ->on('role_user.user_id', '=', 'users.id')
                        ->on('role_user.access_group_id', '=', 'events.access_group_id')
                        ->where('role_user.user_id', '=', Auth::id());
                })
                ->pluck('role_user.role_id');
        });

        return !empty(array_intersect($roles->toArray(), $roleIds));
    }

    public function hasRolesByChatIdOrFail($chatId, array $roleIds)
    {
        if (!$this->hasRolesByChatId($chatId, $roleIds)) {
            throw new AccessForbiddenException();
        }
        return true;
    }
    public function hasRolesByEventSessionId($eventSessionId, array $roleIds)
    {
        $roles = CacheServiceFacade::remember(CacheKeys::rolesByEventSessionIdKey($eventSessionId, userId: Auth::id()), config('cache.ttl'), function () use ($eventSessionId) {
            return $this
                ->join('event_sessions', function ($join) use ($eventSessionId) {
                    $join
                        ->where('event_sessions.id', '=', $eventSessionId);
                })
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
                ->join('role_user', function ($join) {
                    $join
                        ->on('role_user.user_id', '=', 'users.id')
                        ->on('role_user.access_group_id', '=', 'events.access_group_id')
                        ->where('role_user.user_id', '=', Auth::id());
                })
                ->pluck('role_user.role_id');
        });

        return !empty(array_intersect($roles->toArray(), $roleIds));
    }

    public function hasRolesByEventSessionIdOrFail($eventSessionId, array $roleIds)
    {
        if (!$this->hasRolesByEventSessionId($eventSessionId, $roleIds)) {
            throw new AccessForbiddenException();
        }
        return true;
    }

    public function hasRolesByEventId($eventId, array $roleIds)
    {
        $roles = CacheServiceFacade::remember(CacheKeys::rolesByEventSessionIdKey($eventId, Auth::id()), config('cache.ttl'), function () use ($eventId) {
            return $this
                ->join('events', function ($join) use ($eventId) {
                    $join
                        ->where('events.id', '=', $eventId);
                })
                ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
                ->join('role_user', function ($join) {
                    $join
                        ->on('role_user.user_id', '=', 'users.id')
                        ->on('role_user.access_group_id', '=', 'events.access_group_id')
                        ->where('role_user.user_id', '=', Auth::id());
                })
                ->pluck('role_user.role_id');
        });

        return !empty(array_intersect($roles->toArray(), $roleIds));
    }

    public function hasRolesByEventIdOrFail($eventId, array $roleIds)
    {
        if (!$this->hasRolesByEventId($eventId, $roleIds)) {
            throw new AccessForbiddenException();
        }
        return true;
    }

    public function hasRolesByPollId($pollId, array $roleIds)
    {
        $roles = CacheServiceFacade::remember(CacheKeys::rolesByPollIdKey($pollId, Auth::id()), config('cache.ttl'), function () use ($pollId) {
            return $this
                ->join('polls', function ($join) use ($pollId) {
                    $join
                        ->where('polls.id', '=', $pollId);
                })
                ->join('event_sessions', 'event_sessions.id', '=', 'polls.event_session_id')
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
                ->join('role_user', function ($join) {
                    $join
                        ->on('role_user.user_id', '=', 'users.id')
                        ->on('role_user.access_group_id', '=', 'events.access_group_id')
                        ->where('role_user.user_id', '=', Auth::id());
                })
                ->pluck('role_user.role_id');
        });

        return !empty(array_intersect($roles->toArray(), $roleIds));
    }

    public function hasRolesByPollIdOrFail($pollId, array $roleIds)
    {
        if (!$this->hasRolesByPollId($pollId, $roleIds)) {
            throw new AccessForbiddenException();
        }
        return true;
    }

    public function hasRolesBySaleId($saleId, array $roleIds)
    {
        $roles = CacheServiceFacade::remember(CacheKeys::rolesBySaleIdKey($saleId, Auth::id()), config('cache.ttl'), function () use ($saleId) {
            return $this
                ->join('sales', function ($join) use ($saleId) {
                    $join
                        ->where('sales.id', '=', $saleId);
                })
                ->join('event_sessions', 'event_sessions.id', '=', 'sales.event_session_id')
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
                ->join('role_user', function ($join) {
                    $join
                        ->on('role_user.user_id', '=', 'users.id')
                        ->on('role_user.access_group_id', '=', 'events.access_group_id')
                        ->where('role_user.user_id', '=', Auth::id());
                })
                ->pluck('role_user.role_id');
        });

        return !empty(array_intersect($roles->toArray(), $roleIds));
    }

    public function hasRolesBySaleIdOrFail($saleId, array $roleIds)
    {
        if (!$this->hasRolesBySaleId($saleId, $roleIds)) {
            throw new AccessForbiddenException();
        }
        return true;
    }

    public function hasRolesByBanId($banId, array $roleIds)
    {
        $roles = CacheServiceFacade::remember(CacheKeys::rolesBySaleIdKey($banId, Auth::id()), config('cache.ttl'), function () use ($banId) {
            return $this
                ->join('bans', function ($join) use ($banId) {
                    $join
                        ->where('bans.id', '=', $banId);
                })
                ->join('events',  'events.id', '=', 'bans.event_id')
                ->join('access_groups', 'access_groups.id', '=', 'events.access_group_id')
                ->join('role_user', function ($join) {
                    $join
                        ->on('role_user.user_id', '=', 'users.id')
                        ->on('role_user.access_group_id', '=', 'events.access_group_id')
                        ->where('role_user.user_id', '=', Auth::id());
                })
                ->pluck('role_user.role_id');
        });

        return !empty(array_intersect($roles->toArray(), $roleIds));
    }

    public function hasRolesByBanIdOrFail($banId, array $roleIds)
    {
        if (!$this->hasRolesByBanId($banId, $roleIds)) {
            throw new AccessForbiddenException();
        }
        return true;
    }


    // ------------------- GET PERMISSIONS -------------------

    public function getPermissions()
    {
        return $this->permissions()->get();
    }

    public function getPermissionsFromRoles()
    {
        return $this->roles()
            ->join('permission_role', 'permission_role.role_id', 'roles.id')
            ->join('permissions', 'permission_role.permission_id', 'permissions.id')
            ->groupBy('permissions.id')
            ->get(['permissions.*']);
    }

    public function getPermissionsByRoles($roles)
    {
        return $this->roles()->join('permission_role', 'permission_role.role_id', 'roles.id')
            ->join('permissions', 'permission_role.permission_id', 'permissions.id')
            ->whereIn('roles.name', $roles)
            ->groupBy('permissions.id')
            ->get(['permissions.*']);
    }

    public function getPermissionsWhereHasAccessGroups()
    {
        return $this->permissions()->whereNotNull('access_group_id')->get();
    }

    public function getPermissionsFromRolesWhereHasAccessGroups()
    {
        return $this->roles()->join('permission_role', 'permission_role.role_id', 'roles.id')
            ->join('permissions', 'permission_role.permission_id', 'permissions.id')
            ->whereNotNull('access_group_id')
            ->groupBy('permissions.id')
            ->get(['permissions.*']);
    }

    public function getPermissionsByAccessGroups($accessGroups)
    {
        return $this->permissions()
            ->join('access_groups', 'permission_user.access_group_id', 'access_groups.id')
            ->whereIn('access_groups.name', $accessGroups)
            ->groupBy('permissions.id')
            ->get(['permissions.*']);
    }

    public function getPermissionsFromRolesByAccessGroups($accessGroups)
    {
        return $this->roles()->join('permission_role', 'permission_role.role_id', 'roles.id')
            ->join('permissions', 'permission_role.permission_id', 'permissions.id')
            ->join('access_groups', 'role_user.access_group_id', 'access_groups.id')
            ->whereIn('access_groups.name', $accessGroups)
            ->groupBy('permissions.id')
            ->get(['permissions.*']);
    }

    public function getPermissionsByRolesAndByAccessGroups($roles, $accessGroups)
    {
        return $this->roles()->join('permission_role', 'permission_role.role_id', 'roles.id')
            ->join('permissions', 'permission_role.permission_id', 'permissions.id')
            ->join('access_groups', 'role_user.access_group_id', 'access_groups.id')
            ->whereIn('roles.name', $roles)
            ->whereIn('access_groups.name', $accessGroups)
            ->groupBy('permissions.id')
            ->get(['permissions.*']);
    }

    // ------------------- GET ROLES -------------------

    public function getRoles()
    {
        return $this->roles()->get();
    }

    public function getRolesWhereHasAccessGroups()
    {
        return $this->roles()->whereNotNull('access_group_id')->get();
    }


    public function getRolesByAccessGroups($accessGroups)
    {
        return $this->roles()
            ->join('access_groups', 'role_user.access_group_id', 'access_groups.id')
            ->whereIn('access_groups.name', $accessGroups)
            ->groupBy('roles.id')
            ->get(['roles.*']);
    }

    public function getAccessGroups()
    {
        return array_unique([
            ...$this->roles()
                ->join('access_groups', 'role_user.access_group_id', 'access_groups.id')
                ->groupBy('access_groups.id')
                ->get(['access_groups.*']),

            ...$this->permissions()
                ->join('access_groups', 'permission_user.access_group_id', 'access_groups.id')
                ->groupBy('access_groups.id')
                ->get(['access_groups.*']),
        ]);
    }
}
