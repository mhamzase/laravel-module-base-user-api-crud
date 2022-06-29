<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class PermissionAlias extends Model
{
    protected $fillable = ['module_name', 'permission_id', 'name'];
}
