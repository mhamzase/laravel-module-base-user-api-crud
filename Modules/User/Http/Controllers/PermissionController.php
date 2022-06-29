<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Http\Requests\PermissionRequest;
use Modules\User\Proxies\Repositories\PermissionRepository;
use Modules\User\Transformers\PermissionResource;

class PermissionController extends Controller
{
    /**
     * Display a listing o.f the resource.
     * @return Response
     */
    public function index()
    {
        return PermissionResource::collection(PermissionRepository::filter());
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(PermissionRequest $request)
    {
        return new PermissionResource(PermissionRepository::create($request->all()));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return new PermissionResource(PermissionRepository::findOrFail($id));
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(PermissionRequest $request, $id)
    {
        $permission = PermissionRepository::findOrFail($id);
        $permission->update($request->validated());

        return new PermissionResource($permission);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $permission  = PermissionRepository::findOrFail( $id );

        $permission->delete();

        return response()->json( [ 'message' => __('Permission deleted successfully') ], 200 );
    }
}
