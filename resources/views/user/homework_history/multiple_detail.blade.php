@extends('user.master')

@section('title', 'Chi tiết bài kiểm tra')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Chi tiết bài kiểm tra: {{ $test->content }}</h2>
        <p><strong>Số câu đúng:</strong> {{ $history->correct_count }} / {{ $history->total_questions }}</p>
        <p><strong>Điểm:</strong> {{ $history->score }}</p>
        <p><strong>Thời gian làm:</strong> {{ $history->time_spent }}</p>

        @foreach ($questions as $questionId => $items)
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Câu hỏi:</strong> {{ $items[0]->question }}
                </div>
                <div class="card-body">
                    @foreach ($items as $opt)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" disabled {{ $opt->answer == 1 ? 'checked' : '' }}>
                            <label class="form-check-label {{ $opt->answer == 1 ? 'text-success' : '' }}">
                                {{ $opt->option }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <a href="{{ route('user.history_multiple_choice') }}" class="btn btn-secondary">Quay lại</a>
    </div>
@endsection
