<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Base\Proxies\Entities\Term;
use Modules\Base\Transformers\TermResource;
use Modules\User\Entities\Permission;
use Modules\User\Proxies\Http\Requests\UserRequest;
use Modules\User\Proxies\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Proxies\Repositories\PermissionRepository;
use Modules\User\Proxies\Repositories\RoleRepository;
use Modules\User\Proxies\Repositories\UserRepository;
use Modules\User\Proxies\Transformers\UserResource;
use Modules\Base\Repositories\BaseRepository;


class UserController extends Controller
{
    /**
     * Display a listing o.f the resource.
     * @return Response
     */
    public function index()
    {
        return UserResource::collection(UserRepository::filter());
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(UserRequest $request)
    {
        $user = UserRepository::create($request->validated());
        return new UserResource($user);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return new UserResource(UserRepository::findOrFail($id));
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(UserRequest $request, $id)
    {
        $user = UserRepository::update($request->validated(), $id);
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = UserRepository::findOrFail( $id );
        $user->delete();

        return response()->json( [ 'message' => 'User deleted successfully' ], 200 );
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
