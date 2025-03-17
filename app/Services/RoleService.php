<?php

namespace App\Services;

class RoleService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function store(array $data): Role
    {
        return Role::create($data);
    }

    public static function update(Role $entity, array $data): Role
    {
        $entity->update($data);
        return $entity;
    }

}
