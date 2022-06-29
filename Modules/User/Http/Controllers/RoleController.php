<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\Role;
use Modules\User\Http\Requests\RoleRequest;
use Modules\User\Http\Requests\UserRequest;
use Modules\User\Proxies\Repositories\PermissionRepository;
use Modules\User\Proxies\Repositories\RoleRepository;
use Modules\User\Transformers\RoleResource;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\RefreshesPermissionCache;

class RoleController extends Controller
{
    use HasPermissions;
    use RefreshesPermissionCache;

    /**
     * Display a listing o.f the resource.
     * @return Response
     */
    public function index()
    {
        return RoleResource::collection(RoleRepository::filter());
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(RoleRequest $request)
    {
        $role = RoleRepository::create($request->validated());
        return new RoleResource($role);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return new RoleResource(RoleRepository::findOrFail($id));
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(RoleRequest $request, $id)
    {
        $role = RoleRepository::update($request->validated(), $id);
        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $role = RoleRepository::findOrFail( $id );
        $role->delete();

        return response()->json( [ 'message' => __('Role deleted successfully') ], 200 );
    }
}
