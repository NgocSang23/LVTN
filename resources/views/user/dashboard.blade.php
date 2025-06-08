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
    </style>

    @if (Session::has('success'))
        <div class="alert alert-success fixed-top text-center p-3 shadow-lg js-div-dissappear"
            style="width: 100%; max-width: 400px; margin: 10px auto; z-index: 1050;">
            <i class="fas fa-check-circle me-2"></i> {{ Session::get('success') }}
        </div>
    @endif
    <div class="container">
        <!-- Các khái niệm và định nghĩa -->
        <div class="mb-4">
            <h2 class="h4 mb-4">📘 Khái niệm / định nghĩa</h2>
            <div class="row g-4">
                @forelse ($card_defines as $card_define)
                    <div class="col-12 col-sm-6 col-lg-4 position-relative">
                        <div class="card h-100 p-3 shadow-sm border-0 rounded-4 card-3d">
                            <div class="dropdown position-absolute top-0 end-0 p-3" style="z-index: 9999;"> <span
                                    data-bs-toggle="dropdown" role="button"
                                    style="cursor: pointer; font-size: 20px; line-height: 1;">
                                    ⋮
                                </span>

                                <ul class="dropdown-menu dropdown-menu-start">
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('define.edit', ['id' => $card_define['first_card']->id]) }}">✏️
                                            Chỉnh sửa</a>
                                    </li>
                                    <li>
                                        <form id="deleteForm" method="POST" action="#">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="dropdown-item text-danger"
                                                onclick="confirmDelete('{{ route('define.destroy', ['id' => $card_define['first_card']->id]) }}')">
                                                🗑️ Xoá toàn bộ thẻ
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>

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

        <!-- Câu hỏi tự luận -->
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
                                    <img src="./assets/img/test.jpg" alt="Icon" class="rounded-circle bg-primary p-1"
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
                    <p class="text-muted">Chưa có bài kiểm tra nào được tạo.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Modal xóa --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #ff5f6d, #ffc371); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title" style="font-weight: 600;">⚠️ Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <div class="modal-body" style="font-size: 1rem; color: #333;">
                    <p>Bạn có chắc chắn muốn xóa câu hỏi này không?</p>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="padding: 6px 20px; border-radius: 6px;">Hủy</button>
                </div>
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
    </script>

    <script>
        function confirmDelete(url) {
            // Cập nhật action của form trong modal
            const form = document.getElementById('deleteForm');
            form.action = url;

            // Hiện modal xác nhận xóa
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
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
