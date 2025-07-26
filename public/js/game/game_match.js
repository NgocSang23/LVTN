// Sự kiện 'DOMContentLoaded' đảm bảo rằng toàn bộ tài liệu HTML đã được tải và phân tích cú pháp
// trước khi JavaScript này được thực thi. Điều này quan trọng để đảm bảo tất cả các phần tử DOM
// mà script muốn tương tác đều đã tồn tại.
document.addEventListener("DOMContentLoaded", function () {
    // 'selected' là một mảng dùng để lưu trữ hai nút mà người dùng đã chọn.
    // Khi mảng này đạt 2 phần tử, logic kiểm tra cặp sẽ được kích hoạt.
    let selected = [];
    // 'matchedPairs' đếm số cặp đã được ghép đúng.
    let matchedPairs = 0;
    // 'totalPairs' tính tổng số cặp cần ghép trong trò chơi.
    // Nó lấy tổng số nút có class 'match-btn' và chia đôi, vì mỗi cặp có 2 nút.
    const totalPairs = document.querySelectorAll(".match-btn").length / 2;

    // 'activeBg' định nghĩa màu nền khi một nút được chọn (màu vàng nhạt).
    const activeBg = "#ccc"; // nền vàng nhạt khi được chọn

    // --- Gắn sự kiện click cho tất cả các nút ghép cặp ---
    // Duyệt qua tất cả các phần tử có class 'match-btn'.
    document.querySelectorAll(".match-btn").forEach((btn) => {
        // 'isQuestion' là một biến boolean kiểm tra xem nút hiện tại có phải là nút câu hỏi không.
        // Mặc dù biến này được định nghĩa, nhưng nó không được sử dụng trong logic hiện tại.
        const isQuestion = btn.classList.contains("question-btn");

        // Gắn một hàm xử lý sự kiện click cho từng nút 'match-btn'.
        btn.addEventListener("click", function () {
            // --- Logic kiểm tra trạng thái của nút khi click ---
            // 'this' ở đây tham chiếu đến nút 'btn' đang được click.
            // Nếu nút đã được ghép đúng ('matched') hoặc đã được chọn ('selected') trước đó,
            // thì không làm gì cả, ngăn chặn việc click lại nút đó.
            if (
                this.classList.contains("matched") ||
                this.classList.contains("selected")
            )
                return; // Thoát khỏi hàm xử lý sự kiện.

            // --- Đánh dấu nút được chọn ---
            // Thêm class 'selected' vào nút để đánh dấu nó đã được chọn.
            this.classList.add("selected");
            // Đặt màu nền của nút thành 'activeBg' để người dùng biết nó đã được chọn.
            this.style.backgroundColor = activeBg;
            // Thêm nút hiện tại vào mảng 'selected'.
            selected.push(this);

            // --- Logic kiểm tra cặp khi đã có 2 nút được chọn ---
            // Nếu đã có 2 nút trong mảng 'selected' (người dùng đã chọn một cặp).
            if (selected.length === 2) {
                // Gán hai nút được chọn vào các biến 'first' và 'second' bằng destructuring.
                const [first, second] = selected;
                // Lấy giá trị của thuộc tính 'data-word' từ nút đầu tiên.
                // Thuộc tính này thường chứa từ hoặc ID của cặp.
                const word1 = first.dataset.word;
                // Lấy giá trị của thuộc tính 'data-word' từ nút thứ hai.
                const word2 = second.dataset.word;

                // --- Kiểm tra xem hai từ có khớp nhau không ---
                if (word1 === word2) {
                    // --- Xử lý khi cặp khớp đúng ---
                    // Sử dụng 'setTimeout' để tạo độ trễ nhỏ (300ms) trước khi ẩn cặp.
                    // Điều này giúp người dùng có thời gian nhìn thấy cặp đúng.
                    setTimeout(() => {
                        // Duyệt qua cả hai nút đã chọn.
                        [first, second].forEach((btn) => {
                            // Thêm class 'matched' (để đánh dấu đã khớp) và 'hidden' (để ẩn nút).
                            // Class 'hidden' thường có CSS 'display: none' hoặc 'visibility: hidden'.
                            btn.classList.add("matched", "hidden");
                            // Xóa class 'selected' vì nút không còn trong trạng thái được chọn nữa.
                            btn.classList.remove("selected");
                        });
                        // Xóa rỗng mảng 'selected' để sẵn sàng cho cặp chọn tiếp theo.
                        selected = [];
                        // Tăng số cặp đã khớp đúng lên 1.
                        matchedPairs++;

                        // --- Kiểm tra chiến thắng trò chơi ---
                        // Nếu số cặp đã khớp bằng tổng số cặp trong trò chơi.
                        if (matchedPairs === totalPairs) {
                            // Sử dụng 'setTimeout' để tạo độ trễ (2000ms = 2 giây) trước khi hiển thị modal thông báo chiến thắng.
                            setTimeout(() => {
                                // Khởi tạo một modal Bootstrap.
                                const modal = new bootstrap.Modal(
                                    document.getElementById("gameCompleteModal") // Tìm phần tử modal bằng ID.
                                );
                                modal.show(); // Hiển thị modal thông báo chiến thắng.
                                // Sau khi modal hiển thị, tạo thêm độ trễ (2000ms = 2 giây) trước khi tải lại trang.
                                // Tải lại trang sẽ reset trò chơi.
                                setTimeout(
                                    () => window.location.reload(),
                                    2000
                                );
                            }, 2000); // Thời gian chờ trước khi hiển thị modal.
                        }
                    }, 300); // Thời gian chờ trước khi ẩn cặp đúng.
                } else {
                    // --- Xử lý khi cặp không khớp ---
                    // Sử dụng 'setTimeout' để tạo độ trễ (700ms) trước khi các nút trở lại trạng thái ban đầu.
                    // Điều này giúp người dùng nhận ra họ đã chọn sai.
                    setTimeout(() => {
                        // Duyệt qua cả hai nút đã chọn.
                        [first, second].forEach((btn) => {
                            // Xóa class 'selected' để bỏ trạng thái được chọn.
                            btn.classList.remove("selected");
                            // Đặt lại màu nền của nút về mặc định (hoặc màu nền ban đầu).
                            btn.style.backgroundColor = ""; // reset màu nền
                        });
                        // Xóa rỗng mảng 'selected' để sẵn sàng cho cặp chọn tiếp theo.
                        selected = [];
                    }, 700); // Thời gian chờ trước khi reset các nút không khớp.
                }
            }
        });
    });
});
