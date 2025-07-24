@extends('user.master')

@section('title', 'Flashcard')

@section('content')
    <style>
        .flashcard-wrapper {
            perspective: 1000px;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }

        .flashcard-inner {
            position: relative;
            width: 100%;
            min-height: 260px;
            transition: transform 0.8s;
            transform-style: preserve-3d;
            cursor: pointer;
        }

        .flashcard-flip {
            transform: rotateY(180deg);
        }

        .flashcard-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            background: linear-gradient(to bottom right, #f8f9fa, #e9ecef);
            border-radius: 1.25rem;
            border: 1px solid #ddd;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
            padding: 30px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 500;
            color: #333;
            flex-direction: column;
            text-align: center;
        }

        .flashcard-back {
            transform: rotateY(180deg);
        }

        .flashcard-back .answer-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: 16px;
        }

        #flashcardImage {
            width: 100px;
            height: auto;
            max-height: 100px;
            object-fit: contain;
            border-radius: 0.5rem;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }

        .nav-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-top: 30px;
        }

        .nav-controls button {
            padding: 12px 28px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            border: none;
            color: white;
            transition: 0.25s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .nav-controls button:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #6610f2, #0d6efd);
        }

        .nav-controls #flashcardCounter {
            font-weight: bold;
            font-size: 1.1rem;
            color: #555;
            min-width: 80px;
            text-align: center;
        }

        .dropdown-menu-flashcard {
            background-color: #0d1117;
            color: white;
            min-width: 220px;
            border-radius: 0.5rem;
        }

        .dropdown-item-flashcard {
            color: white;
            padding: 10px 15px;
        }

        .dropdown-item-flashcard:hover {
            background-color: #161b22;
            color: white;
        }

        .dropdown-toggle-flashcard {
            background-color: #0d1117;
            color: white;
            border: none;
            border-radius: 0.5rem;
        }

        .dropdown-toggle-flashcard:hover {
            background-color: #161b22;
        }

        .dropdown-divider-flashcard {
            border-top: 1px solid #30363d;
        }
    </style>

    <div class="container mt-4" style="min-height: 90vh;">
        {{-- Thanh điều hướng --}}
        <div class="d-flex align-items-center mb-3">
            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle-flashcard d-flex align-items-center rounded-3" type="button"
                    id="flashcardMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-clone text-primary"></i>
                    <span class="mx-2">Chọn chế độ học</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-flashcard shadow-lg rounded-3 border-0 mt-2"
                    aria-labelledby="flashcardMenu">
                    @php
                        $encodedIds = base64_encode(implode(',', $idsArray));
                    @endphp
                    <li><a class="dropdown-item dropdown-item-flashcard d-flex align-items-center gap-2"
                            href="{{ route('game.flashcard', ['ids' => $encodedIds]) }}">
                            <i class="fas fa-clone text-primary"></i> Flashcard</a></li>
                    <li><a class="dropdown-item dropdown-item-flashcard d-flex align-items-center gap-2"
                            href="{{ route('game.match', ['ids' => $encodedIds]) }}">
                            <i class="fas fa-sync-alt text-primary"></i> Tìm cặp</a></li>
                    @if (count($idsArray) > 3)
                        <li><a class="dropdown-item dropdown-item-flashcard d-flex align-items-center gap-2"
                                href="{{ route('game.study', ['ids' => $encodedIds]) }}">
                                <i class="fas fa-file-alt text-primary"></i> Học tập</a></li>
                    @endif
                    <li><a class="dropdown-item dropdown-item-flashcard d-flex align-items-center gap-2"
                            href="{{ route('game.fill_blank', ['ids' => $encodedIds]) }}">
                            <i class="fas fa-layer-group text-primary"></i> Điền chỗ trống</a></li>
                    <li><a class="dropdown-item dropdown-item-flashcard d-flex align-items-center gap-2"
                            href="{{ route('game.essay', ['ids' => $encodedIds]) }}"><i
                                class="fas fa-edit text-primary"></i> Tự luận</a></li>
                    <li>
                        <hr class="dropdown-divider-flashcard">
                    </li>
                    <li><a class="dropdown-item dropdown-item-flashcard d-flex align-items-center gap-2"
                            href="{{ route('user.dashboard') }}">
                            <i class="fas fa-home text-primary"></i> Trang chủ</a></li>
                </ul>
            </div>

            <button class="btn btn-primary d-flex align-items-center mx-2 rounded-3" onclick="window.location.reload();">
                Tải lại trò chơi
            </button>
        </div>

        {{-- Tiêu đề --}}
        <h2 class="fw-bold text-dark text-center mb-4">Flashcard</h2>

        {{-- Flashcard --}}
        <div class="flashcard-wrapper mb-4" onclick="flipFlashcard()">
            <div id="flashcardInner" class="flashcard-inner">
                <div class="flashcard-face flashcard-front" id="flashcardFront">Nhấn để xem câu trả lời</div>
                <div class="flashcard-face flashcard-back d-flex flex-row justify-content-between align-items-center gap-3">
                    <div id="flashcardAnswerText" class="flex-fill text-start">
                        Đáp án ở đây
                    </div>
                    <div style="flex-shrink: 0;">
                        <img id="flashcardImage" class="rounded shadow-sm d-none"
                            style="width: 120px; height: auto; max-height: 100px; object-fit: contain;" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Nút điều hướng --}}
        <div class="nav-controls d-flex justify-content-center align-items-center gap-3 mt-4">
            <button onclick="prevFlashcard()">
                <i class="fas fa-arrow-left"></i> Trước
            </button>
            <div id="flashcardCounter" class="px-3 fw-bold text-secondary" style="min-width: 70px;">
                1 / {{ count($flashcards) }}
            </div>
            <button onclick="nextFlashcard()">
                Tiếp <i class="fas fa-arrow-right"></i>
            </button>
        </div>

    </div>

    {{-- JavaScript --}}
    <script>
        window.flashcardData = [
            @foreach ($flashcards as $fc)
                @php
                    $question = $fc->question->content ?? 'Không có câu hỏi';
                    $answer = $fc->question->answers->first()->content ?? 'Không có đáp án';
                    $imagePath = optional($fc->question->images->first())->path;
                @endphp {
                    question: `{!! $question !!}`,
                    answer: `{!! $answer !!}`,
                    image: `{{ $imagePath ? asset('storage/' . $imagePath) : '' }}`
                },
            @endforeach
        ];
    </script>
    <script src="{{ asset('js/game/game_flashcard.js') }}"></script>

@endsection
