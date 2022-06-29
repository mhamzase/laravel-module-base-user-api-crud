<?php

namespace App\Extend\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\UserField;

class User extends ProxyUser
{
    use HasFactory;

    protected $searchAble = [
        'id'            => 'fixed',
        'username'      => 'free',
        'email'         => 'free',
        'fields'        => [
            'age'          => 'free',
            'gender'          => 'free',
            'phone'          => 'free',
            'address'         => 'free',
        ],
    ];

    public function fields()
    {
        return $this->hasOne(UserField::class, 'user_id');
    }
}
