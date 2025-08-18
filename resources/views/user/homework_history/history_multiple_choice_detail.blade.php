{{-- @foreach ($test->MultipleQuestions as $question)
    <div class="card mb-3 border-0 shadow-sm rounded-4">
        <div class="card-header bg-light">
            <strong>Câu hỏi:</strong> {{ $question->question }}
        </div>
        <div class="card-body">
            @foreach ($question->options as $opt)
                <div class="form-check">
                    <input class="form-check-input" type="radio" disabled {{ $opt->is_correct ? 'checked' : '' }}>
                    <label class="form-check-label {{ $opt->is_correct ? 'text-success' : '' }}">
                        {{ $opt->option }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
@endforeach --}}
