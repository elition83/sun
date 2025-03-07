<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\Api\Post_tag\StoreRequest;
use App\Http\Requests\Api\Post_tag\UpdateRequest;
use App\Http\Resources\Post_tag\Post_tagResource;
use App\Services\Post_tagService;

use App\Http\Controllers\Controller;
use App\Models\Post_tag;
use Illuminate\Http\Request;

class Post_tagController extends Controller
{
    

    

    

    

    

    public function index()
    {
        return Post_tagResource::collection(Post_tag::all());
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $entity = Post_tagService::store($data);
        return new Post_tagResource($entity);
    }

    public function show(Post_tag $entity)
    {
        return new Post_tagResource($entity);
    }

    public function update(UpdateRequest $request, Post_tag $entity)
    {
        $data = $request->validated();
        $entity = Post_tagService::update($entity, $data);
        return new Post_tagResource($entity);
    }

    public function destroy(Post_tag $entity)
    {
        $id = $entity->id;
        $title = $entity->title ?? '';
        $entity->delete();

        return response([
            'message' => "Post_tag: $id ($title) успешно удалён",
        ], 200);
    }

}
