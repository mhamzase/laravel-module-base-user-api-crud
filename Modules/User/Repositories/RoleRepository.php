<?php


namespace Modules\User\Repositories;


use Illuminate\Support\Facades\DB;
use Modules\Base\Repositories\BaseRepository;
use Modules\User\Entities\Role;
use Modules\User\Proxies\Repositories\PermissionRepository;

class RoleRepository extends BaseRepository
{
    public function model()
    {
        return Role::class;
    }

    public function fetchCreate($query, $data)
    {
        /* transaction handling */
        $role = DB::transaction(function () use ($data)
        {
            $role = $this->create($data);

            /* verify and assign multiple permissions to user */
            if(isset($data['permissions']) && sizeof($data['permissions']) )
            {
                $permissionIds = PermissionRepository::whereIn('id', $data['permissions'])->get()->pluck('id')->toArray();
                $role->givePermissionTo($permissionIds);
            }

            return $role;
        });

        return $role;
    }

    /**
     * @param $query
     * @param $data
     * @param $id
     */
    public function fetchUpdate($query, $data, $id)
    {
        /* transaction handling */
        $role = DB::transaction(function () use ($data, $id)
        {
            $role = $this->findOrFail($id);

            $role->update($data);

            /* verify and assign multiple permissions to user */
            if(isset($data['permissions']) && sizeof($data['permissions']) )
            {
                $permissionIds = PermissionRepository::whereIn('id', $data['permissions'])->get()->pluck('id')->toArray();
                $role->syncPermissions($permissionIds);
            }

            return $role;
        });

        return $role;
    }
}
