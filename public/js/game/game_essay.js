const essayQuestions = window.essayData || [];
const answers = Array(essayQuestions.length).fill("");
let currentIndex = 0;

const questionElem = document.getElementById("essayQuestion");
const answerElem = document.getElementById("essayAnswer");
const counterElem = document.getElementById("questionCounter");

function renderEssay(index) {
    questionElem.innerHTML = essayQuestions[index];
    answerElem.value = answers[index];
    counterElem.textContent = `${index + 1} / ${essayQuestions.length}`;
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

function checkEssayProgress() {
    answers[currentIndex] = answerElem.value;

    const answered = answers.filter((a) => a.trim() !== "").length;
    const total = essayQuestions.length;
    const percent = Math.round((answered / total) * 100);

    const progressBar = document.getElementById("essayProgressBar");
    progressBar.style.width = percent + "%";
    progressBar.setAttribute("aria-valuenow", percent);
    progressBar.textContent = percent + "%";

    if (percent === 100) {
        progressBar.classList.remove("bg-info");
        progressBar.classList.add("bg-success");
    } else {
        progressBar.classList.remove("bg-success");
        progressBar.classList.add("bg-info");
    }
}
