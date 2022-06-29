<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Base\Traits\FilterCriteria;
use Modules\User\Proxies\Entities\PermissionAlias;

class Permission extends Model
{
    use FilterCriteria;

    protected $fillable = ['module_name', 'name', 'guard_name'];

    public function alias()
    {
        return $this->hasMany(PermissionAlias::class);
    }

}
