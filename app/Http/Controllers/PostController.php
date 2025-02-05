<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Models\Post;

class PostController extends Controller
{
	public function index() {
		//dd ('Hello POst');
		return Post::all();
	}

	public function store() {
		$data = [
			'title' => fake()->sentence(),
			'content' => fake()->text(),
			'author' => fake()->name(),
			'image_path' => 'null'
		];
		return Post::create($data);
	}  
}
