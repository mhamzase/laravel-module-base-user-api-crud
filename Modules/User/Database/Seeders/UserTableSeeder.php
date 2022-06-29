<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\User\Enums\UserType;
use Modules\User\Proxies\Repositories\RoleRepository;
use Modules\User\Proxies\Repositories\UserRepository;
use phpDocumentor\Reflection\Types\Self_;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $users = [
            ['username' => 'admin',      'token' => 'DBtDegeRB5ZbHtiZg8aM90HWkxvKEOekdPWdSuFgvKEBdYcVEXI2Yt0FKBQJEcZBKDf5zALIEO0d1dDE'],
            //['username' => 'seller',   'type_id' => '',    'token' => '82tDncqRD3ZbHtiZg8aM90HWkxvFVOfndPWdSuFgvJCUdYcVEXI2Yt0FKBQJEcZBKDf4zDZOGO0d0bPI'],
            //['username' => 'customer', 'type_id' => '',  'token' => '873qVPeBLGWWK2ynubng65I0NZCcgN3TPcwjOrow2TTZbH62jg9zfWVQ2TNCw1CYGXvsyMWZyglXwyTk'],
            //['username' => 'user',      'token' => 'TO6SXO6Sd03EZ6eKXWXBeiIpxLjX6mWtSlXgcbWduQIYQOEelm7XqDOSWo7FdBL2aSeShSTWmBqpgh1n']
        ];

        DB::transaction(function() use ($users)
        {
            foreach ($users as $data)
            {
                $roleName = $data['username'];
                $role = RoleRepository::create(['name' => $roleName, 'guard_name' => 'sanctum']);

                $userData = [
                    'username' => $data['username'],
                    'email'    =>  $data['username'] . '@test.com',
                    'password' => $data['username'] . '123',
                    'lang'     => App::getLocale()
                ];

                if(!empty($data['type_id']) && ($data['type_id'] == UserType::SELLER || $data['type_id'] == UserType::CUSTOMER) )
                {
                    $userData['type_id'] = $data['type_id'];
                    $userData = array_merge($userData, self::getFakePerson());
                    $userData = array_merge($userData, self::getFakeAddress());
                    $userData = array_merge($userData, self::getFakeStore());
                    $user = UserRepository::register($userData);
                }
                else
                {
                    $user = UserRepository::create($userData);
                    $user->assignRole($role);
                }

                $user->tokens()->create([
                    'name'  => 'site',
                    'token' => hash('sha256', $data['token']),
                    'abilities' => '*'
                ]);
            }
        });

    }

    public function moveCodeToEcommerceModule()
    {
        Model::unguard();

        $users = [
            ['username' => 'admin',      'token' => 'DBtDegeRB5ZbHtiZg8aM90HWkxvKEOekdPWdSuFgvKEBdYcVEXI2Yt0FKBQJEcZBKDf5zALIEO0d1dDE'],
            ['username' => 'seller',   'type_id' => 'seller',    'token' => '82tDncqRD3ZbHtiZg8aM90HWkxvFVOfndPWdSuFgvJCUdYcVEXI2Yt0FKBQJEcZBKDf4zDZOGO0d0bPI'],
            ['username' => 'customer', 'type_id' => 'customer',  'token' => '873qVPeBLGWWK2ynubng65I0NZCcgN3TPcwjOrow2TTZbH62jg9zfWVQ2TNCw1CYGXvsyMWZyglXwyTk'],
            ['username' => 'user',      'token' => 'TO6SXO6Sd03EZ6eKXWXBeiIpxLjX6mWtSlXgcbWduQIYQOEelm7XqDOSWo7FdBL2aSeShSTWmBqpgh1n']
        ];

        DB::transaction(function() use ($users)
        {
            foreach ($users as $data)
            {
                $roleName = $data['username'];
                $role = RoleRepository::create(['name' => $roleName, 'guard_name' => 'sanctum']);

                $userData = [
                    'username' => $data['username'],
                    'email'    =>  $data['username'] . '@test.com',
                    'password' => $data['username'] . '123',
                    'lang'     => App::getLocale()
                ];

                if(!empty($data['type_id']) && ($data['type_id'] == UserType::SELLER || $data['type_id'] == UserType::CUSTOMER) )
                {
                    $userData['type_id'] = $data['type_id'];
                    $userData = array_merge($userData, self::getFakePerson());
                    $userData = array_merge($userData, self::getFakeAddress());
                    $userData = array_merge($userData, self::getFakeStore());
                    $user = UserRepository::register($userData);
                }
                else
                {
                    $user = UserRepository::create($userData);
                    $user->assignRole($role);
                }

                $user->tokens()->create([
                    'name'  => 'site',
                    'token' => hash('sha256', $data['token']),
                    'abilities' => '*'
                ]);
            }
        });
    }

    public static function getFakeStore()
    {
        return ['store' => [
                'title'       => Str::random(10),
                'description' => Str::random(50),
                'tagline'     => Str::random(20),
                'category_id' => 1,
            ]
        ];
    }

    public static function getFakePerson()
    {
        return ['title' => 'Mr.', 'first_name' => Str::random(10), 'last_name' => Str::random(8), 'email' => Str::random(10).'@test.com'];
    }

    public static function getFakeAddress()
    {
        return [
            'addr1'     => Str::random('20'),
            'addr2'     => Str::random('10'),
            'country'   => 'United Kingdom',
            'state'     => Str::random(10),
            'city'      => 'London',
            'zip_code'  => random_int(20000, 50000),
        ];
    }
}
