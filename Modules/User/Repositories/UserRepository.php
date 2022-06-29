<?php


namespace Modules\User\Repositories;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Base\Repositories\BaseRepository;
use Modules\Entity\Proxies\Repositories\PersonRepository;
use Modules\Multistore\Proxies\Repositories\StoreRepository;

use Modules\User\Enums\UserType;
use Modules\User\Proxies\Entities\User;
use Modules\User\Proxies\Repositories\PermissionRepository;
use Modules\User\Proxies\Repositories\RoleRepository;
use Modules\User\Proxies\Repositories;

class UserRepository extends BaseRepository
{
    public function model()
    {
        return User::class;
    }

    /**
     * authenticate user via username/email and password
     * @param $query
     * @param $data
     * @return false
     */
    public function fetchAuthenticate($query, $data)
    {
        /* get user via username */
        $user = $this->where(['username' => $data['username']])->orWhere(['email' => $data['username']])->first();
        return (! $user || ! Hash::check($data['password'], $user->password)) ? false : $user;
    }

    /**
     * @param $query
     * @param $data
     * @return mixed
     */
    public function fetchCreate($query, $data)
    {
        /* transaction handling */
        return DB::transaction(function () use ($data)
        {
            $user = $this->create(Arr::only($data, ['username', 'email', 'password', 'lang', 'status_id']));
            $user->save();

            /* verify and assign multiple permissions to user */
            if(isset($data['permissions']) && sizeof($data['permissions']) )
            {
                $permissionIds = PermissionRepository::whereIn('id', $data['permissions'])->get()->pluck('id')->toArray();
                $user->givePermissionTo($permissionIds);
            }

            /* verify and assign multiple roles to user */
            if(isset($data['roles']) && sizeof($data['roles']) )
            {
                $roleIds = RoleRepository::whereIn('id', $data['roles'])->get()->pluck('id')->toArray();
                $user->assignRole($roleIds);
            }

            return $user;
        });

    }

    /**
     * @param $query
     * @param $data
     * @param $id
     * @return mixed
     */
    public function fetchUpdate($query, $data, $id)
    {
        return DB::transaction(function () use ($data, $id)
        {
            $user = $this->findOrFail($id);
            $user->update(Arr::only($data, ['username', 'email', 'lang', 'status_id']));

            if(!empty($data['password'])) $user['password'] = $data['password'];

            $user->update();

            /* verify, remove previous permissions and assign new ones, sync will do the job */
            if(isset($data['permissions']) && sizeof($data['permissions']) )
            {
                $permissionIds = PermissionRepository::whereIn('id', $data['permissions'])->get()->pluck('id')->toArray();
                $user->syncPermissions($permissionIds);
            }

            /* verify, remove previous roles and assign new ones, sync will do the job */
            if(isset($data['roles']) && sizeof($data['roles']) )
            {
                $roleIds = RoleRepository::whereIn('id', $data['roles'])->get()->pluck('id')->toArray();
                $user->syncRoles($roleIds);
            }

            return $user;
        });
    }

    /**
     * handle logged in user profile udpate
     * @param $query
     * @param $data
     * @return mixed
     */
    public function fetchUpdateUserInfo($query, $data)
    {
        $user = $this->findOrFail(request()->user()->id);
        $user->update($data);
        return $user;
    }

    /**
     * handle user registration
     * @param $query
     * @param $data
     * @return mixed
     */
    /*public function fetchRegister($query, $data)
    {
        return DB::transaction(function() use ($data)
        {
            $person = PersonRepository::create(
                Arr::only($data, ['title', 'middle_name', 'first_name', 'last_name', 'email', 'gender_id', 'phone1', 'phone2'])
            );

            $person->address()->create(Arr::only($data, ['addr1', 'addr2', 'city', 'state', 'zip_code', 'country']));

            $user = User::create(Arr::only($data, ['username', 'password', 'email']));

            $user->personUser()->create(['person_id' => $person->id]);

            //handle customer related discussion
            if(!empty($data['type_id']) && $data['type_id'] == UserType::CUSTOMER)
            {
                $person->customer()->create();
            }

            //handle seller related discussion
            if(!empty($data['type_id']) && $data['type_id'] == UserType::SELLER)
            {
                $storeData              = $data['store'];
                $storeData['owner_id']  = $user->id;
                $storeData['slug']      = Str::slug($storeData['title']);
                $storeData['status_id'] = 0;//disable by default
                $store = StoreRepository::create($storeData);
            }

            $roleId = RoleRepository::where('name', $data['type_id'])->get()->pluck('id')->toArray();
            //dd($roleId, $data['type_id']);
            if($roleId) $user->syncRoles($roleId);

            return $user;

        });
    }*/

    /**
     * @param $query
     * @param $data
     */
    public function fetchGetProfile($query, $data)
    {
        return $query->where('id', auth()->user()->id)->with(['personUser.person.address'])->first();
    }

    /**
     * @param $query
     * @param $data
     */
    public function fetchUpdateProfile($query, $data)
    {
        dd(auth()->user());
    }

    /**
     * @param $query
     * @param $data
     * @return bool
     */
    public function fetchChangePassword($query, $data)
    {
        $user = $this->findOrFail(Auth::user()->id);
        $user->password = $data['password'];
        $user->update();
        return true;
    }

}
