<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\Api\Comment\StoreRequest;
use App\Http\Requests\Api\Comment\UpdateRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Services\CommentService;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        return CommentResource::collection(Comment::all());
    }
    
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $entity = CommentService::store($data);
        return new CommentResource($entity);
    }
    
    public function show(Comment $entity)
    {
        return new CommentResource($entity);
    }
    
    public function update(UpdateRequest $request, Comment $entity)
    {
        $data = $request->validated();
        $entity = CommentService::update($entity, $data);
        return new CommentResource($entity);
    }
    
    public function destroy(Comment $entity)
    {
        $id = $entity->id;
        $title = $entity->title ?? '';
        $entity->delete();
    
        return response([
            'message' => "Comment: $id ($title) успешно удалён",
        ], 200);
    }
    
}
