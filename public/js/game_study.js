
document.addEventListener("DOMContentLoaded", function () {
    // selectedGoal: Lưu trữ mục tiêu học tập mà người dùng ĐANG CHỌN trên giao diện (ví dụ: 'exam_prep', 'memorize_all').
    let selectedGoal = null;

    // selectedLevel: Lưu trữ cấp độ câu hỏi mà người dùng ĐANG CHỌN trên giao diện (ví dụ: 'easy', 'medium', 'hard').
    let selectedLevel = null;

    // studyGoal: Mục tiêu học tập ĐƯỢC XÁC NHẬN khi người dùng nhấn "Bắt đầu học".
    let studyGoal = null;

    // studyLevel: Cấp độ câu hỏi ĐƯỢC XÁC NHẬN khi người dùng nhấn "Bắt đầu học".
    let studyLevel = null;

    // lastAnswerCorrect: Biến boolean kiểm tra xem lần trả lời câu hỏi TRƯỚC CỦA NGƯỜI DÙNG có đúng không.
    let lastAnswerCorrect = false;

    // currentQuestionIndex: Chỉ số (index) của câu hỏi hiện tại trong mảng quizData.
    let currentQuestionIndex = 0;

    // countCorrectAnswers: Đếm tổng số câu hỏi mà người dùng đã trả lời đúng trong phiên học hiện tại.
    let countCorrectAnswers = 0;

    // answered: Biến boolean cho biết người dùng đã trả lời câu hỏi hiện tại hay chưa.
    let answered = false;

    // MAX_ANSWERS_TO_SHOW: Hằng số xác định số lượng đáp án tối đa sẽ hiển thị cho mỗi câu hỏi.
    const MAX_ANSWERS_TO_SHOW = 4;

    // countdownTimer: Biến để lưu trữ ID của hàm setInterval (bộ đếm ngược).
    let countdownTimer = null;

    // --- Lấy các phần tử HTML để thao tác ---
    const flashcardContainer = document.getElementById("flashcardContainer"); // Container chứa nội dung flashcard (câu hỏi, đáp án).
    const instructionText = document.getElementById("instructionText"); // Phần tử hiển thị hướng dẫn cho người dùng.
    const nextButton = document.getElementById("nextButton"); // Nút "Tiếp tục" để chuyển sang câu hỏi kế tiếp.
    const progressBar = document.getElementById("progressBar"); // Thanh tiến trình hiển thị phần trăm hoàn thành.
    const currentCardNumber = document.getElementById("currentCardNumber"); // Số thứ tự của thẻ flashcard hiện tại.
    const totalCards = document.getElementById("totalCards"); // Tổng số thẻ flashcard.
    const countdownDisplay = document.getElementById("countdownTimerDisplay"); // Phần tử hiển thị bộ đếm ngược thời gian.
    const startStudyBtn = document.getElementById("startStudy"); // Nút "Bắt đầu học" để khởi tạo phiên học.
    const studyModalEl = document.getElementById("studyModal"); // Phần tử modal chọn mục tiêu và cấp độ.
    const resultModalEl = document.getElementById("resultModal"); // Phần tử modal hiển thị kết quả cuối cùng.
    const correctCountDisplay = document.getElementById("correctCount"); // Phần tử hiển thị số câu đúng trong modal kết quả.
    const totalCountDisplay = document.getElementById("totalCount"); // Phần tử hiển thị tổng số câu trong modal kết quả.

    // --- Tự động chọn mục tiêu mặc định (exam_prep) ---
    const defaultGoal = document.querySelector(
        '.goal-btn[data-value="exam_prep"]'
    );
    if (defaultGoal) {
        defaultGoal.classList.add("active");
        selectedGoal = defaultGoal.getAttribute("data-value");
    }

    // --- Tự động chọn cấp độ mặc định (easy) ---
    const defaultLevel = document.querySelector(
        '.level-btn[data-value="easy"]'
    );
    if (defaultLevel) {
        defaultLevel.classList.add("active");
        selectedLevel = defaultLevel.getAttribute("data-value");
    }

    // --- Cài đặt trạng thái ban đầu của giao diện ---
    // Ẩn container flashcard và nút "Tiếp tục" khi trang mới tải.
    flashcardContainer.style.display = "none";
    nextButton.style.display = "none";
    // Xóa nội dung hướng dẫn ban đầu (sẽ được cập nhật sau).
    instructionText.textContent = "";

    // --- Lấy dữ liệu quiz ---
    // Lấy dữ liệu câu hỏi từ biến toàn cục `window.quizData`.
    // Giả định rằng dữ liệu này được truyền từ backend (ví dụ: Laravel) dưới dạng JavaScript.
    let quizData = window.quizData;

    // --- Hiển thị tổng số câu hỏi ---
    // Cập nhật phần tử 'totalCards' với tổng số câu hỏi từ dữ liệu.
    totalCards.textContent = quizData.length;

    // --- Gắn sự kiện cho các nút chọn mục tiêu học ---
    // Lặp qua tất cả các nút có class 'goal-btn'.
    document.querySelectorAll(".goal-btn").forEach((btn) => {
        // Gắn sự kiện 'click' cho mỗi nút.
        btn.addEventListener("click", function () {
            // Khi nút được click, lấy giá trị 'data-value' của nút đó và gán cho selectedGoal.
            selectedGoal = this.getAttribute("data-value");
            // Xóa class 'active' khỏi tất cả các nút mục tiêu khác để chỉ có một nút được chọn.
            document
                .querySelectorAll(".goal-btn")
                .forEach((b) => b.classList.remove("active"));
            // Thêm class 'active' vào nút vừa được click.
            this.classList.add("active");
        });
    });

    // --- Gắn sự kiện cho các nút chọn cấp độ ---
    // Tương tự như nút mục tiêu, xử lý sự kiện click cho các nút cấp độ.
    document.querySelectorAll(".level-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            selectedLevel = this.getAttribute("data-value");
            document
                .querySelectorAll(".level-btn")
                .forEach((b) => b.classList.remove("active"));
            this.classList.add("active");
        });
    });

    // --- Khi nhấn nút "Bắt đầu học" ---
    // Kiểm tra xem nút "Bắt đầu học" có tồn tại trên trang không.
    if (startStudyBtn) {
        startStudyBtn.addEventListener("click", function () {
            // Kiểm tra xem người dùng đã chọn mục tiêu và cấp độ chưa.
            if (!selectedGoal || !selectedLevel) {
                alert("Vui lòng chọn mục tiêu và cấp độ trước khi bắt đầu.");
                return; // Dừng hàm nếu chưa chọn đủ.
            }

            // Ghi nhận lựa chọn cuối cùng của người dùng vào các biến 'studyGoal' và 'studyLevel'.
            // Các biến này sẽ được sử dụng trong suốt phiên học.
            studyGoal = selectedGoal;
            studyLevel = selectedLevel;

            // Reset trạng thái của phiên học mới.
            currentQuestionIndex = 0; // Bắt đầu từ câu hỏi đầu tiên.
            countCorrectAnswers = 0; // Số câu đúng ban đầu là 0.
            lastAnswerCorrect = false; // Reset trạng thái câu trả lời trước.

            // Hiện giao diện flashcard.
            flashcardContainer.style.display = "block";

            // Bắt đầu hiển thị câu hỏi đầu tiên.
            renderQuestion();

            // Đóng modal chọn mục tiêu và cấp độ bằng cách lấy instance của modal Bootstrap.
            const modalInstance = bootstrap.Modal.getInstance(studyModalEl);
            modalInstance.hide();
        });
    }

    // --- Hàm hiển thị một câu hỏi (renderQuestion) ---
    // Hàm này chịu trách nhiệm hiển thị câu hỏi hiện tại và các đáp án của nó.
    function renderQuestion() {
        // Dừng bất kỳ bộ đếm ngược thời gian cũ nào đang chạy để tránh xung đột.
        if (countdownTimer) clearInterval(countdownTimer);
        // Xóa nội dung hiển thị thời gian của câu hỏi trước đó.
        countdownDisplay.textContent = "";

        // Kiểm tra nếu đã hết tất cả các câu hỏi trong dữ liệu.
        if (currentQuestionIndex >= quizData.length) {
            // Nếu hết, gọi hàm để hiển thị kết quả cuối cùng và dừng hàm renderQuestion.
            displayResults();
            return;
        }

        // Lấy dữ liệu của câu hỏi hiện tại từ mảng quizData.
        const questionData = quizData[currentQuestionIndex];
        // Đặt lại trạng thái 'answered' về false cho câu hỏi mới.
        answered = false;
        // Ẩn nút "Tiếp tục" khi câu hỏi mới được hiển thị, nó sẽ chỉ xuất hiện khi cần.
        nextButton.style.display = "none";
        // Tạm thời vô hiệu hóa lắng nghe sự kiện nhấn phím để người dùng không thể vô tình chuyển câu.
        document.removeEventListener("keydown", keydownHandler);

        // --- Xử lý logic hiển thị đáp án và đảm bảo đáp án đúng luôn có ---
        // Tạo một bản sao của mảng đáp án gốc để tránh thay đổi dữ liệu gốc.
        let answersPool = questionData.answers.slice();
        // Tìm đáp án đúng từ pool đáp án.
        const correctAnswer = answersPool.find(
            (ans) => ans.id === questionData.correct_answer_id
        );
        let answersToDisplay = []; // Mảng sẽ chứa các đáp án cuối cùng được hiển thị.

        // Đảm bảo đáp án đúng luôn được thêm vào danh sách hiển thị.
        if (correctAnswer) {
            answersToDisplay.push(correctAnswer);
            // Loại bỏ đáp án đúng ra khỏi pool để khi chọn ngẫu nhiên các đáp án sai,
            // chúng ta không chọn lại đáp án đúng.
            answersPool = answersPool.filter(
                (ans) => ans.id !== correctAnswer.id
            );
        }

        // Tính toán số lượng đáp án sai cần thêm vào để đạt MAX_ANSWERS_TO_SHOW.
        const numWrongAnswersNeeded =
            MAX_ANSWERS_TO_SHOW - answersToDisplay.length;
        if (numWrongAnswersNeeded > 0) {
            // Trộn ngẫu nhiên các đáp án sai còn lại trong pool.
            const shuffledWrongAnswers = shuffleArray(answersPool);
            // Nối (concat) các đáp án sai đã trộn (lấy đủ số lượng cần thiết) vào answersToDisplay.
            answersToDisplay = answersToDisplay.concat(
                shuffledWrongAnswers.slice(0, numWrongAnswersNeeded)
            );
        }

        // Cuối cùng, trộn lại toàn bộ các đáp án sẽ hiển thị (đúng + sai)
        // để đảm bảo vị trí của đáp án đúng cũng ngẫu nhiên.
        answersToDisplay = shuffleArray(answersToDisplay);

        // --- Tạo HTML các nút đáp án ---
        let answersHtml = "";
        answersToDisplay.forEach((answer, index) => {
            answersHtml += `
                <div class="col d-flex">
                    <button type="button" class="w-100 h-100 py-2 px-3 d-flex align-items-center gap-2 border rounded answer-button" data-answer-id="${
                        answer.id
                    }">
                        <span class="me-2">${String.fromCharCode(
                            65 + index // Chuyển đổi index thành ký tự A, B, C, D...
                        )}</span> <span>${answer.content}</span>
                    </button>
                </div>
            `;
        });

        // --- Hiển thị câu hỏi và đáp án vào giao diện ---
        // Cập nhật nội dung của flashcardContainer với câu hỏi và các nút đáp án đã tạo.
        flashcardContainer.innerHTML = `
            <p class="text-uppercase fw-bold small mb-2">Câu hỏi</p>
            <p class="fs-5 mb-4">${questionData.question}</p>
            <div class="row row-cols-1 row-cols-md-2 g-3">
                ${answersHtml}
            </div>
            <div class="d-flex justify-content-end mt-4 text-muted small">
                <i class="fas fa-volume-up me-2"></i> <span>Bạn không biết?</span>
            </div>
        `;

        // Cập nhật hướng dẫn cho người dùng.
        instructionText.textContent = "Chọn một đáp án để tiếp tục.";
        // Gắn lại các sự kiện click cho các nút đáp án mới được tạo.
        attachAnswerListeners();
        // Cập nhật thanh tiến trình.
        updateProgressBar();

        // --- Bắt đầu đếm ngược nếu có giới hạn thời gian ---
        let timeLimit = 0;
        // Đặt giới hạn thời gian dựa trên 'studyLevel'.
        if (studyLevel === "medium") timeLimit = 8;
        else if (studyLevel === "hard") timeLimit = 5;

        if (timeLimit > 0) {
            let timeLeft = timeLimit;
            // Hiển thị thời gian còn lại.
            countdownDisplay.textContent = `⏳ Thời gian còn lại: ${timeLeft}s`;

            // Bắt đầu bộ đếm ngược, chạy mỗi giây.
            countdownTimer = setInterval(() => {
                timeLeft--; // Giảm thời gian còn lại.
                countdownDisplay.textContent = `⏳ Thời gian còn lại: ${timeLeft}s`;

                // Nếu hết thời gian.
                if (timeLeft <= 0) {
                    clearInterval(countdownTimer); // Dừng bộ đếm ngược.
                    countdownDisplay.textContent = "⏰ Hết thời gian!";
                    // Nếu người dùng chưa kịp trả lời khi hết giờ.
                    if (!answered) {
                        const buttons =
                            flashcardContainer.querySelectorAll(
                                ".answer-button"
                            );
                        // Gọi hàm xử lý đáp án với thông tin là hết giờ (không có nút nào được chọn, coi là sai).
                        handleAnswer(
                            null, // Không có nút nào được chọn khi hết giờ
                            false, // Coi là trả lời sai
                            buttons,
                            questionData.correct_answer_id
                        );
                    }
                }
            }, 1000); // 1000ms = 1 giây.
        }
    }

    // --- Hàm hiển thị kết quả cuối cùng (displayResults) ---
    // Hàm này được gọi khi người dùng đã hoàn thành tất cả các câu hỏi.
    function displayResults() {
        // Cập nhật nội dung của flashcardContainer để hiển thị thông báo hoàn thành và nút quay về trang chủ.
        flashcardContainer.innerHTML = `
            <div class="text-center py-5">
                <h3 class="text-success mb-3">Bạn đã hoàn thành tất cả các flashcard!</h3>
                <a href="/user/dashboard" class="btn btn-primary">Quay về trang chủ</a>
            </div>
        `;
        instructionText.textContent = "Bạn đã hoàn thành!"; // Cập nhật hướng dẫn.
        nextButton.style.display = "none"; // Ẩn nút "Tiếp tục".
        // Ngừng lắng nghe sự kiện nhấn phím vì phiên học đã kết thúc.
        document.removeEventListener("keydown", keydownHandler);

        // --- Hiển thị modal kết quả ---
        // Cập nhật số câu đúng và tổng số câu trong modal kết quả.
        correctCountDisplay.textContent = countCorrectAnswers;
        totalCountDisplay.textContent = quizData.length;
        // Tạo một instance mới của modal Bootstrap và hiển thị nó.
        const resultModal = new bootstrap.Modal(resultModalEl);
        resultModal.show();
    }

    // --- Gắn sự kiện cho các nút đáp án (attachAnswerListeners) ---
    // Hàm này được gọi sau mỗi khi câu hỏi mới được hiển thị để gắn lại sự kiện click cho các nút đáp án.
    function attachAnswerListeners() {
        // Lấy tất cả các nút đáp án trong flashcardContainer.
        const answerButtons =
            flashcardContainer.querySelectorAll(".answer-button");
        // Lấy ID của đáp án đúng cho câu hỏi hiện tại.
        const correctAnswerId =
            quizData[currentQuestionIndex].correct_answer_id;

        answerButtons.forEach((button) => {
            // Gắn sự kiện 'click' cho từng nút đáp án.
            button.addEventListener("click", function () {
                // Nếu người dùng đã trả lời câu này rồi, bỏ qua click tiếp theo.
                if (answered) return;

                // Đặt trạng thái 'answered' về true để đánh dấu câu hỏi đã được trả lời.
                answered = true;

                // Lấy ID của đáp án mà người dùng đã chọn.
                const selectedAnswerId = parseInt(this.dataset.answerId);
                // Kiểm tra xem đáp án đã chọn có đúng không.
                const isCorrect = selectedAnswerId === correctAnswerId;
                // Cập nhật biến lastAnswerCorrect với kết quả của lần trả lời này.
                lastAnswerCorrect = isCorrect;

                // Dừng bộ đếm ngược ngay lập tức khi người dùng chọn đáp án, không cần chờ hết giờ.
                if (countdownTimer) clearInterval(countdownTimer);

                // Gọi hàm xử lý chung 'handleAnswer' với thông tin chi tiết về lựa chọn của người dùng.
                handleAnswer(this, isCorrect, answerButtons, correctAnswerId);
            });
        });
    }

    // --- Hàm xử lý chung khi người dùng trả lời hoặc hết giờ (handleAnswer) ---
    // selectedButton: Nút đáp án mà người dùng đã click (null nếu hết giờ).
    // isCorrect: true nếu đáp án đúng, false nếu sai.
    // allButtons: NodeList chứa tất cả các nút đáp án của câu hỏi hiện tại.
    // correctAnswerId: ID của đáp án đúng.
    function handleAnswer(
        selectedButton,
        isCorrect,
        allButtons,
        correctAnswerId
    ) {
        answered = true; // Đánh dấu là đã trả lời.
        // Vô hiệu hóa tất cả các nút đáp án để người dùng không thể chọn lại (trừ chế độ memorize_all khi sai).
        allButtons.forEach((btn) => (btn.disabled = true));

        // --- Đánh dấu nút người dùng chọn (nếu có) ---
        if (selectedButton) {
            if (isCorrect) {
                selectedButton.classList.add("correct"); // Thêm class 'correct' (màu xanh) nếu đúng.
                countCorrectAnswers++; // Tăng số câu đúng.
            } else {
                selectedButton.classList.add("incorrect"); // Thêm class 'incorrect' (màu đỏ) nếu sai.
            }
        }

        // --- Luôn hiển thị đáp án đúng sau khi trả lời ---
        // Duyệt qua tất cả các nút đáp án.
        allButtons.forEach((btn) => {
            // Tìm nút là đáp án đúng.
            if (parseInt(btn.dataset.answerId) === correctAnswerId) {
                btn.classList.add("correct"); // Thêm class 'correct' (màu xanh) cho đáp án đúng.
                // Thêm viền đứt nét để làm nổi bật đáp án đúng, đặc biệt khi người dùng chọn sai.
                btn.style.borderStyle = "dashed";
            }
        });

        // --- Logic phản hồi và chuyển tiếp dựa trên mục tiêu và cấp độ ---
        switch (studyGoal) {
            case "exam_prep":
                if (isCorrect) {
                    instructionText.textContent =
                        "Chính xác! Tự động chuyển câu.";
                    nextButton.style.display = "none";
                    setTimeout(() => {
                        currentQuestionIndex++;
                        renderQuestion();
                    }, 1000);
                } else {
                    instructionText.textContent =
                        "Sai rồi. Nhấn 'Tiếp tục' để chuyển câu.";
                    nextButton.style.display = "block";
                    document.addEventListener("keydown", keydownHandler);
                }
                break;

            case "memorize_all":
                if (isCorrect) {
                    instructionText.textContent = "Tuyệt vời! Bạn đã ghi nhớ.";
                    nextButton.style.display = "block";
                    document.addEventListener("keydown", keydownHandler);
                } else {
                    instructionText.textContent =
                        "Cần ghi nhớ kỹ hơn. Hãy chọn lại cho đến khi đúng.";
                    answered = false; // Đặt lại 'answered' về false để cho phép người dùng chọn lại.
                    if (selectedButton) selectedButton.disabled = true; // Chỉ vô hiệu hóa nút mà người dùng vừa chọn sai.
                    allButtons.forEach((btn) => {
                        // Kích hoạt lại các nút đáp án khác (không phải đáp án đúng và không phải nút vừa chọn sai).
                        if (
                            parseInt(btn.dataset.answerId) !==
                                correctAnswerId &&
                            btn !== selectedButton
                        ) {
                            btn.disabled = false; // Bật lại nút.
                            btn.classList.remove("incorrect"); // Xóa highlight sai nếu có.
                        }
                    });
                    nextButton.style.display = "none"; // Ẩn nút "Tiếp tục" vì phải chọn đúng mới được qua.
                }
                break;

            default: // Các chế độ khác (ví dụ: easy, medium)
                if (isCorrect) {
                    instructionText.textContent =
                        "Chính xác! Nhấn 'Tiếp tục' để chuyển câu.";
                } else {
                    instructionText.textContent =
                        "Sai rồi. Đáp án đúng đã được đánh dấu. Nhấn 'Tiếp tục' để chuyển câu.";
                }
                nextButton.style.display = "block";
                document.addEventListener("keydown", keydownHandler);
                break;
        }
    }

    // --- Cập nhật tiến trình flashcard (updateProgressBar) ---
    function updateProgressBar() {
        // Tính phần trăm hoàn thành.
        const progressPercent = (currentQuestionIndex / quizData.length) * 100;
        progressBar.style.width = progressPercent + "%"; // Cập nhật chiều rộng của thanh tiến trình.
        currentCardNumber.textContent = currentQuestionIndex; // Cập nhật số câu hỏi hiện tại.
        // Điều chỉnh vị trí của số câu hỏi hiện tại để nó không tràn ra khỏi thanh tiến trình
        // khi tiến độ gần 100%. `min` đảm bảo nó không vượt quá 100% trừ đi chiều rộng của chính nó.
        currentCardNumber.style.left = `min(${progressPercent}%, calc(100% - ${currentCardNumber.offsetWidth}px))`;
    }

    // --- Hàm trộn ngẫu nhiên mảng - Fisher-Yates Shuffle (shuffleArray) ---
    // Đây là một thuật toán hiệu quả để trộn ngẫu nhiên thứ tự các phần tử trong một mảng.
    // Đảm bảo mỗi hoán vị đều có xác suất xảy ra như nhau.
    function shuffleArray(array) {
        // Lặp từ phần tử cuối cùng đến phần tử thứ hai (index 1).
        for (let i = array.length - 1; i > 0; i--) {
            // Sinh một chỉ số ngẫu nhiên 'j' từ 0 đến 'i' (bao gồm cả 'i').
            const j = Math.floor(Math.random() * (i + 1));
            // Hoán đổi vị trí của phần tử tại 'i' và 'j' bằng cú pháp destructuring assignment.
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array; // Trả về mảng đã được trộn.
    }

    // --- Khi click nút "Tiếp tục" ---
    nextButton.addEventListener("click", function () {
        currentQuestionIndex++;
        renderQuestion();
    });

    // --- Nhấn phím bất kỳ để tiếp tục nếu được cho phép (keydownHandler) ---
    function keydownHandler(event) {
        // Chỉ thực hiện hành động nếu nút "Tiếp tục" đang hiển thị trên giao diện.
        if (nextButton.style.display === "block") {
            // Ngăn chặn hành vi mặc định của trình duyệt đối với phím nhấn (ví dụ: phím cách cuộn trang).
            event.preventDefault();
            currentQuestionIndex++;
            renderQuestion();
        }
    }
    // Gắn sự kiện 'keydown' ban đầu cho toàn bộ tài liệu.
    // Sự kiện này sẽ được 'removeEventListener' và 'addEventListener' trong hàm handleAnswer
    document.addEventListener("keydown", keydownHandler);

    const initialModal = new bootstrap.Modal(studyModalEl);
    initialModal.show();
});
