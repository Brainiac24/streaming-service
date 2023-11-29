<?php

namespace App\Repositories\Role;

use App\Constants\CacheKeys;
use App\Models\Role;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;
use Auth;

class RoleRepository extends BaseRepository
{
    public function __construct(public Role $role)
    {
        parent::__construct($role);
    }

    public function getRolesByCurrentUserAndAccessGroupId($accessGroupId)
    {
        $roles = CacheServiceFacade::get(CacheKeys::rolesByUserIdKey(Auth::id()));

        if (!$roles) {
            $roles = $this->role
                ->join('role_user', function ($join) use ($accessGroupId) {
                    $join
                        ->on('role_user.role_id', '=', 'roles.id')
                        ->where('user_id', '=', Auth::id())
                        ->where('access_group_id', '=', $accessGroupId);
                })
                ->get(['roles.*']);

            CacheServiceFacade::tags(CacheKeys::accessGroupIdTag($accessGroupId))
                ->set(CacheKeys::rolesByUserIdKey(Auth::id()), $roles, config('cache.ttl'));
        }

        return $roles;
    }
}
