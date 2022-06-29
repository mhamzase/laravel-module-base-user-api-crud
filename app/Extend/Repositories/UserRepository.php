<?php


namespace App\Extend\Repositories;

use Illuminate\Support\Arr;

class UserRepository extends ProxyUserRepository
{
    public function fetchGetAll()
    {
        return $this->getModel()->all();
    }

    public function fetchRegisterUser($query, $data)
    {
        $userData = Arr::only($data, ['password', 'email', 'username']);
        $user = $this->create($userData);

        $userFields = Arr::only($data, ['age', 'gender','phone','address']);
        !empty($userFields['phone']) ? $data['phone'] : null;
        !empty($userFields['address']) ? $data['address'] : null;

        $user->fields()->create($userFields);

        return $user;
    }

    public function fetchFindUser($query,$id)
    {
        return $this->findOrFail($id);
    }

    public function fetchUpdateUser($query,$id,$data)
    {
        $user = $this->findOrFail($id);
        $user->update($data);

        $userFields = Arr::only($data, ['age', 'gender','phone','address']);
        $user->fields()->update($userFields);

        return $user;
    }

    public function fetchDeleteUser($query,$id){
        $user = $this->findOrFail($id);
        return $user->delete();
    }




}
