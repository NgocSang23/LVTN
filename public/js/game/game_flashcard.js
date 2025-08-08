let flashcards = window.flashcardData || [];
let currentIndex = 0;
let isFlipped = false;

function updateFlashcard(index) {
    const fc = flashcards[index];
    document.getElementById("flashcardFront").innerHTML = fc.question;
    document.getElementById("flashcardAnswerText").innerHTML = fc.answer;

    const imageElement = document.getElementById("flashcardImage");
    if (fc.image) {
        imageElement.src = fc.image;
        imageElement.classList.remove("d-none");
    } else {
        imageElement.classList.add("d-none");
    }

    document.getElementById("flashcardCounter").innerText = `${index + 1} / ${
        flashcards.length
    }`;

    document
        .getElementById("flashcardInner")
        .classList.remove("flashcard-flip");
    isFlipped = false;

    // Luôn hiển thị nút "Tiếp tục"
    document.getElementById("nextFlashcardBtn").classList.remove("d-none");
}

function flipFlashcard() {
    isFlipped = !isFlipped;
    const card = document.getElementById("flashcardInner");
    card.classList.toggle("flashcard-flip", isFlipped);
}

function randomFlashcard() {
    if (flashcards.length === 0) return;

    const newIndex = Math.floor(Math.random() * flashcards.length);
    currentIndex = newIndex;
    updateFlashcard(currentIndex);
}

document
    .getElementById("nextFlashcardBtn")
    .addEventListener("click", function () {
        randomFlashcard(); // Chỉ đổi flashcard khi nhấn nút "Tiếp tục"
    });

document.addEventListener("DOMContentLoaded", function () {
    if (flashcards.length > 0) {
        updateFlashcard(currentIndex);
    } else {
        document.getElementById("flashcardFront").innerHTML =
            "Không có flashcard nào!";
    }
});

document.addEventListener("click", function (e) {
    const playBtn = e.target.closest(".play-audio");
    if (playBtn) {
        const from = playBtn.dataset.from;
        const fc = flashcards[currentIndex];
        const text = from === "question" ? fc.question : fc.answer;
        const strippedText = text.replace(/<[^>]*>?/gm, "");

        if (speechSynthesis.speaking) {
            speechSynthesis.cancel();
        }

        const utterance = new SpeechSynthesisUtterance(strippedText);
        const vnChars =
            /[ăâđêôơưáàảãạấầẩẫậắằẳẵặéèẻẽẹếềểễệíìỉĩịóòỏõọốồổỗộớờởỡợúùủũụứừửữựýỳỷỹỵ]/i;
        utterance.lang = vnChars.test(text) ? "vi-VN" : "en-US";
        speechSynthesis.speak(utterance);
        e.stopPropagation();
    }
});

document
    .querySelector(".flashcard-wrapper")
    .addEventListener("click", function (e) {
        if (!e.target.closest(".play-audio")) {
            flipFlashcard(); // Click vào thẻ thì lật
        }
    });
