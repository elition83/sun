<?php

namespace App\Services;

use App\Models\Profile;

class ProfileService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public static function store(array $data): Profile
    {
        return Profile::create($data);
    }

    public static function update(Profile $entity, array $data): Profile
    {
        $entity->update($data);
        return $entity;
    }

}
