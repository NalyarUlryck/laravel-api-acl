<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class PermisssionUserController extends Controller
{
    public function __construct(private UserRepository $userRepository) {}

    public function syncPermissionOfUser(string $id, Request $request)
    {
       $reponse = $this->userRepository->syncPermissions($id, $request->permissions);
       if (!$reponse) {
              return response()->json(['message' => 'Erro ao sincronizar permissões'], 404);
       }
         return response()->json(['message' => 'Permissões sincronizadas com sucesso'], 200);
    }

    public function getPermissionOfUser(string $id)
    {
        $permissions = $this->userRepository->getPermissions($id);
        if (!$permissions) {
            return response()->json(['message' => 'Permissões não encontradas'], 404);
        }
        return PermissionResource::collection($permissions);
    }
}
