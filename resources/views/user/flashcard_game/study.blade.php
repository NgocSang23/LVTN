@extends('user.master')

@section('title', 'Học tập flashcard')

@section('content')
    <style>
        .dropdown-menu-study {
            background-color: #0d1117;
            color: white;
            min-width: 220px;
        }

        .dropdown-item-study {
            color: white;
            padding: 10px 15px;
            transition: 0.2s;
        }

        .dropdown-item-study:hover {
            background-color: #161b22;
            color: white;
        }

        .dropdown-toggle-study {
            background-color: #0d1117;
            color: white;
            border: none;
        }

        .dropdown-toggle-study:hover {
            background-color: #161b22;
        }

        .dropdown-divider-study {
            border-top: 1px solid #30363d;
        }

        .content-area {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 0 1rem;
            }
        }

        .answer-button {
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
            white-space: normal;
            text-align: left;
            align-items: flex-start;
        }

        .answer-button:hover:not(:disabled) {
            background-color: #e0e0e0;
        }

        .answer-button.correct {
            border: 2px solid #28a745;
            color: #28a745;
            background-color: #e6ffe6;
        }

        .answer-button.incorrect {
            border: 2px solid #dc3545;
            color: #dc3545;
            background-color: #ffe6e6;
        }

        .answer-button.disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .goal-btn.active,
        .level-btn.active {
            background-color: #6c75ff !important;
            border-color: #6c75ff !important;
            color: white !important;
        }

        .answer-button.wrong-flash {
            animation: flashRed 0.4s;
        }

        @keyframes flashRed {
            0% {
                background-color: #ffcdd2;
            }

            100% {
                background-color: transparent;
            }
        }
    </style>

    <div class="container mt-4 d-flex flex-column" style="min-height: 90vh;">
        {{-- Thanh điều hướng và tiêu đề --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-dark dropdown-toggle dropdown-toggle-study d-flex align-items-center rounded-3"
                        type="button" id="flashcardMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-clone text-primary"></i>
                        <span class="mx-2">Chọn chế độ học</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-study shadow-lg rounded-3 border-0 mt-2"
                        aria-labelledby="flashcardMenu">
                        @php
                            $encodedIds = base64_encode(implode(',', $idsArray));
                        @endphp
                        <li><a href="{{ route('game.match', ['ids' => $encodedIds]) }}"
                                class="dropdown-item dropdown-item-study d-flex align-items-center gap-2"><i
                                    class="fas fa-sync-alt text-primary"></i> Tìm cặp</a></li>
                        <li><a href="{{ route('game.study', ['ids' => $encodedIds]) }}"
                                class="dropdown-item dropdown-item-study d-flex align-items-center gap-2"><i
                                    class="fas fa-file-alt text-primary"></i> Học tập</a></li>
                        <li><a href="{{ route('game.fill_blank', ['ids' => $encodedIds]) }}"
                                class="dropdown-item dropdown-item-study d-flex align-items-center gap-2"><i
                                    class="fas fa-file-alt text-primary"></i> Điền chỗ trống</a></li>
                        <li>
                            <hr class="dropdown-divider dropdown-divider-study">
                        </li>
                        <li><a class="dropdown-item dropdown-item-study d-flex align-items-center gap-2"
                                href="{{ route('user.dashboard') }}"><i class="fas fa-home text-primary"></i> Trang chủ</a>
                        </li>
                    </ul>
                </div>

                <button class="btn btn-primary d-flex align-items-center" onclick="window.location.reload();">
                    Tải lại trò chơi
                </button>
            </div>

            <button type="button" class="btn btn-outline-secondary rounded-circle" data-bs-toggle="modal"
                data-bs-target="#studyModal" title="Cài đặt chế độ học tập">
                <i class="bi bi-gear-fill"></i>
            </button>
        </div>

        {{-- Nội dung chính --}}
        <div class="content-area mt-3">
            <div class="position-relative mb-4" style="max-width: 700px; margin: auto;">
                <!-- Progress bar nền -->
                <div style="height: 10px; background-color: #dee2e6; border-radius: 5px;">
                    <div id="progressBar" style="width: 0%; height: 100%; background-color: #28a745; border-radius: 5px;">
                    </div>
                </div>

                <!-- Bubble hiển thị số câu hiện tại -->
                <div id="currentCardNumber"
                    class="position-absolute translate-middle bg-warning text-white d-flex align-items-center justify-content-center rounded-circle"
                    style="top: 50%; left: 0%; width: 32px; height: 32px; font-weight: bold; font-size: 0.85rem; box-shadow: 0 0 6px rgba(255, 193, 7, 0.8); transition: left 0.4s ease;">
                    0
                </div>

                <!-- Bubble hiển thị tổng số câu hỏi -->
                <div class="position-absolute translate-middle" style="right: -30px; top: 50%;">
                    <div id="totalCards"
                        class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-circle"
                        style="width: 32px; height: 32px; font-weight: 600; font-size: 0.9rem; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                        {{ count($quizData) }}
                    </div>
                </div>
            </div>

            <div id="flashcardContainer" class="bg-white text-dark border rounded-lg p-4 mb-4 shadow-sm"
                style="min-height: 350px;">

                {{-- Dữ liệu sẽ được render bởi JavaScript --}}
                <p class="text-center text-muted">Đang tải câu hỏi...</p>
            </div>

            <div
                class="max-w-6xl mx-auto w-full px-4 py-4 d-flex justify-content-between align-items-center text-black fw-semibold mt-3">
                <span id="instructionText" class="flex-grow-1 text-center mx-2">Nhấp vào câu trả lời để tiếp tục</span>
                <button id="nextButton" class="btn btn-primary px-4 py-2 mx-2" style="display: none;">Tiếp tục</button>
            </div>
            <div class="d-flex justify-content-center">
                <p id="countdownTimerDisplay" class="text-warning fw-bold mt-2"></p>
            </div>
        </div>

        <!-- Modal kết quả -->
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header border-0">
                        <h5 class="modal-title" id="resultModalLabel">Kết quả ôn luyện</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body text-center">
                        <h4 class="mb-3">🎉 Bạn đã hoàn thành!</h4>
                        <p class="fs-5">Số câu đúng: <span id="correctCount" class="fw-bold text-success">0</span> / <span
                                id="totalCount">0</span></p>
                        <p class="text-muted">Cố gắng ôn luyện nhiều hơn nhé!</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <a href="{{ route('user.dashboard') }}" class="btn btn-primary">Quay về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal chế độ -->
        <div class="modal fade" id="studyModal" tabindex="-1" aria-labelledby="studyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="background-color:#0B0D2B; border-radius:1rem;">
                    <div class="modal-body p-4">
                        <div class="d-flex flex-wrap justify-content-between" style="gap:1rem;">
                            <div style="flex-grow:1; padding-right:1.5rem; min-width:280px;">
                                <div class="d-flex justify-content-between align-items-center mb-4" style="width:100%;">
                                    <h2
                                        style="font-weight:600; font-size:0.875rem; line-height:1.25rem; letter-spacing:0.05em; text-transform:uppercase; color:#D9E0F7; max-width:80%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:0;">
                                        CHỦ ĐỀ:
                                        {{ optional($quizData[0]['question'] ?? null)['topic']['name'] ?? 'Không rõ chủ đề' }}
                                    </h2>
                                    <div class="d-flex justify-content-end">
                                        <button id="closeModalBtn" type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                            style="background-color:#4B6BFF; border:none; border-radius:9999px; padding:1rem 2rem; font-weight:600; font-size:1rem; display:flex; align-items:center; gap:0.5rem; color:#fff; cursor:pointer; transition:background-color 0.3s ease; white-space:nowrap;">Đóng</button>
                                    </div>
                                </div>
                                <h1
                                    style="font-weight:800; font-size:1.75rem; line-height:2.25rem; margin-bottom:2rem; color:#fff; width:100%;">
                                    Bạn muốn ôn luyện như thế nào?
                                </h1>
                                <form>
                                    <div class="mb-4">
                                        <label for="goal" class="d-flex align-items-center"
                                            style="font-weight:600; font-size:1rem; line-height:1.5rem; gap:0.5rem; margin-bottom:0.5rem; color:#fff;">
                                            Mục tiêu của bạn cho học phần này là gì?
                                            <i class="fas fa-info-circle" style="color:#A3A3A3; font-size:0.875rem;"></i>
                                        </label>
                                        <div class="d-flex flex-wrap gap-3">
                                            <button type="button" class="goal-btn"
                                                style="display:flex; align-items:center; gap:0.5rem; border:1px solid #4B4F7B; color:#D9E0F7; background-color:transparent; font-size:0.875rem; font-weight:400; padding:0.5rem 1.25rem; border-radius:0.5rem; white-space:nowrap; cursor:pointer; transition:background-color 0.3s ease;"
                                                onmouseover="this.style.backgroundColor='#1E2149'"
                                                onmouseout="this.style.backgroundColor='transparent'"
                                                data-value="exam_prep">
                                                Học nhồi nhét cho bài thi
                                                <span
                                                    style="width:24px; height:24px; border-radius:50%; background:#3B3F7D; color:#F9D923; font-size:1.125rem; line-height:1; display:flex; align-items:center; justify-content:center; user-select:none;">⚡</span>
                                                <span
                                                    style="width:24px; height:24px; border-radius:50%; background:#3B3F7D; color:#3B9FFF; font-size:1.125rem; line-height:1; display:flex; align-items:center; justify-content:center; user-select:none;">🕒</span>
                                            </button>
                                            <button type="button" class="goal-btn"
                                                style="display:flex; align-items:center; gap:0.5rem; border:1px solid #4B4F7B; color:#D9E0F7; background-color:transparent; font-size:0.875rem; font-weight:400; padding:0.5rem 1.25rem; border-radius:0.5rem; white-space:nowrap; cursor:pointer; transition:background-color 0.3s ease;"
                                                onmouseover="this.style.backgroundColor='#1E2149'"
                                                onmouseout="this.style.backgroundColor='transparent'"
                                                data-value="memorize_all">
                                                Ghi nhớ tất cả
                                                <span
                                                    style="position:relative; display:flex; align-items:center; justify-content:center; border-radius:0.375rem; background:#3B5FFF; color:#fff; font-size:1.125rem; width:24px; height:24px;">
                                                    <i class="fas fa-book"></i>
                                                    <span
                                                        style="position:absolute; top:0; right:0; width:6px; height:6px; background:#F9D923; border-radius:50%;"></span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="level" class="d-flex align-items-center"
                                            style="font-weight:600; font-size:1rem; line-height:1.5rem; gap:0.5rem; margin-bottom:0.5rem; color:#fff;">
                                            Chọn một cấp độ
                                            <i class="fas fa-info-circle" style="color:#A3A3A3; font-size:0.875rem;"></i>
                                        </label>
                                        <div class="d-flex flex-wrap gap-3">
                                            <button type="button" class="level-btn"
                                                style="display:flex; justify-content:space-between; align-items:center; border:1px solid #4B4F7B; color:#D9E0F7; background-color:transparent; font-size:0.875rem; font-weight:400; padding:0.5rem 1.25rem; border-radius:0.5rem; min-width:140px; white-space:nowrap; cursor:pointer; transition:background-color 0.3s ease;"
                                                onmouseover="this.style.backgroundColor='#1E2149'"
                                                onmouseout="this.style.backgroundColor='transparent'" data-value="easy">
                                                Dễ
                                                <span style="display:flex; gap:0.25rem; margin-left:0.5rem;">
                                                    <span
                                                        style="width:6px; height:16px; border-radius:9999px; background:#B15AFF;"></span>
                                                    <span
                                                        style="width:6px; height:20px; border-radius:9999px; background:#B15AFF;"></span>
                                                    <span
                                                        style="width:6px; height:24px; border-radius:9999px; background:#B15AFF;"></span>
                                                </span>
                                            </button>

                                            <button type="button" class="level-btn"
                                                style="display:flex; justify-content:space-between; align-items:center; border:1px solid #4B4F7B; color:#D9E0F7; background-color:transparent; font-size:0.875rem; font-weight:400; padding:0.5rem 1.25rem; border-radius:0.5rem; min-width:140px; white-space:nowrap; cursor:pointer; transition:background-color 0.3s ease;"
                                                onmouseover="this.style.backgroundColor='#1E2149'"
                                                onmouseout="this.style.backgroundColor='transparent'" data-value="medium">
                                                Trung bình
                                                <span style="display:flex; gap:0.25rem; margin-left:0.5rem;">
                                                    <span
                                                        style="width:6px; height:20px; border-radius:9999px; background:#B15AFF;"></span>
                                                    <span
                                                        style="width:6px; height:24px; border-radius:9999px; background:#B15AFF;"></span>
                                                    <span
                                                        style="width:6px; height:28px; border-radius:9999px; background:#B15AFF;"></span>
                                                </span>
                                            </button>

                                            <button type="button" class="level-btn"
                                                style="display:flex; justify-content:space-between; align-items:center; border:1px solid #4B4F7B; color:#D9E0F7; background-color:transparent; font-size:0.875rem; font-weight:400; padding:0.5rem 1.25rem; border-radius:0.5rem; min-width:140px; white-space:nowrap; cursor:pointer; transition:background-color 0.3s ease;"
                                                onmouseover="this.style.backgroundColor='#1E2149'"
                                                onmouseout="this.style.backgroundColor='transparent'" data-value="hard">
                                                Khó
                                                <span style="display:flex; gap:0.25rem; margin-left:0.5rem;">
                                                    <span
                                                        style="width:6px; height:24px; border-radius:9999px; background:#B15AFF;"></span>
                                                    <span
                                                        style="width:6px; height:28px; border-radius:9999px; background:#B15AFF;"></span>
                                                    <span
                                                        style="width:6px; height:32px; border-radius:9999px; background:#B15AFF;"></span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="d-flex justify-content-end mt-4 w-100">
                                <button type="button"
                                    style="background-color:#4B6BFF; border:none; border-radius:9999px; padding:1rem 2rem; font-weight:600; font-size:1rem; display:flex; align-items:center; gap:0.5rem; color:#fff; cursor:pointer; transition:background-color 0.3s ease; white-space:nowrap;"
                                    onmouseover="this.style.backgroundColor='#3B5FFF'"
                                    onmouseout="this.style.backgroundColor='#4B6BFF'" id="startStudy">
                                    Bắt đầu chế độ Học
                                    <i class="fas fa-arrow-right" style="font-size:1.25rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.quizData = @json($quizData);
    </script>

    <script src="{{ asset('js/game/game_study.js') }}"></script>
@endsection
