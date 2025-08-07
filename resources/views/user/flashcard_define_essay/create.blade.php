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

        input.form-control:focus,
        select.form-select:focus {
            outline: none;
            box-shadow: none;
            border-color: #dee2e6;
            /* Giữ lại màu viền mặc định của Bootstrap */
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
                    <select class="form-select" name="subject_id" id="subject-select" style="border-radius: 8px;">
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
                    <select class="form-select" name="topic_title" id="topic-select" style="border-radius: 8px;" disabled>
                        <option selected disabled>Chọn chủ đề</option>
                    </select>
                    @error('topic_title')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <hr class="mb-4">

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
                <div class="card mb-4 flashcard" style="border-radius: 10px; border: 1px solid #e0e0e0;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold card-number">Câu 1</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-card"
                                style="border-radius: 50px; padding: 6px 12px;">Xóa</button>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <select name="question_content[]" class="form-select question-select"
                                    style="border-radius: 8px;" disabled>
                                    <option selected disabled>Chọn thuật ngữ</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <select name="answer_content[]" class="form-select answer-select"
                                    style="border-radius: 8px;" disabled>
                                    <option selected disabled>Chọn định nghĩa</option>
                                </select>
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

    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Thông báo lỗi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Đóng"></button>
                </div>
                <div class="modal-body" id="errorModalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const subjectSelect = document.getElementById('subject-select');
            const topicSelect = document.getElementById('topic-select');
            const flashcardContent = document.getElementById("flashcard-content");
            const numberOfCardsInput = document.getElementById("number-of-cards");

            const errorModalEl = document.getElementById('errorModal');
            const errorModalBody = document.getElementById('errorModalBody');
            const bootstrapErrorModal = new bootstrap.Modal(errorModalEl);

            // Disable các nút thao tác ban đầu
            document.getElementById("add-card").disabled = true;
            document.getElementById("create-multiple-cards").disabled = true;
            numberOfCardsInput.disabled = true;

            let cachedFlashcards = [];

            function showErrorModal(message) {
                errorModalBody.textContent = message;
                bootstrapErrorModal.show();
            }

            function getUsedQuestions() {
                return Array.from(document.querySelectorAll('.question-select'))
                    .map(select => select.value)
                    .filter(Boolean);
            }

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
                                <select name="question_content[]" class="form-select question-select" style="border-radius: 8px;" disabled>
                                    <option selected disabled>Chọn thuật ngữ</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <select name="answer_content[]" class="form-select answer-select" style="border-radius: 8px;" disabled>
                                    <option selected disabled>Chọn định nghĩa</option>
                                </select>
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

            function renderFlashcards(qaPairs) {
                const count = parseInt(numberOfCardsInput.value) || qaPairs.length;
                flashcardContent.innerHTML = "";

                for (let i = 0; i < count; i++) {
                    const item = qaPairs[i] ?? qaPairs[0];
                    const newCard = createFlashcardCard(i + 1);
                    const questionSelect = newCard.querySelector('.question-select');
                    const answerSelect = newCard.querySelector('.answer-select');

                    // Thêm các lựa chọn vào dropdown
                    qaPairs.forEach(entry => {
                        const option = document.createElement('option');
                        option.value = entry.question;
                        option.textContent = entry.question;
                        option.dataset.answer = entry.answer;
                        questionSelect.appendChild(option);
                    });

                    questionSelect.disabled = false;
                    answerSelect.disabled = false;

                    questionSelect.addEventListener("change", function() {
                        const selectedQuestion = this.value;
                        const selectedOption = this.options[this.selectedIndex];
                        const answer = selectedOption?.dataset.answer || '';

                        // ✅ Kiểm tra trùng (ngoại trừ chính thẻ đang chọn)
                        const allSelects = Array.from(document.querySelectorAll('.question-select'));
                        const duplicates = allSelects.filter(sel => sel !== this && sel.value ===
                            selectedQuestion);

                        if (duplicates.length > 0) {
                            showErrorModal(`Thuật ngữ "${selectedQuestion}" đã được chọn ở một thẻ khác.`);
                            this.value = '';
                            answerSelect.innerHTML = '<option selected disabled>Chọn định nghĩa</option>';
                            return;
                        }

                        // ✅ Gán định nghĩa tương ứng
                        answerSelect.innerHTML = `<option value="${answer}">${answer}</option>`;
                        answerSelect.value = answer;
                    });

                    questionSelect.value = item.question;
                    questionSelect.dispatchEvent(new Event("change"));

                    // ✅ Thêm ảnh từ AI nếu có
                    const previewContainer = newCard.querySelector(".preview-container");
                    const previewImage = newCard.querySelector(".image-preview");
                    if (item.image_url) {
                        previewImage.src = item.image_url;
                        previewImage.classList.remove("d-none");
                        previewContainer.classList.remove("d-none");
                    }

                    flashcardContent.appendChild(newCard);
                }
            }

            async function fetchFlashcards(subjectName, count = 3) {
                let collected = [];
                let attempts = 0;
                const maxAttempts = 5;

                while (collected.length < count && attempts < maxAttempts) {
                    const excluded = [...getUsedQuestions(), ...collected.map(item => item.question)];
                    const res = await fetch("user/ai/suggest", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            subject_name: subjectName,
                            count: count - collected.length,
                            excluded_questions: excluded
                        })
                    });

                    const data = await res.json();
                    if (data?.data?.length) {
                        collected = [...collected, ...data.data];
                    } else {
                        break;
                    }

                    attempts++;
                }

                return {
                    data: collected
                };
            }

            subjectSelect.addEventListener("change", async function() {
                const subjectName = this.options[this.selectedIndex].text;
                const count = parseInt(numberOfCardsInput.value) || 3;

                topicSelect.disabled = true;
                topicSelect.innerHTML = '<option selected disabled>Đang tải chủ đề...</option>';
                flashcardContent.innerHTML = "";

                // Vô hiệu hóa các nút khi chỉ chọn môn học
                document.getElementById("add-card").disabled = true;
                document.getElementById("create-multiple-cards").disabled = true;
                numberOfCardsInput.disabled = true;

                try {
                    const [topicRes, flashcardData] = await Promise.all([
                        fetch("user/ai/suggest-topics", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                subject_name: subjectName
                            })
                        }).then(res => res.json()),
                        fetchFlashcards(subjectName, count)
                    ]);

                    if (flashcardData.data) {
                        cachedFlashcards = flashcardData.data;
                        renderFlashcards(cachedFlashcards);
                    } else {
                        showErrorModal(flashcardData.error || "Không có gợi ý flashcard.");
                    }

                    if (Array.isArray(topicRes.data)) {
                        topicSelect.innerHTML = '<option selected disabled>Chọn chủ đề</option>';
                        topicRes.data.forEach(topic => {
                            const opt = document.createElement('option');
                            opt.value = topic;
                            opt.textContent = topic;
                            topicSelect.appendChild(opt);
                        });
                        topicSelect.disabled = false;
                    } else {
                        topicSelect.innerHTML = '<option selected disabled>Không có gợi ý</option>';
                    }

                } catch (err) {
                    console.error(err);
                    showErrorModal("Lỗi khi lấy dữ liệu từ AI.");
                    topicSelect.innerHTML = '<option selected disabled>Lỗi tải chủ đề</option>';
                }
            });

            topicSelect.addEventListener("change", async function() {
                const subjectName = subjectSelect.options[subjectSelect.selectedIndex].text;
                const topicTitle = this.value;
                const count = parseInt(numberOfCardsInput.value) || 3;
                flashcardContent.innerHTML = "";

                try {
                    const data = await fetchFlashcards(`${subjectName} - ${topicTitle}`, count);
                    if (data.data) {
                        cachedFlashcards = data.data;
                        renderFlashcards(cachedFlashcards);

                        // ✅ Cho phép thao tác các nút sau khi chọn chủ đề thành công
                        document.getElementById("add-card").disabled = false;
                        document.getElementById("create-multiple-cards").disabled = false;
                        numberOfCardsInput.disabled = false;
                    } else {
                        showErrorModal(data.error || "Không có dữ liệu gợi ý.");
                    }
                } catch (err) {
                    console.error(err);
                    showErrorModal("Lỗi khi lấy flashcard theo chủ đề.");
                }
            });

            flashcardContent.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-card")) {
                    e.target.closest(".flashcard").remove();
                    updateCardNumbers();
                }
            });

            function updateCardNumbers() {
                document.querySelectorAll(".flashcard").forEach((card, i) => {
                    const num = card.querySelector(".card-number");
                    if (num) num.textContent = "Câu " + (i + 1);
                });
            }

            document.getElementById("create-multiple-cards").addEventListener("click", async function() {
                const count = parseInt(numberOfCardsInput.value);
                if (!count || count < 1 || count > 50) {
                    showErrorModal("Vui lòng nhập số lượng thẻ hợp lệ (1-50).");
                    return;
                }

                const subjectName = subjectSelect.options[subjectSelect.selectedIndex]?.text;
                if (!subjectName) {
                    showErrorModal("Vui lòng chọn môn học.");
                    return;
                }

                const topicName = topicSelect.value;
                const fullSubject = topicName ? `${subjectName} - ${topicName}` : subjectName;

                try {
                    let missing = count - cachedFlashcards.length;

                    if (missing > 0) {
                        const result = await fetchFlashcards(fullSubject, missing);
                        if (result?.data?.length) {
                            cachedFlashcards = [...cachedFlashcards, ...result.data];
                        } else {
                            showErrorModal("Không thể lấy đủ flashcards từ AI.");
                            return;
                        }
                    }

                    renderFlashcards(cachedFlashcards.slice(0, count));
                } catch (err) {
                    console.error(err);
                    showErrorModal("Đã xảy ra lỗi khi tạo flashcard.");
                }
            });

            numberOfCardsInput.addEventListener("change", function() {
                document.getElementById("create-multiple-cards").click();
            });

            document.getElementById("add-card").addEventListener("click", async function() {
                try {
                    const subjectName = subjectSelect.options[subjectSelect.selectedIndex]?.text;
                    const topicName = topicSelect.value;
                    if (!subjectName) {
                        showErrorModal("Vui lòng chọn môn học.");
                        return;
                    }

                    const fullSubject = topicName ? `${subjectName} - ${topicName}` : subjectName;
                    const data = await fetchFlashcards(fullSubject, 1);

                    if (data.data && data.data.length > 0) {
                        const newCardData = data.data[0];
                        cachedFlashcards.push(newCardData);

                        const index = flashcardContent.querySelectorAll(".flashcard").length + 1;
                        const newCard = createFlashcardCard(index);

                        const questionSelect = newCard.querySelector('.question-select');
                        const answerSelect = newCard.querySelector('.answer-select');

                        cachedFlashcards.forEach(entry => {
                            const option = document.createElement('option');
                            option.value = entry.question;
                            option.textContent = entry.question;
                            option.dataset.answer = entry.answer;
                            questionSelect.appendChild(option);
                        });

                        questionSelect.disabled = false;
                        answerSelect.disabled = false;

                        questionSelect.addEventListener("change", function() {
                            const selectedQuestion = this.value;
                            const selectedOption = this.options[this.selectedIndex];
                            const answer = selectedOption?.dataset.answer || '';

                            const allSelects = Array.from(document.querySelectorAll(
                                '.question-select'));
                            const duplicates = allSelects.filter(sel => sel !== this && sel
                                .value === selectedQuestion);

                            if (duplicates.length > 0) {
                                showErrorModal(
                                    `Thuật ngữ "${selectedQuestion}" đã được chọn ở một thẻ khác.`
                                );
                                this.value = '';
                                answerSelect.innerHTML =
                                    '<option selected disabled>Chọn định nghĩa</option>';
                                return;
                            }

                            answerSelect.innerHTML =
                                `<option value="${answer}">${answer}</option>`;
                            answerSelect.value = answer;
                        });

                        questionSelect.value = newCardData.question;
                        questionSelect.dispatchEvent(new Event("change"));

                        // ✅ Hiển thị ảnh nếu có
                        const previewContainer = newCard.querySelector(".preview-container");
                        const previewImage = newCard.querySelector(".image-preview");
                        if (newCardData.image_url && typeof newCardData.image_url === "string") {
                            previewImage.src = newCardData.image_url;
                            previewImage.classList.remove("d-none");
                            previewContainer.classList.remove("d-none");
                        }

                        flashcardContent.appendChild(newCard);
                        updateCardNumbers();
                    } else {
                        showErrorModal("Không có flashcard mới để thêm.");
                    }
                } catch (err) {
                    console.error(err);
                    showErrorModal("Lỗi khi thêm thẻ mới.");
                }
            });

            document.addEventListener("change", function(event) {
                if (event.target.classList.contains("image-input")) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const parentCard = event.target.closest(".row");
                            const imgDiv = parentCard.querySelector(".preview-container");
                            const img = parentCard.querySelector(".image-preview");
                            if (imgDiv && img) {
                                img.src = e.target.result;
                                imgDiv.classList.remove("d-none");
                                img.classList.remove("d-none");
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });
        });
    </script>
@endsection
