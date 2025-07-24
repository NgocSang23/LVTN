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
}

function flipFlashcard() {
    isFlipped = !isFlipped;
    document
        .getElementById("flashcardInner")
        .classList.toggle("flashcard-flip", isFlipped);
}

function nextFlashcard() {
    currentIndex = (currentIndex + 1) % flashcards.length;
    updateFlashcard(currentIndex);
}

function prevFlashcard() {
    currentIndex = (currentIndex - 1 + flashcards.length) % flashcards.length;
    updateFlashcard(currentIndex);
}

document.addEventListener("DOMContentLoaded", function () {
    if (flashcards.length > 0) {
        updateFlashcard(currentIndex);
    } else {
        document.getElementById("flashcardFront").innerHTML =
            "Không có flashcard nào!";
    }
});
