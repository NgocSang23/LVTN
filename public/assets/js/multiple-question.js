document.addEventListener("DOMContentLoaded", function() {
    let multiplequestions = []; // Mảng lưu danh sách câu hỏi từ API
    let testId = window.location.pathname.split("/").pop(); // Lấy ID bài kiểm tra từ URL
    let timeLeft = 0; // Biến lưu thời gian còn lại của bài kiểm tra
    let answeredQuestions = {}; // Đối tượng lưu trạng thái câu hỏi đã trả lời

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

                // Hiển thị tiêu đề bài kiểm tra (nếu có)
                document.getElementById("topic-title").innerText = tests.questionnumbers?.[0]?.topic?.title || "Không có tiêu đề";

                // Chuyển đổi thời gian từ định dạng "MM:SS" thành tổng số giây
                timeLeft = tests.time.split(":").reduce((acc, val) => acc * 60 + parseInt(val), 0);

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
                let question = multiplequestions.find(q => q.id == questionId); // Tìm câu hỏi trong danh sách
                if (!question) return;

                let selectedAnswer = question.testresults.find(opt => opt.option_id == selectedOptionId); // Tìm phương án được chọn

                // Xóa màu nền của các đáp án trước đó
                document.querySelectorAll(`input[name='question${questionId}']`).forEach(
                    radio => {
                        let label = radio.nextElementSibling;
                        label.classList.remove("bg-success", "bg-danger", "text-white",
                            "p-1", "rounded");
                        radio.disabled = true; // Khóa ô chọn sau khi đã chọn đáp án
                    });

                // Đánh dấu đúng/sai
                if (selectedAnswer && selectedAnswer.answer === "1") {
                    this.nextElementSibling.classList.add("bg-success", "text-white", "p-1", "rounded");
                } else {
                    this.nextElementSibling.classList.add("bg-danger", "text-white", "p-1", "rounded");
                }

                // Cập nhật trạng thái câu hỏi đã trả lời
                answeredQuestions[questionId] = true;
                updateNavigationButtons();
            });
        });
    }

    // Cập nhật màu của nút điều hướng khi câu hỏi đã được trả lời
    function updateNavigationButtons() {
        document.querySelectorAll(".question-nav").forEach((btn, index) => {
            if (answeredQuestions[multiplequestions[index].id]) {
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

    // Gọi API khi trang tải xong
    fetchTests();
});