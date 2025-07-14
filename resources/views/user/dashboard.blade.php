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

    {{-- Success Message --}}
    @if (Session::has('success'))
        <div class="alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3 shadow js-div-dissappear d-flex align-items-center text-start"
            style="max-width: 420px; min-width: 300px; z-index: 1050;">
            <i class="fas fa-check-circle me-2 fs-5 text-success"></i>
            <div class="flex-grow-1">
                {{ Session::get('success') }}
            </div>
            <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif

    {{-- Error Message --}}
    @if (Session::has('error'))
        <div class="alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3 shadow js-div-dissappear d-flex align-items-center text-start"
            style="max-width: 420px; min-width: 300px; z-index: 1050;">
            <i class="fas fa-exclamation-circle me-2 fs-5 text-danger"></i>
            <div class="flex-grow-1">
                {{ Session::get('error') }}
            </div>
            <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif

    <div class="container">
        <!-- Các khái niệm và định nghĩa -->
        <div class="mb-4">
            <h2 class="h4 mb-4">📘 Khái niệm / định nghĩa</h2>
            <div class="row g-4 position-relative" style="z-index: 1;">
                @forelse ($card_defines as $card_define)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d position-relative"
                            style="overflow: visible; z-index: 10;">

                            <!-- Dropdown menu -->
                            <div class="dropdown position-absolute top-0 end-0 p-3" style="z-index: 99999;">
                                <span data-bs-toggle="dropdown" role="button"
                                    style="cursor: pointer; font-size: 20px; line-height: 1;">
                                    ⋮
                                </span>
                                <ul class="dropdown-menu dropdown-menu-end show-on-top">
                                    {{-- Chia sẻ --}}
                                    <li class="dropdown-header text-muted">Chia sẻ</li>

                                    {{-- Sao chép liên kết --}}
                                    <li>
                                        <a class="dropdown-item" href="#"
                                            onclick="copyToClipboard('{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}')">
                                            📋 Sao chép liên kết
                                        </a>
                                    </li>

                                    {{-- Mã QR --}}
                                    <li>
                                        <a class="dropdown-item" href="#"
                                            onclick="showQrModal('{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}')">
                                            🌐 Tạo mã QR
                                        </a>
                                    </li>

                                    {{-- Facebook --}}
                                    <li>
                                        <a class="dropdown-item" href="#"
                                            onclick="shareFacebook('{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}')">
                                            📤 Chia sẻ Facebook
                                        </a>
                                    </li>

                                    {{-- Zalo --}}
                                    <li>
                                        <a class="dropdown-item" href="#"
                                            onclick="shareZalo('{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}')">
                                            💬 Chia sẻ Zalo
                                        </a>
                                    </li>

                                    @if (empty($card_define['first_card']->flashcardSet?->slug))
                                        {{-- Nếu chưa có FlashcardSet, hiển thị nút tạo --}}
                                        <li>
                                            <form method="POST" action="{{ route('flashcard.share.create') }}">
                                                @csrf
                                                @foreach (explode(',', $card_define['card_ids']) as $id)
                                                    <input type="hidden" name="card_ids[]" value="{{ $id }}">
                                                @endforeach
                                                <button type="submit" class="dropdown-item text-primary">
                                                    🌍 Chia sẻ công khai
                                                </button>
                                            </form>
                                        </li>
                                    @else
                                        {{-- Nếu đã có, hiển thị nút xem --}}
                                        <li>
                                            <a class="dropdown-item text-success"
                                                href="{{ route('flashcard.share', ['slug' => $card_define['first_card']->flashcardSet->slug]) }}">
                                                🔗 Xem chia sẻ công khai
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Nội dung thẻ -->
                            <a href="{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_define['card_ids'])]) }}"
                                class="text-decoration-none text-dark">
                                <div class="d-flex align-items-center">
                                    <img src="./assets/img/card_define.jpg" alt="Icon"
                                        class="rounded-circle bg-primary p-1" width="50" height="50"
                                        style="object-fit: cover;">
                                    <div class="ms-3">
                                        <h5 class="mb-1 fw-semibold text-truncate">
                                            {{ optional($card_define['first_card']->question->topic)->title ?? 'Không có chủ đề' }}
                                        </h5>
                                        <small class="text-muted d-block">
                                            Số thẻ: {{ count(explode(',', $card_define['card_ids'])) }} |
                                            Tác giả: {{ $card_define['first_card']->user->name ?? 'Ẩn danh' }}
                                        </small>
                                        <small class="text-muted">Ngày tạo:
                                            {{ $card_define['first_card']->created_at->format('Y-m-d') }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Chưa có thẻ nào được tạo.</p>
                @endforelse
            </div>
        </div>

        {{-- <!-- Câu hỏi tự luận -->
        <div class="mb-4">
            <h2 class="h4 mb-4">📝 Câu hỏi tự luận</h2>
            <div class="row g-4">
                @forelse ($card_essays as $card_essay)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('user.flashcard_define_essay', ['ids' => implode(',', (array) $card_essay['card_ids'])]) }}"
                            class="text-decoration-none text-dark">
                            <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                                <div class="d-flex align-items-center">
                                    <img src="./assets/img/card_essay.jpg" alt="Icon"
                                        class="rounded-circle bg-primary p-1" width="50" height="50"
                                        style="object-fit: cover;">
                                    <div class="ms-3">
                                        <h5 class="mb-1 fw-semibold text-truncate">
                                            {{ optional($card_essay['first_card']->question->topic)->title ?? 'Không có chủ đề' }}
                                        </h5>
                                        <small class="text-muted d-block">
                                            Số thẻ: {{ count(explode(',', $card_essay['card_ids'])) }} |
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
                    <p class="text-muted">Chưa có thẻ nào được tạo.</p>
                @endforelse
            </div>
        </div> --}}

        <!-- Bài kiểm tra -->
        <div class="mb-4">
            <h2 class="h4 mb-4">🧠 Bài kiểm tra</h2>

            <div class="row g-4">
                @forelse ($tests as $test)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="position-relative">
                            <!-- Nếu là giáo viên -->
                            @if (auth()->check() && auth()->user()->roles === 'teacher' && count($myClassrooms) > 0)
                                <div class="dropdown position-absolute top-0 end-0 p-3" style="z-index: 1;">
                                    <span data-bs-toggle="dropdown" role="button"
                                        style="cursor: pointer; font-size: 20px; line-height: 1;">
                                        ⋮
                                    </span>

                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item text-primary" href="javascript:void(0);"
                                                data-bs-toggle="modal" data-bs-target="#assignModal_{{ $test->id }}">
                                                📤 Giao bài kiểm tra
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @endif

                            {{-- Nội dung thẻ --}}
                            <a href="javascript:void(0);" class="text-decoration-none text-dark"
                                onclick="showConfirmModal('{{ $test->id }}',
                                     '{{ optional($test->questionNumbers->first()->topic)->title ?? 'Không có' }}',
                                     '{{ $test->time }}',
                                     '{{ $test->questionNumbers->first()->question_number ?? 'Không có' }}',
                                     '{{ $test->user->name ?? 'Ẩn danh' }}',
                                     '{{ $test->created_at->format('Y-m-d') }}')">
                                <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d" style="overflow: visible;">
                                    <div class="d-flex align-items-center">
                                        <img src="./assets/img/test.jpg" alt="Icon"
                                            class="rounded-circle bg-primary p-1" width="50" height="50"
                                            style="object-fit: cover;">
                                        <div class="ms-3">
                                            <h5 class="mb-1 fw-semibold text-truncate">
                                                {{ optional($test->questionNumbers->first()->topic)->title ?? 'Không có' }}
                                            </h5>
                                            <small class="text-muted d-block">⏱ Thời gian: {{ $test->time }}
                                                phút</small>
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
                    </div>
                @empty
                    <p class="text-muted">Chưa có bài kiểm tra nào được tạo.</p>
                @endforelse

                @foreach ($tests as $test)
                    @if (auth()->check() && auth()->user()->roles === 'teacher' && count($myClassrooms) > 0)
                        <div class="modal fade" id="assignModal_{{ $test->id }}" tabindex="-1"
                            aria-labelledby="assignModalLabel_{{ $test->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 rounded-3">
                                    <div class="modal-header bg-secondary text-white">
                                        <h5 class="modal-title" id="assignModalLabel_{{ $test->id }}">📚 Chia sẻ bài
                                            kiểm tra</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                            aria-label="Đóng"></button>
                                    </div>
                                    <form method="POST" action="{{ route('teacher.assignTest') }}">
                                        @csrf
                                        <input type="hidden" name="test_id" value="{{ $test->id }}">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Chọn lớp học:</label>
                                                <div class="mb-2" style="max-height: 150px; overflow-y: auto;">
                                                    @foreach ($myClassrooms as $classroom)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="classroom_ids[]" value="{{ $classroom->id }}"
                                                                id="classroom_modal_{{ $test->id }}_{{ $classroom->id }}">
                                                            <label class="form-check-label small"
                                                                for="classroom_modal_{{ $test->id }}_{{ $classroom->id }}">
                                                                {{ $classroom->name }} ({{ $classroom->code }})
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="deadline_{{ $test->id }}" class="form-label">📅 Hạn
                                                    nộp:</label>
                                                <input type="datetime-local" name="deadline"
                                                    id="deadline_{{ $test->id }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-primary">✅ Chia sẻ</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
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
                    <p class="mb-2"><strong>📖 Số câu hỏi:</strong> <span id="testQuestions"
                            class="fw-semibold"></span>
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
        function shareFacebook(link) {
            // Tạo URL chia sẻ của Facebook, thêm tham số đường link
            const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`;

            // Mở popup chia sẻ với kích thước cố định
            window.open(url, '_blank', 'width=600,height=500');
        }

        // 💬 Chia sẻ Zalo: mở cửa sổ chia sẻ Zalo qua liên kết zalo.me
        function shareZalo(link) {
            // Zalo không hỗ trợ JavaScript chia sẻ trực tiếp, nên chỉ chuyển hướng sang trang zalo.me/share
            const zaloUrl = `https://zalo.me/share?url=${encodeURIComponent(link)}`;

            // Mở cửa sổ mới để người dùng chia sẻ đường link
            window.open(zaloUrl, '_blank');
        }
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
