<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\Api\Role\StoreRequest;
use App\Http\Requests\Api\Role\UpdateRequest;
use App\Http\Resources\Role\RoleResource;
use App\Services\RoleService;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    

    

    

    

    

    public function index()
    {
        return RoleResource::collection(Role::all());
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $entity = RoleService::store($data);
        return new RoleResource($entity);
    }

    public function show(Role $entity)
    {
        return new RoleResource($entity);
    }

    public function update(UpdateRequest $request, Role $entity)
    {
        $data = $request->validated();
        $entity = RoleService::update($entity, $data);
        return new RoleResource($entity);
    }

    public function destroy(Role $entity)
    {
        $id = $entity->id;
        $title = $entity->title ?? '';
        $entity->delete();

        return response([
            'message' => "Role: $id ($title) успешно удалён",
        ], 200);
    }

}
