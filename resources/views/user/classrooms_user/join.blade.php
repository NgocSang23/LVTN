@extends('user.master')

@section('title', 'Tham gia lớp học')

@section('content')
    <div class="container py-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">Tham gia lớp học</h2>
            <p class="text-muted">Nhập mã lớp để tham gia lớp học cùng giáo viên và bạn bè</p>
        </div>

        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        <form method="POST" action="{{ route('classrooms.join') }}" class="mx-auto"
            style="max-width: 500px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
            @csrf

            <div class="mb-3">
                <label for="code" class="form-label fw-semibold">Mã lớp học</label>
                <input type="text" name="code" id="code" class="form-control text-uppercase" autocomplete="off"
                    placeholder="Nhập mã lớp (ví dụ: AB12CD)" maxlength="6" style="border-radius: 8px;" required>
                @error('code')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">Tham gia lớp</button>
            </div>
        </form>
    </div>
@endsection
