@extends('user.master')

@section('title', 'Ôn luyện các bài kiểm tra')

@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success fixed-top text-center p-3 shadow-lg js-div-dissappear"
            style="width: 100%; max-width: 400px; margin: 10px auto; z-index: 1050;">
            <i class="fas fa-check-circle me-2"></i> {{ Session::get('success') }}
        </div>
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        input.form-control:focus {
            outline: none;
            box-shadow: none;
            border-color: none;
        }
    </style>
    <div class="container mt-5">
        <div class="mb-3 d-flex">
            <a href="{{ route('user.dashboard') }}" class="btn btn-primary me-3">&lt;</a>
            <h4 class="topic_title align-content-start m-0" id="topic-title"></h4>
        </div>
        <div class="text-center mb-4">
            <h2 class="fw-bold">Bài Kiểm Tra Trắc Nghiệm</h2>
        </div>

        <div class="row">
            <!-- Câu hỏi và lựa chọn -->
            <div class="col-lg-8">
                <div class="card shadow-sm p-4">
                    {{-- <form id="quiz-form" method="POST"> --}}
                    <div id="questions-container"></div>
                    <button type="submit" data-bs-toggle="modal" data-bs-target="#submitTestModal"
                        class="btn btn-success w-100 mt-3">Nộp bài</button>
                    {{-- </form> --}}
                </div>
            </div>

            <!-- Thanh bên phải: Bộ đếm thời gian và danh sách câu hỏi -->
            <div class="col-lg-4">
                <div class="card shadow-sm p-3 text-center">
                    <h5 class="fw-bold">Thời gian còn lại</h5>
                    <span id="timer" class="badge bg-danger fs-4 py-2 px-3 mt-2">10:00</span>
                </div>
                <div class="card shadow-sm p-3 mt-3">
                    <h5 class="fw-bold">Câu hỏi</h5>
                    <div class="d-flex flex-wrap justify-content-center" id="questions-number" style="gap: 8px;"></div>
                </div>
                @if (Auth::guard('web')->user()->id == $test->user_id)
                    <div class="d-flex justify-content-start mt-3">
                        <button class="btn btn-warning me-2" data-bs-toggle="modal"
                            data-bs-target="#editTestModal">Sửa</button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTestModal">Xóa</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="deleteTestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 400px;">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #ff6a6a, #ff0000); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title">⚠️ Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <div class="modal-body" style="font-size: 1rem; color: #333; text-align: center; padding: 20px;">
                    <p>Bạn có chắc chắn muốn xóa bài kiểm tra này không?</p>
                </div>
                <div class="modal-footer d-flex justify-content-center" style="padding: 20px;">
                    <form id="deleteForm" method="POST" action="{{ route('flashcard_multiple_choice.destroy', 0) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                            style="padding: 8px 20px; border-radius: 6px;">Xóa</button>
                    </form>
                    <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal"
                        style="padding: 8px 20px; border-radius: 6px;">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận nộp bài -->
    <div class="modal fade" id="submitTestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 400px;">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #00c6ff, #0072ff); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title">📤 Xác nhận nộp bài</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <div class="modal-body" style="font-size: 1rem; color: #333; text-align: center; padding: 20px;">
                    <p>Bạn muốn nộp bài không?</p>
                </div>
                <div class="modal-footer d-flex justify-content-center" style="padding: 20px;">
                    <button type="submit" data-bs-toggle="modal" data-bs-target="#resultModal" id="confirmSubmit"
                        class="btn btn-danger" style="padding: 8px 20px; border-radius: 6px;">Có</button>
                    <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal"
                        style="padding: 8px 20px; border-radius: 6px;">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal kết quả -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #00c6ff, #0072ff); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title" style="font-weight: 600;">🎉 Kết quả</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <div class="modal-body" style="font-size: 1.1rem; color: #333; text-align: center; padding: 20px;">
                    <p id="resultMessage" style="margin-bottom: 10px; font-weight: 500;"></p>
                    <p id="resultScore" style="font-size: 1.2rem; color: #0072ff; font-weight: 600;"></p>
                    <p id="resultTime" style="margin-top: 10px;"></p>
                </div>
                <div class="modal-footer d-flex justify-content-center" style="padding: 20px;">
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary"
                        style="padding: 8px 20px; border-radius: 6px;">Đóng</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa Bài Kiểm Tra -->
    <div class="modal fade" id="editTestModal" tabindex="-1" aria-labelledby="editTestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title" id="editTestModalLabel">✏️ Chỉnh sửa bài kiểm tra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <div class="modal-body">
                    <form id="editTestForm" action="{{ route('flashcard_multiple_choice.update', 0) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="editTitle" class="form-label">Nội dung bài kiểm tra</label>
                                <input type="text" name="test_content" class="form-control" id="editTitle">
                                @error('test_content')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="editTime" class="form-label">Thời gian (phút)</label>
                                <input type="number" name="test_time" class="form-control" id="editTime">\
                                @error('test_time')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div id="editQuestionsContainer"></div>

                        <input type="submit" class="btn btn-primary mt-3" style="padding: 8px 20px; border-radius: 6px;"
                            value="Lưu thay đổi">
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- <script src="{{ asset('assets/js/multiple-question.js') }}"></script> --}}

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const userId = {{ Auth::guard('web')->user()->id }};
            let multiplequestions = []; // Mảng lưu danh sách câu hỏi từ API
            let testId = window.location.pathname.split("/").pop(); // Lấy ID bài kiểm tra từ URL
            let timeLeft = 0; // Biến lưu thời gian còn lại của bài kiểm tra
            let initialTime = 0; // Biến lưu thời gian bài kiểm tra ban đầu
            let statusQuestions = {}; // Đối tượng lưu trạng thái câu hỏi đã trả lời

            // Hàm gọi API để lấy danh sách câu hỏi
            async function fetchTests() {
                try {
                    // Gọi API để lấy danh sách câu hỏi
                    let response = await fetch(`http://127.0.0.1:8000/api/card_multiple_choice/${testId}`);
                    let data = await response.json();
                    let tests = data.data;

                    // Kiểm tra xem API có trả về dữ liệu không
                    if (data.status_code === 200 && tests.multiplequestions.length > 0) {
                        multiplequestions = tests.multiplequestions; // Lưu danh sách câu hỏi
                        // console.log(tests.id);

                        document.getElementById("topic-title").innerText = tests.questionnumbers?.[0]?.topic?.title || "Không có tiêu đề";

                        document.getElementById("deleteForm").action = `{{ route('flashcard_multiple_choice.destroy', ':id') }}`.replace(':id', tests.id);
                        document.getElementById("editTestForm").action = `{{ route('flashcard_multiple_choice.update', ':id') }}`.replace(':id', tests.id);

                        // Chuyển đổi thời gian từ định dạng "MM:SS" thành tổng số giây
                        timeLeft = tests.time.split(":").reduce((acc, val) => acc * 60 + parseInt(val), 0);
                        initialTime = timeLeft; // ➜ lưu lại thời gian ban đầu

                        // Load các thông tin vào modal sửa bài kiểm tra
                        document.getElementById("editTitle").value = tests.content;
                        document.getElementById('editTime').value = parseInt(tests.time.split(':')[1]);

                        let containerEdit = document.getElementById("editQuestionsContainer");
                        containerEdit.innerHTML = "";

                        // Lặp qua từng câu hỏi trong bài kiểm tra
                        tests.multiplequestions.forEach((question, index) => {
                            let option = '';
                            let optionIds = ''; // Mảng lưu option_id bằng input hidden
                            let correctAnswer = 0;

                            // Lặp qua các option của các câu hỏi
                            question.testresults.forEach((opt, idx) => {
                                if (opt.answer === "1") {
                                    correctAnswer = idx;
                                }
                                option += `
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Phương án ${String.fromCharCode(65 + idx)}</label>
                                        <input type="text" class="form-control" name="option_content[${index}][]" value="${opt.option.content}" id="opt${question.id}-${idx}">
                                        @error('option_content')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                `;
                                // Thêm input hidden cho option_id
                                optionIds += `<input type="hidden" name="option_id[${index}][]" value="${opt.option.id}">`;
                            });

                            containerEdit.innerHTML += `
                                <input type="hidden" name="question_id[${index}]" value="${question.id}">
                                ${optionIds}
                                <div class="card mb-3 p-3" style="border-radius: 8px; border: 1px solid #ddd;">
                                    <h6>Câu hỏi ${index + 1}</h6>
                                    <div class="mb-2">
                                        <label class="form-label">Nội dung câu hỏi</label>
                                        <input type="text" class="form-control" name="multiple_question[]" value="${question.content}">
                                        @error('multiple_question.*')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        ${option}
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Đáp án đúng</label>
                                        <select class="form-select" name="answer[${index}]">
                                            <option value="0" ${correctAnswer === 0 ? 'selected' : ''}>Phương án A</option>
                                            <option value="1" ${correctAnswer === 1 ? 'selected' : ''}>Phương án B</option>
                                            <option value="2" ${correctAnswer === 2 ? 'selected' : ''}>Phương án C</option>
                                            <option value="3" ${correctAnswer === 3 ? 'selected' : ''}>Phương án D</option>
                                        </select>
                                        @error('answer.*')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            `;
                        });

                        renderQuestions(); // Gọi hàm để hiển thị tất cả câu hỏi ngay từ đầu
                        renderNavigation(); // Hiển thị danh sách số thứ tự câu hỏi
                        startTimer(); // Bắt đầu bộ đếm thời gian
                    } else {
                        document.getElementById("questions-number").innerText = "Không có dữ liệu!";
                    }
                } catch (error) {
                    console.error("Lỗi API:", error);
                    alert("Không thể tải dữ liệu, vui lòng thử lại sau.");
                }
            }

            // Hàm hiển thị danh sách số thứ tự câu hỏi
            function renderNavigation() {
                let navContainer = document.getElementById("questions-number");
                // Tạo danh sách các nút số thứ tự câu hỏi
                navContainer.innerHTML = multiplequestions.map((_, index) =>
                    `<button class="btn btn-outline-primary btn-sm question-nav" id="btn-q${index}" onclick="scrollToQuestion(${index})">${index + 1}</button>`
                ).join("");
            }

            // Hàm hiển thị tất cả câu hỏi ngay từ đầu
            function renderQuestions() {
                let container = document.getElementById("questions-container");
                container.innerHTML = ""; // Xóa nội dung cũ trước khi thêm mới

                multiplequestions.forEach((question, index) => {
                    // Hiển thị nội dung câu hỏi
                    container.innerHTML += `
                        <div class="card p-3 mb-3" id="question-${index}">
                            <h5 class="fw-bold">Câu ${index + 1}: ${question.content}</h5>
                            <div class="mt-3">
                                ${question.testresults?.map((opt, idx) => `
                                <div class="form-check">
                                        <input class="form-check-input option-input" type="radio" name="question${question.id}" value="${opt.option_id}" id="opt${question.id}-${idx}">
                                        <label class="form-check-label" for="opt${question.id}-${idx}">
                                            <strong>${String.fromCharCode(65 + idx)}.</strong> ${opt.option.content}
                                        </label>
                                    </div>
                                `).join('') || '<p>Không có đáp án</p>'}
                            </div>
                        </div>`;
                });

                // Gán sự kiện khi người dùng chọn đáp án
                document.querySelectorAll(".option-input").forEach(input => {
                    input.addEventListener("change", function() {
                        let questionId = this.name.replace("question", ""); // Lấy ID câu hỏi
                        let selectedOptionId = this.value; // Lấy ID của phương án được chọn
                        let question = multiplequestions.find(q => q.id ==
                            questionId); // Tìm câu hỏi trong danh sách
                        if (!question) return;

                        let selectedAnswer = question.testresults.find(opt => opt.option_id ==
                            selectedOptionId); // Tìm phương án được chọn

                        // Xóa màu nền của các đáp án trước đó
                        document.querySelectorAll(`input[name='question${questionId}']`).forEach(
                            radio => {
                                let label = radio.nextElementSibling;
                                label.classList.remove("bg-success", "bg-danger", "text-white", "p-1", "rounded");
                                radio.disabled = true; // Khóa ô chọn sau khi đã chọn đáp án
                            });

                        // Đánh dấu đúng/sai
                        if (selectedAnswer && selectedAnswer.answer === "1") {
                            this.nextElementSibling.classList.add("bg-success", "text-white", "p-1", "rounded");
                        } else {
                            this.nextElementSibling.classList.add("bg-danger", "text-white", "p-1", "rounded");
                        }

                        // Cập nhật trạng thái câu hỏi đã trả lời
                        statusQuestions[questionId] = true;
                        updateNavigationButtons();
                    });
                });
            }

            // Cập nhật màu của nút điều hướng khi câu hỏi đã được trả lời
            function updateNavigationButtons() {
                document.querySelectorAll(".question-nav").forEach((btn, index) => {
                    if (statusQuestions[multiplequestions[index].id]) {
                        btn.classList.add("btn-warning"); // Nếu đã trả lời, đánh dấu màu vàng
                    } else {
                        btn.classList.remove("btn-warning");
                    }
                });
            }

            // Hàm cuộn đến câu hỏi khi bấm vào số thứ tự
            window.scrollToQuestion = function(index) {
                let questionElement = document.getElementById(`question-${index}`);
                if (questionElement) {
                    questionElement.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    }); // Cuộn mượt
                }
            };

            // Bắt đầu bộ đếm thời gian
            function startTimer() {
                let timeDisplay = document.getElementById("timer");
                let updateTimer = () => {
                    let minutes = Math.floor(timeLeft / 60);
                    let seconds = timeLeft % 60;
                    timeDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    if (timeLeft-- <= 0) {
                        clearInterval(timerInterval);
                        alert("Hết thời gian! Bài làm sẽ tự động nộp.");
                        document.getElementById("quiz-form").submit();
                    }
                };
                updateTimer();
                let timerInterval = setInterval(updateTimer, 1000);
            }

            // Hiển thị kết quả bài làm
            document.getElementById("confirmSubmit").addEventListener("click", function() {
                let correctCount = 0;
                let totalQuestions = multiplequestions.length;

                multiplequestions.forEach(question => {
                    let selected = document.querySelector(`input[name='question${question.id}']:checked`);
                    if (selected) {
                        let selectedOptionId = parseInt(selected.value);
                        let selectAnswer = question.testresults.find(opt => opt.option_id ===
                            selectedOptionId);
                        if (selectAnswer && selectAnswer.answer === "1") {
                            correctCount++;
                        }
                    }
                });

                let score = (correctCount / totalQuestions) * 100;
                let textResult = `Bạn đã trả lời đúng ${correctCount}/${totalQuestions} câu hỏi`;

                let timeSpent = initialTime - timeLeft;
                if (timeSpent < 0) timeSpent = 0;
                let minutes = Math.floor(timeSpent / 60);
                let seconds = timeSpent % 60;
                let timeMessage = `Thời gian làm bài: ${minutes} phút ${seconds.toString().padStart(2, '0')} giây`;

                document.getElementById("resultMessage").innerText = textResult;
                document.getElementById("resultScore").innerText = `Điểm của bạn là ${score}`;
                document.getElementById("resultTime").innerText = timeMessage;

                saveHistory(correctCount, totalQuestions);
            });

            // Lưu lịch sử bài làm
            function saveHistory(correctCount, totalQuestions) {
                // Tính điểm số: số câu đúng / tổng số câu * 100 (%)
                let score = (correctCount / totalQuestions) * 100;

                let timeSpent = initialTime - timeLeft;
                // Nếu lỡ timeSpent bị âm, cho bằng 0
                if (timeSpent < 0) timeSpent = 0;
                // Chuyển sang phút, giây
                let minutes = Math.floor(timeSpent / 60);
                let seconds = timeSpent % 60;
                let timeSpentStr = `${minutes} phút ${seconds.toString().padStart(2, '0')} giây`;

                // Tạo FormData để chứa dữ liệu gửi đi
                let formData = new FormData();
                formData.append("correct_count", correctCount);
                formData.append("total_questions", totalQuestions);
                formData.append("score", score.toFixed(2)); // Điểm số (có thể làm tròn 2 chữ số)
                formData.append("time_spent", timeSpentStr);
                formData.append("test_id", testId);
                formData.append("user_id", userId);

                // Lấy CSRF token từ thẻ meta (Laravel yêu cầu khi POST)
                let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                formData.append("_token", csrfToken);

                // Gửi dữ liệu qua fetch API
                fetch("user/history/save", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json()) // Parse response JSON từ server
                    .then(data => {
                        console.log("Lịch sử bài làm đã lưu:", data);
                    })
                    .catch(error => {
                        console.error("Lỗi khi lưu lịch sử bài làm:", error);
                    });
            }

            // Gọi API khi trang tải xong
            fetchTests();
        });
    </script>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/message.css') }}" />
@endsection

@section('js')
    @if (Session::has('success'))
        <script src="{{ asset('assets/js/message.js') }}"></script>
    @endif
@endsection