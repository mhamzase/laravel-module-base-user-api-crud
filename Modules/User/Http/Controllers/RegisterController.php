<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Entity\Proxies\Repositories\PersonRepository;
use Modules\User\Http\Requests\RegisterRequest;
use Modules\User\Proxies\Repositories\UserRepository;

class RegisterController extends Controller
{
    /**
     * @param RegisterRequest $request
     * @return mixed
     */
    public function register(RegisterRequest $request)
    {
        $user = UserRepository::register($request->validated());
        dd($user);
    }
}
