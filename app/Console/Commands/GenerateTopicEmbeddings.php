<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateTopicEmbeddings extends Command
{
    protected $signature = 'embedding:generate-topics';
    protected $description = 'Generate embeddings for all topic titles';

    public function handle()
    {
        $topics = Topic::all();
        foreach ($topics as $topic) {
            $response = Http::post('http://localhost:5000/embed', [
                'text' => $topic->title,
            ]);
            $embedding = $response->json()['embedding'] ?? null;

            if ($embedding) {
                $topic->embedding = $embedding;
                $topic->save();
                $this->info("✅ Embedded: {$topic->title}");
            } else {
                $this->warn("⚠️ Failed: {$topic->title}");
            }
        }

        $this->info('✅ Done generating topic embeddings!');
    }
}
