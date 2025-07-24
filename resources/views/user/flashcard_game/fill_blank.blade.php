@extends('user.master')

@section('title', 'Điền chỗ trống flashcard')

@section('content')
    <style>
        :root {
            --primary: #0d6efd;
            --dark-bg: #0d1117;
            --dark-hover: #161b22;
            --light-text: #f0f6fc;
            --danger: #dc3545;
            --success: #28a745;
            --warning: #ffc107;
        }

        .content-area {
            max-width: 700px;
            margin: 0 auto;
            padding: 1rem;
        }

        .submit-btn {
            font-weight: 600;
            font-size: 1rem;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            transition: background 0.3s ease;
        }

        input.fill-blank-input {
            display: inline-block;
            width: auto;
            min-width: 100px;
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            transition: border 0.3s ease;
        }

        input.fill-blank-input:focus {
            border-color: var(--primary);
            outline: none;
        }

        .dropdown-menu-fillblank {
            background-color: #0d1117;
            color: white;
            min-width: 220px;
        }

        .dropdown-item-fillblank {
            color: white;
            padding: 10px 15px;
            transition: 0.2s;
        }

        .dropdown-item-fillblank:hover {
            background-color: #161b22;
            color: white;
        }

        .dropdown-item-fillblank i {
            font-size: 1rem;
        }

        .dropdown-toggle-fillblank {
            background-color: #0d1117;
            color: white;
            border: none;
        }

        .dropdown-toggle-fillblank:hover {
            background-color: #161b22;
        }

        .dropdown-divider-fillblank {
            border-top: 1px solid #30363d;
        }

        #submitExamBtn[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .filled-answer-underline {
            text-decoration: underline;
            font-weight: bold;
        }

        .question-container .card-question {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
        }

        .list-group-item {
            border: none;
            padding: 1.2rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .list-group-item p {
            margin-bottom: 0.5rem;
        }

        .modal-content {
            border-radius: 16px;
        }

        .btn-rounded {
            border-radius: 50px !important;
            padding: 0.6rem 1.6rem;
        }

        .nav-controls {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .nav-controls button {
            padding: 12px 24px;
            min-width: 130px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            color: #fff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .nav-controls button:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #6f42c1, #0d6efd);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        }

        .nav-controls button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>

    <div class="container mt-4 d-flex flex-column" style="min-height: 90vh;">
        @php
            $encodedIds = isset($idsArray) && is_array($idsArray) ? base64_encode(implode(',', $idsArray)) : '';
        @endphp

        {{-- Thanh điều hướng và tiêu đề --}}
        <div class="d-flex align-items-center mb-3">
            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle-fillblank d-flex align-items-center rounded-3" type="button"
                    id="flashcardMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-clone text-primary"></i>
                    <span class="mx-2">Chọn chế độ học</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-fillblank shadow-lg rounded-3 border-0 mt-2"
                    aria-labelledby="flashcardMenu">
                    @php
                        $encodedIds = base64_encode(implode(',', $idsArray));
                    @endphp
                    <li>
                        <a class="dropdown-item dropdown-item-fillblank d-flex align-items-center gap-2"
                            href="{{ route('game.flashcard', ['ids' => $encodedIds]) }}">
                            <i class="fas fa-clone text-primary"></i> Flashcard
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item dropdown-item-fillblank d-flex align-items-center gap-2"
                            href="{{ route('game.match', ['ids' => $encodedIds]) }}">
                            <i class="fas fa-sync-alt text-primary"></i> Tìm cặp
                        </a>
                    </li>
                    @if (isset($idsArray) && count($idsArray) > 3)
                        <li>
                            <a class="dropdown-item dropdown-item-fillblank d-flex align-items-center gap-2"
                                href="{{ route('game.study', ['ids' => $encodedIds]) }}">
                                <i class="fas fa-file-alt text-primary"></i> Học tập
                            </a>
                        </li>
                    @endif
                    <li>
                        <a class="dropdown-item dropdown-item-fillblank d-flex align-items-center gap-2"
                            href="{{ route('game.fill_blank', ['ids' => $encodedIds]) }}">
                            <i class="fas fa-layer-group text-primary"></i> Điền chỗ trống
                        </a>
                    </li>
                    <li><a class="dropdown-item dropdown-item-fillblank d-flex align-items-center gap-2"
                            href="{{ route('game.essay', ['ids' => $encodedIds]) }}"><i
                                class="fas fa-edit text-primary"></i> Tự luận</a></li>
                    <li>
                        <hr class="dropdown-divider-fillblank">
                    </li>
                    <li>
                        <a class="dropdown-item dropdown-item-fillblank d-flex align-items-center gap-2"
                            href="{{ route('user.dashboard') }}">
                            <i class="fas fa-home text-primary"></i> Trang chủ
                        </a>
                    </li>
                </ul>
            </div>
            <button class="btn btn-primary d-flex align-items-center mx-2" onclick="window.location.reload();">
                Tải lại trò chơi
            </button>
        </div>

        <h2 class="fw-bold text-dark text-center mb-4">Bài kiểm tra điền chỗ trống</h2>

        <div class="content-area mt-3" id="quizContainer">
        </div>

        {{-- Nút Gửi bài kiểm tra (hiện ra khi đã hoàn thành tất cả câu hỏi) --}}
        <div class="text-end mt-4 mb-4" id="submitButtonWrapper" style="display:none;">
            <button id="submitExamBtn" class="btn btn-primary submit-btn">
                <i class="fas fa-paper-plane me-2"></i> Gửi bài kiểm tra
            </button>
        </div>
    </div>

    {{-- Modal xác nhận nộp bài (đã đổi tên và chức năng rõ ràng hơn) --}}
    <div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white rounded-4">
                <div class="modal-body text-center py-4">
                    <h5 class="fw-bold mb-2" id="confirmSubmitModalLabel">Xác nhận nộp bài?</h5>
                    <p class="mb-4">Bạn chắc chắn muốn gửi bài kiểm tra?</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">
                            Hủy
                        </button>
                        <button type="button" id="finalSubmitBtn" class="btn btn-primary rounded-pill px-4">
                            Gửi bài kiểm tra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal kết quả bài kiểm tra --}}
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content bg-dark text-white rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="resultModalLabel">📊 Kết quả bài kiểm tra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="resultBody">
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.quizData = @json($quizData);
    </script>

    <script src="{{ asset('js/game/game_fillblank.js') }}"></script>
@endsection
