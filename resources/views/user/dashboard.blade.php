@extends('user.master')

@section('title', 'Studying For Exams')

@section('content')
    <style>
        .card-3d {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            transform-style: preserve-3d;
            will-change: transform;
        }

        .card-3d:hover {
            transform: perspective(1000px) rotateX(5deg) rotateY(5deg) scale(1.02);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }

        .dropdown-menu {
            z-index: 99999 !important;
        }

        /* Dropdown luôn nổi trên */
        .show-on-top {
            z-index: 999999 !important;
            position: absolute !important;
        }
    </style>

    <div class="container">
        <div id="searchResults">
            {{-- Nội dung mặc định ban đầu, có thể là tất cả flashcard/test hoặc trống --}}
            @include('user.partials.search_result', [
                'card_defines' => $card_defines ?? [],
                'tests' => $tests ?? [],
            ])
        </div>

    </div>

    {{-- Modal sao chép liên kết --}}
    <div class="modal fade" id="copySuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <h5 class="mb-2 text-success"><i class="fas fa-check-circle"></i> Đã sao chép liên kết</h5>
                <p class="text-muted mb-0">Liên kết đã được sao chép vào clipboard.</p>
            </div>
        </div>
    </div>

    {{-- Modal mã QR --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <h5 class="mb-3">🌐 Mã QR chia sẻ</h5>
                <div id="qrcode-container" class="d-flex justify-content-center"></div>
                <button class="btn btn-secondary mt-3" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>

    <!-- Modal Xác Nhận Làm Bài Kiểm Tra -->
    <div class="modal fade" id="confirmTestModal" tabindex="-1" aria-labelledby="confirmTestLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-3">
                <!-- Header -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="confirmTestLabel">
                        <i class="bi bi-patch-question-fill me-2"></i> Xác nhận làm bài kiểm tra
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <p class="mb-2"><strong>📌 Chủ đề:</strong> <span id="testTopic" class="fw-semibold"></span></p>
                    <p class="mb-2"><strong>⏳ Thời gian:</strong> <span id="testTime" class="fw-semibold"></span> phút
                    </p>
                    <p class="mb-2"><strong>📖 Số câu hỏi:</strong> <span id="testQuestions" class="fw-semibold"></span>
                        câu</p>
                    <p class="mb-2"><strong>👤 Tác giả:</strong> <span id="testAuthor" class="fw-semibold"></span></p>
                    <p class="mb-3"><strong>📅 Ngày tạo:</strong> <span id="testDate" class="fw-semibold"></span></p>
                    <div class="alert alert-warning d-flex align-items-center p-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span>Bạn có chắc chắn muốn bắt đầu bài kiểm tra?</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <a id="startTestButton" href="#" class="btn btn-primary">
                        <i class="bi bi-play-circle"></i> Bắt đầu
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Truyền dữ liệu và mở modal --}}
    <script>
        function showConfirmModal(testId, topic, time, questions, author, date) {
            document.getElementById('testTopic').innerText = topic || 'Không có';
            document.getElementById('testTime').innerText = time;
            document.getElementById('testQuestions').innerText = questions;
            document.getElementById('testAuthor').innerText = author || 'Ẩn danh';
            document.getElementById('testDate').innerText = date;

            $('#startTestButton').attr('href', '{{ url('user/flashcard_multiple_choice') }}/' + testId);

            var myModal = new bootstrap.Modal(document.getElementById('confirmTestModal'));
            myModal.show();
        }

        // ✅ Hàm sao chép liên kết vào clipboard và hiển thị modal thông báo
        function copyToClipboard(link) {
            // Sử dụng Clipboard API để ghi văn bản vào clipboard
            navigator.clipboard.writeText(link).then(() => {
                // Sau khi sao chép thành công, hiển thị modal thông báo "Đã sao chép"
                const copyModal = new bootstrap.Modal(document.getElementById('copySuccessModal'));
                copyModal.show();

                // Tự động ẩn modal sau 2.5 giây
                setTimeout(() => copyModal.hide(), 2500);
            }).catch(err => {
                // Nếu có lỗi trong quá trình sao chép, ghi log ra console
                console.error("❌ Không thể sao chép liên kết: ", err);
            });
        }

        // 🌐 Hiển thị mã QR trong modal với đường link cần chia sẻ
        function showQrModal(link) {
            // Lấy thẻ chứa QR code trong modal
            const qrContainer = document.getElementById("qrcode-container");

            // Xoá mã QR cũ nếu đã có (để tránh chồng lặp)
            qrContainer.innerHTML = "";

            // Tạo mã QR mới với liên kết truyền vào
            new QRCode(qrContainer, {
                text: link, // Đường link sẽ được mã hoá thành mã QR
                width: 200, // Chiều rộng mã QR
                height: 200 // Chiều cao mã QR
            });

            // Hiển thị modal chứa mã QR
            const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
            qrModal.show();
        }

        // 📤 Chia sẻ Facebook: mở cửa sổ popup để chia sẻ đường link
        // function shareFacebook(link) {
        //     // Tạo URL chia sẻ của Facebook, thêm tham số đường link
        //     const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`;

        //     // Mở popup chia sẻ với kích thước cố định
        //     window.open(url, '_blank', 'width=600,height=500');
        // }

        // // 💬 Chia sẻ Zalo: mở cửa sổ chia sẻ Zalo qua liên kết zalo.me
        // function shareZalo(link) {
        //     // Zalo không hỗ trợ JavaScript chia sẻ trực tiếp, nên chỉ chuyển hướng sang trang zalo.me/share
        //     const zaloUrl = `https://zalo.me/share?url=${encodeURIComponent(link)}`;

        //     // Mở cửa sổ mới để người dùng chia sẻ đường link
        //     window.open(zaloUrl, '_blank');
        // }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('instantSearchForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // Chặn submit mặc định
                    submitInstantSearch();
                });
            }

            // Chặn luôn phím Enter trong input
            const input = document.getElementById('instantSearchInput');
            if (input) {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        submitInstantSearch();
                    }
                });
            }
        });
    </script>

    {{-- Tìm kiếm --}}
    {{-- <script>
        $(document).ready(function() {
            let timeout = null;

            $('input[name="search"]').on('keyup', function() {
                const keyword = $(this).val();
                clearTimeout(timeout);

                if (keyword.length < 2) {
                    $('#search-suggestions').hide();
                    return;
                }

                timeout = setTimeout(function() {
                    $.ajax({
                        url: '{{ route('user.search') }}',
                        type: 'GET',
                        data: {
                            search: keyword
                        },
                        success: function(data) {
                            let html = '';

                            if (data.length > 0) {
                                data.forEach(item => {
                                    html += `
                                    <a href="${item.url}" class="d-block text-dark text-decoration-none px-3 py-2 border-bottom">
                                        <div class="fw-semibold">${item.title}</div>
                                        <small class="text-muted">${item.type} | Tác giả: ${item.author}</small>
                                    </a>
                                `;
                                });
                            } else {
                                html =
                                    '<div class="px-3 py-2 text-muted">Không tìm thấy kết quả</div>';
                            }

                            $('#search-suggestions').html(html).show();
                        },
                        error: function() {
                            $('#search-suggestions').html(
                                '<div class="px-3 py-2 text-danger">Lỗi khi tìm kiếm</div>'
                            ).show();
                        }
                    });
                }, 300);
            });

            // Ẩn khi click ra ngoài
            $(document).click(function(e) {
                if (!$(e.target).closest('#search-suggestions, input[name="search"]').length) {
                    $('#search-suggestions').hide();
                }
            });
        });
    </script> --}}

@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/message.css') }}" />
@endsection

@section('js')
    @if (Session::has('success'))
        <script src="{{ asset('assets/js/message.js') }}"></script>
    @endif
@endsection
