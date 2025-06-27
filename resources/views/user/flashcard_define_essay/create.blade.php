@extends('user.master')

@section('title', 'Thêm thẻ mới')

@section('content')
    <style>
        .preview-container {
            max-width: 80px;
            height: 80px;
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
    </style>

    <div class="container my-5">
        <div class="mb-4 text-center">
            <h1 class="h3 fw-bold text-primary">Tạo khái niệm hoặc định nghĩa mới</h1>
            <p class="text-muted">Điền thông tin bên dưới để thêm thẻ ghi nhớ</p>
        </div>
        <form method="post" id="flashcard-form" action="{{ route('flashcard_define_essay.store') }}"
            enctype="multipart/form-data"
            style="background: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            @csrf
            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
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
                <div class="col-md-6">
                    <input type="text" name="topic_title" placeholder="Nhập chủ đề, ví dụ: Sinh học - Chương 22"
                        class="form-control" style="border-radius: 8px;">
                    @error('topic_title')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mb-4 d-flex justify-content-center gap-4">
                <label class="d-flex align-items-center gap-2">
                    <input type="radio" name="question_type" value="definition" checked> <span>Khái niệm / Định
                        nghĩa</span>
                </label>
                <label class="d-flex align-items-center gap-2">
                    <input type="radio" name="question_type" value="essay"> <span>Tự luận</span>
                </label>
            </div>

            <hr class="mb-4">

            <div id="flashcard-content">
                <div class="card mb-4 flashcard" style="border-radius: 10px; border: 1px solid #e0e0e0;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold card-number">Câu 1</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-card"
                                style="border-radius: 50px; padding: 6px 12px;">Xóa</button>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="question_content[]" placeholder="Thuật ngữ" class="form-control"
                                    style="border-radius: 8px;">
                                @error('question_content.0')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" name="answer_content[]" placeholder="Định nghĩa" class="form-control"
                                    style="border-radius: 8px;">
                                @error('answer_content.0')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="file" name="image_name[]" class="form-control image-input" accept="image/*"
                                    style="border-radius: 8px;">
                                @error('image_name.0')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="preview-container text-center d-none">
                                    <img src="" width="80" alt="Xem trước ảnh" class="image-preview d-none"
                                        style="border-radius: 8px; border: 1px solid #ddd;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @can('teacher')
                <div class="mb-4">
                    <label for="classroom_id" class="form-label">Chia sẻ ngay vào lớp học (tuỳ chọn):</label>
                    {{-- <select class="form-select" name="classroom_id" id="classroom_id" style="border-radius: 8px;">
                        <option value="">-- Không chia sẻ --</option>
                        @foreach ($myClassrooms as $classroom)
                            <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                        @endforeach
                    </select> --}}
                </div>
            @endcan

            <div class="d-flex justify-content-end align-items-center mt-4">
                <button type="button" id="add-card" class="btn btn-outline-primary me-2"
                    style="border-radius: 8px; padding: 8px 20px;">+ THÊM THẺ</button>
                <input type="submit" class="btn btn-primary text-white" value="Tạo và ôn luyện"
                    style="border-radius: 8px; padding: 8px 20px;">
            </div>
        </form>
    </div>

    {{-- <script>
        document.addEventListener("DOMContentLoaded", function() {
            let form = document.getElementById("flashcard-form");
            let questionType = document.querySelectorAll("input[name='question_type']");

            function updateForm() {
                let selectType = document.querySelector('input[name="question_type"]:checked').value;
                form.action = selectType === "definition"
                    ? "{{ route('flashcard_define.store') }}"
                    : "{{ route('flashcard_essay.store') }}";
            }

            questionType.forEach(radio => {
                radio.addEventListener("change", updateForm);
            });

            updateForm();
        });
    </script> --}}

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let flashcardContent = document.getElementById("flashcard-content");
            let addCard = document.getElementById("add-card");

            // Sự kiện khi click nút "Thêm thẻ"
            addCard.addEventListener("click", function() {
                // Đếm số lượng flashcard hiện tại để gán số thứ tự
                let cardCount = document.querySelectorAll(".flashcard").length + 1;

                let newCard = document.createElement("div");
                newCard.classList.add("card", "mb-3", "flashcard");

                newCard.innerHTML = `
                    <div class="card-body">
                        <!-- Header của card gồm số thứ tự và nút Xóa -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold card-number">Câu ${cardCount}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-card" style="border-radius: 50px;">Xóa</button>
                        </div>

                        <!-- Input Thuật ngữ và Định nghĩa -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="question_content[]" placeholder="Thuật ngữ" class="form-control" style="border-radius: 8px;">
                                @error('question_content.0')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" name="answer_content[]" placeholder="Định nghĩa" class="form-control" style="border-radius: 8px;">
                                @error('answer_content.0')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Input upload ảnh và vùng preview -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="file" name="image_name[]" class="form-control image-input" accept="image/*" style="border-radius: 8px;">
                                @error('image_name.0')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="preview-container text-center d-none"> <!-- container preview ẩn lúc đầu -->
                                    <img src="" width="80" alt="Xem trước ảnh" class="image-preview d-none" style="border-radius: 8px; border: 1px solid #ddd;">
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Thêm thẻ mới vào phần flashcardContent
                flashcardContent.appendChild(newCard);

                // Cập nhật lại số thứ tự cho tất cả thẻ flashcard
                updateCardNumbers();
            });

            // Xử lý sự kiện click cho nút "Xóa"
            flashcardContent.addEventListener("click", function(e) {
                // Nếu click vào nút "remove-card"
                if (e.target.classList.contains("remove-card")) {
                    // Tìm thẻ flashcard gần nhất và xóa nó
                    e.target.closest(".flashcard").remove();
                    updateCardNumbers();
                }
            });

            // Hàm cập nhật số thứ tự cho các flashcard
            function updateCardNumbers() {
                let cards = document.querySelectorAll(".flashcard");
                cards.forEach((card, index) => {
                    let cardNumber = card.querySelector(".card-number");
                    cardNumber.textContent = "Câu " + (index + 1); // set lại số thứ tự 1,2,3...
                });
            }
        });

        // Đợi DOM load xong, sau đó xử lý preview ảnh khi chọn file
        document.addEventListener('DOMContentLoaded', function() {

            // Bắt sự kiện "change" khi chọn file ảnh
            document.addEventListener("change", function(event) {
                // Kiểm tra nếu phần tử change là input file có class "image-input"
                if (event.target.classList.contains("image-input")) {
                    let file = event.target.files[0];
                    if (file) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            // Tìm khối .row cha chứa input và preview
                            let parentCard = event.target.closest(".row");
                            if (parentCard) {
                                // Hiện container chứa ảnh nếu đang ẩn
                                let imgDiv = parentCard.querySelector(".preview-container");
                                if (imgDiv) {
                                    imgDiv.classList.remove("d-none");
                                }
                                // Tìm ảnh preview và hiển thị hình ảnh vừa chọn
                                let img = parentCard.querySelector(".image-preview");
                                if (img) {
                                    img.src = e.target.result;
                                    img.classList.remove("d-none");
                                }
                            }
                        };
                        // Đọc file và chuyển sang dạng base64 để gán vào src của thẻ <img>
                        reader.readAsDataURL(file);
                    }
                }
            });
        });
    </script>

@endsection
