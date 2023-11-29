<?php

namespace App\Repositories\User;

use App\Constants\CacheKeys;
use App\Constants\Roles;
use App\Exceptions\User\CannotCreateUserException;
use App\Models\RoleUser;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Str;

class UserRepository extends BaseRepository
{
    public function __construct(public User $user)
    {
        parent::__construct($user);
    }


    public function findByEmail($email)
    {
        return $this->user->whereEmail($email)->first();
    }


    public function findByToken($token)
    {
        return $this->user->whereToken($token)->first();
    }

    public function findByEmailAndNotEmptyPassword($email)
    {
        return $this->user->whereEmail($email)->whereNotNull('password')->first();
    }

    public function findByEmailAndEmptyPassword($email)
    {
        return $this->user->whereEmail($email)->whereNull('password')->first();
    }


    public function createByEmail($email)
    {
        $user = $this->user->create([
            'email' => $email
        ]);

        if (!$user) {
            throw new CannotCreateUserException();
        }

        CacheServiceFacade::forget(CacheKeys::userEmailKey($user->email));
        CacheServiceFacade::forget(CacheKeys::userIdKey($user->id));
        CacheServiceFacade::set(CacheKeys::userEmailKey($user->email), $user);
        CacheServiceFacade::set(CacheKeys::userIdKey($user->id), $user);

        return $user;
    }

    public function create(array $data)
    {
        $user = $this->user->create([
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            //'password' => Hash::make($data['password']),
            'channel' => Str::lower(Str::random(16))
        ]);

        if (!$user) {
            throw new CannotCreateUserException();
        }

        CacheServiceFacade::forget(CacheKeys::userEmailKey($user->email));
        CacheServiceFacade::forget(CacheKeys::userIdKey($user->id));
        CacheServiceFacade::set(CacheKeys::userEmailKey($user->email), $user);
        CacheServiceFacade::set(CacheKeys::userIdKey($user->id), $user);

        return $user;
    }

    public function createGuest(array $data)
    {
        $user = $this->user->create([
            'token' => $data['token'],
            'channel' => Str::lower(Str::random(16))
        ]);

        if (!$user) {
            throw new CannotCreateUserException();
        }

        CacheServiceFacade::forget(CacheKeys::userTokenKey($user->token));
        CacheServiceFacade::forget(CacheKeys::userIdKey($user->id));
        CacheServiceFacade::set(CacheKeys::userTokenKey($user->token), $user);
        CacheServiceFacade::set(CacheKeys::userIdKey($user->id), $user);

        return $user;
    }


    public function update(array $data, $id)
    {
        $user = $this->model->findOrFail($id);
        $user->update($data);

        CacheServiceFacade::forget(CacheKeys::userEmailKey($user->email));
        CacheServiceFacade::forget(CacheKeys::userIdKey($user->id));
        CacheServiceFacade::set(CacheKeys::userEmailKey($user->email), $user);
        CacheServiceFacade::set(CacheKeys::userIdKey($user->id), $user);

        return $user;
    }


    public function delete($id)
    {
        $user = $this->model->findOrFail($id);

        CacheServiceFacade::forget(CacheKeys::userEmailKey($user->email));
        CacheServiceFacade::forget(CacheKeys::userIdKey($user->id));

        return $user->delete();
    }

    public function findAdminsAndModeratorsByAccessGroupId($accessGroupId)
    {
        return $this->model
            ->join('role_user', function ($join) use ($accessGroupId) {
                $join
                    ->on('role_user.user_id', '=', 'users.id')
                    ->where('role_user.access_group_id', '=', $accessGroupId)
                    ->where(function ($query) {
                        $query
                            ->orWhere('role_user.role_id', '=', Roles::ADMIN)
                            ->orWhere('role_user.role_id', '=', Roles::MODERATOR);
                    });
            })
            ->groupBy('users.id')
            ->get([
                'users.*'
            ]);
    }

    public function isSuperadmin($id){
        return RoleUser::where(["user_id" => $id, "role_id" => Roles::SUPERADMIN])->exists();
    }

    public function getVerifiedList(){

        $perPage = (int)Request::get('perPage', 50);
        $page = (int)Request::get('page', 1);
        $sortBy = Request::get('sortBy', 'id');
        $sortDir = Request::get('sortDir', false);
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        $verified = (int)Request::get('verified', '-1');
        $search = Request::get('search', false);

        $query = $this->user
            ->selectRaw(DB::getTablePrefix().'users.*, '.DB::getTablePrefix().'projects.id as project_id,
                    count('.DB::getTablePrefix().'events.id) as event_count,
                    count(DISTINCT '.DB::getTablePrefix().'projects.id) as project_count'
                )
            ->leftJoin('role_user', function($join) {
                $join->on('role_user.user_id', 'users.id');
                $join->where('role_user.role_id', Roles::ADMIN);
            })
            ->leftJoin('access_groups', 'access_groups.id', '=', 'role_user.access_group_id')
            ->leftJoin('events', 'events.access_group_id', '=', 'access_groups.id')
            ->leftJoin('projects', 'projects.user_id', '=', 'users.id');

        if($verified !== -1){
            $query = $query->whereIsVerified($verified);
        }

        if($search){
            $query = $query->where(function ($query) use ($search) {
                $query->where('users.id', $search)
                    ->orWhere('users.email', 'LIKE', $search.'%')
                    ->orWhere('users.name', 'LIKE', $search.'%')
                    ->orWhere('users.lastname', 'LIKE', $search.'%');
            });
        }

        $list = $query
            ->groupBy('users.id')
            ->orderBy($sortBy, $sortDir)
            ->paginate(perPage: $perPage, page: $page);

        return $list;
    }

    public function findByProjectId($projectId){
        return $this->model
            ->join('projects', function ($join) use ($projectId) {
                $join
                    ->on('projects.user_id', '=', 'users.id')
                    ->where('projects.id', $projectId);
            })
            ->get(['users.id','users.email','users.name','users.lastname'])
            ->first();
    }
}
