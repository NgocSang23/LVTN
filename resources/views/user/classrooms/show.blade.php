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
    </div>

    <!-- Modal: Xác nhận xoá học viên -->
    <div class="modal fade" id="removeStudentModal" tabindex="-1" aria-labelledby="removeStudentLabel" aria-hidden="true">
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
                    <h5 class="modal-title" id="leaveClassLabel"><i class="fa-solid fa-door-open me-2"></i> Rời khỏi lớp học
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
    </script>
@endsection
