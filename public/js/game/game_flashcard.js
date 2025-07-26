// Khai báo mảng 'flashcards'.
// Nó cố gắng lấy dữ liệu từ 'window.flashcardData'. Nếu 'window.flashcardData' không tồn tại
// (tức là không có dữ liệu flashcard được tải từ bên ngoài), nó sẽ mặc định là một mảng rỗng.
// 'window.flashcardData' thường được định nghĩa ở một script khác hoặc trực tiếp trong HTML.
let flashcards = window.flashcardData || [];

// 'currentIndex' theo dõi chỉ số của flashcard hiện tại đang được hiển thị trong mảng 'flashcards'.
// Ban đầu được đặt là 0, nghĩa là thẻ đầu tiên.
let currentIndex = 0;

// 'isFlipped' là một biến boolean để theo dõi trạng thái lật của flashcard hiện tại.
// 'false' nghĩa là đang hiển thị mặt trước (câu hỏi), 'true' nghĩa là đang hiển thị mặt sau (câu trả lời).
let isFlipped = false;

// Hàm 'updateFlashcard' có nhiệm vụ cập nhật nội dung và trạng thái của flashcard trên giao diện người dùng.
function updateFlashcard(index) {
    // Lấy đối tượng flashcard từ mảng 'flashcards' dựa trên 'index' được truyền vào.
    const fc = flashcards[index];

    // --- Cập nhật nội dung mặt trước và mặt sau của flashcard ---
    // Tìm phần tử có id 'flashcardFront' và cập nhật nội dung HTML của nó bằng 'question' của flashcard.
    document.getElementById("flashcardFront").innerHTML = fc.question;
    // Tìm phần tử có id 'flashcardAnswerText' và cập nhật nội dung HTML của nó bằng 'answer' của flashcard.
    document.getElementById("flashcardAnswerText").innerHTML = fc.answer;

    // --- Xử lý hiển thị ảnh minh họa (nếu có) ---
    const imageElement = document.getElementById("flashcardImage"); // Lấy phần tử <img>.
    if (fc.image) {
        // Nếu flashcard có thuộc tính 'image' (có đường dẫn ảnh):
        imageElement.src = fc.image; // Đặt nguồn ảnh cho thẻ <img>.
        imageElement.classList.remove("d-none"); // Bỏ class 'd-none' (display: none) để hiển thị ảnh.
    } else {
        // Nếu flashcard không có thuộc tính 'image' (không có ảnh):
        imageElement.classList.add("d-none"); // Thêm class 'd-none' để ẩn ảnh.
    }

    // --- Cập nhật chỉ số flashcard (ví dụ: "1 / 10") ---
    // Tìm phần tử có id 'flashcardCounter' và cập nhật văn bản hiển thị chỉ số hiện tại
    // (index + 1 vì index bắt đầu từ 0) và tổng số flashcard.
    document.getElementById("flashcardCounter").innerText = `${index + 1} / ${
        flashcards.length
    }`;

    // --- Đặt lại trạng thái lật của flashcard về mặt trước ---
    // Tìm phần tử có id 'flashcardInner' (thường là phần tử bao bọc nội dung có hiệu ứng lật).
    // Xóa class 'flashcard-flip' để đảm bảo flashcard luôn hiển thị mặt trước khi chuyển thẻ mới.
    document
        .getElementById("flashcardInner")
        .classList.remove("flashcard-flip");
    // Đặt lại biến trạng thái lật về 'false'.
    isFlipped = false;
}

// Hàm 'flipFlashcard' dùng để lật flashcard giữa mặt trước và mặt sau.
function flipFlashcard() {
    // Đảo ngược giá trị của 'isFlipped' (true thành false, false thành true).
    isFlipped = !isFlipped;
    // Tìm phần tử 'flashcardInner'.
    // 'classList.toggle("flashcard-flip", isFlipped)' sẽ:
    // - Thêm class 'flashcard-flip' nếu 'isFlipped' là 'true'.
    // - Xóa class 'flashcard-flip' nếu 'isFlipped' là 'false'.
    // Class 'flashcard-flip' này sẽ kích hoạt hiệu ứng CSS để lật thẻ.
    document
        .getElementById("flashcardInner")
        .classList.toggle("flashcard-flip", isFlipped);
}

// Hàm 'nextFlashcard' dùng để chuyển sang flashcard tiếp theo trong danh sách.
function nextFlashcard() {
    // Tăng 'currentIndex' lên 1.
    // Toán tử '%' (modulo) đảm bảo rằng khi 'currentIndex' đạt đến cuối mảng,
    // nó sẽ quay lại 0 (thẻ đầu tiên), tạo thành một vòng lặp.
    currentIndex = (currentIndex + 1) % flashcards.length;
    // Gọi 'updateFlashcard' để hiển thị flashcard mới.
    updateFlashcard(currentIndex);
}

