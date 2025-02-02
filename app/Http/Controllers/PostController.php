<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Models\Post;

class PostController extends Controller
{
	public function index(){
		//dd ('Hello POst');
		return Post::all();
	}

	public function store() {
		$data = [
			'title' => 'title',
			'content' => 'content',
			'author'=>'author'
		];
		return Post::create($data);
	}  
}
