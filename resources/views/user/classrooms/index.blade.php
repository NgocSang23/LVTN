@extends('user.master')

@section('title', 'Danh sách lớp học')

@section('content')
    <style>
        .badge.bg-info {
            background-color: #e0f3ff !important;
            color: #0275d8 !important;
            font-weight: 600;
        }
    </style>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0 text-primary">Lớp học của bạn</h2>
            <a href="{{ route('classrooms.create') }}" class="btn btn-primary rounded-3 px-4">
                + Tạo lớp mới
            </a>
        </div>

        @if ($classrooms->count())
            <div class="row g-4">
                @foreach ($classrooms as $classroom)
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100 position-relative" style="border-radius: 12px;">
                            <div class="card-body">
                                <!-- Góc phải hiển thị số lượng học viên -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-info text-dark rounded-pill px-3 py-1">
                                        {{ $classroom->users_count }} học viên
                                    </span>
                                </div>

                                <h5 class="card-title fw-bold">{{ $classroom->name }}</h5>
                                <p class="mb-1 text-muted">Mã lớp: <code>{{ $classroom->code }}</code></p>
                                <p class="card-text">{{ $classroom->description ?: 'Không có mô tả' }}</p>
                                <a href="{{ route('classrooms.show', $classroom->id) }}" class="btn btn-outline-primary btn-sm mt-2 rounded-2">Xem lớp</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info mt-4">Bạn chưa tạo lớp học nào.</div>
        @endif
    </div>
@endsection
