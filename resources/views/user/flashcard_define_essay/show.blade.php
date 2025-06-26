@extends('user.master')

@section('title', 'Ôn luyện các khái niệm / các câu tự luận')

@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success fixed-top text-center p-3 shadow-lg js-div-dissappear"
            style="width: 100%; max-width: 400px; margin: 10px auto; z-index: 1050;">
            <i class="fas fa-check-circle me-2"></i> {{ Session::get('success') }}
        </div>
    @endif
    <style>
        .preview-container {
            width: 200px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        input.form-control:focus {
            outline: none;
            box-shadow: none;
            border-color: none;
        }

        .mark-difficult {
            transition: all 0.3s ease-in-out;
        }

        .mark-difficult:hover {
            transform: scale(1.05);
        }
    </style>

    <div class="bg-light d-flex align-items-center justify-content-center">
        <div class="container py-4" style="max-width: 900px;">

            {{-- Thanh tiêu đề và nút quay lại --}}
            <div class="d-flex align-items-center mb-3 header-bar">
                <a href="{{ route('user.dashboard') }}" class="btn btn-primary me-3">&lt;</a>
                <h2 class="topic_title m-0"></h2>
            </div>

            {{-- Các nút chế độ học --}}
            <div class="d-flex justify-content-center mb-4 gap-3 mode-buttons flex-wrap">
                @php
                    $encodedIds = base64_encode(implode(',', $cards->pluck('id')->toArray()));
                @endphp

                <a href="{{ route('game.flashcard', ['ids' => $encodedIds]) }}" class="btn btn-outline-warning px-4 py-2">🃏
                    Flashcard</a>
                {{-- <a href="{{ route('game.essay', ['ids' => $encodedIds]) }}" class="btn btn-outline-dark px-4 py-2">✏️ Tự luận</a> --}}
                <a href="{{ route('game.match', ['ids' => $encodedIds]) }}" class="btn btn-outline-success px-4 py-2">🧩 Tìm
                    cặp</a>
                <a href="{{ route('game.study', ['ids' => $encodedIds]) }}" class="btn btn-outline-primary px-4 py-2">📚 Học
                    tập</a>
                <a href="{{ route('game.check', ['ids' => $encodedIds]) }}" class="btn btn-outline-danger px-4 py-2">📝 Kiểm
                    tra</a>
            </div>

            {{-- Khu vực Flashcard --}}
            <div class="flashcard-area d-flex flex-column align-items-center mb-4">
                <div class="card shadow-sm w-100 flip-card" style="max-width: 600px;">
                    {{-- Mặt trước --}}
                    <div class="card-body front-card-body">
                        <div class="d-flex justify-content-between mb-4">
                            <div></div>
                            <button class="btn btn-light border">Ôn tập</button>
                        </div>
                        <div class="text-center">
                            <div class="question-scroll" style="max-height: 150px; overflow-y: auto;">
                                <p class="display-4 fs-4 fw-bold mb-2 question_content"></p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button class="btn btn-link text-secondary"><i class="fas fa-sync-alt"></i></button>
                        </div>
                    </div>

                    {{-- Mặt sau --}}
                    <div class="card-body back-card-body" style="display: none;">
                        <div class="d-flex justify-content-between mb-4">
                            <div></div>
                            <button class="btn btn-light border">Ôn tập</button>
                        </div>
                        <div class="row">
                            <div class="col-8" style="max-height: 150px; overflow-y: auto;">
                                <p class="display-5 fw-bold fs-4 ms-5 answer_content text-center"></p>
                            </div>
                            <div class="col-4">
                                <img class="img-fluid rounded shadow-sm image_path d-none" style="width: 200px;">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button class="btn btn-link text-secondary"><i class="fas fa-sync-alt"></i></button>
                        </div>
                    </div>
                </div>

                {{-- Thanh điều hướng và đánh giá --}}
                <div class="card shadow-sm w-100 mt-3" style="max-width: 600px;">
                    <div class="card-body d-flex flex-column align-items-center">
                        <div class="d-flex justify-content-around w-100 mb-3">
                            <div class="d-flex align-items-center text-success">
                                <i class="far fa-smile me-1"></i> <span>Dễ</span>
                            </div>
                            <div class="d-flex align-items-center text-warning">
                                <i class="far fa-meh me-1"></i> <span>Trung bình</span>
                            </div>
                            <div class="d-flex align-items-center text-danger mark-difficult" style="cursor: pointer">
                                <i class="far fa-frown me-1"></i> <span>Khó</span>
                            </div>
                        </div>
                        <!-- ✅ THÊM PHẦN NÀY -->
                        <div class="d-flex justify-content-center mt-2">
                            <div class="resolve-container"></div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-primary prev-question me-3">&lt;</button>
                            <span class="current-question">1</span>/<span class="total-questions">2</span>
                            <button class="btn btn-primary next-question ms-3">&gt;</button>
                        </div>
                    </div>

                    @if (collect($cards)->contains(fn($card) => Auth::user()->id == $card->user_id))
                        <div class="d-flex justify-content-end mb-3 me-3">
                            <button class="btn btn-warning me-2 edit-question" data-bs-toggle="modal"
                                data-bs-target="#editQuestionModal">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal">
                                <i class="fas fa-trash-alt"></i> Xóa
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <hr>

            {{-- Danh sách định nghĩa dạng bảng --}}
            <div class="definition-list mt-4 w-100 mx-auto" style="max-width: 700px;">
                <table class="table table-bordered table-striped bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Câu hỏi</th>
                            <th scope="col">Định nghĩa / Đáp án</th>
                            <th scope="col" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="definition-table-body">
                        {{-- Dữ liệu sẽ được JS render vào đây --}}
                    </tbody>
                </table>
            </div>

        </div>
    </div>


    {{-- Modal chỉnh sửa --}}
    <div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #00c6ff, #0072ff); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title" id="editQuestionModalLabel" style="font-weight: 600;">✏️ Chỉnh sửa câu hỏi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <div class="modal-body" style="font-size: 1rem; color: #333;">
                    <form id="editQuestionForm" method="POST" action="{{ route('flashcard_define_essay.update', 0) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="editQuestionContent" class="form-label">Câu hỏi</label>
                            <textarea name="question" class="form-control auto-resize" id="editQuestionContent" rows="5"
                                placeholder="Nhập câu hỏi tại đây..."
                                style="resize: vertical; max-height: 300px; min-height: 100px; overflow-y: auto;"></textarea>
                            @error('question')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editAnswerContent" class="form-label">Đáp án</label>
                            <textarea name="answer" class="form-control auto-resize" id="editAnswerContent" rows="5"
                                placeholder="Nhập đáp án tại đây..."
                                style="resize: vertical; max-height: 300px; min-height: 100px; overflow-y: auto;"></textarea>
                            @error('answer')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3 d-none">
                            <label for="editImagePath" class="form-label">Đường dẫn ảnh</label>
                            <input type="text" class="form-control" id="editImagePath" name="image_path" readonly>
                            @error('image_path')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-6">
                                <input type="file" name="image" id="editImageInput"
                                    class="form-control image-input" accept="image/*">
                            </div>
                            <div class="col-md-3">
                                <div class="preview-container text-center">
                                    <img src="" width="180" height="80" id="editImagePreview"
                                        alt="Xem trước ảnh" class="image-preview d-none" style="border-radius: 6px;">
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary"
                                style="padding: 6px 20px; border-radius: 6px;">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal xóa --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #ff5f6d, #ffc371); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title" style="font-weight: 600;">⚠️ Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <div class="modal-body" style="font-size: 1rem; color: #333;">
                    <p>Bạn có chắc chắn muốn xóa câu hỏi này không?</p>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <form id="deleteForm" method="POST" action="{{ route('flashcard_define_essay.destroy', 0) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                            style="padding: 6px 20px; border-radius: 6px;">Xóa</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="padding: 6px 20px; border-radius: 6px;">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    {{-- <script src="{{ asset('assets/js/define-essay.js') }}"></script> --}}

    <script>
        // Chờ đến khi DOM tải xong mới chạy code bên trong
        document.addEventListener("DOMContentLoaded", function() {

            let currentIndex = 0; // Biến lưu chỉ số câu hỏi hiện tại
            let questions = []; // Mảng chứa danh sách các câu hỏi từ API

            const cardId = window.location.pathname.split("/").pop(); // Lấy ID thẻ từ URL

            // Hàm fetch câu hỏi từ API
            function fetchQuestions() {
                fetch(`http://127.0.0.1:8000/api/card_define_essay/${cardId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Kiểm tra nếu API trả về dữ liệu thành công
                        if (data.status_code === 200 && data.data.length > 0) {
                            questions = data.data; // Lưu danh sách câu hỏi vào biến questions
                            currentIndex = 0; // Reset chỉ số câu hỏi về câu đầu tiên
                            updateQuestion(); // Cập nhật giao diện câu hỏi
                        } else {
                            // Nếu không có dữ liệu
                            document.querySelector(".question_content").innerText = "Không có dữ liệu!";
                            document.querySelector(".answer_content").innerText = "";
                        }
                    })
                    .catch(error => {
                        // Bắt lỗi khi gọi API
                        console.error("Lỗi API:", error);
                        alert("Không thể tải dữ liệu, vui lòng thử lại sau.");
                    });
            }

            // Lắng nghe sự kiện lật thẻ flashcard
            let flipCard = document.querySelector('.flip-card');
            if (flipCard) {
                flipCard.addEventListener('click', function() {
                    flipCard.classList.toggle('flipped'); // Thêm/xóa class 'flipped' để lật thẻ
                    let backCardBody = document.querySelector('.back-card-body');
                    if (backCardBody) {
                        // Nếu thẻ bị lật, hiển thị mặt sau
                        if (flipCard.classList.contains('flipped')) {
                            backCardBody.style.display = 'block';
                        } else {
                            backCardBody.style.display = 'none'; // Nếu không, ẩn mặt sau
                        }
                        saveAnswer(); // Gọi hàm lưu câu trả lời
                    } else {
                        console.error("Element .back-card-body not found.");
                    }
                });
            }

            // Hàm lưu câu trả lời của người dùng khi lật thẻ
            function saveAnswer() {
                if (!questions || questions.length === 0) return;

                let question = questions[currentIndex].question;

                fetch("{{ route('flashcard_define_essay.save') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            question_id: question.id // Gửi ID câu hỏi lên server
                        })
                    })
                    .then(async response => {
                        if (!response.ok) {
                            const text = await response.text();
                            throw new Error(`Lỗi Server: ${response.status}, ${text}`);
                        }
                        return response.json(); // Trả về dữ liệu JSON nếu thành công
                    })
                    .then(data => {
                        console.log("Đã lưu câu trả lời", data);
                    })
                    .catch(error => {
                        console.error("Lỗi khi lưu câu trả lời:", error);
                    });
            }

            // Hàm cập nhật giao diện câu hỏi khi next/prev hoặc load dữ liệu
            function updateQuestion() {
                if (questions.length === 0) return;

                let cardData = questions[currentIndex]; // Lấy dữ liệu câu hỏi hiện tại
                let question = cardData.question; // Lấy đối tượng câu hỏi
                let type = question.type; // Loại câu hỏi: definition hoặc essay
                let topic = question.topic; // Lấy thông tin chủ đề câu hỏi
                let answer = (question.answers && question.answers.length > 0) ? question.answers[0].content :
                    "Chưa có đáp án"; // Đáp án
                let image = (question.images && question.images.length > 0) ? question.images[0].path :
                    null; // Đường dẫn ảnh
                let card = document.querySelector(".card"); // Thẻ chứa nội dung
                let listQuestion = "";

                questions.forEach((cardData) => {
                    let question = cardData.question;
                    let answer = (question.answers && question.answers.length > 0) ? question.answers[0]
                        .content : "Chưa có đáp án";

                    listQuestion += `
                        <tr>
                            <td class="fw-bold">${question.content}</td>
                            <td>${answer}</td>
                            <td class="text-center">
                                <i class="fas fa-volume-up text-primary" role="button"></i>
                            </td>
                        </tr>
                    `;
                });

                // Đổ dữ liệu vào phần thân bảng
                document.querySelector(".definition-table-body").innerHTML = listQuestion;

                document.querySelector(".topic_title").innerText = 'Chủ đề: ' + topic.title;

                if (type === "definition") {
                    document.querySelector(".question_content").innerHTML =
                        `<div style="max-height: 150px; overflow-y: auto;">${question.content}</div>`;
                    document.querySelector(".answer_content").innerHTML = answer;

                    const markBtn = document.querySelector(".mark-difficult"); // Nút "Khó"
                    const resolveContainer = document.querySelector(
                    ".resolve-container"); // Nơi hiện nút "Tôi đã hiểu"

                    // Gán ID câu hỏi vào nút
                    markBtn.dataset.questionId = question.id;

                    // Reset giao diện mặc định
                    markBtn.innerHTML = `<i class="far fa-frown me-1"></i> Khó`;
                    markBtn.classList.remove("btn-success", "fw-bold", "text-success");
                    markBtn.classList.add("text-danger");
                    markBtn.style.pointerEvents = "auto";
                    resolveContainer.innerHTML = "";

                    // Gọi API kiểm tra trạng thái
                    fetch(`/user/api/flashcard/check-difficult/${question.id}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.is_difficult) {
                                if (!data.is_resolved) {
                                    // 🔸 Đã đánh dấu "Khó" nhưng chưa "Tôi đã hiểu"
                                    markBtn.innerHTML =
                                        `<i class="fas fa-check-circle me-1"></i> <span>Đã đánh dấu</span>`;
                                    markBtn.style.pointerEvents = "none";

                                    // Hiện nút "Tôi đã hiểu"
                                    resolveContainer.innerHTML = `
                                        <button class="btn btn-warning mark-resolved mt-2 mb-2" data-question-id="${question.id}" style="min-width: 140px;">
                                            <i class="fas fa-check"></i> Tôi đã hiểu
                                        </button>
                                    `;

                                    // Bắt sự kiện click vào "Tôi đã hiểu"
                                    document.querySelector(".mark-resolved").addEventListener("click",
                                    function() {
                                        const qid = this.dataset.questionId;

                                        fetch("/user/flashcard/resolved", {
                                                method: "POST",
                                                headers: {
                                                    "Content-Type": "application/json",
                                                    "X-CSRF-TOKEN": document.querySelector(
                                                        'meta[name="csrf-token"]').content
                                                },
                                                body: JSON.stringify({
                                                    question_id: qid
                                                })
                                            })
                                            .then(res => res.json())
                                            .then(result => {
                                                if (result.status === "resolved") {
                                                    markBtn.innerHTML =
                                                        `<i class="far fa-frown me-1"></i> Khó`;
                                                    markBtn.classList.remove("text-success");
                                                    markBtn.classList.add("text-danger");
                                                    markBtn.style.pointerEvents = "auto";
                                                    resolveContainer.innerHTML = "";
                                                }
                                            });
                                    });

                                } else {
                                    // 🔸 Đã đánh dấu + đã "Tôi đã hiểu"
                                    markBtn.innerHTML = `<i class="far fa-frown me-1"></i> Khó`;
                                    markBtn.classList.remove("text-success");
                                    markBtn.classList.add("text-danger");
                                    markBtn.style.pointerEvents = "auto";

                                    // Cho phép đánh dấu lại
                                    markBtn.addEventListener("click", function() {
                                        const qid = this.dataset.questionId;

                                        fetch("/user/flashcard/mark-difficult", {
                                                method: "POST",
                                                headers: {
                                                    "Content-Type": "application/json",
                                                    "X-CSRF-TOKEN": document.querySelector(
                                                        'meta[name="csrf-token"]').content
                                                },
                                                body: JSON.stringify({
                                                    question_id: qid
                                                })
                                            })
                                            .then(res => res.json())
                                            .then(result => {
                                                if (result.status === "marked") {
                                                    markBtn.innerHTML =
                                                        `<i class="fas fa-check-circle me-1"></i> Đã đánh dấu`;
                                                    markBtn.style.pointerEvents = "none";

                                                    // Hiện nút "Tôi đã hiểu"
                                                    resolveContainer.innerHTML = `
                                                        <button class="btn btn-warning mark-resolved mt-2 mb-2" data-question-id="${qid}" style="min-width: 140px;">
                                                            <i class="fas fa-check"></i> Tôi đã hiểu
                                                        </button>
                                                    `;

                                                    document.querySelector(".mark-resolved")
                                                        .addEventListener("click", function() {
                                                            fetch("/user/flashcard/resolved", {
                                                                    method: "POST",
                                                                    headers: {
                                                                        "Content-Type": "application/json",
                                                                        "X-CSRF-TOKEN": document
                                                                            .querySelector(
                                                                                'meta[name="csrf-token"]'
                                                                                ).content
                                                                    },
                                                                    body: JSON.stringify({
                                                                        question_id: qid
                                                                    })
                                                                })
                                                                .then(res => res.json())
                                                                .then(result => {
                                                                    if (result.status ===
                                                                        "resolved") {
                                                                        markBtn.innerHTML =
                                                                            `<i class="far fa-frown me-1"></i> Khó`;
                                                                        markBtn.classList
                                                                            .remove(
                                                                                "text-success"
                                                                                );
                                                                        markBtn.classList
                                                                            .add(
                                                                                "text-danger"
                                                                                );
                                                                        markBtn.style
                                                                            .pointerEvents =
                                                                            "auto";
                                                                        resolveContainer
                                                                            .innerHTML = "";
                                                                    }
                                                                });
                                                        });
                                                }
                                            });
                                    }, {
                                        once: true
                                    });
                                }
                            } else {
                                // ❌ Chưa từng đánh dấu
                                markBtn.innerHTML = `<i class="far fa-frown me-1"></i> Khó`;
                                markBtn.classList.remove("text-success");
                                markBtn.classList.add("text-danger");
                                markBtn.style.pointerEvents = "auto";
                                resolveContainer.innerHTML = "";
                            }
                        });
                } else if (type === "essay") {
                    // Render form cho câu hỏi essay
                    card.innerHTML = `
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-4">
                                <div></div>
                                <button class="btn btn-light border">Ôn tập</button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center scrollbar-hidden-y">
                                <div style="max-height: 150px; overflow-y: auto;" class="fw-bold fs-4 ms-5 question_content">${question.content}</div>
                                <img class="img-fluid rounded shadow-sm image_path d-none" style="max-width: 40%;">
                            </div>
                            <hr>
                            <meta name="csrf-token" content="{{ csrf_token() }}">
                            <form id="answerForm">
                                <input type="hidden" name="question_id" value="${question.id}">
                                <div class="d-flex row">
                                    <div class="col-9">
                                        <input type="text" name="answeruser_content" id="userAnswer" placeholder="Nhập câu trả lời của bạn" class="form-control me-3">
                                        <small id="error-message" class="text-danger d-none">Xin nhập câu trả lời</small>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-primary text-white check-answer">Kiểm tra</button>
                                    </div>
                                </div>
                            </form>
                            <div class="progress mt-3 w-100" style="height: 25px; max-width: 600px;">
                                <div id="percentBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                    role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    0%
                                </div>
                            </div>
                            <div id="resultContainer" class="mt-2 text-center"></div>
                        </div>
                    `;

                    // Ngăn form reload trang
                    document.querySelector("#answerForm").addEventListener("submit", function(event) {
                        event.preventDefault();
                    });

                    // Bắt sự kiện khi bấm nút "Kiểm tra"
                    document.querySelector(".check-answer").addEventListener("click", function() {
                        console.log("Bắt sự kiện khi bấm nút 'Kiểm tra'");
                        let userAnswer = document.getElementById("userAnswer").value.trim();
                        let questionId = document.querySelector("input[name='question_id']").value;
                        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                            "content");
                        let resultContainer = document.querySelector("#resultContainer");
                        let percentBar = document.getElementById("percentBar");

                        // Kiểm tra nếu người dùng chưa nhập câu trả lời
                        if (!userAnswer) {
                            document.getElementById("error-message").classList.remove("d-none");
                            return;
                        } else {
                            document.getElementById("error-message").classList.add("d-none");
                        }

                        // Gửi câu trả lời người dùng lên server để AI đánh giá
                        fetch("user/ai/check-answer", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": csrfToken
                                },
                                body: JSON.stringify({
                                    question_id: questionId,
                                    answeruser_content: userAnswer
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log("Phản hồi từ AI", data);
                                // Xử lý phản hồi từ server AI
                                if (!data || !data.type || !data.feedback) {
                                    resultContainer.innerHTML =
                                        "<p class='text-danger fw-bold text-center'>Lỗi phản hồi từ AI</p>";
                                    return;
                                }

                                // Nếu phản hồi là JSON string, parse lại
                                if (typeof data.feedback === "string" && isJson(data.feedback)) {
                                    try {
                                        data = JSON.parse(data.feedback);
                                    } catch (error) {
                                        console.error("Lỗi parse JSON:", error);
                                    }
                                }

                                // Nếu là câu hỏi dạng toán
                                if (data.type.includes("math") && data.percent !== undefined) {
                                    let percent = Math.max(0, Math.min(100, data.percent));
                                    percentBar.style.width = percent + "%";
                                    percentBar.setAttribute("aria-valuenow", percent);
                                    percentBar.textContent = percent + "%";
                                    percentBar.classList.remove("d-none");

                                    resultContainer.innerHTML =
                                        `<p class='fw-bold text-center'>Mức độ chính xác: ${percent}%</p>`;
                                }
                                // Nếu là câu hỏi dạng lý thuyết
                                else if (data.type.includes("theory") && data.category && data
                                    .feedback) {
                                    let categoryClass = "text-warning";
                                    if (data.category.toLowerCase().includes("chính xác"))
                                        categoryClass = "text-success";
                                    if (data.category.toLowerCase().includes("sai")) categoryClass =
                                        "text-danger";

                                    resultContainer.innerHTML = `
                                        <p class='fw-bold text-center ${categoryClass}'>Đánh giá: ${data.category}</p>
                                        <p class='text-center'>${data.feedback}</p>
                                    `;

                                    // Ẩn progress bar
                                    percentBar.style.width = "0%";
                                    percentBar.setAttribute("aria-valuenow", 0);
                                    percentBar.textContent = "0%";
                                    percentBar.classList.add("d-none");
                                }
                            })
                            .catch(error => {
                                console.error("Lỗi:", error);
                                resultContainer.innerHTML =
                                    "<p class='text-danger fw-bold text-center'>Lỗi kết nối đến máy chủ</p>";
                            });
                    });

                    // Hàm kiểm tra chuỗi có phải JSON hay không
                    function isJson(str) {
                        try {
                            JSON.parse(str);
                            return true;
                        } catch (e) {
                            return false;
                        }
                    }
                }

                // Hiển thị hình ảnh khi chọn ảnh khác
                document.getElementById('editImageInput').addEventListener('change', function(event) {
                    const fileInput = event.target;
                    const previewImg = document.getElementById('editImagePreview');

                    if (fileInput.files && fileInput.files[0]) {
                        const reader = new FileReader();

                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewImg.classList.remove('d-none');
                        };

                        reader.readAsDataURL(fileInput.files[0]);
                    } else {
                        previewImg.src = '';
                        previewImg.classList.add('d-none');
                    }
                });

                // Hiển thị ảnh nếu có
                let imagePathInput = document.getElementById("editImagePath");
                let imagePreview = document.getElementById("editImagePreview");
                let imagePath = document.querySelector(".image_path");

                if (image) {
                    imagePath.src = `http://127.0.0.1:8000/storage/${encodeURIComponent(image)}`;
                    imagePath.classList.remove("d-none");
                    imagePath.onerror = function() {
                        imagePath.classList.add("d-none");
                    };

                    imagePathInput.value = `http://127.0.0.1:8000/storage/${encodeURIComponent(image)}`;
                    imagePreview.src = imagePathInput.value;
                    imagePreview.classList.remove("d-none");
                } else {
                    imagePath.classList.add("d-none");
                    imagePathInput.value = "";
                    imagePreview.classList.add("d-none");
                }

                // Cập nhật chỉ số câu hỏi
                document.querySelector(".current-question").textContent = currentIndex + 1;
                document.querySelector(".total-questions").textContent = questions.length;

                // Cập nhật form edit & delete
                document.getElementById("editQuestionForm").action =
                    `{{ route('flashcard_define_essay.update', ':id') }}`.replace(':id', cardData.id);
                document.getElementById("deleteForm").action =
                    `{{ route('flashcard_define_essay.destroy', ':id') }}`.replace(':id', cardData.id);

                // Load dữ liệu vào form sửa
                const editQuestionInput = document.getElementById("editQuestionContent");
                if (editQuestionInput) {
                    editQuestionInput.value = question.content;
                }

                const editAnswerInput = document.getElementById("editAnswerContent");
                if (editAnswerInput) {
                    editAnswerInput.value = answer;
                }
            }

            // Nút lùi câu hỏi
            document.querySelector(".prev-question").addEventListener("click", function() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateQuestion();
                }
            });

            // Nút tiến câu hỏi
            document.querySelector(".next-question").addEventListener("click", function() {
                if (currentIndex < questions.length - 1) {
                    currentIndex++;
                    updateQuestion();

                }
            });

            // Bắt sự kiện khi click vào số câu hỏi hiện tại để load lại câu hỏi đầu tiên
            document.querySelector(".current-question").addEventListener("click", function() {
                currentIndex = 0;
                updateQuestion();
            });

            // Bắt sự kiện khi click vào phần khó
            document.querySelectorAll(".mark-difficult").forEach(el => {
                el.addEventListener("click", function() {
                    const questionId = this.dataset.questionId; // Lấy ID câu hỏi

                    fetch("{{ route('flashcard.mark_difficult') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                question_id: questionId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            // ✅ Cập nhật nút "Khó"
                            this.innerHTML =
                                '<i class="fas fa-check-circle me-1"></i> Đã đánh dấu';
                            this.classList.remove("text-danger");
                            this.classList.add("text-success");
                            this.style.pointerEvents = "none";

                            // ✅ Hiện lại nút "Tôi đã hiểu"
                            const resolveContainer = document.querySelector(
                                ".resolve-container");
                            resolveContainer.innerHTML = `
                                <button class="btn btn-warning mark-resolved mt-2 mb-2" data-question-id="${questionId}" style="min-width: 140px;">
                                    <i class="fas fa-check"></i> Tôi đã hiểu
                                </button>
                            `;

                            // Gắn sự kiện cho nút "Tôi đã hiểu"
                            document.querySelector(".mark-resolved").addEventListener("click",
                                function() {
                                    fetch("{{ route('flashcard.mark_resolved') }}", {
                                            method: "POST",
                                            headers: {
                                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                                "Content-Type": "application/json"
                                            },
                                            body: JSON.stringify({
                                                question_id: questionId
                                            })
                                        })
                                        .then(res => res.json())
                                        .then(result => {
                                            if (result.status === "resolved") {
                                                // ✅ Reset lại giao diện "Khó"
                                                el.innerHTML =
                                                    '<i class="far fa-frown me-1"></i> Khó';
                                                el.classList.remove("text-success");
                                                el.classList.add("text-danger");
                                                el.style.pointerEvents = "auto";

                                                resolveContainer.innerHTML = '';
                                            }
                                        });
                                });
                        })
                        .catch(err => {
                            alert("Lỗi khi đánh dấu thẻ khó.");
                            console.error(err);
                        });
                });
            });

            // Gọi API khi load trang lần đầu
            fetchQuestions();
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