// Hàm 'prevFlashcard' dùng để chuyển về flashcard trước đó trong danh sách.
function prevFlashcard() {
    // Giảm 'currentIndex' đi 1.
    // Cần thêm 'flashcards.length' trước khi thực hiện toán tử modulo để xử lý trường hợp
    // 'currentIndex' đang ở 0 và cần quay về thẻ cuối cùng (ví dụ: 0 - 1 = -1, -1 % N không hoạt động như ý muốn).
    // (0 - 1 + length) % length sẽ cho ra chỉ số cuối cùng.
    currentIndex = (currentIndex - 1 + flashcards.length) % flashcards.length;
    // Gọi 'updateFlashcard' để hiển thị flashcard mới.
    updateFlashcard(currentIndex);
}

// --- Xử lý khi tài liệu HTML đã được tải và phân tích cú pháp hoàn chỉnh ---
// 'DOMContentLoaded' đảm bảo rằng các phần tử HTML đã sẵn sàng để được thao tác bằng JavaScript.
document.addEventListener("DOMContentLoaded", function () {
    // Kiểm tra xem có flashcard nào trong mảng không.
    if (flashcards.length > 0) {
        // Nếu có, hiển thị flashcard đầu tiên.
        updateFlashcard(currentIndex);
    } else {
        // Nếu không có flashcard nào, hiển thị thông báo "Không có flashcard nào!" trên mặt trước.
        document.getElementById("flashcardFront").innerHTML =
            "Không có flashcard nào!";
        // Lưu ý: flashcardAnswerText có thể vẫn hiển thị nội dung rỗng nếu không xử lý.
        // Bạn có thể cân nhắc ẩn toàn bộ wrapper nếu muốn.
    }
});

// --- Xử lý chức năng phát âm (Text-to-Speech) ---
// Lắng nghe sự kiện click trên toàn bộ tài liệu để xử lý các nút phát âm.
document.addEventListener("click", function (e) {
    // Tìm phần tử gần nhất (hoặc chính nó) có class 'play-audio' từ vị trí click.
    const playBtn = e.target.closest(".play-audio");
    // Nếu tìm thấy một nút phát âm:
    if (playBtn) {
        // Lấy giá trị từ thuộc tính 'data-from' của nút.
        // Thuộc tính này cho biết văn bản cần đọc là từ 'question' hay 'answer'.
        const from = playBtn.dataset.from;
        // Lấy flashcard hiện tại.
        const fc = flashcards[currentIndex];
        // Chọn văn bản cần đọc dựa vào giá trị 'from'.
        const text = from === "question" ? fc.question : fc.answer;

        // --- Xóa các thẻ HTML khỏi văn bản trước khi đọc ---
        // Sử dụng biểu thức chính quy để loại bỏ tất cả các thẻ HTML (ví dụ: <p>, <strong>)
        // để 'speechSynthesis' chỉ đọc văn bản thuần túy.
        const strippedText = text.replace(/<[^>]*>?/gm, "");

        // --- Dừng phát âm đang diễn ra (nếu có) ---
        // Nếu có giọng nói nào đang được phát, hãy dừng nó lại để tránh chồng chéo.
        if (speechSynthesis.speaking) {
            speechSynthesis.cancel();
        }

        // Tạo một đối tượng 'SpeechSynthesisUtterance' với văn bản đã được làm sạch.
        const utterance = new SpeechSynthesisUtterance(strippedText);

        // --- Tự động chọn ngôn ngữ (Tiếng Việt hoặc Tiếng Anh) ---
        // Biểu thức chính quy để kiểm tra sự có mặt của các ký tự đặc trưng tiếng Việt.
        const vnChars =
            /[ăâđêôơưáàảãạấầẩẫậắằẳẵặéèẻẽẹếềểễệíìỉĩịóòỏõọốồổỗộớờởỡợúùủũụứừửữựýỳỷỹỵ]/i;
        // Nếu văn bản chứa ký tự tiếng Việt, đặt ngôn ngữ là "vi-VN", ngược lại là "en-US".
        // (Lưu ý: đoạn này có thể được cải tiến để chọn giọng đọc cụ thể như đã thảo luận trước đó.)
        utterance.lang = vnChars.test(text) ? "vi-VN" : "en-US";

        // Phát âm văn bản.
        speechSynthesis.speak(utterance);

        // 'e.stopPropagation()' ngăn chặn sự kiện click này lan truyền lên các phần tử cha.
        // Điều này quan trọng để khi người dùng click vào nút phát âm, nó sẽ không làm lật thẻ flashcard.
        e.stopPropagation(); // ⛔ Ngăn sự kiện lật thẻ khi bấm nút
    }
});

// --- Xử lý lật flashcard khi click vào vùng thẻ (trừ nút phát âm) ---
// Lắng nghe sự kiện click trên phần tử có class 'flashcard-wrapper' (thường là vùng chứa toàn bộ flashcard).
document
    .querySelector(".flashcard-wrapper")
    .addEventListener("click", function (e) {
        if (!e.target.closest(".play-audio")) {
            flipFlashcard();
        }
    });
