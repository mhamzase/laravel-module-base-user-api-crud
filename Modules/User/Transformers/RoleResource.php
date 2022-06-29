<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Arr;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $keys = ['id', 'name'];
        $role = Arr::only($this->resource->toArray(), $keys);

        $role['permissions'] = PermissionResource::collection($this->permissions);

        return $role;
    }
}

