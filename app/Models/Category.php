<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['model', 'lowmodel', 'table', 'lowtable', 'pivot', 'fields'];
}
