@extends('user.master')

@section('title', 'Tạo Bài Kiểm Tra Mới')

@section('content')
    <style>
        input.form-control:focus {
            outline: none;
            box-shadow: none;
            border-color: none;
        }
    </style>
    <div class="container mt-5">
        <div class="mb-4 text-center">
            <h1 class="h3 fw-bold text-primary">Tạo Bài Kiểm Tra Mới</h1>
            <p class="text-muted">Điền thông tin bên dưới để thêm các câu hỏi và các phương án</p>
        </div>
        <div class="card shadow p-4" style="border-radius: 12px;">
            <form id="createTestForm" method="POST" action="{{ route('flashcard_multiple_choice.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="bkt_noidung" class="form-label" style="font-weight: 500;">Nội Dung</label>
                    <textarea class="form-control" id="bkt_noidung" name="test_content" rows="3" style="border-radius: 8px;"></textarea>
                    @error('test_content')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="bkt_thoigian" class="form-label" style="font-weight: 500;">Thời Gian Làm Bài
                            (phút)</label>
                        <input type="number" class="form-control" id="bkt_thoigian" name="test_time"
                            style="border-radius: 8px;">
                        @error('test_time')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="bkt_mon" class="form-label" style="font-weight: 500;">Môn</label>
                        <select class="form-select" name="subject_id" style="border-radius: 8px;">
                            <option selected disabled>Chọn môn học</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-weight: 500;">Chủ đề</label>
                        <input type="text" class="form-control" name="topic_title" style="border-radius: 8px;">
                        @error('topic_title')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    @if (auth()->user()->roles === 'teacher' && count($myClassrooms) > 0)
                        <div class="mb-4">
                            <label for="classroom_id" class="form-label fw-semibold">Chia sẻ ngay vào lớp học (tuỳ
                                chọn):</label>
                            <select class="form-select" name="classroom_ids[]" id="classroom_id" style="border-radius: 8px;">
                                <option value="">-- Không chia sẻ --</option>
                                @foreach ($myClassrooms as $classroom)
                                    <option value="{{ $classroom->id }}">{{ $classroom->name }} ({{ $classroom->code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Bạn có thể chia sẻ bài kiểm tra này trực tiếp với một lớp học.</div>
                            @error('classroom_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <label for="questionCountInput" class="form-label fw-semibold">Số lượng câu hỏi muốn tạo</label>
                    <div class="d-flex gap-2">
                        <input type="number" id="questionCountInput" class="form-control" min="1" max="100"
                            placeholder="Nhập số câu hỏi" style="border-radius: 8px; max-width: 200px;">
                        <button type="button" id="generateQuestions" class="btn btn-secondary"
                            style="border-radius: 8px;">Tạo</button>
                    </div>
                </div>

                <div id="questions">
                    <div class="question-group mb-4 p-3 border rounded bg-light" style="border-radius: 8px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0" style="font-weight: 600;">Câu Hỏi 1</h5>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-card"
                                style="border-radius: 50px; padding: 6px 12px;">Xóa</button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 500;">Nội dung câu hỏi</label>
                            <textarea class="form-control" name="multiple_question[]" rows="2" style="border-radius: 8px;"></textarea>
                            @error('multiple_question.*')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 500;">Phương án</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="text" class="form-control" name="option_content[0][]"
                                        placeholder="Phương án A" style="border-radius: 8px;">
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control" name="option_content[0][]"
                                        placeholder="Phương án B" style="border-radius: 8px;">
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control" name="option_content[0][]"
                                        placeholder="Phương án C" style="border-radius: 8px;">
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control" name="option_content[0][]"
                                        placeholder="Phương án D" style="border-radius: 8px;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 500;">Đáp án đúng</label>
                            <select class="form-select" name="answer[0][]" style="border-radius: 8px;">
                                <option value="0">Phương án A</option>
                                <option value="1">Phương án B</option>
                                <option value="2">Phương án C</option>
                                <option value="3">Phương án D</option>
                            </select>
                            @error('answer.*')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" id="addQuestion" class="btn btn-outline-primary"
                        style="border-radius: 8px; padding: 8px 20px;">+ Thêm Câu Hỏi</button>
                    <button type="submit" class="btn btn-primary text-white"
                        style="border-radius: 8px; padding: 8px 20px;">Tạo và Ôn luyện</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addQuestionButton = document.getElementById('addQuestion'); // Nút "Thêm Câu Hỏi"
            const generateButton = document.getElementById('generateQuestions'); // Nút "Tạo" theo số lượng nhập
            const questionCountInput = document.getElementById('questionCountInput'); // Ô nhập số lượng câu hỏi
            const questionsContainer = document.getElementById('questions'); // Container chứa toàn bộ câu hỏi

            // ✅ Hàm cập nhật lại tiêu đề câu hỏi "Câu Hỏi 1", "Câu Hỏi 2", ...
            function updateCardNumbers() {
                document.querySelectorAll(".question-group").forEach((card, index) => {
                    const title = card.querySelector(".card-number");
                    if (title) {
                        title.textContent = `Câu Hỏi ${index + 1}`;
                    }
                });
            }

            // ✅ Xử lý sự kiện khi bấm nút "Thêm Câu Hỏi"
            addQuestionButton.addEventListener('click', function() {
                const questionCount = document.querySelectorAll('.question-group').length;

                const newQuestion = document.createElement('div');
                newQuestion.classList.add('question-group', 'mb-4', 'p-3', 'border', 'rounded', 'bg-light');

                // Nội dung HTML của 1 câu hỏi mới (có .card-number để cập nhật)
                newQuestion.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 card-number" style="font-weight: 600;">Câu Hỏi ${questionCount + 1}</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-card" style="border-radius: 50px; padding: 6px 12px;">Xóa</button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Nội dung câu hỏi</label>
                        <textarea class="form-control" name="multiple_question[]" rows="2" style="border-radius: 8px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Phương án</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="text" class="form-control" name="option_content[${questionCount}][]" placeholder="Phương án A" style="border-radius: 8px;">
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control" name="option_content[${questionCount}][]" placeholder="Phương án B" style="border-radius: 8px;">
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control" name="option_content[${questionCount}][]" placeholder="Phương án C" style="border-radius: 8px;">
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control" name="option_content[${questionCount}][]" placeholder="Phương án D" style="border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Đáp án đúng</label>
                        <select class="form-select" name="answer[${questionCount}][]" style="border-radius: 8px;">
                            <option value="0">Phương án A</option>
                            <option value="1">Phương án B</option>
                            <option value="2">Phương án C</option>
                            <option value="3">Phương án D</option>
                        </select>
                    </div>
                `;

                questionsContainer.appendChild(newQuestion); // Thêm vào DOM
                updateCardNumbers(); // Cập nhật tiêu đề
            });

            // ✅ Xử lý khi nhấn nút "Tạo" theo số lượng nhập
            generateButton.addEventListener("click", function() {
                const count = parseInt(questionCountInput.value);

                if (isNaN(count) || count < 1 || count > 100) {
                    alert("Vui lòng nhập số câu hỏi hợp lệ (từ 1 đến 100)");
                    return;
                }

                questionsContainer.innerHTML = ""; // Xoá hết câu hỏi cũ trước khi tạo mới

                // Tạo mới từng câu hỏi
                for (let i = 0; i < count; i++) {
                    const questionHtml = `
                        <div class="question-group mb-4 p-3 border rounded bg-light" style="border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0 card-number" style="font-weight: 600;">Câu Hỏi ${i + 1}</h5>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-card" style="border-radius: 50px; padding: 6px 12px;">Xóa</button>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-weight: 500;">Nội dung câu hỏi</label>
                                <textarea class="form-control" name="multiple_question[]" rows="2" style="border-radius: 8px;"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-weight: 500;">Phương án</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="option_content[${i}][]" placeholder="Phương án A" style="border-radius: 8px;">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="option_content[${i}][]" placeholder="Phương án B" style="border-radius: 8px;">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="option_content[${i}][]" placeholder="Phương án C" style="border-radius: 8px;">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="option_content[${i}][]" placeholder="Phương án D" style="border-radius: 8px;">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-weight: 500;">Đáp án đúng</label>
                                <select class="form-select" name="answer[${i}][]" style="border-radius: 8px;">
                                    <option value="0">Phương án A</option>
                                    <option value="1">Phương án B</option>
                                    <option value="2">Phương án C</option>
                                    <option value="3">Phương án D</option>
                                </select>
                            </div>
                        </div>
                    `;
                    questionsContainer.insertAdjacentHTML("beforeend", questionHtml);
                }

                updateCardNumbers(); // Cập nhật số thứ tự tiêu đề sau khi tạo
            });

            // ✅ Xử lý khi nhấn nút "Xóa" câu hỏi bất kỳ
            questionsContainer.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-card")) {
                    e.target.closest(".question-group").remove(); // Xoá phần tử chứa câu hỏi
                    updateCardNumbers(); // Cập nhật lại thứ tự tiêu đề
                }
            });
        });
    </script>
@endsection
