@extends('user.master')

@section('title', 'Lớp đã tham gia')

@section('content')
    <style>
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
    </style>

    <div class="container py-4">
        <div class="top-header mb-4">
            <h2 class="fw-bold text-primary">Lớp học đã tham gia</h2>

            <div class="search-container">
                <form method="GET" action="{{ route('classrooms.my') }}" class="w-100">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Tìm tên lớp học..." autocomplete="off">
                    <i class="fas fa-search search-icon"></i>
                </form>
            </div>
        </div>

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
            <div class="alert alert-info mt-4 text-center">Bạn chưa tham gia lớp học nào.</div>
        @endif
    </div>
@endsection
