<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\Api\Category\StoreRequest;
use App\Http\Requests\Api\Category\UpdateRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Services\CategoryService;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }
    
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $entity = CategoryService::store($data);
        return new CategoryResource($entity);
    }
    
    public function show(Category $entity)
    {
        return new CategoryResource($entity);
    }
    
    public function update(UpdateRequest $request, Category $entity)
    {
        $data = $request->validated();
        $entity = CategoryService::update($entity, $data);
        return new CategoryResource($entity);
    }
    
    public function destroy(Category $entity)
    {
        $id = $entity->id;
        $title = $entity->title ?? '';
        $entity->delete();
    
        return response([
            'message' => "Category: $id ($title) успешно удалён",
        ], 200);
    }
    
}
