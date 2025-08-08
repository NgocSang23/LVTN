// --- Khai báo các biến và khởi tạo ban đầu ---

// Lấy danh sách các câu hỏi từ biến toàn cục `window.essayData`.
// Nếu biến này không tồn tại, mặc định sẽ là một mảng rỗng.
const essayQuestions = window.essayData || [];

// Tạo một mảng để lưu trữ câu trả lời của người dùng cho từng câu hỏi.
// Kích thước của mảng này bằng số lượng câu hỏi, và ban đầu tất cả các phần tử đều rỗng.
const answers = Array(essayQuestions.length).fill("");

// Biến này lưu chỉ số của câu hỏi hiện tại đang được hiển thị. Bắt đầu từ 0.
let currentIndex = 0;

// Lấy các phần tử HTML cần thiết từ DOM.
const questionElem = document.getElementById("essayQuestion"); // Ô hiển thị câu hỏi
const answerElem = document.getElementById("essayAnswer"); // Ô nhập câu trả lời của người dùng

// --- Các hàm chức năng chính ---

// Hàm này có nhiệm vụ cập nhật giao diện để hiển thị câu hỏi và câu trả lời tương ứng.
function renderEssay(index) {
    // Hiển thị nội dung câu hỏi tại vị trí 'index'.
    questionElem.innerHTML = essayQuestions[index].content;

    // Hiển thị câu trả lời đã lưu của người dùng (nếu có).
    answerElem.value = answers[index];

    // Reset kết quả của lần kiểm tra trước đó mỗi khi chuyển sang câu hỏi mới.
    const resultContainer = document.querySelector("#resultContainer"); // Khu vực hiển thị phản hồi từ AI
    const percentBar = document.getElementById("essayProgressBar"); // Thanh tiến độ đánh giá
    const loadingElem = document.querySelector("#essayLoading"); // Icon "đang tải"

    if (resultContainer) resultContainer.innerHTML = ""; // Xóa nội dung phản hồi.

    if (percentBar) {
        // Đặt lại thanh tiến độ về 0%.
        percentBar.style.width = "0%";
        percentBar.textContent = "0%";
        percentBar.setAttribute("aria-valuenow", 0);
        // Ẩn thanh tiến độ đi.
        percentBar.classList.add("d-none");
    }

    // Ẩn icon "đang tải".
    if (loadingElem) loadingElem.classList.add("d-none");
}

// Hàm này xử lý việc chuyển sang câu hỏi tiếp theo.
function nextEssay() {
    // Lưu câu trả lời hiện tại của người dùng vào mảng `answers`.
    answers[currentIndex] = answerElem.value;

    // Nếu chỉ có 1 câu hỏi, không làm gì cả.
    if (essayQuestions.length <= 1) return;

    let newIndex;
    // Chọn một chỉ số ngẫu nhiên cho câu hỏi mới, đảm bảo nó không trùng với câu hỏi hiện tại.
    do {
        newIndex = Math.floor(Math.random() * essayQuestions.length);
    } while (newIndex === currentIndex);

    // Cập nhật chỉ số câu hỏi hiện tại.
    currentIndex = newIndex;
    // Hiển thị câu hỏi mới.
    renderEssay(currentIndex);
}

// --- Xử lý sự kiện (Event Handling) ---

// Chờ cho toàn bộ nội dung HTML của trang được tải xong.
document.addEventListener("DOMContentLoaded", () => {
    // Hiển thị câu hỏi đầu tiên khi trang vừa load.
    renderEssay(currentIndex);

    const nextButton = document.getElementById("nextEssayBtn");
    if (nextButton) {
        // Gán sự kiện "click" cho nút "Câu hỏi tiếp theo".
        nextButton.addEventListener("click", nextEssay);
    }
});

