@extends('user.master')

@section('title', 'Tìm kiếm')

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
    </style>

    <div class="container">
        <!-- Khái niệm / định nghĩa -->
        <div class="mb-4">
            <h2 class="h4 mb-4">📘 Khái niệm / định nghĩa</h2>
            <div class="row g-4">
                @forelse ($card_defines as $card_define)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('user.flashcard_define_essay', ['ids' => implode(',', $card_define['card_ids'])]) }}"
                            class="text-decoration-none text-dark">
                            <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                                <div class="d-flex align-items-center">
                                    <img src="/assets/img/card_define.jpg" alt="Icon"
                                        class="rounded-circle bg-primary p-1" width="50" height="50"
                                        style="object-fit: cover;">
                                    <div class="ms-3">
                                        <h5 class="mb-1 fw-semibold text-truncate">
                                            {{ optional($card_define['first_card']->question->topic)->title ?? 'Không có chủ đề' }}
                                        </h5>
                                        <small class="text-muted d-block">
                                            Số thẻ: {{ count($card_define['card_ids']) }} |
                                            Tác giả: {{ $card_define['first_card']->user->name ?? 'Ẩn danh' }}
                                        </small>
                                        <small class="text-muted">Ngày tạo:
                                            {{ $card_define['first_card']->created_at->format('Y-m-d') }}</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Không tìm thấy thẻ nào.</p>
                @endforelse
            </div>
        </div>

        <!-- Câu hỏi tự luận -->
        <div class="mb-4">
            <h2 class="h4 mb-4">📝 Câu hỏi tự luận</h2>
            <div class="row g-4">
                @forelse ($card_essays as $card_essay)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('user.flashcard_define_essay', ['ids' => implode(',', $card_essay['card_ids'])]) }}"
                            class="text-decoration-none text-dark">
                            <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                                <div class="d-flex align-items-center">
                                    <img src="/assets/img/card_essay.jpg" alt="Icon"
                                        class="rounded-circle bg-primary p-1" width="50" height="50"
                                        style="object-fit: cover;">
                                    <div class="ms-3">
                                        <h5 class="mb-1 fw-semibold text-truncate">
                                            {{ optional($card_essay['first_card']->question->topic)->title ?? 'Không có chủ đề' }}
                                        </h5>
                                        <small class="text-muted d-block">
                                            Số thẻ: {{ count($card_essay['card_ids']) }} |
                                            Tác giả: {{ $card_essay['first_card']->user->name ?? 'Ẩn danh' }}
                                        </small>
                                        <small class="text-muted">Ngày tạo:
                                            {{ $card_essay['first_card']->created_at->format('Y-m-d') }}</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Không tìm thấy câu hỏi tự luận nào.</p>
                @endforelse
            </div>
        </div>

        <!-- Bài kiểm tra -->
        <div class="mb-4">
            <h2 class="h4 mb-4">🧠 Bài kiểm tra</h2>
            <div class="row g-4">
                @forelse ($tests as $test)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="javascript:void(0);" class="text-decoration-none text-dark"
                            onclick="showConfirmModal('{{ $test->id }}',
                                                 '{{ optional($test->questionNumbers->first()->topic)->title ?? 'Không có' }}',
                                                 '{{ $test->time }}',
                                                 '{{ $test->questionNumbers->first()->question_number ?? 'Không có' }}',
                                                 '{{ $test->user->name ?? 'Ẩn danh' }}',
                                                 '{{ $test->created_at->format('Y-m-d') }}')">
                            <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                                <div class="d-flex align-items-center">
                                    <img src="/assets/img/test.jpg" alt="Icon" class="rounded-circle bg-primary p-1"
                                        width="50" height="50" style="object-fit: cover;">
                                    <div class="ms-3">
                                        <h5 class="mb-1 fw-semibold text-truncate">
                                            {{ optional($test->questionNumbers->first()->topic)->title ?? 'Không có' }}
                                        </h5>
                                        <small class="text-muted d-block">⏱ Thời gian: {{ $test->time }} phút</small>
                                        <small class="text-muted d-block">❓ Số câu:
                                            {{ $test->questionNumbers->first()->question_number ?? 'Không có' }}</small>
                                        <small class="text-muted d-block">👤 Tác giả:
                                            {{ $test->user->name ?? 'Ẩn danh' }}</small>
                                        <small class="text-muted">📅 Ngày tạo:
                                            {{ $test->created_at->format('Y-m-d') }}</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Không tìm thấy bài kiểm tra nào.</p>
                @endforelse
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
    </script>

    {{-- Tìm kiếm --}}
    {{-- <script>
        $(document).ready(function() {
            let timeout = null; // Biến để lưu thời gian timeout của hàm setTimeout

            // Lắng nghe sự kiện 'keyup' (khi người dùng gõ phím) trên ô input tìm kiếm
            $('input[name="search"]').on('keyup', function() {
                const keyword = $(this).val(); // Lấy giá trị người dùng nhập vào ô tìm kiếm
                clearTimeout(timeout); // Hủy bỏ timeout cũ để tránh gọi AJAX quá nhiều lần

                // Nếu độ dài từ khóa nhập vào nhỏ hơn 2 ký tự, ẩn gợi ý tìm kiếm
                if (keyword.length < 2) {
                    $('#search-suggestions').hide();
                    return;
                }

                // Đặt timeout mới để chỉ gọi AJAX khi người dùng ngừng gõ trong 300ms
                timeout = setTimeout(function() {
                    // Gửi yêu cầu AJAX tới server
                    $.ajax({
                        url: '{{ route('user.search') }}', // Địa chỉ URL để gửi yêu cầu
                        type: 'GET', // Phương thức gửi yêu cầu là GET
                        data: {
                            search: keyword // Truyền từ khóa tìm kiếm vào dữ liệu yêu cầu
                        },
                        success: function(data) { // Xử lý kết quả trả về khi AJAX thành công
                            let html = ''; // Biến để lưu HTML của kết quả gợi ý

                            // Nếu có dữ liệu trả về
                            if (data.length > 0) {
                                // Duyệt qua từng phần tử trong dữ liệu và tạo HTML
                                data.forEach(item => {
                                    html += `
                                        <a href="${item.url}" class="d-block text-dark text-decoration-none px-3 py-2 border-bottom">
                                            <div class="fw-semibold">${item.title}</div>
                                            <small class="text-muted">${item.type} | Tác giả: ${item.author}</small>
                                        </a>
                                    `;
                                });
                            } else {
                                // Nếu không có kết quả tìm kiếm, hiển thị thông báo không tìm thấy kết quả
                                html = '<div class="px-3 py-2 text-muted">Không tìm thấy kết quả</div>';
                            }

                            // Cập nhật nội dung HTML của phần tử '#search-suggestions' và hiển thị nó
                            $('#search-suggestions').html(html).show();
                        },
                        error: function() { // Xử lý khi có lỗi xảy ra trong quá trình gửi AJAX
                            $('#search-suggestions').html(
                                '<div class="px-3 py-2 text-danger">Lỗi khi tìm kiếm</div>'
                            ).show(); // Hiển thị thông báo lỗi
                        }
                    });
                }, 300); // Gọi AJAX sau khi người dùng ngừng gõ 300ms
            });

            // Xử lý sự kiện click ra ngoài để ẩn gợi ý tìm kiếm
            $(document).click(function(e) {
                if (!$(e.target).closest('#search-suggestions, input[name="search"]').length) {
                    $('#search-suggestions').hide(); // Ẩn gợi ý khi click ra ngoài vùng tìm kiếm
                }
            });
        });
    </script> --}}
@endsection
