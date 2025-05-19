<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Test;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('search');

        $defineCards = Card::with(['question.topic', 'user'])
            ->whereHas('question', fn($q) => $q->where('type', 'definition')
                ->whereHas('topic', fn($q2) => $q2->where('title', 'like', "%$keyword%")))
            ->get()
            ->groupBy(fn($card) => $card->question->topic->id)
            ->map(fn($group) => [
                'title' => $group->first()->question->topic->title,
                'url' => route('user.flashcard_define_essay', ['ids' => $group->pluck('id')->implode(',')]),
                'author' => $group->first()->user->name ?? 'Ẩn danh',
                'type' => 'Khái niệm',
            ])
            ->values();

        $essayCards = Card::with(['question.topic', 'user'])
            ->whereHas('question', fn($q) => $q->where('type', 'essay')
                ->whereHas('topic', fn($q2) => $q2->where('title', 'like', "%$keyword%")))
            ->get()
            ->groupBy(fn($card) => $card->question->topic->id)
            ->map(fn($group) => [
                'title' => $group->first()->question->topic->title,
                'url' => route('user.flashcard_define_essay', ['ids' => $group->pluck('id')->implode(',')]),
                'author' => $group->first()->user->name ?? 'Ẩn danh',
                'type' => 'Tự luận',
            ])
            ->values();

        $tests = Test::with(['questionNumbers.topic', 'user'])
            ->whereHas('questionNumbers.topic', fn($q) => $q->where('title', 'like', "%$keyword%"))
            ->get()
            ->map(fn($test) => [
                'title' => $test->questionNumbers->first()->topic->title ?? 'Không có',
                'url' => '#', // hoặc route mở modal
                'author' => $test->user->name ?? 'Ẩn danh',
                'type' => 'Bài kiểm tra',
            ]);

        $results = $defineCards->merge($essayCards)->merge($tests)->take(10);

        return response()->json($results);
    }

    public function show(Request $request)
    {
        $keyword = $request->input('search');

        $card_defines = Card::with(['question.topic', 'user'])
            ->whereHas('question', fn($q) => $q->where('type', 'definition')
                ->whereHas('topic', fn($q2) => $q2->where('title', 'like', "%$keyword%")))
            ->get()
            ->groupBy(fn($card) => $card->question->topic->id)
            ->map(fn($group) => [
                'card_ids' => $group->pluck('id')->toArray(),
                'first_card' => $group->first(),
            ])
            ->values();

        $card_essays = Card::with(['question.topic', 'user'])
            ->whereHas('question', fn($q) => $q->where('type', 'essay')
                ->whereHas('topic', fn($q2) => $q2->where('title', 'like', "%$keyword%")))
            ->get()
            ->groupBy(fn($card) => $card->question->topic->id)
            ->map(fn($group) => [
                'card_ids' => $group->pluck('id')->toArray(),
                'first_card' => $group->first(),
            ])
            ->values();

        $tests = Test::with(['questionNumbers.topic', 'user'])
            ->whereHas('questionNumbers.topic', fn($q) => $q->where('title', 'like', "%$keyword%"))
            ->get();

        return view('user.search', compact('card_defines', 'card_essays', 'tests'));
    }
}
