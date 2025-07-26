@extends('user.master')

@section('title', 'Tự luận Flashcard')

@section('content')
    <style>
        .essay-wrapper {
            max-width: 700px;
            margin: 0 auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease-in-out;
        }

        .essay-question {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1a1a;
            padding: 1rem;
            background-color: #f1f3f5;
            border-left: 5px solid #0d6efd;
            border-radius: 0.5rem;
        }

        .essay-textarea {
            width: 100%;
            min-height: 160px;
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 1rem;
            border: 1px solid #ced4da;
            font-size: 1rem;
            background: #fdfdfd;
            transition: 0.3s ease;
            resize: vertical;
        }

        .essay-textarea:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }

        .btn-check-progress {
            background: linear-gradient(135deg, #198754, #28a745);
            color: white;
            font-weight: 600;
            border-radius: 2rem;
            padding: 0.6rem 1.8rem;
            transition: 0.25s ease;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
        }

        .btn-check-progress:hover {
            background: linear-gradient(135deg, #28a745, #198754);
            transform: translateY(-2px);
        }

        .progress {
            height: 24px;
            border-radius: 9999px;
            overflow: hidden;
            background-color: #dee2e6;
        }

        .progress-bar {
            background: linear-gradient(90deg, #0d6efd, #6610f2);
            font-weight: 600;
            line-height: 24px;
        }

        .nav-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1.2rem;
            margin-top: 2rem;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .nav-controls button:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #6610f2, #0d6efd);
        }

        .nav-controls #questionCounter {
            font-weight: bold;
            font-size: 1.1rem;
            color: #444;
            min-width: 80px;
            text-align: center;
        }

        .dropdown-menu-essay {
            background-color: #0d1117;
            color: white;
            min-width: 220px;
            border-radius: 0.5rem;
        }

        .dropdown-item-essay {
            color: white;
            padding: 10px 15px;
        }

        .dropdown-item-essay:hover {
            background-color: #161b22;
            color: white;
        }

        .dropdown-toggle-essay {
            background-color: #0d1117;
            color: white;
            border: none;
            border-radius: 0.5rem;
        }

        .dropdown-toggle-essay:hover {
            background-color: #161b22;
        }

        .dropdown-divider-essay {
            border-top: 1px solid #30363d;
        }
    </style>

    <div class="container mt-4" style="min-height: 90vh;">
        {{-- Dropdown chế độ học --}}
        <div class="d-flex align-items-center mb-3">
            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle-essay d-flex align-items-center rounded-3" type="button"
                    id="essayMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-edit text-primary"></i>
                    <span class="mx-2">Chọn chế độ học</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-essay shadow-lg rounded-3 border-0 mt-2" aria-labelledby="essayMenu">
                    @php $encodedIds = base64_encode(implode(',', $idsArray)); @endphp
                    <li><a class="dropdown-item dropdown-item-essay d-flex align-items-center gap-2"
                            href="{{ route('game.flashcard', ['ids' => $encodedIds]) }}"><i
                                class="fas fa-clone text-primary"></i> Flashcard</a></li>
                    <li><a class="dropdown-item dropdown-item-essay d-flex align-items-center gap-2"
                            href="{{ route('game.match', ['ids' => $encodedIds]) }}"><i
                                class="fas fa-sync-alt text-primary"></i> Tìm cặp</a></li>
                    @if (isset($idsArray) && count($idsArray) > 3)
                        <li><a class="dropdown-item dropdown-item-essay d-flex align-items-center gap-2"
                                href="{{ route('game.study', ['ids' => $encodedIds]) }}"><i
                                    class="fas fa-file-alt text-primary"></i> Học tập</a></li>
                    @endif
                    <li><a class="dropdown-item dropdown-item-essay d-flex align-items-center gap-2"
                            href="{{ route('game.fill_blank', ['ids' => $encodedIds]) }}"><i
                                class="fas fa-layer-group text-primary"></i> Điền chỗ trống</a></li>
                    <li><a class="dropdown-item dropdown-item-essay d-flex align-items-center gap-2"
                            href="{{ route('game.essay', ['ids' => $encodedIds]) }}"><i
                                class="fas fa-edit text-primary"></i> Tự luận</a></li>
                    <li>
                        <hr class="dropdown-divider-essay">
                    </li>
                    <li><a class="dropdown-item dropdown-item-essay d-flex align-items-center gap-2"
                            href="{{ route('user.dashboard') }}"><i class="fas fa-home text-primary"></i> Trang chủ</a></li>
                </ul>
            </div>

            <button class="btn btn-primary d-flex align-items-center mx-2 rounded-3" onclick="window.location.reload();">
                Tải lại trò chơi
            </button>
        </div>

        {{-- Tiêu đề --}}
        <h2 class="fw-bold text-dark text-center mb-4">Bài tự luận</h2>

        {{-- Vùng câu hỏi --}}
        <div class="essay-wrapper" id="essayContainer">
            {{-- Câu hỏi --}}
            <div class="essay-question mb-3" id="essayQuestion">
                Đang tải...
            </div>

            {{-- Nhập đáp án --}}
            <textarea class="essay-textarea" id="essayAnswer" placeholder="Viết câu trả lời tại đây..."></textarea>

            {{-- Nút kiểm tra và thanh tiến độ --}}
            <div class="text-center mt-4">
                <button class="btn btn-check-progress" onclick="checkEssayProgress()">
                    <i class="fas fa-check-circle me-2"></i> Kiểm tra kết quả
                </button>

                <div class="progress mt-3 mx-auto" style="max-width: 400px;">
                    <div class="progress-bar text-white text-center" id="essayProgressBar" role="progressbar"
                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        0%
                    </div>
                </div>
            </div>
        </div>

        {{-- Điều hướng --}}
        <div class="nav-controls">
            <button onclick="prevEssay()"><i class="fas fa-arrow-left"></i> Trước</button>
            <div id="questionCounter">1 / {{ count($flashcards) }}</div>
            <button onclick="nextEssay()">Tiếp <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    {{-- Dữ liệu --}}
    <script>
        window.essayData = [
            @foreach ($flashcards as $fc)
                @php
                    $content = str_replace(["\r", "\n"], ['\r', '\n'], addslashes($fc->question->content ?? 'Không có câu hỏi'));
                @endphp
                    `{!! $content !!}`,
            @endforeach
        ];
    </script>

    {{-- JavaScript điều hướng --}}
    <script src="{{ asset('js/game/game_essay.js') }}"></script>
@endsection
