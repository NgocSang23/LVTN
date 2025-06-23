@extends('user.master')

@section('title', 'Học tập flashcard')

@section('content')
    <style>
        .dropdown-menu-check {
            background-color: #0d1117;
            color: white;
            min-width: 220px;
        }

        .dropdown-item-check {
            color: white;
            padding: 10px 15px;
            transition: 0.2s;
        }

        .dropdown-item-check:hover {
            background-color: #161b22;
            color: white;
        }

        .dropdown-toggle-check {
            background-color: #0d1117;
            color: white;
            border: none;
        }

        .dropdown-toggle-check:hover {
            background-color: #161b22;
        }

        .dropdown-divider-check {
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
    </style>

    <div class="container mt-4 d-flex flex-column" style="min-height: 90vh;">
        {{-- Thanh điều hướng --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                {{-- Dropdown --}}
                <div class="dropdown">
                    <button class="btn dropdown-toggle-check rounded-3 d-flex align-items-center" type="button"
                        id="flashcardMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-clone text-primary"></i>
                        <span class="mx-2">Chọn chế độ học</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-check shadow-lg rounded-3 border-0 mt-2"
                        aria-labelledby="flashcardMenu">
                        <li><a class="dropdown-item dropdown-item-check d-flex align-items-center gap-2"><i
                                    class="fas fa-sync-alt text-primary"></i> Tìm cặp</a></li>
                        <li><a class="dropdown-item dropdown-item-check d-flex align-items-center gap-2"><i
                                    class="fas fa-file-alt text-primary"></i> Học tập</a></li>
                        <li><a class="dropdown-item dropdown-item-check d-flex align-items-center gap-2"><i
                                    class="fas fa-file-alt text-primary"></i> Kiểm tra</a></li>
                        <li>
                            <hr class="dropdown-divider dropdown-divider-check">
                        </li>
                        <li><a class="dropdown-item dropdown-item-check d-flex align-items-center gap-2"
                                href="{{ route('user.dashboard') }}"><i class="fas fa-home text-primary"></i> Trang chủ</a>
                        </li>
                    </ul>
                </div>

                {{-- Tải lại --}}
                <button class="btn btn-primary d-flex align-items-center" onclick="window.location.reload();">
                    Tải lại trò chơi
                </button>
            </div>

            {{-- Nút cài đặt --}}
            <button type="button" class="btn btn-outline-secondary rounded-circle" data-bs-toggle="modal"
                data-bs-target="#checkModal" title="Cài đặt chế độ học tập">
                <i class="bi bi-gear-fill"></i>
            </button>
        </div>

        {{-- Nội dung chính --}}
        <div class="content-area mt-3" id="quizContainer">
            <!-- Nội dung sẽ được render ở đây bằng JavaScript -->
        </div>

        <div class="text-end mt-4 mb-4">
            <button id="submitExamBtn" class="btn btn-primary px-4 py-2" style="border-radius: 9999px; font-weight: 600;">
                <i class="fas fa-paper-plane me-2"></i> Gửi bài kiểm tra
            </button>
        </div>
    </div>

    {{-- Modal tùy chỉnh bài kiểm tra --}}
    <div class="modal fade" id="checkModal" tabindex="-1" aria-labelledby="checkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color:#0B0D2B; border-radius:1rem; color:#fff; border:none;">
                <div class="modal-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h6 style="font-weight:600; font-size:0.875rem; color:#9CA3AF;">Đề dự đoán đặc biệt số 1</h6>
                            <h2 style="font-weight:800; font-size:1.75rem;">Thiết lập bài kiểm tra</h2>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="background-color:#4B6BFF; border:none; border-radius:9999px; padding:1rem 2rem; font-weight:600;">
                            Đóng
                        </button>
                    </div>

                    {{-- Số câu hỏi --}}
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <label style="font-weight:600;">
                            Câu hỏi (tối đa {{ $questionCount ?? 0 }})
                        </label>
                        <input type="number" value="{{ $questionCount ?? 10 }}" class="form-control ms-3"
                            id="questionLimit"
                            style="max-width:150px; background-color:#14163C; border:none; border-radius:0.5rem; color:#fff;">
                    </div>

                    <hr style="border-color:#2F336D;">

                    {{-- Loại câu hỏi --}}
                    <div class="mb-4">
                        @foreach ([['label' => 'Đúng/Sai', 'id' => 'toggleTrueFalse'], ['label' => 'Trắc nghiệm', 'id' => 'toggleMCQ', 'checked' => true], ['label' => 'Tự luận', 'id' => 'toggleEssay']] as $q)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $q['label'] }}</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="{{ $q['id'] }}"
                                        {{ $q['checked'] ?? false ? 'checked' : '' }}>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Bắt đầu --}}
                    <div class="text-end">
                        <button type="button" class="btn" id="startExamBtn"
                            style="background-color:#4B6BFF; color:white; font-weight:600; font-size:1rem; padding:0.75rem 1.5rem; border-radius:9999px;">
                            Bắt đầu làm kiểm tra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal cảnh báo chưa hoàn thành -->
    <div class="modal fade" id="unansweredModal" tabindex="-1" aria-labelledby="unansweredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white rounded-4">
                <div class="modal-body text-center py-4">
                    <h5 class="fw-bold mb-2" id="unansweredModalLabel">Có vẻ như bạn đã bỏ qua một số câu hỏi</h5>
                    <p class="mb-4">Bạn muốn xem lại các câu hỏi đã bỏ qua hay gửi bài kiểm tra ngay bây giờ?</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">
                            Xem lại các câu hỏi đã bỏ qua
                        </button>
                        <button type="button" id="confirmSubmit" class="btn btn-primary rounded-pill px-4">
                            Gửi bài kiểm tra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const quizData = @json($quizData);
            const quizContainer = document.getElementById("quizContainer");
            quizContainer.innerHTML = "";

            if (!quizData.length) {
                quizContainer.innerHTML = `
            <div class="alert alert-warning text-center">
                Không có dữ liệu câu hỏi phù hợp, vui lòng chọn lại!
            </div>`;
                return;
            }

            quizData.forEach((q, index) => {
                let html = `<div class='question-container mb-4'>
            <div class='bg-white text-dark p-4 rounded shadow-sm'>
                <p class='fw-bold'>Câu ${index + 1}</p>
                <p>${q.question}</p>
                <div class='row g-2'>`;

                if (q.type === 'mcq' || q.type === 'true_false') {
                    q.answers.forEach(ans => {
                        html += `<div class='col-6'>
                    <button class='btn btn-outline-dark w-100'
                            data-id='${ans.id}'
                            data-type='${q.type}'>
                        ${ans.content}
                    </button>
                </div>`;
                    });
                } else if (q.type === 'essay') {
                    html += `<div class='col-12'>
                <textarea class='form-control' placeholder='Nhập câu trả lời của bạn...' rows='3'></textarea>
            </div>`;
                }

                html += `</div></div></div>`;
                quizContainer.insertAdjacentHTML("beforeend", html);
            });

            document.getElementById("startExamBtn").addEventListener("click", function() {
                const selectedTypes = [];
                if (document.getElementById("toggleTrueFalse").checked) selectedTypes.push("true_false");
                if (document.getElementById("toggleMCQ").checked) selectedTypes.push("mcq");
                if (document.getElementById("toggleEssay").checked) selectedTypes.push("essay");

                const limit = document.getElementById("questionLimit").value;
                const baseUrl = window.location.pathname;
                const query = new URLSearchParams();

                query.append("ids", "{{ base64_encode(implode(',', $idsArray)) }}");
                selectedTypes.forEach(type => query.append("selectedTypes[]", type));
                query.append("limit", limit);

                window.location.href = baseUrl + "?" + query.toString();
            });
        });
    </script>

@endsection
