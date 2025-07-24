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

            {{-- Bài kiểm tra nâng cao --}}
            <div class="container mb-4">
                @php
                    $encodedIds = base64_encode(implode(',', $cards->pluck('id')->toArray()));
                @endphp

                <div class="row g-3"> {{-- BỎ justify-content-center --}}
                    <div class="col-12 col-md-4">
                        <a href="{{ route('game.flashcard', ['ids' => $encodedIds]) }}"
                            class="btn btn-outline-warning w-100 py-2">
                            🃏 Flashcard
                        </a>
                    </div>

                    <div class="col-12 col-md-4">
                        <a href="{{ route('game.match', ['ids' => $encodedIds]) }}"
                            class="btn btn-outline-success w-100 py-2">
                            🧩 Tìm cặp
                        </a>
                    </div>

                    @if ($cards->count() > 3)
                        <div class="col-12 col-md-4">
                            <a href="{{ route('game.study', ['ids' => $encodedIds]) }}"
                                class="btn btn-outline-primary w-100 py-2">
                                📚 Học tập
                            </a>
                        </div>
                    @endif

                    <div class="col-12 col-md-4">
                        <a href="{{ route('game.fill_blank', ['ids' => $encodedIds]) }}"
                            class="btn btn-outline-danger w-100 py-2">
                            📝 Điền chỗ trống
                        </a>
                    </div>

                    <div class="col-12 col-md-4">
                        <a href="{{ route('game.essay', ['ids' => $encodedIds]) }}"
                            class="btn btn-outline-info w-100 py-2">
                            ✏️ Tự luận
                        </a>
                    </div>
                </div>
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
                    <h5 class="modal-title" id="editQuestionModalLabel" style="font-weight: 600;">✏️ Chỉnh sửa câu hỏi
                    </h5>
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
        // document.addEventListener("DOMContentLoaded", function() { ... });
        // Dòng này đảm bảo rằng toàn bộ mã JavaScript bên trong sẽ chỉ chạy khi tất cả các phần tử HTML trên trang đã được tải và phân tích cú pháp hoàn tất.
        // Điều này ngăn chặn lỗi khi cố gắng truy cập các phần tử HTML chưa tồn tại.
        document.addEventListener("DOMContentLoaded", function() {

            // let currentIndex = 0;
            // Biến này dùng để lưu trữ chỉ số của câu hỏi hiện tại đang được hiển thị trên giao diện.
            // Bắt đầu từ 0, nghĩa là câu hỏi đầu tiên trong mảng.
            let currentIndex = 0;

            // let questions = [];
            // Mảng này sẽ lưu trữ toàn bộ danh sách các câu hỏi được lấy về từ API.
            let questions = [];

            // const cardId = window.location.pathname.split("/").pop();
            // Dòng này dùng để lấy ID của "thẻ" (card) từ URL hiện tại.
            // Ví dụ: nếu URL là "http://localhost:8000/flashcard/123", thì cardId sẽ là "123".
            // - window.location.pathname: Lấy phần đường dẫn của URL (ví dụ: "/flashcard/123").
            // - .split("/"): Chia chuỗi đường dẫn thành một mảng các chuỗi con dựa trên ký tự "/".
            //   Ví dụ: ["", "flashcard", "123"].
            // - .pop(): Lấy phần tử cuối cùng của mảng, tức là ID thẻ.
            const cardId = window.location.pathname.split("/").pop();

            // Hàm fetch câu hỏi từ API
            // Hàm này có nhiệm vụ gọi API để lấy danh sách các câu hỏi liên quan đến cardId.
            function fetchQuestions() {
                // fetch(): Là một API tích hợp sẵn trong trình duyệt để gửi các yêu cầu mạng (HTTP requests).
                // `http://localhost:8000/api/card_define_essay/${cardId}`: Đây là URL của API sẽ được gọi.
                // ${cardId} là một template literal, cho phép nhúng giá trị của biến cardId vào chuỗi URL.
                fetch(`http://localhost:8000/api/card_define_essay/${cardId}`)
                    // .then(response => response.json()):
                    // Khi nhận được phản hồi từ API, .then() đầu tiên sẽ được thực thi.
                    // response.json() sẽ phân tích cú pháp phản hồi dưới dạng JSON và trả về một Promise khác.
                    .then(response => response.json())
                    // .then(data => { ... }):
                    // Khi dữ liệu JSON đã được phân tích cú pháp thành công, .then() thứ hai sẽ nhận được đối tượng data.
                    .then(data => {
                        // Kiểm tra nếu API trả về dữ liệu thành công (status_code là 200) và có ít nhất một câu hỏi.
                        if (data.status_code === 200 && data.data.length > 0) {
                            // questions = data.data.filter(item => item.question.type === "definition");
                            // Lọc ra các câu hỏi có thuộc tính 'type' là "definition".
                            // Điều này đảm bảo rằng chỉ các câu hỏi định nghĩa được hiển thị.
                            questions = data.data.filter(item => item.question.type === "definition");
                            currentIndex = 0; // Reset chỉ số câu hỏi về câu đầu tiên sau khi tải dữ liệu mới.
                            updateQuestion(); // Gọi hàm để cập nhật giao diện hiển thị câu hỏi đầu tiên.
                        } else {
                            // Nếu không có dữ liệu hoặc API trả về lỗi
                            document.querySelector(".question_content").innerText = "Không có dữ liệu!";
                            document.querySelector(".answer_content").innerText =
                                ""; // Xóa nội dung câu trả lời.
                        }
                    })
                    // .catch(error => { ... }):
                    // Bắt bất kỳ lỗi nào xảy ra trong quá trình fetch API (ví dụ: mất mạng, server không phản hồi).
                    .catch(error => {
                        console.error("Lỗi API:", error); // Ghi lỗi ra console để debug.
                        alert("Không thể tải dữ liệu, vui lòng thử lại sau."); // Thông báo cho người dùng.
                    });
            }

            // Lắng nghe sự kiện lật thẻ flashcard
            // Lấy phần tử có class 'flip-card'. Đây thường là phần tử cha của cả mặt trước và mặt sau thẻ.
            let flipCard = document.querySelector('.flip-card');
            // Kiểm tra xem phần tử flipCard có tồn tại không trước khi thêm lắng nghe sự kiện.
            if (flipCard) {
                // Thêm sự kiện 'click' vào thẻ flashcard.
                flipCard.addEventListener('click', function() {
                    // flipCard.classList.toggle('flipped');
                    // Thêm hoặc xóa class 'flipped' khỏi thẻ.
                    // Class này thường được dùng với CSS để tạo hiệu ứng lật thẻ (ví dụ: dùng transform: rotateY()).
                    flipCard.classList.toggle('flipped');

                    // Lấy phần tử chứa nội dung mặt sau của thẻ.
                    let backCardBody = document.querySelector('.back-card-body');
                    if (backCardBody) {
                        // Nếu thẻ bị lật (có class 'flipped'), hiển thị mặt sau.
                        if (flipCard.classList.contains('flipped')) {
                            backCardBody.style.display = 'block'; // Hiển thị mặt sau.
                        } else {
                            backCardBody.style.display =
                                'none'; // Ẩn mặt sau khi thẻ được lật về mặt trước.
                        }
                        saveAnswer(); // Gọi hàm lưu câu trả lời mỗi khi thẻ được lật.
                    } else {
                        console.error(
                            "Element .back-card-body not found."); // Báo lỗi nếu không tìm thấy phần tử.
                    }
                });
            }

            // Hàm lưu câu trả lời của người dùng khi lật thẻ
            // Hàm này gửi một yêu cầu POST đến server để ghi nhận việc người dùng đã xem một câu hỏi.
            function saveAnswer() {
                // Kiểm tra nếu mảng câu hỏi rỗng hoặc không tồn tại thì thoát khỏi hàm.
                if (!questions || questions.length === 0) return;

                // Lấy đối tượng câu hỏi hiện tại từ mảng 'questions' dựa trên 'currentIndex'.
                let question = questions[currentIndex].question;

                // fetch("{{ route('flashcard_define_essay.save') }}", { ... });
                // Gửi yêu cầu POST đến endpoint lưu trạng thái flashcard.
                // {{ route('flashcard_define_essay.save') }} là một cú pháp của Blade (framework PHP Laravel)
                // để tạo ra URL cho một route đã định nghĩa.
                fetch("{{ route('flashcard_define_essay.save') }}", {
                        method: "POST", // Phương thức HTTP là POST.
                        headers: {
                            // "X-CSRF-TOKEN": "{{ csrf_token() }}":
                            // Đây là một token bảo mật (Cross-Site Request Forgery) cần thiết cho các yêu cầu POST trong Laravel.
                            // {{ csrf_token() }} cũng là cú pháp Blade để lấy token CSRF.
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json" // Chỉ định kiểu nội dung của yêu cầu là JSON.
                        },
                        body: JSON.stringify({ // Chuyển đổi đối tượng JavaScript thành chuỗi JSON để gửi đi.
                            question_id: question.id // Gửi ID của câu hỏi hiện tại lên server.
                        })
                    })
                    // .then(async response => { ... });
                    // Khi nhận được phản hồi từ server.
                    .then(async response => {
                        if (!response.ok) { // Nếu phản hồi không thành công (ví dụ: status 4xx hoặc 5xx).
                            const text = await response.text(); // Đọc nội dung phản hồi dưới dạng văn bản.
                            throw new Error(
                                `Lỗi Server: ${response.status}, ${text}`
                            ); // Ném lỗi với thông tin chi tiết.
                        }
                        return response.json(); // Trả về dữ liệu JSON nếu thành công.
                    })
                    .then(data => {
                        console.log("Đã lưu câu trả lời", data); // Ghi thông báo thành công ra console.
                    })
                    .catch(error => {
                        console.error("Lỗi khi lưu câu trả lời:", error); // Bắt và ghi lỗi nếu có.
                    });
            }

            // Hàm cập nhật giao diện câu hỏi khi next/prev hoặc load dữ liệu
            // Hàm này chịu trách nhiệm hiển thị câu hỏi và câu trả lời hiện tại lên giao diện người dùng,
            // cũng như cập nhật trạng thái của các nút "Khó" và "Tôi đã hiểu".
            function updateQuestion() {
                // Nếu không có câu hỏi nào, thoát khỏi hàm.
                if (questions.length === 0) return;

                // Lấy dữ liệu của câu hỏi hiện tại từ mảng 'questions'.
                let cardData = questions[currentIndex];
                let question = cardData.question; // Lấy đối tượng câu hỏi.
                let topic = question.topic; // Lấy thông tin chủ đề.
                // Lấy nội dung câu trả lời. Nếu không có đáp án, hiển thị "Chưa có đáp án".
                let answer = (question.answers && question.answers.length > 0) ? question.answers[0].content :
                    "Chưa có đáp án";
                // Lấy đường dẫn ảnh. Nếu không có ảnh, đặt là null.
                let image = (question.images && question.images.length > 0) ? question.images[0].path :
                    null;

                // Chuỗi HTML để xây dựng danh sách câu hỏi trong bảng
                let listQuestion = "";

                // Duyệt qua tất cả các câu hỏi để tạo hàng cho bảng hiển thị danh sách định nghĩa.
                questions.forEach((cardData) => {
                    let question = cardData.question;
                    let answer = (question.answers && question.answers.length > 0) ? question.answers[0]
                        .content : "Chưa có đáp án";

                    // Thêm HTML cho mỗi hàng vào chuỗi listQuestion.
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

                // Cập nhật tiêu đề chủ đề.
                document.querySelector(".topic_title").innerText = 'Chủ đề: ' + topic.title;

                // Cập nhật nội dung câu hỏi, giới hạn chiều cao và thêm cuộn nếu cần.
                document.querySelector(".question_content").innerHTML =
                    `<div style="max-height: 150px; overflow-y: auto;">${question.content}</div>`;
                // Cập nhật nội dung câu trả lời.
                document.querySelector(".answer_content").innerHTML = answer;

                const markBtn = document.querySelector(".mark-difficult"); // Nút "Khó"
                const resolveContainer = document.querySelector(
                    ".resolve-container"); // Nơi hiện nút "Tôi đã hiểu"

                // Gán ID câu hỏi vào thuộc tính data-question-id của nút "Khó".
                markBtn.dataset.questionId = question.id;

                // Reset giao diện mặc định của nút "Khó"
                markBtn.innerHTML = `<i class="far fa-frown me-1"></i> Khó`; // Nội dung nút.
                // Xóa các class CSS đã thêm trước đó (nếu có).
                markBtn.classList.remove("btn-success", "fw-bold", "text-success");
                markBtn.classList.add("text-danger"); // Thêm class màu đỏ.
                markBtn.style.pointerEvents = "auto"; // Cho phép click lại.
                resolveContainer.innerHTML = ""; // Xóa nội dung của vùng chứa nút "Tôi đã hiểu".

                // Gọi API kiểm tra trạng thái của câu hỏi (đã đánh dấu khó chưa, đã giải quyết chưa).
                fetch(`/user/api/flashcard/check-difficult/${question.id}`)
                    .then(res => res.json())
                    .then(data => {
                        // Nếu câu hỏi đã được đánh dấu là "Khó" (is_difficult là true)
                        if (data.is_difficult) {
                            // Nếu đã đánh dấu "Khó" nhưng chưa "Tôi đã hiểu"
                            if (!data.is_resolved) {
                                // Cập nhật nội dung và trạng thái của nút "Khó".
                                markBtn.innerHTML =
                                    `<i class="fas fa-check-circle me-1"></i> <span>Đã đánh dấu</span>`;
                                markBtn.style.pointerEvents =
                                    "none"; // Vô hiệu hóa nút "Khó" để không đánh dấu lại.

                                // Hiện nút "Tôi đã hiểu"
                                resolveContainer.innerHTML = `
                                    <button class="btn btn-warning mark-resolved mt-2 mb-2" data-question-id="${question.id}" style="min-width: 140px;">
                                        <i class="fas fa-check"></i> Tôi đã hiểu
                                    </button>
                                `;

                                // Bắt sự kiện click vào "Tôi đã hiểu"
                                // Khi nút "Tôi đã hiểu" được click, gửi yêu cầu POST để đánh dấu câu hỏi là đã giải quyết.
                                document.querySelector(".mark-resolved").addEventListener("click",
                                    function() {
                                        const qid = this.dataset.questionId; // Lấy ID câu hỏi từ nút.

                                        fetch("/user/flashcard/resolved", {
                                                method: "POST",
                                                headers: {
                                                    "Content-Type": "application/json",
                                                    "X-CSRF-TOKEN": document.querySelector(
                                                            'meta[name="csrf-token"]')
                                                        .content // Lấy CSRF token từ thẻ meta.
                                                },
                                                body: JSON.stringify({
                                                    question_id: qid
                                                })
                                            })
                                            .then(res => res.json())
                                            .then(result => {
                                                if (result.status === "resolved") {
                                                    // Nếu server trả về trạng thái "resolved", reset lại giao diện của nút "Khó".
                                                    markBtn.innerHTML =
                                                        `<i class="far fa-frown me-1"></i> Khó`;
                                                    markBtn.classList.remove("text-success");
                                                    markBtn.classList.add("text-danger");
                                                    markBtn.style.pointerEvents =
                                                        "auto"; // Kích hoạt lại nút "Khó".
                                                    resolveContainer.innerHTML =
                                                        ""; // Ẩn nút "Tôi đã hiểu".
                                                }
                                            });
                                    });

                            } else {
                                // 🔸 Đã đánh dấu "Khó" và đã "Tôi đã hiểu"
                                // Trong trường hợp này, nút "Khó" sẽ được hiển thị như bình thường
                                // và có thể click để đánh dấu lại.
                                markBtn.innerHTML = `<i class="far fa-frown me-1"></i> Khó`;
                                markBtn.classList.remove("text-success");
                                markBtn.classList.add("text-danger");
                                markBtn.style.pointerEvents = "auto";

                                // Cho phép đánh dấu lại (gắn lại sự kiện click)
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
                                                // Nếu server trả về trạng thái "marked", cập nhật giao diện.
                                                markBtn.innerHTML =
                                                    `<i class="fas fa-check-circle me-1"></i> Đã đánh dấu`;
                                                markBtn.style.pointerEvents = "none";

                                                // Hiện nút "Tôi đã hiểu" sau khi đánh dấu khó.
                                                resolveContainer.innerHTML = `
                                                    <button class="btn btn-warning mark-resolved mt-2 mb-2" data-question-id="${qid}" style="min-width: 140px;">
                                                        <i class="fas fa-check"></i> Tôi đã hiểu
                                                    </button>
                                                `;

                                                // Gắn sự kiện cho nút "Tôi đã hiểu" mới tạo.
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
                                                                    // Reset lại giao diện nút "Khó" sau khi đã "Tôi đã hiểu".
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
                                    once: true // Đảm bảo sự kiện chỉ được gắn một lần để tránh lỗi.
                                });
                            }
                        } else {
                            // ❌ Chưa từng đánh dấu "Khó"
                            // Reset lại trạng thái của nút "Khó" về mặc định.
                            markBtn.innerHTML = `<i class="far fa-frown me-1"></i> Khó`;
                            markBtn.classList.remove("text-success");
                            markBtn.classList.add("text-danger");
                            markBtn.style.pointerEvents = "auto";
                            resolveContainer.innerHTML = ""; // Đảm bảo nút "Tôi đã hiểu" không hiển thị.
                        }
                    });

                // Hiển thị hình ảnh khi chọn ảnh khác (trong form chỉnh sửa)
                // Lắng nghe sự kiện 'change' trên input file có ID 'editImageInput'.
                document.getElementById('editImageInput').addEventListener('change', function(event) {
                    const fileInput = event.target;
                    const previewImg = document.getElementById(
                        'editImagePreview'); // Element để hiển thị ảnh xem trước.

                    // Kiểm tra xem có file nào được chọn không.
                    if (fileInput.files && fileInput.files[0]) {
                        const reader = new FileReader(); // Tạo đối tượng FileReader để đọc nội dung file.

                        reader.onload = function(e) {
                            previewImg.src = e.target
                                .result; // Gán data URL của ảnh vào thuộc tính src của thẻ <img>.
                            previewImg.classList.remove('d-none'); // Hiển thị ảnh xem trước.
                        };

                        reader.readAsDataURL(fileInput.files[0]); // Đọc file dưới dạng data URL.
                    } else {
                        previewImg.src = ''; // Xóa src nếu không có file.
                        previewImg.classList.add('d-none'); // Ẩn ảnh xem trước.
                    }
                });

                // Hiển thị ảnh hiện có (nếu có) khi load câu hỏi
                let imagePathInput = document.getElementById("editImagePath"); // Input ẩn chứa đường dẫn ảnh.
                let imagePreview = document.getElementById("editImagePreview"); // Thẻ img để hiển thị ảnh.
                let imagePath = document.querySelector(
                    ".image_path"); // Thẻ img khác để hiển thị ảnh trên flashcard.

                if (image) { // Nếu có đường dẫn ảnh từ dữ liệu câu hỏi.
                    imagePath.src =
                        `http://localhost:8000/storage/${encodeURIComponent(image)}`; // Đặt src cho ảnh hiển thị trên flashcard.
                    imagePath.classList.remove("d-none"); // Hiển thị ảnh.
                    // Xử lý lỗi nếu ảnh không tải được.
                    imagePath.onerror = function() {
                        imagePath.classList.add("d-none"); // Ẩn ảnh nếu bị lỗi.
                    };

                    // Cập nhật giá trị và hiển thị ảnh trong form chỉnh sửa.
                    imagePathInput.value = `http://localhost:8000/storage/${encodeURIComponent(image)}`;
                    imagePreview.src = imagePathInput.value;
                    imagePreview.classList.remove("d-none");
                } else {
                    // Nếu không có ảnh, ẩn tất cả các phần tử liên quan đến ảnh.
                    imagePath.classList.add("d-none");
                    imagePathInput.value = "";
                    imagePreview.classList.add("d-none");
                }

                // Cập nhật chỉ số câu hỏi (ví dụ: "1/10")
                document.querySelector(".current-question").textContent = currentIndex +
                    1; // Số câu hỏi hiện tại (bắt đầu từ 1).
                document.querySelector(".total-questions").textContent = questions.length; // Tổng số câu hỏi.

                // Cập nhật form edit & delete (URL action cho form)
                // Cập nhật action của form chỉnh sửa câu hỏi với ID của câu hỏi hiện tại.
                document.getElementById("editQuestionForm").action =
                    `{{ route('flashcard_define_essay.update', ':id') }}`.replace(':id', cardData.id);
                // Cập nhật action của form xóa câu hỏi.
                document.getElementById("deleteForm").action =
                    `{{ route('flashcard_define_essay.destroy', ':id') }}`.replace(':id', cardData.id);

                // Load dữ liệu câu hỏi và trả lời vào các input trong form sửa.
                const editQuestionInput = document.getElementById("editQuestionContent");
                if (editQuestionInput) {
                    editQuestionInput.value = question.content; // Đặt nội dung câu hỏi vào input.
                }

                const editAnswerInput = document.getElementById("editAnswerContent");
                if (editAnswerInput) {
                    editAnswerInput.value = answer; // Đặt nội dung câu trả lời vào input.
                }
            }

            // Nút lùi câu hỏi
            document.querySelector(".prev-question").addEventListener("click", function() {
                // Nếu currentIndex lớn hơn 0, tức là vẫn còn câu hỏi phía trước.
                if (currentIndex > 0) {
                    currentIndex--; // Giảm chỉ số câu hỏi.
                    updateQuestion(); // Cập nhật giao diện.
                }
            });

            // Nút tiến câu hỏi
            document.querySelector(".next-question").addEventListener("click", function() {
                // Nếu currentIndex nhỏ hơn tổng số câu hỏi trừ 1 (tức là chưa phải câu cuối cùng).
                if (currentIndex < questions.length - 1) {
                    currentIndex++; // Tăng chỉ số câu hỏi.
                    updateQuestion(); // Cập nhật giao diện.
                }
            });

            // Bắt sự kiện khi click vào số câu hỏi hiện tại để load lại câu hỏi đầu tiên
            // (Đây là một tính năng tiện ích để người dùng có thể quay về câu hỏi đầu tiên nhanh chóng).
            document.querySelector(".current-question").addEventListener("click", function() {
                currentIndex = 0; // Đặt chỉ số về 0.
                updateQuestion(); // Cập nhật giao diện.
            });

            // Bắt sự kiện khi click vào nút "Khó"
            // Duyệt qua tất cả các phần tử có class "mark-difficult" (để đảm bảo gắn sự kiện cho cả nút "Khó" ở lần đầu và sau khi reset)
            document.querySelectorAll(".mark-difficult").forEach(el => {
                el.addEventListener("click", function() {
                    const questionId = this.dataset
                        .questionId; // Lấy ID câu hỏi từ thuộc tính data-question-id của nút.

                    // Gửi yêu cầu POST để đánh dấu câu hỏi là khó.
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
                            // ✅ Cập nhật giao diện nút "Khó" sau khi đánh dấu thành công
                            this.innerHTML =
                                '<i class="fas fa-check-circle me-1"></i> Đã đánh dấu'; // Thay đổi nội dung nút.
                            this.classList.remove("text-danger"); // Xóa màu đỏ.
                            this.classList.add("text-success"); // Thêm màu xanh lá cây.
                            this.style.pointerEvents =
                                "none"; // Vô hiệu hóa nút để tránh đánh dấu lại.

                            // ✅ Hiện lại nút "Tôi đã hiểu"
                            const resolveContainer = document.querySelector(
                                ".resolve-container");
                            resolveContainer.innerHTML = `
                                <button class="btn btn-warning mark-resolved mt-2 mb-2" data-question-id="${questionId}" style="min-width: 140px;">
                                    <i class="fas fa-check"></i> Tôi đã hiểu
                                </button>
                            `;

                            // Gắn sự kiện cho nút "Tôi đã hiểu" mới được thêm vào DOM.
                            document.querySelector(".mark-resolved").addEventListener("click",
                                function() {
                                    // Gửi yêu cầu POST để đánh dấu câu hỏi là đã giải quyết.
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
                                                // ✅ Reset lại giao diện "Khó" sau khi đã "Tôi đã hiểu"
                                                el.innerHTML = // Sử dụng 'el' vì 'this' ở đây là nút 'mark-resolved'.
                                                    '<i class="far fa-frown me-1"></i> Khó';
                                                el.classList.remove("text-success");
                                                el.classList.add("text-danger");
                                                el.style.pointerEvents =
                                                    "auto"; // Kích hoạt lại nút "Khó".
                                            }
                                        });
                                });
                        });
                });
            });

            // Gọi hàm fetchQuestions khi DOM đã tải xong
            // Đây là điểm khởi đầu, hàm này sẽ được gọi ngay sau khi trang được tải để lấy dữ liệu câu hỏi ban đầu.
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
