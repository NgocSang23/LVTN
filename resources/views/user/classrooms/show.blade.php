@extends('user.master')

@section('title', 'Chi tiết lớp học')

@section('content')
    <div class="container py-4">
        {{-- Thông tin lớp học --}}
        <div class="card shadow-sm mb-4 border-0" style="border-radius: 14px;">
            <div class="card-body d-flex justify-content-between align-items-start flex-wrap position-relative">
                <div>
                    <h2 class="fw-bold text-primary mb-1">{{ $classroom->name }}</h2>
                    <p class="mb-1">
                        Mã lớp:
                        <span class="badge bg-secondary text-white fw-bold px-2 py-1">
                            {{ $classroom->code }}
                        </span>
                    </p>
                    <p class="text-muted mb-0">{{ $classroom->description ?: 'Không có mô tả' }}</p>
                </div>
                <div class="text-end mt-2 mt-md-0">
                    <span class="badge bg-info text-dark rounded-pill fs-6 px-3 py-2 shadow-sm">
                        {{ $classroom->users->count() }} học viên
                    </span>
                </div>
            </div>
        </div>

        {{-- ✅ Nút tạo bài kiểm tra (chỉ hiển thị nếu là giáo viên) --}}
        @can('teacher')
            <div class="text-end mb-4">
                <a href="{{ route('flashcard_multiple_choice.create', ['classroom_id' => $classroom->id]) }}"
                    class="btn btn-primary rounded-3">
                    <i class="fa-solid fa-file-circle-plus me-1"></i> Tạo bài kiểm tra mới
                </a>
            </div>
        @endcan

        {{-- Nút rời lớp cho học viên --}}
        @can('student')
            <div class="text-end mb-4">
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#leaveClassModal">
                    <i class="fa-solid fa-door-open me-1"></i> Rời lớp học
                </button>
            </div>
        @endcan

        {{-- Danh sách học viên cho giáo viên --}}
        @can('teacher')
            <h4 class="fw-semibold mt-4 mb-3">Danh sách học viên</h4>

            @if ($classroom->members->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle text-center shadow-sm">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>👤 Họ tên</th>
                                <th>📧 Email</th>
                                <th>📅 Ngày tham gia</th>
                                <th>⚙️ Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classroom->members as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ optional($user->pivot->created_at)->format('d/m/Y') ?? 'Không rõ' }}</td>
                                    <td>
                                        <!-- Nút xoá -->
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#removeStudentModal"
                                            onclick="prepareRemoveStudent({{ $classroom->id }}, {{ $user->id }})">
                                            <i class="fa-solid fa-user-xmark me-1"></i> Xoá
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Chưa có học viên nào tham gia lớp học này.</div>
            @endif
        @endcan

        {{-- Danh sách bộ flashcard được chia sẻ --}}
        <h4 class="fw-semibold mt-4 mb-3">📚 Bộ flashcard được chia sẻ</h4>
        @php
            $sharedSets = $classroom->sharedFlashcards->unique('flashcard_set_id');
        @endphp
        @if ($sharedSets->count())

            <div class="row">
                @foreach ($sharedSets as $item)
                    @php
                        $set = $item->flashcardSet;
                    @endphp
                    @if ($set && !empty($set->question_ids))
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <h5 class="fw-bold text-primary">{{ $set->title }}</h5>
                                        <p class="text-muted mb-1">{{ $set->description ?? 'Không có mô tả' }}</p>
                                    </div>
                                    <div class="mt-3 text-end">
                                        <a href="{{ route('user.flashcard_define_essay', ['ids' => $set->question_ids]) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-eye me-1"></i> Xem bộ thẻ
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="alert alert-info">Chưa có bộ flashcard nào được chia sẻ cho lớp học này.</div>
        @endif

        {{-- ✅ Danh sách bài kiểm tra đã chia sẻ cho lớp --}}
        <h4 class="fw-semibold mt-4 mb-3">📝 Bài kiểm tra đã chia sẻ</h4>

        @if ($classroom->tests->count())
            <div class="row">
                @foreach ($classroom->tests as $test)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="fw-bold text-dark">📝 {{ $test->content }}</h5>
                                    <p class="text-muted mb-1">Thời gian:
                                        {{ \Carbon\Carbon::parse($test->time)->format('i') }} phút</p>
                                    <p class="text-muted small mb-0">Tác giả: {{ $test->user->name ?? 'Không rõ' }}</p>
                                </div>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#confirmTestModal"
                                        onclick="showTestModal(
                                            '{{ $test->id }}',
                                            '{{ $test->content }}',
                                            '{{ \Carbon\Carbon::parse($test->time)->format('i') }}',
                                            '{{ $test->user->name ?? 'Không rõ' }}',
                                            '{{ $test->created_at->format('d/m/Y') }}',
                                            '{{ $test->questionNumbers->first()->question_number ?? 'Không có' }}',
                                            '{{ route('flashcard_multiple_choice.show', $test->id) }}'
                                        )">
                                        <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                                    </button>

                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">Chưa có bài kiểm tra nào được chia sẻ cho lớp học này.</div>
        @endif
    </div>

    <!-- Modal Xác Nhận Làm Bài Kiểm Tra -->
    <div class="modal fade" id="confirmTestModal" tabindex="-1" aria-labelledby="confirmTestLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-3">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="confirmTestLabel">
                        <i class="bi bi-patch-question-fill me-2"></i> Xác nhận làm bài kiểm tra
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>📌 Nội dung:</strong> <span id="testTopic" class="fw-semibold"></span></p>
                    <p><strong>⏳ Thời gian:</strong> <span id="testTime" class="fw-semibold"></span> phút</p>
                    <p><strong>📖 Số câu hỏi:</strong> <span id="testQuestions" class="fw-semibold"></span> câu</p>
                    <p><strong>👤 Tác giả:</strong> <span id="testAuthor" class="fw-semibold"></span></p>
                    <p><strong>📅 Ngày tạo:</strong> <span id="testDate" class="fw-semibold"></span></p>
                    <div class="alert alert-warning d-flex align-items-center p-2 mt-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span>Bạn có chắc chắn muốn bắt đầu bài kiểm tra?</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Huỷ
                    </button>
                    <a id="startTestButton" href="#" class="btn btn-primary">
                        <i class="bi bi-play-circle"></i> Bắt đầu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Xác nhận xoá học viên -->
    <div class="modal fade" id="removeStudentModal" tabindex="-1" aria-labelledby="removeStudentLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="removeStudentLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Xoá học viên
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn <strong>xóa học viên này</strong> khỏi lớp học?
                </div>
                <div class="modal-footer">
                    <form id="removeStudentForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                        <button type="submit" class="btn btn-danger">Xoá</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal: Rời khỏi lớp học -->
    <div class="modal fade" id="leaveClassModal" tabindex="-1" aria-labelledby="leaveClassLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="leaveClassLabel"><i class="fa-solid fa-door-open me-2"></i> Rời khỏi lớp
                        học
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn <strong>rời khỏi lớp học này</strong> không?
                </div>
                <div class="modal-footer">
                    <form id="leaveClassForm" method="POST" action="{{ route('classrooms.leave', $classroom->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                        <button type="submit" class="btn btn-danger text-white">Rời lớp</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function prepareRemoveStudent(classroomId, userId) {
            const form = document.getElementById('removeStudentForm');
            form.action = `/user/classrooms/${classroomId}/remove-student/${userId}`;
        }

        function showTestModal(id, content, time, author, date, questionCount, link) {
            document.getElementById('testTopic').textContent = content;
            document.getElementById('testTime').textContent = time;
            document.getElementById('testAuthor').textContent = author;
            document.getElementById('testDate').textContent = date;
            document.getElementById('testQuestions').textContent = questionCount;
            document.getElementById('startTestButton').href = link;
        }
    </script>
@endsection
