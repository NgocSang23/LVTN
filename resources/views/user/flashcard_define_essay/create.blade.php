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
                                <input type="text" name="question_content[]" class="form-control question-input"
                                    placeholder="Nhập câu hỏi (thuật ngữ)" style="border-radius: 8px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" name="answer_content[]" class="form-control answer-input"
                                    placeholder="Nhập định nghĩa" style="border-radius: 8px;">
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
        // Chờ DOM được load xong mới bắt đầu thực thi
        document.addEventListener("DOMContentLoaded", function() {
            // Lấy các phần tử cần thao tác
            const subjectSelect = document.getElementById('subject-select'); // dropdown chọn môn học
            const topicSelect = document.getElementById('topic-select'); // dropdown chọn chủ đề
            const flashcardContent = document.getElementById("flashcard-content"); // nơi chứa các flashcard
            const numberOfCardsInput = document.getElementById("number-of-cards"); // ô nhập số lượng thẻ muốn tạo

            // Modal báo lỗi
            const errorModalEl = document.getElementById('errorModal');
            const errorModalBody = document.getElementById('errorModalBody');
            const bootstrapErrorModal = new bootstrap.Modal(errorModalEl); // khởi tạo modal Bootstrap

            // Nút thao tác
            const addBtn = document.getElementById("add-card"); // nút thêm flashcard
            const createBtn = document.getElementById("create-multiple-cards"); // nút tạo nhiều flashcard

            let cachedFlashcards = []; // Lưu các flashcard đã được AI gợi ý

            // Vô hiệu hóa các nút hành động khi chưa chọn môn và chủ đề
            setActionButtonsState({
                subjectSelected: false,
                topicSelected: false
            });

            // Hàm hiển thị lỗi
            function showErrorModal(message) {
                errorModalBody.textContent = message;
                bootstrapErrorModal.show();
            }

            // Hàm bật/tắt các nút tùy theo trạng thái đã chọn môn/chủ đề
            function setActionButtonsState({subjectSelected,topicSelected}) {
                const enabled = subjectSelected && topicSelected;
                addBtn.disabled = !enabled;
                createBtn.disabled = !enabled;
                numberOfCardsInput.disabled = !enabled;
            }

            // Lấy danh sách các câu hỏi đã được sử dụng
            function getUsedQuestions() {
                return Array.from(document.querySelectorAll('.question-select'))
                    .map(select => select.value)
                    .filter(Boolean);
            }

            // Tạo một khối flashcard HTML
            function createFlashcardCard(index) {
                const card = document.createElement("div");
                card.classList.add("card", "mb-4", "flashcard");
                card.style.borderRadius = "10px";
                card.style.border = "1px solid #e0e0e0";

                // Thêm nội dung HTML vào thẻ flashcard
                card.innerHTML = `
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold card-number">Câu ${index}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-card" style="border-radius: 50px; padding: 6px 12px;">Xóa</button>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="question_content[]" class="form-control question-input question-select" placeholder="Nhập câu hỏi (thuật ngữ)" style="border-radius: 8px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" name="answer_content[]" class="form-control answer-input answer-select" placeholder="Nhập định nghĩa" style="border-radius: 8px;">
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

            // Render các flashcard từ dữ liệu
            function renderFlashcards(qaPairs) {
                const count = parseInt(numberOfCardsInput.value) || qaPairs.length;
                flashcardContent.innerHTML = ""; // xóa nội dung cũ

                for (let i = 0; i < count; i++) {
                    const item = qaPairs[i] ?? qaPairs[0]; // lấy dữ liệu, nếu thiếu thì dùng phần tử đầu
                    const newCard = createFlashcardCard(i + 1);
                    const questionSelect = newCard.querySelector('.question-select');
                    const answerSelect = newCard.querySelector('.answer-select');

                    // Thêm các option câu hỏi gợi ý vào select
                    qaPairs.forEach(entry => {
                        const option = document.createElement('option');
                        option.value = entry.question;
                        option.textContent = entry.question;
                        option.dataset.answer = entry.answer;
                        questionSelect.appendChild(option);
                    });

                    // Bắt sự kiện khi người dùng chọn câu hỏi
                    questionSelect.addEventListener("change", function() {
                        const selectedQuestion = this.value.trim();

                        // Kiểm tra trùng lặp
                        const duplicates = Array.from(document.querySelectorAll('.question-select'))
                            .filter(input => input !== this && input.value === selectedQuestion);

                        if (duplicates.length > 0) {
                            showErrorModal(`Thuật ngữ "${selectedQuestion}" đã được chọn ở một thẻ khác.`);
                            this.value = '';
                            answerSelect.value = '';
                            return;
                        }

                        // Tự động gán định nghĩa nếu có sẵn
                        const match = cachedFlashcards.find(item => item.question === selectedQuestion);
                        if (match) {
                            answerSelect.value = match.answer;
                        }
                    });

                    // Tự động set câu hỏi/đáp án ban đầu
                    questionSelect.value = item.question;
                    questionSelect.dispatchEvent(new Event("change"));

                    flashcardContent.appendChild(newCard);
                }
            }

            // Gọi API lấy flashcard gợi ý từ AI
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

            // Khi chọn môn học
            subjectSelect.addEventListener("change", async function() {
                const subjectName = this.options[this.selectedIndex].text;
                flashcardContent.innerHTML = "";
                topicSelect.disabled = true;
                topicSelect.innerHTML = '<option selected disabled>Đang tải chủ đề...</option>';
                setActionButtonsState({
                    subjectSelected: true,
                    topicSelected: false
                });

                try {
                    const [topicRes, flashcardData] = await Promise.all([
                        // Gợi ý chủ đề theo môn
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
                        // Gợi ý flashcard
                        fetchFlashcards(subjectName)
                    ]);

                    // Hiển thị flashcard gợi ý
                    if (flashcardData.data?.length) {
                        cachedFlashcards = flashcardData.data;
                        renderFlashcards(cachedFlashcards);
                    } else {
                        showErrorModal("Không có flashcard gợi ý.");
                    }

                    // Đổ dữ liệu chủ đề
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
                        topicSelect.innerHTML =
                            '<option selected disabled>Không có chủ đề gợi ý</option>';
                    }

                } catch (err) {
                    console.error(err);
                    showErrorModal("Lỗi khi tải dữ liệu.");
                }
            });

            // Khi chọn chủ đề
            topicSelect.addEventListener("change", async function() {
                const subjectName = subjectSelect.options[subjectSelect.selectedIndex].text;
                const topicTitle = this.value;
                const fullSubject = `${subjectName} - ${topicTitle}`;
                flashcardContent.innerHTML = "";

                try {
                    const data = await fetchFlashcards(fullSubject);
                    if (data.data?.length) {
                        cachedFlashcards = data.data;
                        renderFlashcards(cachedFlashcards);
                        setActionButtonsState({
                            subjectSelected: true,
                            topicSelected: true
                        });
                    } else {
                        showErrorModal("Không có flashcard cho chủ đề này.");
                    }
                } catch (err) {
                    console.error(err);
                    showErrorModal("Lỗi khi lấy dữ liệu theo chủ đề.");
                }
            });

            // Xóa flashcard khi bấm nút "Xóa"
            flashcardContent.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-card")) {
                    e.target.closest(".flashcard").remove();
                    updateCardNumbers();
                }
            });

            // Cập nhật lại số thứ tự "Câu X" sau khi xóa thẻ
            function updateCardNumbers() {
                document.querySelectorAll(".flashcard").forEach((card, i) => {
                    const num = card.querySelector(".card-number");
                    if (num) num.textContent = "Câu " + (i + 1);
                });
            }

            // Khi bấm "Tạo nhiều thẻ"
            document.getElementById("create-multiple-cards").addEventListener("click", async function() {
                const count = parseInt(numberOfCardsInput.value);
                if (!count || count < 1 || count > 50) {
                    showErrorModal("Vui lòng nhập số lượng thẻ hợp lệ (1-50).");
                    return;
                }

                const subjectName = subjectSelect.options[subjectSelect.selectedIndex]?.text;
                const topicName = topicSelect.value;
                const fullSubject = topicName ? `${subjectName} - ${topicName}` : subjectName;

                try {
                    let missing = count - cachedFlashcards.length;
                    if (missing > 0) {
                        const result = await fetchFlashcards(fullSubject, missing);
                        if (result?.data?.length) {
                            cachedFlashcards = [...cachedFlashcards, ...result.data];
                        } else {
                            showErrorModal("Không đủ dữ liệu từ AI.");
                            return;
                        }
                    }

                    renderFlashcards(cachedFlashcards.slice(0, count));
                } catch (err) {
                    console.error(err);
                    showErrorModal("Lỗi khi tạo flashcard.");
                }
            });

            // Khi bấm "Thêm 1 thẻ"
            document.getElementById("add-card").addEventListener("click", async function() {
                const subjectName = subjectSelect.options[subjectSelect.selectedIndex]?.text;
                const topicName = topicSelect.value;
                const fullSubject = topicName ? `${subjectName} - ${topicName}` : subjectName;

                try {
                    const result = await fetchFlashcards(fullSubject, 1);
                    if (result.data?.length > 0) {
                        const newCardData = result.data[0];
                        cachedFlashcards.push(newCardData);

                        const index = flashcardContent.querySelectorAll(".flashcard").length + 1;
                        const newCard = createFlashcardCard(index);
                        const questionSelect = newCard.querySelector('.question-select');
                        const answerSelect = newCard.querySelector('.answer-select');

                        // Thêm câu hỏi gợi ý vào dropdown
                        cachedFlashcards.forEach(entry => {
                            const option = document.createElement('option');
                            option.value = entry.question;
                            option.textContent = entry.question;
                            option.dataset.answer = entry.answer;
                            questionSelect.appendChild(option);
                        });

                        // Bắt sự kiện thay đổi để cập nhật đáp án
                        questionSelect.addEventListener("change", function() {
                            const selected = this.value;
                            const duplicate = [...document.querySelectorAll('.question-select')]
                                .filter(sel => sel !== this && sel.value === selected);
                            if (duplicate.length > 0) {
                                showErrorModal(`Thuật ngữ "${selected}" đã được chọn.`);
                                this.value = '';
                                answerSelect.value = '';
                                return;
                            }

                            const matched = cachedFlashcards.find(x => x.question === selected);
                            answerSelect.value = matched?.answer || '';
                        });

                        questionSelect.value = newCardData.question;
                        questionSelect.dispatchEvent(new Event("change"));

                        flashcardContent.appendChild(newCard);
                        updateCardNumbers();
                    } else {
                        showErrorModal("Không có flashcard mới.");
                    }
                } catch (err) {
                    console.error(err);
                    showErrorModal("Lỗi khi thêm flashcard.");
                }
            });

            // Xem trước ảnh khi người dùng chọn file
            document.addEventListener("change", function(event) {
                if (event.target.classList.contains("image-input")) {
                    const file = event.target.files[0];
                    const previewContainer = event.target.closest(".row").querySelector(
                        ".preview-container");
                    const previewImg = previewContainer?.querySelector(".image-preview");

                    if (file && previewImg && previewContainer) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewImg.classList.remove("d-none");
                            previewContainer.classList.remove("d-none");
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });

            // Validate dữ liệu trước khi submit form
            document.getElementById("form-submit-btn")?.addEventListener("click", function(e) {
                if (!validateFlashcardInputs()) {
                    e.preventDefault();
                }
            });

            function validateFlashcardInputs() {
                const flashcards = document.querySelectorAll(".flashcard");
                for (let card of flashcards) {
                    const question = card.querySelector(".question-input")?.value.trim();
                    const answer = card.querySelector(".answer-input")?.value.trim();
                    if (!question || !answer) {
                        showErrorModal("Vui lòng nhập đầy đủ câu hỏi và định nghĩa.");
                        return false;
                    }
                }
                return true;
            }
        });
    </script>
@endsection
