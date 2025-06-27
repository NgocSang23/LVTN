@extends('user.master')

@section('title', 'Tạo lớp học mới')

@section('content')
    <div class="container my-5">
        <div class="mb-4 text-center">
            <h1 class="h3 fw-bold text-primary">Tạo lớp học mới</h1>
            <p class="text-muted">Nhập thông tin bên dưới để khởi tạo một lớp học ảo</p>
        </div>

        <form method="POST" action="{{ route('classrooms.store') }}"
            style="background: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Tên lớp học</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Ví dụ: Lớp Sinh 10A3"
                    style="border-radius: 8px;" value="{{ old('name') }}">
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="form-label fw-semibold">Mô tả lớp học (tuỳ chọn)</label>
                <textarea name="description" id="description" class="form-control" rows="4"
                    placeholder="Thông tin thêm về lớp học, lịch học, nhóm ôn thi..." style="border-radius: 8px;">{{ old('description') }}</textarea>
                @error('description')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">Tạo lớp học</button>
            </div>
        </form>
    </div>
@endsection