// Hàm bất đồng bộ (async) để gửi câu trả lời của người dùng và nhận phản hồi từ AI.
async function checkEssayProgress() {
    // Lưu câu trả lời hiện tại trước khi xử lý.
    answers[currentIndex] = answerElem.value;

    const currentQuestion = essayQuestions[currentIndex];
    const userAnswer = answers[currentIndex].trim(); // Lấy câu trả lời và xóa khoảng trắng thừa.

    // Lấy CSRF token để gửi kèm theo yêu cầu (để đảm bảo tính bảo mật).
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const resultContainer = document.querySelector("#resultContainer");
    const loadingElem = document.querySelector("#essayLoading");
    const percentBar = document.getElementById("essayProgressBar");

    // Nếu người dùng chưa nhập câu trả lời, hiển thị thông báo lỗi.
    if (!userAnswer) {
        resultContainer.innerHTML =
            "<p class='text-danger'>Vui lòng nhập câu trả lời trước khi kiểm tra.</p>";
        return;
    }

    // Xóa các class CSS cũ và nội dung cũ để chuẩn bị hiển thị kết quả mới.
    resultContainer.classList.remove(
        "text-danger",
        "text-success",
        "text-warning"
    );
    resultContainer.innerHTML = "";

    // Hiển thị icon "đang tải" để báo hiệu cho người dùng.
    if (loadingElem) loadingElem.classList.remove("d-none");

    try {
        // Gửi yêu cầu POST đến API của AI.
        const response = await fetch("/user/ai/check-answer", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                question_id: currentQuestion.id,
                answeruser_content: userAnswer,
            }),
        });

        // Lấy phản hồi từ máy chủ dưới dạng văn bản thô.
        let rawResponse = await response.text();
        console.log("📥 Phản hồi từ AI (thô):", rawResponse);

        let data = null;
        try {
            // Kiểm tra và phân tích phản hồi JSON.
            // Có thể phản hồi là một chuỗi JSON lồng trong một chuỗi JSON khác, nên cần xử lý hai lần.
            if (isJson(rawResponse)) {
                data = JSON.parse(rawResponse);
                if (isJson(data.response)) data = JSON.parse(data.response);
            } else {
                throw new Error("Phản hồi không phải JSON");
            }
        } catch (err) {
            // Nếu có lỗi khi phân tích JSON, hiển thị thông báo lỗi.
            console.error("❌ Lỗi định dạng JSON:", err);
            resultContainer.innerHTML = `<p class='text-danger fw-bold'>Phản hồi từ AI không đúng định dạng. Hãy thử lại sau.</p>`;
            if (loadingElem) loadingElem.classList.add("d-none");
            return;
        }

        // Ẩn icon "đang tải" sau khi có kết quả.
        if (loadingElem) loadingElem.classList.add("d-none");

        // Kiểm tra xem dữ liệu trả về có hợp lệ không (có đủ feedback và category).
        if (
            !data ||
            typeof data.feedback !== "string" ||
            typeof data.category !== "string"
        ) {
            resultContainer.innerHTML =
                "<p class='text-danger fw-bold'>Lỗi phản hồi từ AI (thiếu thông tin).</p>";
            return;
        }

        // Dựa vào category (loại đánh giá) để gán class CSS phù hợp (xanh, đỏ, vàng).
        let categoryClass = "text-warning";
        if (data.category.toLowerCase().includes("chính xác"))
            categoryClass = "text-success";
        if (data.category.toLowerCase().includes("sai"))
            categoryClass = "text-danger";

        // Lấy điểm phần trăm và cập nhật thanh tiến độ.
        const percent = typeof data.percent === "number" ? data.percent : 0;
        if (percentBar) {
            percentBar.style.width = percent + "%";
            percentBar.setAttribute("aria-valuenow", percent);
            percentBar.textContent = percent + "%";
            percentBar.classList.remove("d-none");
        }

        // Hiển thị kết quả và phản hồi từ AI ra giao diện.
        resultContainer.innerHTML = `
            <p class='fw-bold ${categoryClass}'>Đánh giá: ${data.category}</p>
            <p>${data.feedback}</p>
            ${
                // Hiển thị đáp án đúng nếu có.
                data.correct_answer
                    ? `<p class="text-muted fst-italic">Đáp án đúng: ${data.correct_answer}</p>`
                    : ""
            }
        `;
    } catch (error) {
        // Xử lý lỗi nếu có vấn đề về kết nối.
        console.error("❌ Lỗi kết nối:", error);
        if (loadingElem) loadingElem.classList.add("d-none");
        resultContainer.innerHTML =
            "<p class='text-danger fw-bold'>Lỗi kết nối đến máy chủ.</p>";
    }
}

// Hàm kiểm tra xem một chuỗi có phải là JSON hợp lệ hay không.
function isJson(str) {
    try {
        JSON.parse(str);
        return true;
    } catch (e) {
        return false;
    }
}
