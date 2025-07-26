@extends('user.master')

@section('title', 'Flashcard')

@section('content')
    <style>
        /* Tổng thể Flashcard Wrapper */
        .flashcard-wrapper {
            perspective: 1200px;
            /* Tăng phối cảnh để hiệu ứng 3D sâu hơn */
            width: 100%;
            max-width: 680px;
            /* Tăng nhẹ độ rộng tối đa */
            margin: 0 auto;
            min-height: 300px;
            /* Đảm bảo chiều cao tối thiểu để nội dung không bị co lại quá nhiều */
            display: flex;
            /* Dùng flexbox để căn giữa nội dung thẻ */
            align-items: center;
            /* Căn giữa theo chiều dọc */
            justify-content: center;
            /* Căn giữa theo chiều ngang */
        }

        /* Lớp bao bọc phần lật của Flashcard */
        .flashcard-inner {
            position: relative;
            width: 100%;
            height: 100%;
            /* Đảm bảo inner full height của wrapper */
            min-height: 260px;
            /* Giữ lại min-height cho nội dung */
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            /* Easing function mượt mà hơn */
            transform-style: preserve-3d;
            cursor: pointer;
            border-radius: 1.25rem;
            /* Đồng bộ với mặt thẻ */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            /* Bóng đổ mạnh hơn, hiện đại hơn */
        }

        /* Hiệu ứng lật thẻ */
        .flashcard-flip {
            transform: rotateY(180deg);
        }

        /* Mặt trước và mặt sau của Flashcard */
        .flashcard-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            /* Quan trọng cho hiệu ứng 3D */
            background: linear-gradient(135deg, #ffffff, #f0f2f5);
            /* Nền gradient nhẹ nhàng, hiện đại */
            border-radius: 1.25rem;
            border: 1px solid #e0e0e0;
            /* Border tinh tế hơn */
            padding: 30px 28px;
            /* Tăng padding để nội dung có không gian thở */
            display: flex;
            flex-direction: column;
            /* Sắp xếp các phần tử con theo chiều dọc */
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            /* Kích thước chữ lớn hơn */
            font-weight: 600;
            /* Font weight mạnh mẽ hơn */
            color: #343a40;
            /* Màu chữ đậm hơn */
            text-align: center;
            overflow: hidden;
            /* Đảm bảo nội dung không tràn ra ngoài */
        }

        /* Mặt sau của Flashcard */
        .flashcard-back {
            transform: rotateY(180deg);
            /* Đảm bảo mặt sau cũng có background và shadow tương tự */
            background: linear-gradient(135deg, #f0f2f5, #ffffff);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* Nút Nghe trên Flashcard */
        .flashcard-face .play-audio {
            position: absolute;
            /* Đặt vị trí tuyệt đối để không ảnh hưởng bố cục text */
            top: 15px;
            /* Khoảng cách từ trên xuống */
            right: 15px;
            /* Khoảng cách từ phải sang */
            z-index: 2;
            /* Đảm bảo nút nằm trên các nội dung khác */
            font-size: 0.95rem;
            /* Kích thước chữ cho nút */
            padding: 8px 14px;
            /* Kích thước nút lớn hơn chút */
            border-radius: 25px;
            /* Nút bo tròn */
            background-color: rgba(255, 255, 255, 0.8);
            /* Nền hơi trong suốt */
            border: 1px solid #ced4da;
            color: #495057;
            transition: background-color 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .flashcard-face .play-audio:hover {
            background-color: #e9ecef;
            transform: translateY(-1px);
        }

        /* Vùng chứa văn bản trên Flashcard */
        .text-container {
            width: 100%;
            /* Loại bỏ padding-right cứng nhắc, để nút 'play-audio' không ảnh hưởng bố cục */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            /* Cho phép vùng text mở rộng */
            padding: 0 10px;
            /* Thêm padding ngang nhẹ */
        }

        /* Vùng chứa ảnh trên Flashcard */
        #flashcardImage {
            max-width: 150px;
            /* Tăng kích thước ảnh tối đa */
            max-height: 120px;
            /* Tăng chiều cao tối đa */
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 0.75rem;
            /* Bo tròn ảnh hơn */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            /* Bóng đổ rõ ràng hơn */
            margin-top: 15px;
            /* Khoảng cách trên nếu có ảnh */
            margin-bottom: 10px;
            /* Khoảng cách dưới nếu có ảnh */
        }

        /* Nội dung câu trả lời trên mặt sau */
        .flashcard-back .answer-content-wrapper {
            display: flex;
            flex-direction: column;
            /* Sắp xếp dọc nếu có ảnh bên dưới */
            align-items: center;
            justify-content: center;
            width: 100%;
            flex-grow: 1;
        }

        .flashcard-back #flashcardAnswerText {
            text-align: center;
            /* Căn giữa văn bản trả lời */
            margin-bottom: 15px;
            /* Khoảng cách dưới nếu có ảnh */
            flex-grow: 1;
            /* Cho phép phần trả lời mở rộng */
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
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

        .btn-audio {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 2;
            padding: 6px 10px;
            font-size: 0.9rem;
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
        <div class="flashcard-wrapper">
            <div id="flashcardInner" class="flashcard-inner">

                {{-- Mặt trước (Câu hỏi) --}}
                <div class="flashcard-face flashcard-front">
                    <div class="text-container" id="flashcardFront">
                        <p class="mb-0">Nhấn vào thẻ để xem câu trả lời</p>
                    </div>
                    <button class="btn btn-outline-secondary play-audio" data-from="question" title="Nghe câu hỏi">
                        <i class="fas fa-volume-up"></i> Nghe
                    </button>
                </div>

                {{-- Mặt sau (Câu trả lời + Ảnh) --}}
                <div class="flashcard-face flashcard-back">
                    <div class="answer-content-wrapper">
                        <div id="flashcardAnswerText" class="text-container">
                            <p class="mb-0">Đáp án sẽ hiển thị ở đây</p>
                        </div>
                        <img id="flashcardImage" class="rounded d-none" alt="Ảnh minh họa" />
                    </div>
                    <button class="btn btn-outline-secondary play-audio" data-from="answer" title="Nghe đáp án">
                        <i class="fas fa-volume-up"></i> Nghe
                    </button>
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
