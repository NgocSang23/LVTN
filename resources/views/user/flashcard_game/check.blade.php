@extends('user.master')

@section('title', 'Học tập flashcard')

@section('content')
    <style>
        .dropdown-menu-check {
            background-color: #0d1117;
            color: white;
            min-width: 220px;
        }

        .dropdown-item-check {
            color: white;
            padding: 10px 15px;
            transition: 0.2s;
        }

        .dropdown-item-check:hover {
            background-color: #161b22;
            color: white;
        }

        .dropdown-toggle-check {
            background-color: #0d1117;
            color: white;
            border: none;
        }

        .dropdown-toggle-check:hover {
            background-color: #161b22;
        }

        .dropdown-divider-check {
            border-top: 1px solid #30363d;
        }

        .content-area {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 0 1rem;
            }
        }
    </style>

    <div class="container mt-4 d-flex flex-column" style="min-height: 90vh;">
        {{-- Thanh điều hướng --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                {{-- Dropdown --}}
                <div class="dropdown">
                    <button class="btn dropdown-toggle-check rounded-3 d-flex align-items-center" type="button"
                        id="flashcardMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-clone text-primary"></i>
                        <span class="mx-2">Chọn chế độ học</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-check shadow-lg rounded-3 border-0 mt-2"
                        aria-labelledby="flashcardMenu">
                        <li><a class="dropdown-item dropdown-item-check d-flex align-items-center gap-2"><i
                                    class="fas fa-sync-alt text-primary"></i> Tìm cặp</a></li>
                        <li><a class="dropdown-item dropdown-item-check d-flex align-items-center gap-2"><i
                                    class="fas fa-file-alt text-primary"></i> Học tập</a></li>
                        <li><a class="dropdown-item dropdown-item-check d-flex align-items-center gap-2"><i
                                    class="fas fa-file-alt text-primary"></i> Kiểm tra</a></li>
                        <li>
                            <hr class="dropdown-divider dropdown-divider-check">
                        </li>
                        <li><a class="dropdown-item dropdown-item-check d-flex align-items-center gap-2"
                                href="{{ route('user.dashboard') }}"><i class="fas fa-home text-primary"></i> Trang chủ</a>
                        </li>
                    </ul>
                </div>

                {{-- Tải lại --}}
                <button class="btn btn-primary d-flex align-items-center" onclick="window.location.reload();">
                    Tải lại trò chơi
                </button>
            </div>

            {{-- Nút cài đặt --}}
            <button type="button" class="btn btn-outline-secondary rounded-circle" data-bs-toggle="modal"
                data-bs-target="#checkModal" title="Cài đặt chế độ học tập">
                <i class="bi bi-gear-fill"></i>
            </button>
        </div>

        {{-- Nội dung chính --}}
        <div class="content-area mt-3">
            <!-- Progress bar -->
            <div class="position-relative mb-5" style="max-width: 700px; margin: auto;">
                <!-- Thanh nền -->
                <div style="height: 10px; background-color: #dee2e6; border-radius: 5px;">
                    <!-- Phần đã hoàn thành -->
                    <div class="bg-primary" style="width: 30%; height: 100%; border-radius: 5px;"></div>
                </div>

                <!-- Mốc hiện tại -->
                <div class="position-absolute translate-middle" style="left: 30%; top: 50%;">
                    <div class="bg-warning text-white d-flex align-items-center justify-content-center rounded-circle"
                        style="width: 32px; height: 32px; font-weight: bold; font-size: 0.85rem; box-shadow: 0 0 6px rgba(255, 193, 7, 0.8);">
                        3
                    </div>
                </div>

                <!-- Điểm cuối -->
                <div class="position-absolute translate-middle" style="right: -30px; top: 50%;">
                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-circle"
                        style="width: 32px; height: 32px; font-weight: 600; font-size: 0.9rem; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                        264
                    </div>
                </div>
            </div>

            {{-- Đúng/Sai --}}
            <div id="questionTrueFalse" class="question-box d-none">
                <div class="bg-white text-dark border rounded-lg p-4 mb-4 shadow-sm mt-4">
                    <p class="text-uppercase fw-bold small mb-2">Câu hỏi Đúng/Sai</p>
                    <p class="fs-5 mb-4">This statement is correct.</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success">Đúng</button>
                        <button class="btn btn-danger">Sai</button>
                    </div>
                </div>
            </div>

            {{-- Trắc nghiệm --}}
            <div id="questionMCQ" class="question-box">
                <div class="bg-white text-dark border rounded-lg p-4 mb-4 shadow-sm mt-4">
                    <p class="text-uppercase fw-bold small mb-2">Trắc nghiệm</p>
                    <p class="fs-5 mb-4">v /ɪnˈspaɪər/ truyền cảm hứng = motivate = encourage</p>
                    <p class="text-danger fw-semibold small mb-3">Chưa đúng, hãy cố gắng nhé!</p>
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <div class="col">
                            <button
                                class="w-100 border border-danger text-danger py-2 px-3 d-flex align-items-center gap-2">
                                <i class="fas fa-times"></i> <span>rebuild</span>
                            </button>
                        </div>
                        <div class="col">
                            <button
                                class="w-100 border border-success text-success py-2 px-3 d-flex align-items-center gap-2"
                                style="border-style: dashed;">
                                <i class="fas fa-check"></i> <span>inspire</span>
                            </button>
                        </div>
                        <div class="col">
                            <button
                                class="w-100 border border-danger text-danger py-2 px-3 d-flex align-items-center gap-2">
                                <i class="fas fa-times"></i> <span>define</span>
                            </button>
                        </div>
                        <div class="col">
                            <button disabled class="w-100 border text-muted py-2 px-3 d-flex align-items-center gap-2">
                                <span class="me-2">4</span> <span>passion</span>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4 text-muted small">
                        <i class="fas fa-volume-up me-2"></i> <span>Bạn không biết?</span>
                    </div>
                </div>
            </div>

            {{-- Tự luận --}}
            <div id="questionEssay" class="question-box d-none">
                <div class="bg-white text-dark border rounded-lg p-4 mb-4 shadow-sm mt-4">
                    <p class="text-uppercase fw-bold small mb-2">Tự luận</p>
                    <p class="fs-5 mb-3">Hãy viết định nghĩa của từ "inspire" bằng tiếng Anh.</p>
                    <textarea class="form-control mb-3" rows="4" placeholder="Nhập câu trả lời..."></textarea>
                    <button class="btn btn-primary">Tiếp</button>
                </div>
            </div>
        </div>

        <div class="text-end mt-4 mb-4">
            <button id="submitExamBtn" class="btn btn-primary px-4 py-2" style="border-radius: 9999px; font-weight: 600;">
                <i class="fas fa-paper-plane me-2"></i> Gửi bài kiểm tra
            </button>
        </div>
    </div>

    {{-- Modal tùy chỉnh bài kiểm tra --}}
    <div class="modal fade" id="checkModal" tabindex="-1" aria-labelledby="checkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color:#0B0D2B; border-radius:1rem; color:#fff; border:none;">
                <div class="modal-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h6 style="font-weight:600; font-size:0.875rem; color:#9CA3AF;">Đề dự đoán đặc biệt số 1</h6>
                            <h2 style="font-weight:800; font-size:1.75rem;">Thiết lập bài kiểm tra</h2>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="background-color:#4B6BFF; border:none; border-radius:9999px; padding:1rem 2rem; font-weight:600;">
                            Đóng
                        </button>
                    </div>

                    {{-- Số câu hỏi --}}
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <label style="font-weight:600;">Câu hỏi (tối đa 50)</label>
                        <input type="number" value="20" class="form-control ms-3"
                            style="max-width:150px; background-color:#14163C; border:none; border-radius:0.5rem; color:#fff;">
                    </div>

                    <hr style="border-color:#2F336D;">

                    {{-- Loại câu hỏi --}}
                    <div class="mb-4">
                        @foreach ([['label' => 'Đúng/Sai', 'id' => 'toggleTrueFalse'], ['label' => 'Trắc nghiệm', 'id' => 'toggleMCQ', 'checked' => true], ['label' => 'Tự luận', 'id' => 'toggleEssay']] as $q)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $q['label'] }}</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="{{ $q['id'] }}"
                                        {{ $q['checked'] ?? false ? 'checked' : '' }}>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Bắt đầu --}}
                    <div class="text-end">
                        <button type="button" class="btn" id="startExamBtn"
                            style="background-color:#4B6BFF; color:white; font-weight:600; font-size:1rem; padding:0.75rem 1.5rem; border-radius:9999px;">
                            Bắt đầu làm kiểm tra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal cảnh báo chưa hoàn thành -->
    <div class="modal fade" id="unansweredModal" tabindex="-1" aria-labelledby="unansweredModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white rounded-4">
                <div class="modal-body text-center py-4">
                    <h5 class="fw-bold mb-2" id="unansweredModalLabel">Có vẻ như bạn đã bỏ qua một số câu hỏi</h5>
                    <p class="mb-4">Bạn muốn xem lại các câu hỏi đã bỏ qua hay gửi bài kiểm tra ngay bây giờ?</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">
                            Xem lại các câu hỏi đã bỏ qua
                        </button>
                        <button type="button" id="confirmSubmit" class="btn btn-primary rounded-pill px-4">
                            Gửi bài kiểm tra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JS: Hiển thị các dạng câu hỏi --}}
    <script>
        // Khi toàn bộ DOM đã được load xong
        document.addEventListener("DOMContentLoaded", function() {

            // Tạo một đối tượng ánh xạ giữa ID của toggle và ID của khối câu hỏi tương ứng
            const toggles = {
                "toggleTrueFalse": "questionTrueFalse",
                "toggleMCQ": "questionMCQ",
                "toggleEssay": "questionEssay"
            };

            // Hàm cập nhật việc hiển thị các loại câu hỏi dựa trên toggle
            function updateVisibility() {
                Object.entries(toggles).forEach(([toggleId, boxId]) => {
                    const isChecked = document.getElementById(toggleId)
                    .checked; // Kiểm tra toggle có được bật không
                    document.getElementById(boxId).classList.toggle("d-none", !
                    isChecked); // Ẩn nếu toggle không bật
                });
            }

            updateVisibility(); // Gọi lần đầu khi trang được load

            // Gắn sự kiện khi người dùng thay đổi trạng thái các toggle
            Object.keys(toggles).forEach(toggleId => {
                document.getElementById(toggleId).addEventListener("change", updateVisibility);
            });

            // Khi nhấn nút "Bắt đầu làm kiểm tra"
            document.getElementById("startExamBtn").addEventListener("click", function() {
                updateVisibility(); // Cập nhật hiển thị lại cho chắc chắn
                const modal = bootstrap.Modal.getInstance(document.getElementById(
                'checkModal')); // Lấy instance modal
                modal.hide(); // Đóng modal bắt đầu kiểm tra
            });

            // Khi nhấn nút "Gửi bài kiểm tra"
            document.getElementById("submitExamBtn").addEventListener("click", function() {
                // Kiểm tra xem còn câu hỏi nào chưa được chọn đáp án không
                const unanswered = Array.from(document.querySelectorAll('.question-container')).some(q => {
                    return !q.querySelector(
                    'input[type=radio]:checked'); // Nếu không có radio nào được chọn
                });

                if (unanswered) {
                    // Hiển thị modal cảnh báo nếu còn câu chưa làm
                    const modal = new bootstrap.Modal(document.getElementById('unansweredModal'));
                    modal.show();
                } else {
                    // Nếu làm hết rồi thì gửi bài
                    submitExam();
                }
            });

            // Khi người dùng xác nhận vẫn muốn gửi bài dù còn câu bỏ sót
            document.getElementById("confirmSubmit").addEventListener("click", function() {
                submitExam(); // Gửi bài kiểm tra
            });

            // Hàm thực hiện gửi bài kiểm tra
            function submitExam() {
                alert("Bài kiểm tra đã được gửi!");
                // TODO: Thêm logic gửi bài thật sự tại đây
                // Ví dụ: document.getElementById("examForm").submit();
            }
        });
    </script>
@endsection
