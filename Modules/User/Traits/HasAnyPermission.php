<?php

namespace Modules\User\Traits;

use Modules\User\Entities\PermissionAlias;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Traits\HasRoles;

trait HasAnyPermission
{
    protected $exclude = [
        'auth.getAuthUser', 'auth.logout'
    ];

    use \Spatie\Permission\Traits\HasRoles {
        hasPermissionTo as parentHasPermissionTo;
    }

    /**
     * @param $route
     * @param null $guardName
     * @return bool
     */
    public function hasAnyPermission($route, $guardName = null)
    {
        //by pass super admin
        if( ($superAdmin = config('user.super_admin_role')) && !empty($superAdmin) && request()->user()->hasRole(config('user.super_admin_role'))) return true;

        /* check if current route is in exclude list */
        if(in_array($route, $this->exclude)) return true;

        try
        {
            $isAllowed = ($this->parentHasPermissionTo($route, $guardName) || $this->hasPermissionToAlias($route));

        } catch (PermissionDoesNotExist $exception)
        {
            $isAllowed = $this->hasPermissionToAlias($route);
        }

        return $isAllowed;
    }

    public function hasPermissionToAlias($route)
    {
        $permissionIds   = collect($this->getAllPermissions()->toArray())->pluck('id');
        $permissionsList = PermissionAlias::whereIn('permission_id', $permissionIds)->get()->pluck('name')->toArray();
        return in_array($route, $permissionsList);
    }
}
