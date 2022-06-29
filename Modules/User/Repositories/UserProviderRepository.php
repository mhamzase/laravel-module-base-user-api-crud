<?php

namespace Modules\User\Repositories;

use Illuminate\Container\Container as Application;
use Modules\Base\Repositories\BaseRepository;
use Modules\User\Entities\UserProvider;

class UserProviderRepository extends BaseRepository
{
       /**
        * @return string
        */
       public function model()
       {
          return UserProvider::class;
       }
}
