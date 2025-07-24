document.addEventListener("DOMContentLoaded", function () {
    // Lấy dữ liệu bài kiểm tra được truyền từ PHP (Laravel Blade) dưới dạng JSON.
    // `quizData` chứa một mảng các đối tượng câu hỏi. Mỗi đối tượng có thể có `question`, `display_question`, và `correct_answer_text`.
    const quizData = window.quizData ?? [];

    // `?? []` đảm bảo rằng nếu `$quizData` không tồn tại, nó sẽ là một mảng rỗng.

    // Lấy các phần tử DOM cần thiết bằng ID của chúng.
    const quizContainer = document.getElementById("quizContainer"); // Nơi hiển thị câu hỏi hiện tại.
    const submitButtonWrapper = document.getElementById("submitButtonWrapper"); // Wrapper chứa nút "Gửi bài kiểm tra".
    const finalSubmitBtn = document.getElementById("finalSubmitBtn"); // Nút "Gửi bài kiểm tra" trong modal xác nhận.
    const submitExamBtn = document.getElementById("submitExamBtn"); // Nút "Gửi bài kiểm tra" chính bên ngoài modal.

    // --- Xử lý khi không có dữ liệu bài kiểm tra ---
    // Kiểm tra xem `quizData` có rỗng hoặc không hợp lệ không.
    if (!quizData || quizData.length === 0) {
        // Nếu không có dữ liệu, hiển thị thông báo cảnh báo trong `quizContainer`.
        quizContainer.innerHTML = `
            <div class="alert alert-warning text-center">
                Không có dữ liệu phù hợp. Vui lòng thử lại.
            </div>`;
        // Ẩn nút nộp bài vì không có câu hỏi để nộp.
        submitButtonWrapper.style.display = "none";
        // Dừng việc thực thi các phần còn lại của script.
        return;
    }

    // --- Xử lý sự kiện khi người dùng cố gắng rời khỏi trang ---
    // Hàm này sẽ hiển thị một thông báo cảnh báo tiêu chuẩn của trình duyệt
    // nếu người dùng cố gắng đóng tab/cửa sổ hoặc điều hướng đi nơi khác.
    window.onbeforeunload = () =>
        "Bạn có chắc muốn rời khỏi bài kiểm tra? Dữ liệu chưa lưu sẽ bị mất.";

    // --- Khởi tạo trạng thái bài kiểm tra ---
    let currentIndex = 0; // Biến theo dõi chỉ số của câu hỏi hiện tại (bắt đầu từ 0).
    // Mảng `answers` sẽ lưu trữ câu trả lời của người dùng cho mỗi câu hỏi.
    // Nó được khởi tạo với độ dài bằng số lượng câu hỏi và tất cả các giá trị ban đầu là chuỗi rỗng.
    const answers = Array(quizData.length).fill("");

    // --- Hàm kiểm tra tất cả câu hỏi đã được trả lời chưa ---
    const areAllQuestionsAnswered = () => {
        // Sử dụng `every()` để kiểm tra xem mọi phần tử trong mảng `answers`
        // có khác chuỗi rỗng sau khi loại bỏ khoảng trắng đầu/cuối hay không.
        return answers.every((answer) => answer.trim() !== "");
    };

    // --- Hàm hiển thị câu hỏi hiện tại ---
    const renderQuestion = (index) => {
        const q = quizData[index]; // Lấy đối tượng câu hỏi hiện tại từ `quizData`.
        const savedAnswer = answers[index] || ""; // Lấy câu trả lời đã lưu của người dùng cho câu hỏi này (nếu có).

        // Tạo HTML cho câu hỏi, thay thế "___" bằng một thẻ input.
        // `id` của input là duy nhất cho mỗi câu hỏi để dễ dàng truy cập.
        // `value` của input được đặt là `savedAnswer` để giữ lại câu trả lời nếu người dùng quay lại câu hỏi trước.
        const questionHtml = q.question.replace(
            "___",
            `<input type="text" class="fill-blank-input" id="userAnswer_${index}" value="${savedAnswer}" />`
        );

        // Cập nhật nội dung của `quizContainer` với HTML của câu hỏi và các nút điều hướng.
        quizContainer.innerHTML = `
            <div class="question-container mb-4">
                <div class="bg-white text-dark p-4 rounded shadow-sm">
                    <p class="fw-bold">Câu ${index + 1} / ${quizData.length}</p>
                    <p class="mb-2">❓ <strong>${
                        q.display_question || q.question
                    }</strong></p>
                    <p>🔹 ${questionHtml}</p>
                    <div id="inputError" class="text-danger mt-2 fw-semibold" style="display:none;"></div>
                </div>
                <div class="nav-controls">
                    <button id="prevBtn" ${index === 0 ? "disabled" : ""}>
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                    <button id="nextBtn">
                        ${
                            index === quizData.length - 1 ? "Hoàn tất" : "Tiếp theo"
                        } <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>`;

        // Lấy tham chiếu đến input của câu hỏi hiện tại.
        const currentInput = document.getElementById(`userAnswer_${index}`);
        if (currentInput) {
            // Thêm trình nghe sự kiện 'input' để cập nhật `answers` ngay khi người dùng gõ.
            // Điều này giúp lưu trạng thái câu trả lời khi người dùng di chuyển giữa các câu hỏi.
            currentInput.addEventListener("input", (event) => {
                answers[index] = event.target.value.trim(); // Cập nhật câu trả lời đã gõ.
                updateSubmitButtonState(); // Cập nhật trạng thái nút "Gửi bài kiểm tra".
            });
        }

        // --- Xử lý sự kiện click cho nút "Tiếp theo" / "Hoàn tất" ---
        document.getElementById("nextBtn").onclick = () => {
            const userInput = currentInput ? currentInput.value.trim() : ""; // Lấy giá trị từ input.

            // Nếu người dùng chưa điền vào chỗ trống, hiển thị lỗi.
            if (!userInput) {
                const errorDiv = document.getElementById("inputError");
                errorDiv.innerText =
                    "⚠️ Vui lòng điền vào chỗ trống trước khi tiếp tục.";
                errorDiv.style.display = "block";
                return; // Ngăn không cho chuyển sang câu hỏi tiếp theo.
            }

            answers[index] = userInput; // Lưu câu trả lời của người dùng.

            // Nếu đây là câu hỏi cuối cùng.
            if (index === quizData.length - 1) {
                submitButtonWrapper.style.display = "block"; // Hiển thị nút "Gửi bài kiểm tra".
                updateSubmitButtonState(); // Cập nhật trạng thái nút.
            } else {
                currentIndex++; // Tăng chỉ số câu hỏi.
                renderQuestion(currentIndex); // Hiển thị câu hỏi tiếp theo.
                submitButtonWrapper.style.display = "none"; // Ẩn nút "Gửi bài kiểm tra" nếu chưa phải câu cuối.
            }
        };

        // --- Xử lý sự kiện click cho nút "Quay lại" ---
        // Chỉ thêm trình nghe nếu không phải câu hỏi đầu tiên.
        if (index > 0) {
            document.getElementById("prevBtn").onclick = () => {
                if (currentInput) {
                    answers[index] = currentInput.value.trim(); // Lưu câu trả lời trước khi quay lại.
                }
                currentIndex--; // Giảm chỉ số câu hỏi.
                renderQuestion(currentIndex); // Hiển thị câu hỏi trước đó.
                submitButtonWrapper.style.display = "none"; // Ẩn nút "Gửi bài kiểm tra".
            };
        }

        // Cập nhật trạng thái nút "Gửi bài kiểm tra" mỗi khi một câu hỏi được render.
        updateSubmitButtonState();
    };

    // --- Hàm cập nhật trạng thái của nút "Gửi bài kiểm tra" ---
    // Nút sẽ được kích hoạt (enabled) nếu tất cả câu hỏi đã được trả lời, ngược lại sẽ bị vô hiệu hóa (disabled).
    const updateSubmitButtonState = () => {
        if (areAllQuestionsAnswered()) {
            submitExamBtn.removeAttribute("disabled"); // Bỏ thuộc tính disabled.
        } else {
            submitExamBtn.setAttribute("disabled", "disabled"); // Thêm thuộc tính disabled.
        }
    };

    // --- Xử lý sự kiện click cho nút "Gửi bài kiểm tra" (bên ngoài modal) ---
    submitExamBtn.addEventListener("click", () => {
        // Tạo một instance của modal xác nhận và hiển thị nó.
        const confirmModal = new bootstrap.Modal(
            document.getElementById("confirmSubmitModal")
        );
        confirmModal.show();
    });

    // --- Xử lý sự kiện click cho nút "Gửi bài kiểm tra" (trong modal xác nhận) ---
    finalSubmitBtn.addEventListener("click", () => {
        // Lấy instance của modal xác nhận.
        const confirmModalInstance = bootstrap.Modal.getInstance(
            document.getElementById("confirmSubmitModal")
        );
        if (confirmModalInstance) {
            confirmModalInstance.hide(); // Ẩn modal xác nhận.
        }

        let correctCount = 0; // Biến đếm số câu trả lời đúng.
        let resultHtml = `<ul class="list-group list-group-flush">`; // Bắt đầu chuỗi HTML cho kết quả.

        // Duyệt qua từng câu hỏi trong `quizData` để kiểm tra và hiển thị kết quả.
        quizData.forEach((q, index) => {
            const userAnswer = answers[index]?.trim() || ""; // Lấy câu trả lời của người dùng.
            const correctAnswer = q.correct_answer_text?.trim() || ""; // Lấy đáp án đúng.
            // So sánh câu trả lời của người dùng với đáp án đúng (không phân biệt hoa thường).
            const isCorrect =
                userAnswer.toLowerCase() === correctAnswer.toLowerCase();

            if (isCorrect) correctCount++; // Tăng biến đếm nếu đúng.

            // Tạo câu đầy đủ với từ đã điền của người dùng, được gạch chân và tô màu (xanh nếu đúng, đỏ nếu sai).
            let fullQuestionWithAnswer = q.question.replace(
                "___",
                `<span class="filled-answer-underline ${
                    isCorrect ? "text-success" : "text-danger"
                }">${userAnswer || "[Chưa trả lời]"}</span>`
            );

            // Tạo câu đầy đủ với đáp án đúng, được gạch chân và tô màu vàng.
            let fullQuestionWithCorrectAnswer = q.question.replace(
                "___",
                `<span class="filled-answer-underline text-warning">${correctAnswer}</span>`
            );

            // Thêm HTML cho kết quả của từng câu hỏi vào chuỗi `resultHtml`.
            resultHtml += `
                <li class="list-group-item bg-dark text-white border-secondary">
                    <p><strong>Câu ${index + 1}:</strong> ${
                q.display_question || q.question
            }</p>
                    <p>🔹 <strong>Câu trả lời của bạn:</strong> ${fullQuestionWithAnswer}</p>
                    <p>✅ <strong>Đáp án đúng:</strong> ${fullQuestionWithCorrectAnswer}</p>
                    <hr class="text-secondary">
                </li>`;
        });

        // Đóng thẻ ul và thêm tổng kết quả.
        resultHtml += `</ul>
            <div class="text-center mt-3">
                <h5 class="fw-bold text-success">🎉 Bạn đã trả lời đúng ${correctCount} / ${quizData.length} câu</h5>
            </div>`;

        document.getElementById("resultBody").innerHTML = resultHtml;

        const resultModal = new bootstrap.Modal(
            document.getElementById("resultModal")
        );
        resultModal.show();
    });

    renderQuestion(currentIndex);
});
