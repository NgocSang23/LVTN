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

            <hr class="mb-4">

            <!-- Ô nhập số lượng thẻ -->
            <div class="mb-4 d-flex align-items-center gap-3">
                <label for="number-of-cards" class="fw-semibold mb-0">Số lượng thẻ muốn tạo:</label>
                <input type="number" id="number-of-cards" min="1" max="50" value="1" class="form-control"
                    style="width: 100px; border-radius: 8px;">
                <button type="button" id="create-multiple-cards" class="btn btn-outline-success"
                    style="border-radius: 8px;">
                    Tạo thẻ
                </button>
            </div>

            <div id="flashcard-content">
                <!-- Thẻ đầu tiên mặc định -->
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

            @if (auth()->user()->roles === 'teacher' && count($myClassrooms) > 0)
                <div class="mb-4">
                    <label for="classroom_id" class="form-label fw-semibold">Chia sẻ ngay vào lớp học (tuỳ chọn):</label>
                    <select class="form-select" name="classroom_id" id="classroom_id" style="border-radius: 8px;">
                        <option value="">-- Không chia sẻ --</option>
                        @foreach ($myClassrooms as $classroom)
                            <option value="{{ $classroom->id }}">{{ $classroom->name }} ({{ $classroom->code }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">Bạn có thể chia sẻ bài kiểm tra này trực tiếp với một lớp học.</div>
                    @error('classroom_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif

            <div class="d-flex justify-content-end align-items-center mt-4 gap-2">
                <button type="button" id="add-card" class="btn btn-outline-primary"
                    style="border-radius: 8px; padding: 8px 20px;">+ THÊM THẺ</button>
                <input type="submit" class="btn btn-primary text-white" value="Tạo và ôn luyện"
                    style="border-radius: 8px; padding: 8px 20px;">
            </div>
        </form>
    </div>

    <!-- Modal thông báo lỗi -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Thông báo lỗi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Đóng"></button>
                </div>
                <div class="modal-body" id="errorModalBody">
                    <!-- Nội dung lỗi sẽ được thay đổi bằng JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- Các biến DOM chính ---
            const flashcardContent = document.getElementById("flashcard-content");
            const addCardBtn = document.getElementById("add-card");
            const createMultipleBtn = document.getElementById("create-multiple-cards");
            const numberOfCardsInput = document.getElementById("number-of-cards");

            // Bootstrap modal instance cho modal lỗi
            const errorModalEl = document.getElementById('errorModal');
            const errorModalBody = document.getElementById('errorModalBody');
            const bootstrapErrorModal = new bootstrap.Modal(errorModalEl);

            /**
             * Hàm hiển thị modal lỗi với message truyền vào
             * @param {string} message - Nội dung lỗi cần hiển thị
             */
            function showErrorModal(message) {
                errorModalBody.textContent = message; // Cập nhật nội dung modal
                bootstrapErrorModal.show(); // Hiển thị modal
            }

            /**
             * Tạo 1 thẻ flashcard mới với số thứ tự index
             * @param {number} index - số thứ tự của thẻ flashcard (1-based)
             * @returns {HTMLElement} - phần tử DOM thẻ flashcard
             */
            function createFlashcardCard(index) {
                const card = document.createElement("div");
                card.classList.add("card", "mb-4", "flashcard");
                card.style.borderRadius = "10px";
                card.style.border = "1px solid #e0e0e0";

                card.innerHTML = `
              <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                      <span class="fw-bold card-number">Câu ${index}</span>
                      <button type="button" class="btn btn-sm btn-outline-danger remove-card" style="border-radius: 50px; padding: 6px 12px;">Xóa</button>
                  </div>
                  <div class="row">
                      <div class="col-md-6 mb-3">
                          <input type="text" name="question_content[]" placeholder="Thuật ngữ" class="form-control" style="border-radius: 8px;">
                      </div>
                      <div class="col-md-6 mb-3">
                          <input type="text" name="answer_content[]" placeholder="Định nghĩa" class="form-control" style="border-radius: 8px;">
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-md-6 mb-3">
                          <input type="file" name="image_name[]" class="form-control image-input" accept="image/*" style="border-radius: 8px;">
                      </div>
                      <div class="col-md-6">
                          <div class="preview-container text-center d-none">
                              <img src="" width="80" alt="Xem trước ảnh" class="image-preview d-none" style="border-radius: 8px; border: 1px solid #ddd;">
                          </div>
                      </div>
                  </div>
              </div>
          `;
                return card;
            }

            /**
             * Cập nhật lại số thứ tự cho tất cả thẻ flashcard hiện có trên trang
             */
            function updateCardNumbers() {
                const cards = document.querySelectorAll(".flashcard");
                cards.forEach((card, idx) => {
                    const cardNumber = card.querySelector(".card-number");
                    if (cardNumber) {
                        cardNumber.textContent = "Câu " + (idx + 1);
                    }
                });
            }

            // Xử lý sự kiện xóa thẻ flashcard khi click nút Xóa
            flashcardContent.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-card")) {
                    e.target.closest(".flashcard").remove();
                    updateCardNumbers();
                }
            });

            // Xử lý thêm 1 thẻ flashcard mới khi click nút + THÊM THẺ
            addCardBtn.addEventListener("click", function() {
                const cardCount = document.querySelectorAll(".flashcard").length + 1;
                const newCard = createFlashcardCard(cardCount);
                flashcardContent.appendChild(newCard);
            });

            // Xử lý tạo nhiều thẻ flashcard theo số lượng nhập vào
            createMultipleBtn.addEventListener("click", function() {
                let count = parseInt(numberOfCardsInput.value);

                // Kiểm tra đầu vào: phải là số, >= 1, <= 50
                if (isNaN(count) || count < 1) {
                    showErrorModal("Vui lòng nhập số lượng thẻ hợp lệ (tối thiểu 1).");
                    return;
                }
                if (count > 50) {
                    showErrorModal("Số lượng thẻ tối đa là 50.");
                    return;
                }

                // Xóa hết các thẻ flashcard hiện có
                flashcardContent.innerHTML = "";

                // Tạo thẻ mới theo số lượng đã nhập
                for (let i = 1; i <= count; i++) {
                    let newCard = createFlashcardCard(i);
                    flashcardContent.appendChild(newCard);
                }
            });

            // Xử lý preview ảnh khi chọn file input có class image-input
            document.addEventListener("change", function(event) {
                if (event.target.classList.contains("image-input")) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Tìm thẻ cha chứa input file và vùng preview
                            const parentCard = event.target.closest(".row");
                            if (parentCard) {
                                const imgDiv = parentCard.querySelector(".preview-container");
                                if (imgDiv) imgDiv.classList.remove("d-none");
                                const img = parentCard.querySelector(".image-preview");
                                if (img) {
                                    img.src = e.target.result;
                                    img.classList.remove("d-none");
                                }
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });

            document.querySelector('select[name="subject_id"]').addEventListener("change", async function() {
                const subjectId = this.value;
                const subjectName = this.options[this.selectedIndex].text;
                const numberOfCards = parseInt(document.getElementById("number-of-cards").value || 3);

                try {
                    const res = await fetch("user/ai/suggest-topic", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: JSON.stringify({
                            subject_name: subjectName,
                            count: numberOfCards
                        })
                    });

                    const data = await res.json();

                    if (data.error) {
                        showErrorModal(data.error);
                        return;
                    }

                    // Xoá thẻ cũ
                    flashcardContent.innerHTML = "";

                    // Gắn dữ liệu AI vào các thẻ mới
                    data.data.forEach((item, idx) => {
                        const card = createFlashcardCard(idx + 1);

                        card.querySelector('input[name="question_content[]"]').value = item
                            .question || '';
                        card.querySelector('input[name="answer_content[]"]').value = item
                            .answer || '';

                        if (item.image_url) {
                            const imgPreview = card.querySelector(".image-preview");
                            const previewContainer = card.querySelector(".preview-container");

                            imgPreview.src = item.image_url;
                            imgPreview.classList.remove("d-none");
                            previewContainer.classList.remove("d-none");
                        }

                        flashcardContent.appendChild(card);
                    });
                } catch (err) {
                    showErrorModal("Không thể lấy dữ liệu gợi ý từ AI.");
                    console.error(err);
                }
            });
        });
    </script>
@endsection
