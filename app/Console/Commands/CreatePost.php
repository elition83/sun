<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;

class CreatePost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Командля для создания рандомного поста';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $title = $this->ask('Введите заголовок поста');
        $content = $this->ask('Введите содержание поста');

        $post = Post::create([
            'title' => $title,
            'content' => $content,
            'author' => 'test',
            'image_path' => 'null'
        ]);

        $this->info("Пост успешно создан с ID: {$post->id}");
    }
}
