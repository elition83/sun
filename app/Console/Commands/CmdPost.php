<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CmdPost extends Command
{
    protected $signature = 'app:post {cmd} {count?}';
    protected $description = 'Command description';

    public function handle()
    {
        $cmd = $this->argument('cmd');
        if ($cmd == 'all') {
            $posts = Post::all();
            foreach ($posts as $post) {
                $this->info("ID: {$post->id} | Title: {$post->title} | Description: {$post->content}");
            }

        } elseif ($cmd == 'create') {
            $title = $this->ask('Введите заголовок поста');
            $content = $this->ask('Введите содержание поста');
            $post = Post::create([
                'title' => $title,
                'content' => $content,
                'author' => 'test',
                'image_path' => 'null'
            ]);
            $this->info("Пост успешно создан с ID: {$post->id} | Title: {$post->title}");
        } elseif (is_numeric($cmd)){
            
            try {
                $id = (int) $cmd;
                $post=Post::findOrFail($id);
                $this->info("Пост с ID: {$post->id} | Title: {$post->title} | Description {$post->content}");

            } catch (ModelNotFoundException $e) {
                $this->error("Пост с ID $id не найден!");
            }
        } elseif ($cmd == 'fake') {
            $count = $this->argument('count') ?? 1;
            if (!is_numeric($count) || (int) $count <= 0) {
                $this->error('Количество должно быть положительным числом.');
                return;
            } 
            $count = (int) $count;

            for ($i = 0; $i < $count; $i++) {
                $post = Post::create([
                    'title' => fake()->sentence(),
                    'content' => fake()->text(),
                    'author' => fake()->name(),
                    'image_path' => 'null'
                ]);
                $this->info("Пост успешно создан с ID: {$post->id} | Title: {$post->title}");
            }
        } else {
            $this->info('Ошика в команде app:post ( all | create | {id поста} )');
        }
    }
}
