<?php

namespace Modules\User\Entities;


use Modules\Base\Traits\FilterCriteria;
use \Spatie\Permission\Models\Role as SpatieRoleModel;

class Role extends SpatieRoleModel
{
    use FilterCriteria;

    protected $fillable = ['name', 'guard_name'];
}
