<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class UserProvider extends Model
{
    protected $fillable = [ 'provider_id', 'provider_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
