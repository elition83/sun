<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CmdPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:post {cmd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
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
        } else {
            $this->info('Ошика в команде app:post ( all | create | {id поста} )');
        }
    }
}
