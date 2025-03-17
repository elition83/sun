<?php

namespace App\Services;

class PostService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function store(array $data): Post
    {
        return Post::create($data);
    }

    public static function update(Post $entity, array $data): Post
    {
        $entity->update($data);
        return $entity;
    }

}
