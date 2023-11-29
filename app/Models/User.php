<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Constants\Languages;
use App\Traits\AccessTrait;
use App\Traits\QueryFilterableTrait;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, AccessTrait, QueryFilterableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'login',
        'name',
        'lastname',
        'email',
        'contact_email',
        'password',
        'balance',
        'phone',
        'phone_code',
        'country_code',
        'avatar_path',
        'channel',
        'country',
        'region',
        'city',
        'token',
        'work_scope',
        'work_scope',
        'work_company',
        'work_division',
        'work_position',
        'education',
        'is_verified',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'config_json' => 'array'
    ];

    public function scopeCurrentAuthedUser(Builder $query)
    {
        return $query->where('user_id', Auth::id());
    }

    protected function lang(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? Languages::RU,
        );
    }

    protected function fullname(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->name . ' ' . $this->lastname),
        );
    }
}
