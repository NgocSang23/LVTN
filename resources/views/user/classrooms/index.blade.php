@extends('user.master')

@section('title', 'Danh sách lớp học')

@section('content')
    <style>
        .badge.bg-info {
            background-color: #e0f3ff !important;
            color: #0275d8 !important;
            font-weight: 600;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .top-header .search-container {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .top-header .search-container input {
            padding-left: 2.5rem;
            /* Tăng khoảng trống để icon không bị đè */
            border-radius: 20px;
        }

        .top-header .search-container .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            pointer-events: none;
        }

        .top-header .create-btn {
            white-space: nowrap;
            /* Ngăn nút xuống dòng */
        }

        @media (max-width: 768px) {
            .top-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .top-header .search-container {
                width: 100%;
                max-width: none;
            }
        }
    </style>

    <div class="container py-4">
        <div class="top-header mb-4">
            <h2 class="fw-bold text-primary">Lớp học của bạn</h2>

            <div class="search-container">
                <form method="GET" action="{{ route('classrooms.index') }}" class="w-100">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Tìm tên lớp học..." autocomplete="off">
                    <i class="fas fa-search search-icon"></i>
                </form>
            </div>

            <a href="{{ route('classrooms.create') }}" class="btn btn-primary px-4 rounded-pill create-btn">
                <i class="fas fa-plus me-2"></i> Tạo lớp mới
            </a>
        </div>

        @if ($classrooms->count())
            <div class="row g-4">
                @foreach ($classrooms as $classroom)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 position-relative" style="border-radius: 12px;">
                            <div class="card-body">
                                <!-- Góc phải: badge + sửa/xoá -->
                                <div
                                    class="position-absolute top-0 end-0 m-2 text-end d-flex flex-column align-items-end gap-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info rounded-pill px-3 py-1 mb-0">
                                            {{ $classroom->users_count }} học viên
                                        </span>

                                        <button class="btn btn-sm btn-outline-secondary edit-class-btn"
                                            data-id="{{ $classroom->id }}" data-name="{{ $classroom->name }}"
                                            data-description="{{ $classroom->description }}" title="Sửa lớp"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="btn btn-sm btn-outline-danger" title="Xoá lớp" data-bs-toggle="modal"
                                            data-bs-target="#deleteClassModal" data-id="{{ $classroom->id }}"
                                            data-name="{{ $classroom->name }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <h5 class="card-title fw-bold">{{ $classroom->name }}</h5>
                                <p class="text-muted mb-1">Mã lớp: <code>{{ $classroom->code }}</code></p>
                                <p class="card-text small">{{ $classroom->description ?: 'Không có mô tả' }}</p>

                                <a href="{{ route('classrooms.show', $classroom->id) }}"
                                    class="btn btn-outline-primary btn-sm mt-2 rounded-2">Xem lớp</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info text-center mt-4">
                Bạn chưa tạo lớp học nào.
            </div>
        @endif
    </div>

    <!-- Modal: Sửa lớp học -->
    <div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #00c6ff, #0072ff); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title" id="editClassModalLabel" style="font-weight: 600;">✏️ Chỉnh sửa lớp học</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <form method="POST" id="editClassForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" style="font-size: 1rem; color: #333;">
                        <input type="hidden" name="class_id" id="editClassId">

                        <div class="mb-3">
                            <label for="editClassName" class="form-label">Tên lớp</label>
                            <input type="text" class="form-control" name="name" id="editClassName" required
                                placeholder="Nhập tên lớp...">
                        </div>

                        <div class="mb-3">
                            <label for="editClassDescription" class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" id="editClassDescription" rows="4"
                                placeholder="Nhập mô tả lớp học..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary" style="padding: 6px 20px; border-radius: 6px;">
                            Lưu thay đổi
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="padding: 6px 20px; border-radius: 6px;">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Xác nhận xoá lớp -->
    <div class="modal fade" id="deleteClassModal" tabindex="-1" aria-labelledby="deleteClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #ff5f6d, #ffc371); color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title" id="deleteClassModalLabel" style="font-weight: 600;">⚠️ Xác nhận xoá lớp học
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
                </div>
                <form method="POST" id="deleteClassForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body" style="font-size: 1rem; color: #333;">
                        <p>Bạn có chắc chắn muốn xoá lớp học <strong id="deleteClassName"></strong> không?</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="submit" class="btn btn-danger" style="padding: 6px 20px; border-radius: 6px;">
                            Xoá
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="padding: 6px 20px; border-radius: 6px;">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mở modal sửa lớp
            document.querySelectorAll('.edit-class-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.dataset.id;
                    const name = button.dataset.name;
                    const description = button.dataset.description;

                    document.getElementById('editClassId').value = id;
                    document.getElementById('editClassName').value = name;
                    document.getElementById('editClassDescription').value = description;

                    document.getElementById('editClassForm').action =
                        `/user/classrooms/update/${id}`;
                    const modal = new bootstrap.Modal(document.getElementById('editClassModal'));
                    modal.show();
                });
            });

            // Mở modal xoá lớp
            document.querySelectorAll('[data-bs-target="#deleteClassModal"]').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.dataset.id;
                    const name = button.dataset.name;

                    document.getElementById('deleteClassName').textContent = name;
                    document.getElementById('deleteClassForm').action = `/user/classrooms/${id}`;
                });
            });
        });
    </script>
@endsection
