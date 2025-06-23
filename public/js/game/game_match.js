document.addEventListener("DOMContentLoaded", function () {
    let selected = [];
    let matchedPairs = 0;
    const totalPairs = document.querySelectorAll(".match-btn").length / 2;

    const activeBg = "#ccc"; // nền vàng nhạt khi được chọn

    // Gắn sự kiện click cho nút
    document.querySelectorAll(".match-btn").forEach((btn) => {
        const isQuestion = btn.classList.contains("question-btn");

        btn.addEventListener("click", function () {
            if (
                this.classList.contains("matched") ||
                this.classList.contains("selected")
            )
                return;

            this.classList.add("selected");
            this.style.backgroundColor = activeBg;
            selected.push(this);

            if (selected.length === 2) {
                const [first, second] = selected;
                const word1 = first.dataset.word;
                const word2 = second.dataset.word;

                if (word1 === word2) {
                    setTimeout(() => {
                        [first, second].forEach((btn) => {
                            btn.classList.add("matched", "hidden");
                            btn.classList.remove("selected");
                        });
                        selected = [];
                        matchedPairs++;

                        if (matchedPairs === totalPairs) {
                            setTimeout(() => {
                                const modal = new bootstrap.Modal(
                                    document.getElementById("gameCompleteModal")
                                );
                                modal.show();
                                setTimeout(
                                    () => window.location.reload(),
                                    2000
                                );
                            }, 2000);
                        }
                    }, 300);
                } else {
                    setTimeout(() => {
                        [first, second].forEach((btn) => {
                            btn.classList.remove("selected");
                            btn.style.backgroundColor = ""; // reset màu nền
                        });
                        selected = [];
                    }, 700);
                }
            }
        });
    });
});
