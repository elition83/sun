<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\Api\Post\StoreRequest;
use App\Http\Requests\Api\Post\UpdateRequest;
use App\Http\Resources\Post\PostResource;
use App\Services\PostService;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return PostResource::collection(Post::all());
    }
    
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $entity = PostService::store($data);
        return new PostResource($entity);
    }
    
    public function show(Post $entity)
    {
        return new PostResource($entity);
    }
    
    public function update(UpdateRequest $request, Post $entity)
    {
        $data = $request->validated();
        $entity = PostService::update($entity, $data);
        return new PostResource($entity);
    }
    
    public function destroy(Post $entity)
    {
        $id = $entity->id;
        $title = $entity->title ?? '';
        $entity->delete();
    
        return response([
            'message' => "Post: $id ($title) успешно удалён",
        ], 200);
    }
    
}
