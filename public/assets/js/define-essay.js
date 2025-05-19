document.addEventListener("DOMContentLoaded", function() {
    let currentIndex = 0; // Chỉ số câu hỏi hiện tại
    let questions = []; // Danh sách câu hỏi

    // Lấy đường dẫn từ URL
    const path = window.location.pathname;
    const parts = path.split("/");
    const cardId = parts.pop();

    function fetchQuestions() {
        fetch(`http://127.0.0.1:8000/api/card_define_essay/${cardId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status_code === 200 && data.data.length > 0) {
                    questions = data.data;
                    currentIndex = 0;
                    updateQuestion();
                } else {
                    document.querySelector(".question_content").innerText = "Không có dữ liệu!";
                    document.querySelector(".answer_content").innerText = "";
                }
            })
            .catch(error => {
                console.error("Lỗi API:", error);
                alert("Không thể tải dữ liệu, vui lòng thử lại sau.");
            });
    }

    function updateQuestion() {
        if (questions.length === 0) return;
        let cardData = questions[currentIndex];
        let question = cardData.question;
        let type = question.type;
        let topic = question.topic;
        let answer = (question.answers && question.answers.length > 0) ? question.answers[0].content :
            "Chưa có đáp án";
        let image = (question.images && question.images.length > 0) ? question.images[0].path : null;
        let card = document.querySelector(".card");

        document.querySelector(".topic_title").innerText = 'Chủ đề: ' + topic.title;

        if (type === "definition") {
            const flipCard = document.querySelector('.flip-card');

            flipCard.addEventListener('click', function() {
                flipCard.classList.toggle('flipped');

                const frontCardBody = document.querySelector('.front-card-body');
                const backCardBody = document.querySelector('.back-card-body');

                if (flipCard.classList.contains('flipped')) {
                    backCardBody.style.display = 'block';
                } else {
                    backCardBody.style.display = 'none';
                }
            });
            document.querySelector(".question_content").innerText = question.content;
            document.querySelector(".answer_content").innerText = answer;
        } else if (type === "essay") {
            card.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div></div>
                        <button class="btn btn-light border">Ôn tập</button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="display-5 fw-bold ms-5 question_content">${question.content}</h1>
                        <img class="img-fluid rounded shadow-sm image_path d-none" style="max-width: 40%;">
                    </div>
                    <hr>

                    <!-- Thêm meta CSRF token -->
                    <meta name="csrf-token" content="{{ csrf_token() }}">

                    <!-- Xóa action & method, thêm id="answerForm" -->
                    <form id="answerForm">
                        <input type="hidden" name="question_id" value="${question.id}">
                        <div class="d-flex row">
                            <div class="col-9">
                                <input type="text" name="answeruser_content" id="userAnswer" placeholder="Nhập câu trả lời của bạn" class="form-control me-3">
                                <small id="error-message" class="text-danger d-none">Xin nhập câu trả lời</small>
                            </div>
                            <div class="col-3">
                                <!-- Thêm type="button" để tránh submit mặc định -->
                                <button type="button" class="btn btn-primary text-white check-answer">Kiểm tra</button>
                            </div>
                        </div>
                    </form>

                    <!-- Progress bar -->
                    <div class="progress mt-3 w-100" style="height: 25px; max-width: 600px;">
                        <div id="percentBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                            role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            0%
                        </div>
                    </div>

                    <!-- Hiển thị kết quả -->
                    <div id="resultContainer" class="mt-2 text-center"></div>
                </div>
            `;

            document.querySelector("#answerForm").addEventListener("submit", function(event) {
                event.preventDefault(); // Ngăn form gửi mặc định
            });

            document.querySelector(".check-answer").addEventListener("click", function() {
                console.log("Nút kiểm tra đã được bấm!"); // Debug

                let userAnswer = document.getElementById("userAnswer").value.trim();
                let questionId = document.querySelector("input[name='question_id']").value;
                let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
                let resultContainer = document.querySelector("#resultContainer");
                let percentBar = document.getElementById("percentBar");

                if (!userAnswer) {
                    console.log("Người dùng chưa nhập câu trả lời!");
                    document.getElementById("error-message").classList.remove("d-none");
                    return;
                } else {
                    document.getElementById("error-message").classList.add("d-none");
                }

                console.log("Gửi request đến server...");

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
                        console.log("Phản hồi từ server:", data); // Debug

                        // Kiểm tra phản hồi hợp lệ
                        if (!data || !data.type || !data.feedback) {
                            resultContainer.innerHTML = "<p class='text-danger fw-bold text-center'>Lỗi phản hồi từ AI</p>";
                            return;
                        }

                        // Nếu feedback là một chuỗi JSON, giải mã nó
                        if (typeof data.feedback === "string" && isJson(data.feedback)) {
                            try {
                                data = JSON.parse(data.feedback);
                            } catch (error) {
                                console.error("Lỗi giải mã JSON:", error, "Phản hồi:", data
                                    .feedback);
                            }
                        }

                        // Xử lý câu hỏi toán học
                        if (data.type.includes("math") && data.percent !== undefined) {
                            let percent = Math.max(0, Math.min(100, data.percent));
                            percentBar.style.width = percent + "%";
                            percentBar.setAttribute("aria-valuenow", percent);
                            percentBar.textContent = percent + "%";
                            percentBar.classList.remove(
                            "d-none"); // Hiện progress bar nếu là câu toán

                            resultContainer.innerHTML = `<p class='fw-bold text-center'>Mức độ chính xác: ${percent}%</p>`;
                        }
                        // Xử lý câu hỏi lý thuyết
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

                            // Ẩn progress bar nếu là câu lý thuyết
                            percentBar.style.width = "0%";
                            percentBar.setAttribute("aria-valuenow", 0);
                            percentBar.textContent = "0%";
                            percentBar.classList.add("d-none");
                        }
                        // Trường hợp không xác định
                        else {
                            resultContainer.innerHTML =
                                "<p class='text-danger fw-bold text-center'>Lỗi phản hồi từ AI</p>";
                        }
                    })
                    .catch(error => {
                        console.error("Lỗi:", error);
                        resultContainer.innerHTML =
                            "<p class='text-danger fw-bold text-center'>Lỗi kết nối đến máy chủ</p>";
                    });
            });

            function isJson(str) {
                try {
                    JSON.parse(str);
                    return true;
                } catch (e) {
                    return false;
                }
            }

        }

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

        document.querySelector(".current-question").textContent = currentIndex + 1;
        document.querySelector(".total-questions").textContent = questions.length;

        // Cập nhật đường dẫn sửa và xóa
        document.getElementById("editQuestionForm").action =
            `{{ route('flashcard_define_essay.update', ':id') }}`.replace(':id', cardData.id);
        document.getElementById("deleteForm").action =
            `{{ route('flashcard_define_essay.destroy', ':id') }}`.replace(':id', cardData.id);
        document.getElementById("editQuestionContent").value = question.content;
        document.getElementById("editAnswerContent").value = answer;
    }

    document.querySelector(".prev-question").addEventListener("click", function() {
        if (currentIndex > 0) {
            currentIndex--;
            updateQuestion();
        }
    });

    document.querySelector(".next-question").addEventListener("click", function() {
        if (currentIndex < questions.length - 1) {
            currentIndex++;
            updateQuestion();
        }
    });

    document.addEventListener("change", function(event) {
        if (event.target.id === "editImageInput") {
            let file = event.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let imgPreview = document.getElementById("editImagePreview");
                    imgPreview.src = e.target.result;
                    imgPreview.classList.remove("d-none");

                    // Cập nhật đường dẫn (chỉ để hiển thị, không ảnh hưởng file upload)
                    document.getElementById("editImagePath").value = file.name;
                };
                reader.readAsDataURL(file);
            }
        }
    });
    fetchQuestions(); // Gọi API khi trang tải xong
});