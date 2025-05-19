@extends('user.master')
@section('title', 'Hồ sơ cá nhân')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success fixed-top text-center p-3 shadow-lg js-div-dissappear"
            style="width: 100%; max-width: 400px; margin: 10px auto; z-index: 1050;">
            <i class="fas fa-check-circle me-2"></i> {{ Session::get('success') }}
        </div>
    @endif
    <style>
        input.form-control:focus {
            outline: none;
            box-shadow: none;
            border-color: none;
        }
    </style>
    <div class="container rounded bg-white">
        <div class="row">
            @php
                if(Auth::guard('web')->user()->image){
                    $image = Auth::guard('web')->user()->image;
                } else {
                    $image = asset('assets/img/undraw_profile.svg');
                }
            @endphp
            <div class="col-md-3 border-right">
                <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                    <img class="rounded-circle mt-5" width="150px"
                        src="{{ $image }}">
                    <span class="font-weight-bold">{{ $user->name }}</span>
                    <span class="text-black-50">{{ $user->email }}</span>
                </div>
            </div>
            <div class="col-md-9 border-right">
                <form action="{{ route('user.update_profile') }}" method="post">
                    @csrf @method('PUT')

                    <div class="p-3 py-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="text-right">Thông tin cá nhân</h4>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="labels">Họ và tên</label>
                                <input type="text" class="form-control" name="name" value="{{ $user->name }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="labels">Email</label>
                                <input type="text" class="form-control" name="email" value="{{ $user->email }}">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="labels">Username</label>
                                <input type="text" class="form-control" name="username" value="{{ $user->username }}">
                                @error('username')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="labels">Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Nhập mật khẩu mới">
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="labels">Nghề nghiệp</label>
                                <input type="text" class="form-control" name="roles"
                                    value="{{ $user->roles === 'student' ? 'Học sinh' : ($user->roles == 'teacher' ? 'Giáo viên' : 'Khác') }}"
                                    readonly>
                            </div>
                        </div>
                        <div class="mt-5 text-center">
                            <input class="btn btn-primary profile-button" type="submit" value="Cập nhật thông tin">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/message.css') }}" />
@endsection

@section('js')
    @if (Session::has('success'))
        <script src="{{ asset('assets/js/message.js') }}"></script>
    @endif
@endsection
