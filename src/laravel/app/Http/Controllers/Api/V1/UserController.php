<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\User\UserResource;

class UserController
{
    public function show()
    {
        return UserResource::make(auth()->user());
    }
}
