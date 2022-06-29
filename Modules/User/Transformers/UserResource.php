<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Modules\User\Transformers\RoleResource;


class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'email'         => $this->email,
            'status_id'     => $this->status_id,
            'status'        => $this->status,
            'permissions'   => $this->when(isset($this->permissions), function(){
                return PermissionResource::collection($this->permissions);
            }),
            'roles' => $this->when(isset($this->roles), function(){
                return RoleResource::collection($this->roles);
            })
            /*,'permissionIds' => $this->when($this->permissions, function(){
                return $this->permissions->pluck('id')->toArray();
            }),
            'roleIds' => $this->when($this->roles, function(){
                return $this->roles->pluck('id')->toArray();
            }),*/
        ];


    }
}
