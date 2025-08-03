const essayQuestions = window.essayData || [];
const answers = Array(essayQuestions.length).fill("");
let currentIndex = 0;

const questionElem = document.getElementById("essayQuestion");
const answerElem = document.getElementById("essayAnswer");
const counterElem = document.getElementById("questionCounter");

function renderEssay(index) {
    questionElem.innerHTML = essayQuestions[index].content;
    answerElem.value = answers[index];
    counterElem.textContent = `${index + 1} / ${essayQuestions.length}`;

    // Reset kết quả mỗi lần chuyển câu
    const resultContainer = document.querySelector("#resultContainer");
    const percentBar = document.getElementById("essayProgressBar");
    const loadingElem = document.querySelector("#essayLoading");

    if (resultContainer) resultContainer.innerHTML = "";
    if (percentBar) {
        percentBar.style.width = "0%";
        percentBar.textContent = "0%";
        percentBar.setAttribute("aria-valuenow", 0);
        percentBar.classList.add("d-none");
    }
    if (loadingElem) loadingElem.classList.add("d-none");
}

function nextEssay() {
    answers[currentIndex] = answerElem.value;
    if (currentIndex < essayQuestions.length - 1) {
        currentIndex++;
        renderEssay(currentIndex);
    }
}

function prevEssay() {
    answers[currentIndex] = answerElem.value;
    if (currentIndex > 0) {
        currentIndex--;
        renderEssay(currentIndex);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    renderEssay(currentIndex);
});

async function checkEssayProgress() {
    answers[currentIndex] = answerElem.value;

    const currentQuestion = essayQuestions[currentIndex];
    const userAnswer = answers[currentIndex].trim();

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const resultContainer = document.querySelector("#resultContainer");
    const loadingElem = document.querySelector("#essayLoading");
    const percentBar = document.getElementById("essayProgressBar");

    if (!userAnswer) {
        resultContainer.innerHTML =
            "<p class='text-danger'>Vui lòng nhập câu trả lời trước khi kiểm tra.</p>";
        return;
    }

    // Reset UI
    resultContainer.classList.remove(
        "text-danger",
        "text-success",
        "text-warning"
    );
    resultContainer.innerHTML = "";
    if (loadingElem) loadingElem.classList.remove("d-none");

    try {
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

        let rawResponse = await response.text();
        console.log("📥 Phản hồi từ AI (thô):", rawResponse);

        let data = null;
        try {
            if (isJson(rawResponse)) {
                data = JSON.parse(rawResponse);
                if (isJson(data.response)) data = JSON.parse(data.response); // hỗ trợ trường hợp bọc thêm JSON
            } else {
                throw new Error("Phản hồi không phải JSON");
            }
        } catch (err) {
            console.error("❌ Lỗi định dạng JSON:", err);
            resultContainer.innerHTML = `<p class='text-danger fw-bold'>Phản hồi từ AI không đúng định dạng. Hãy thử lại sau.</p>`;
            if (loadingElem) loadingElem.classList.add("d-none");
            return;
        }

        if (loadingElem) loadingElem.classList.add("d-none");

        // Kiểm tra dữ liệu hợp lệ
        if (
            !data ||
            typeof data.feedback !== "string" ||
            typeof data.category !== "string"
        ) {
            resultContainer.innerHTML =
                "<p class='text-danger fw-bold'>Lỗi phản hồi từ AI (thiếu thông tin).</p>";
            return;
        }

        // Phân loại hiển thị
        let categoryClass = "text-warning";
        if (data.category.toLowerCase().includes("chính xác"))
            categoryClass = "text-success";
        if (data.category.toLowerCase().includes("sai"))
            categoryClass = "text-danger";

        const percent = typeof data.percent === "number" ? data.percent : 0;
        if (percentBar) {
            percentBar.style.width = percent + "%";
            percentBar.setAttribute("aria-valuenow", percent);
            percentBar.textContent = percent + "%";
            percentBar.classList.remove("d-none");
        }

        resultContainer.innerHTML = `
            <p class='fw-bold ${categoryClass}'>Đánh giá: ${data.category}</p>
            <p>${data.feedback}</p>
            ${
                data.correct_answer
                    ? `<p class="text-muted fst-italic">Đáp án đúng: ${data.correct_answer}</p>`
                    : ""
            }
        `;
    } catch (error) {
        console.error("❌ Lỗi kết nối:", error);
        if (loadingElem) loadingElem.classList.add("d-none");
        resultContainer.innerHTML =
            "<p class='text-danger fw-bold'>Lỗi kết nối đến máy chủ.</p>";
    }
}

function isJson(str) {
    try {
        JSON.parse(str);
        return true;
    } catch (e) {
        return false;
    }
}
