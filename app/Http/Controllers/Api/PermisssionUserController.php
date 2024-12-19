<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class PermisssionUserController extends Controller
{
    public function __construct(private UserRepository $userRepository) {}

    public function syncPermissionOfUser(string $id, Request $request)
    {
        $this->userRepository->syncPermissions($id, $request->permissions);
    }
}
