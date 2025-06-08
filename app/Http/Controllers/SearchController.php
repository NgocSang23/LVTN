<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Test;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Hiển thị kết quả tìm kiếm.
     */
    public function show(Request $request)
    {
        $keyword = mb_strtolower(trim($request->input('search')));

        if (empty($keyword)) {
            return back()->with('error', 'Vui lòng nhập từ khóa tìm kiếm.');
        }

        // Bước 1: Tìm chủ đề khớp chính xác theo tiêu đề
        $exactMatchTopics = Topic::where('title', 'LIKE', "%$keyword%")->get();

        if ($exactMatchTopics->isNotEmpty()) {
            $relatedTopicIds = $exactMatchTopics->pluck('id')->toArray();
            Log::info("🔍 Exact match tìm thấy: " . implode(', ', $relatedTopicIds));
        } else {
            // Bước 2: Tìm bằng BM25 + cosine similarity
            $embedding = $this->getEmbeddingFromText($keyword);
            if (empty($embedding)) {
                return back()->with('error', 'Không thể phân tích từ khóa.');
            }

            $queryTerms = $this->tokenize($keyword);
            $topics = Topic::select('id', 'title', 'embedding')->whereNotNull('title')->get();

            // Tính điểm BM25 trước, sau đó lọc bằng cosine similarity
            $bm25Ranked = collect($this->calculateBM25Scores($queryTerms, $topics))
                ->sortByDesc('score')
                ->take(30); // chỉ lấy top 30 để giảm số lượng tính similarity

            $relatedTopicIds = $bm25Ranked
                ->map(function ($item) use ($embedding) {
                    $topic = $item['topic'];
                    $topicEmbedding = is_string($topic->embedding)
                        ? json_decode($topic->embedding, true)
                        : ($topic->embedding ?? []);

                    $similarity = $this->cosineSimilarity($embedding, $topicEmbedding);
                    Log::info("🧠 Topic: {$topic->title} | Similarity: $similarity");

                    return ['id' => $topic->id, 'similarity' => $similarity];
                })
                ->filter(fn($item) => $item['similarity'] > 0.25) // tăng ngưỡng để tăng độ chính xác
                ->sortByDesc('similarity')
                ->take(10)
                ->pluck('id')
                ->toArray();

            if (empty($relatedTopicIds)) {
                return back()->with('error', 'Không tìm thấy chủ đề phù hợp.');
            }
        }

        // Lấy các dữ liệu liên quan
        $card_defines = $this->getCardsByType('definition', $relatedTopicIds);
        $card_essays = $this->getCardsByType('essay', $relatedTopicIds);

        $tests = Test::with(['questionNumbers.topic', 'user'])
            ->whereHas('questionNumbers.topic', fn($q) => $q->whereIn('id', $relatedTopicIds))
            ->get();

        return view('user.search', compact('card_defines', 'card_essays', 'tests'));
    }

    /**
     * Trích xuất cards theo loại (ví dụ: definition hoặc essay)
     */
    private function getCardsByType(string $type, array $topicIds)
    {
        return Card::with(['question.topic', 'user'])
            ->whereHas('question', fn($q) => $q->where('type', $type)->whereIn('topic_id', $topicIds))
            ->get()
            ->groupBy(fn($card) => $card->question->topic->id)
            ->map(fn($group) => [
                'card_ids' => $group->pluck('id')->toArray(),
                'first_card' => $group->first(),
            ])
            ->values();
    }

    /**
     * Tách chuỗi thành danh sách từ (token)
     */
    private function tokenize(string $text): array
    {
        return preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * Tính điểm BM25 cho từng topic dựa trên query
     */
    private function calculateBM25Scores(array $queryTerms, $topics): array
    {
        $k1 = 1.5;
        $b = 0.75;
        $N = count($topics);

        if ($N === 0) return [];

        // Tính độ dài trung bình
        $avgdl = collect($topics)->avg(fn($t) => count($this->tokenize($t->title)));

        // Tính document frequency cho mỗi từ khóa
        $docFreq = [];
        foreach ($queryTerms as $term) {
            $docFreq[$term] = $topics->filter(fn($t) => in_array($term, $this->tokenize($t->title)))->count();
        }

        return collect($topics)->map(function ($topic) use ($queryTerms, $docFreq, $N, $k1, $b, $avgdl) {
            $tokens = $this->tokenize($topic->title);
            $dl = count($tokens);
            $score = 0;

            foreach ($queryTerms as $term) {
                $tf = count(array_filter($tokens, fn($t) => $t === $term));
                if ($tf === 0 || empty($docFreq[$term])) continue;

                $df = $docFreq[$term];
                $idf = log((($N - $df + 0.5) / ($df + 0.5)) + 1);

                $score += $idf * ($tf * ($k1 + 1)) / ($tf + $k1 * (1 - $b + $b * $dl / $avgdl));
            }

            return ['topic' => $topic, 'score' => $score];
        })->toArray();
    }

    /**
     * Gửi request đến Flask để lấy embedding
     */
    private function getEmbeddingFromText(string $text): array
    {
        try {
            $response = Http::timeout(3)->post('http://localhost:5000/embed', ['text' => $text]);
            return $response->json()['embedding'] ?? [];
        } catch (\Exception $e) {
            Log::error('❌ Lỗi lấy embedding: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Tính độ tương đồng cosine giữa hai vector
     */
    private function cosineSimilarity(array $vecA, array $vecB): float
    {
        if (count($vecA) !== count($vecB) || empty($vecA)) return 0;

        $dot = array_sum(array_map(fn($a, $b) => $a * $b, $vecA, $vecB));
        $magA = sqrt(array_sum(array_map(fn($a) => $a ** 2, $vecA)));
        $magB = sqrt(array_sum(array_map(fn($b) => $b ** 2, $vecB)));

        return ($magA > 0 && $magB > 0) ? $dot / ($magA * $magB) : 0;
    }
}
