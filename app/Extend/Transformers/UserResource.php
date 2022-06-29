<?php

namespace App\Extend\Transformers;

use GuzzleHttp\Handler\Proxy;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Transformers\UserResource as BaseUserResource;

class UserResource extends ProxyUserResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = new BaseUserResource($this);

        $fields = [
                'age' => $this->fields->age,
                'phone' => $this->fields->phone,
                'address' => $this->fields->address,
                'gender' => $this->fields->gender,
        ];

        return [
            'user' => collect($user)->merge(['fields' => $fields]),
        ];
    }
}
