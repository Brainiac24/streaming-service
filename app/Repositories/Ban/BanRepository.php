<?php

namespace App\Repositories\Ban;

use App\Constants\CacheKeys;
use App\Models\Ban;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BanRepository extends BaseRepository
{
    public function __construct(public Ban $ban)
    {
        parent::__construct($ban);
    }

    public function findByEventIdForCurrentAuthedUser($eventId)
    {
        return CacheServiceFacade::remember(CacheKeys::banByEventIdAndUserId($eventId, Auth::id()), config('cache.ttl'), function () use ($eventId) {
            return (bool)$this->ban
                ->where('event_id', '=', $eventId)
                ->where('user_id', '=', Auth::id())
                ->first();
        });
    }

    public function list($eventId)
    {
        return $this->ban
            ->join('users', 'users.id', '=', 'bans.user_id')
            ->join('users as created_users', 'created_users.id', '=', 'bans.created_by')
            ->where('event_id', '=', $eventId)
            ->get([
                'bans.*',

                'users.name as user_name',
                'users.lastname as user_lastname',
                'users.email as user_email',
                'users.contact_email as user_contact_email',

                'created_users.name as created_user_name',
                'created_users.lastname as created_user_lastname',
                'created_users.email as created_user_email',
            ]);
    }

    public function findByIdForCurrentAuthedUser($banId): Model
    {
        return $this->ban->findOrFail($banId);
    }

    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        $ban = $this->createOrUpdate(
            $data,
            [
                'event_id' => $data['event_id'],
                'user_id' => $data['user_id']
            ]
        );

        CacheServiceFacade::set(
            CacheKeys::banByEventIdAndUserId($data['event_id'], $data['user_id']),
            true,
            config('cache.ttl')
        );

        return $this->ban
            ->join('users', 'users.id', '=', 'bans.user_id')
            ->join('users as created_users', 'created_users.id', '=', 'bans.created_by')
            ->where('bans.id', '=', $ban['id'])
            ->first([
                'bans.*',

                'users.name as user_name',
                'users.lastname as user_lastname',
                'users.email as user_email',

                'created_users.name as created_user_name',
                'created_users.lastname as created_user_lastname',
                'created_users.email as created_user_email',
            ]);
    }

    public function update(array $data, $banId)
    {
        $ban = $this->ban->findOrFail($banId);
        if (!$ban) {
            throw new ModelNotFoundException();
        }
        $ban->update($data);

        CacheServiceFacade::forget(CacheKeys::banByEventIdAndUserId($data['event_id'], $data['user_id']));

        return $this->ban
            ->join('users', 'users.id', '=', 'bans.user_id')
            ->join('users as created_users', 'created_users.id', '=', 'bans.created_by')
            ->where('bans.id', '=', $ban['id'])
            ->first([
                'bans.*',

                'users.name as user_name',
                'users.lastname as user_lastname',
                'users.email as user_email',

                'created_users.name as created_user_name',
                'created_users.lastname as created_user_lastname',
                'created_users.email as created_user_email',
            ]);
    }

    public function deleteByModel($ban)
    {
        CacheServiceFacade::forget(CacheKeys::banByEventIdAndUserId($ban['event_id'], $ban['user_id']));
        CacheServiceFacade::forget(CacheKeys::accessGroupByBanIdKey($ban['id']));

        return (bool)$ban->delete();
    }

    public function accessGroupIdByBanId($banId)
    {
        return CacheServiceFacade::remember(CacheKeys::accessGroupByBanIdKey($banId), config('cache.ttl'), function () use ($banId) {
            return $this->ban
                ->join('events',  'events.id', '=', 'bans.event_id')
                ->where('bans.id', '=', $banId)
                ->first(['events.access_group_id'])
                ->access_group_id;
        });
    }
}
