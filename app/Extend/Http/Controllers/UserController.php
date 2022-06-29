<?php

namespace App\Extend\Http\Controllers;

use Modules\User\Proxies\Http\Requests\UserRequest;
// use Modules\User\Proxies\Transformers\UserResource;
use Modules\User\Proxies\Repositories\UserRepository;
use App\Extend\Transformers\UserResource;

class UserController extends ProxyUserController
{
    public function index()
    {
        $users = UserRepository::getAll();

        return UserResource::collection($users);
    }

    public function store(UserRequest $request)
    {
        $user =  UserRepository::registerUser($request->validated());
        
        return new UserResource($user);
    }

    public function show($id)
    {
        $user = UserRepository::findUser($id);

        return new UserResource($user);
    }

    public function update(UserRequest $request, $id)
    {
        $user = UserRepository::updateUser($id, $request->validated());

        return new UserResource($user);
    }

    public function destroy($id)
    {
        UserRepository::deleteUser($id);

        return response()->json(['message' => 'User deleted']);
    }
}
