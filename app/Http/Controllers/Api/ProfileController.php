<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\Api\Profile\StoreRequest;
use App\Http\Requests\Api\Profile\UpdateRequest;
use App\Http\Resources\Profile\ProfileResource;
use App\Services\ProfileService;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        return ProfileResource::collection(Profile::all());
    }
    
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $entity = ProfileService::store($data);
        return new ProfileResource($entity);
    }
    
    public function show(Profile $entity)
    {
        return new ProfileResource($entity);
    }
    
    public function update(UpdateRequest $request, Profile $entity)
    {
        $data = $request->validated();
        $entity = ProfileService::update($entity, $data);
        return new ProfileResource($entity);
    }
    
    public function destroy(Profile $entity)
    {
        $id = $entity->id;
        $title = $entity->title ?? '';
        $entity->delete();
    
        return response([
            'message' => "Profile: $id ($title) успешно удалён",
        ], 200);
    }
    
}
