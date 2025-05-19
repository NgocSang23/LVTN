@extends('user.master')

@section('title', 'Thư viện')

@section('content')
    <style>
        .card-3d {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            transform-style: preserve-3d;
            will-change: transform;
        }

        .card-3d:hover {
            transform: perspective(1000px) rotateX(5deg) rotateY(5deg) scale(1.02);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
    <div class="container py-4">
        <h1 class="mb-4">Thư viện của bạn</h1>
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('user.library_define_essay') }}">Các khái niệm - Các câu tự luận</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('user.library_multiple') }}">Các bài kiểm tra</a>
            </li>
        </ul>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Gần đây
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Another action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                </ul>
            </div>
            <div class="input-group w-50">
                <input type="text" class="form-control rounded-pill" placeholder="Tìm kiếm thẻ ghi nhớ">
                <span class="input-group-text bg-white border-0"><i class="fas fa-search"></i></span>
            </div>
        </div>

        <h3>Các bài kiểm tra</h3>
        <div class="mb-5">
            <div class="row g-3">
                @forelse ($tests as $test)
                    <div class="col-12 col-sm-6 col-lg-4" style="cursor: pointer;">
                        <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d" data-bs-toggle="modal"
                            data-bs-target="#confirmTestModal" data-id="{{ $test->id }}"
                            data-topic="@foreach ($test->questionNumbers as $questionNumber) {{ optional($questionNumber->topic)->title ?? 'Không có' }} @endforeach"
                            data-time="{{ $test->time }}"
                            data-questions="@foreach ($test->questionNumbers as $questionNumber) {{ $questionNumber->question_number ?? 'Không có' }} @endforeach"
                            data-author="{{ $test->user->name ?? 'Ẩn danh' }}"
                            data-date="{{ $test->created_at->format('Y-m-d') }}">

                            <div class="d-flex align-items-center">
                                <img src="./assets/img/test.jpg" alt="Icon" class="rounded-circle bg-primary p-1"
                                    width="50">
                                <div class="ms-3">
                                    <h5 class="mb-1 text-bold">
                                        @foreach ($test->questionNumbers as $questionNumber)
                                            {{ optional($questionNumber->topic)->title ?? 'Không có' }}
                                        @endforeach
                                    </h5>
                                    <p class="text-muted mb-0">Thời gian: {{ $test->time }} phút</p>
                                    <p class="text-muted mb-0">Số câu:
                                        @foreach ($test->questionNumbers as $questionNumber)
                                            {{ $questionNumber->question_number ?? 'Không có' }}
                                        @endforeach
                                    </p>
                                    <p class="text-muted mb-0">Tác giả: {{ $test->user->name ?? 'Ẩn danh' }}</p>
                                    <p class="text-muted mb-0">Ngày tạo: {{ $test->created_at->format('Y-m-d') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Chưa có bài kiểm tra nào được tạo.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal Xác Nhận Làm Bài Kiểm Tra -->
    <div class="modal fade" id="confirmTestModal" tabindex="-1" aria-labelledby="confirmTestLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-3">
                <!-- Header -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="confirmTestLabel">
                        <i class="bi bi-patch-question-fill me-2"></i> Xác nhận làm bài kiểm tra
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <p class="mb-2"><strong>📌 Chủ đề:</strong> <span id="testTopic" class="fw-semibold"></span></p>
                    <p class="mb-2"><strong>⏳ Thời gian:</strong> <span id="testTime" class="fw-semibold"></span> phút
                    </p>
                    <p class="mb-2"><strong>📖 Số câu hỏi:</strong> <span id="testQuestions" class="fw-semibold"></span>
                        câu</p>
                    <p class="mb-2"><strong>👤 Tác giả:</strong> <span id="testAuthor" class="fw-semibold"></span></p>
                    <p class="mb-3"><strong>📅 Ngày tạo:</strong> <span id="testDate" class="fw-semibold"></span></p>
                    <div class="alert alert-warning d-flex align-items-center p-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span>Bạn có chắc chắn muốn bắt đầu bài kiểm tra?</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <a id="startTestButton" href="#" class="btn btn-primary">
                        <i class="bi bi-play-circle"></i> Bắt đầu
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Truyền dữ liệu và mở modal --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const testCards = document.querySelectorAll(".test-card");

            testCards.forEach(card => {
                card.addEventListener("click", function() {
                    const testId = this.getAttribute("data-id");
                    const testTopic = this.getAttribute("data-topic");
                    const testTime = this.getAttribute("data-time");
                    const testQuestions = this.getAttribute("data-questions");
                    const testAuthor = this.getAttribute("data-author");
                    const testDate = this.getAttribute("data-date");

                    document.getElementById("testTopic").textContent = testTopic;
                    document.getElementById("testTime").textContent = testTime;
                    document.getElementById("testQuestions").textContent = testQuestions;
                    document.getElementById("testAuthor").textContent = testAuthor;
                    document.getElementById("testDate").textContent = testDate;

                    document.getElementById("startTestButton").setAttribute("href",
                        "{{ route('flashcard_multiple_choice.show', '') }}" + "/" + testId);
                });
            });
        });
    </script>
@endsection
