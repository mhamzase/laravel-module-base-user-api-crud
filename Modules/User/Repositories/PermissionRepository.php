<?php


namespace Modules\User\Repositories;
use Modules\Base\Repositories\BaseRepository;
use Modules\User\Proxies\Entities\Permission;


class PermissionRepository extends BaseRepository
{
    public function model()
    {
        return Permission::class;
    }
}
