<?php

namespace App\Services;

class CategoryService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function store(array $data): Category
    {
        return Category::create($data);
    }

    public static function update(Category $entity, array $data): Category
    {
        $entity->update($data);
        return $entity;
    }

}
