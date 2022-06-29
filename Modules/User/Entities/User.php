<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Modules\Base\Traits\FilterCriteria;
use Modules\UserEntity\Proxies\Entities\PersonUser;
use Spatie\Permission\Traits\HasRoles;
use Modules\User\Traits\HasAnyPermission;


class User extends Authenticatable
{
    use HasApiTokens, HasAnyPermission, Notifiable, FilterCriteria;

    protected $searchAble = [
        'id'            => 'fixed',
        'username'      => 'free',
        'email'         => 'free',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'lang', 'status_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if(empty($user->lang)) $user->lang = self::_getDefaultLang();
        });
    }

    protected $guard_name = 'sanctum';

    protected function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * @return string
     */
    protected function getStatusAttribute()
    {
        return ($this->status_id) ? 'Active' : 'InActive';
    }

    public function providers()
    {
        return $this->hasMany(UserProvider::class);
    }

    /**
     * get lang from request or return default
     * @return mixed|string
     */
    public static function _getDefaultLang()
    {
        return App::getLocale();
    }

}
