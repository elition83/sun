<?php

namespace App\Services;

class CommentService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function store(array $data): Comment
    {
        return Comment::create($data);
    }

    public static function update(Comment $entity, array $data): Comment
    {
        $entity->update($data);
        return $entity;
    }

}
