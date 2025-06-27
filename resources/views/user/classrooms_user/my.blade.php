@extends('user.master')

@section('title', 'Lớp đã tham gia')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0 text-primary">Lớp học đã tham gia</h2>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        @endif

        @if ($classrooms->count())
            <div class="row g-4">
                @foreach ($classrooms as $classroom)
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">{{ $classroom->name }}</h5>
                                <p class="mb-1 text-muted">Mã lớp: <code>{{ $classroom->code }}</code></p>
                                <p class="card-text">{{ $classroom->description ?: 'Không có mô tả' }}</p>
                                <a href="{{ route('classrooms.show', $classroom->id) }}"
                                    class="btn btn-outline-primary btn-sm mt-2 rounded-2">Xem lớp</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info mt-4">Bạn chưa tham gia lớp học nào.</div>
        @endif
    </div>
@endsection
