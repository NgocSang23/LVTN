<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Đăng ký</title>

    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(to right, #4e73df, #1e3d8b);
        }

        .register-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .card-register {
            width: 100%;
            margin: 0;
            display: flex;
            flex-direction: row;
        }

        .img-container {
            width: 40%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .bg-register-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .card-body {
            width: 60%;
            padding: 2rem;
        }

        input.form-control:focus {
            outline: none;
            box-shadow: none;
            border-color: none;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="card o-hidden border-0 shadow-lg card-register">
            <div class="img-container">
                <img src="{{ asset('assets/img/study.jpg') }}" alt="Register Image" class="bg-register-image">
            </div>
            <div class="card-body p-0">
                <div class="p-5">
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4">Tạo tài khoản</h1>
                    </div>

                    <form class="user" method="POST" action="{{ route('user.post_register') }}">
                        @csrf
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" name="name"
                                placeholder="Họ và tên" value="{{ old('name') }}">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" name="username"
                                placeholder="Tên đăng nhập" value="{{ old('username') }}">
                            @error('username')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <input type="email" class="form-control form-control-user" name="email"
                                placeholder="Email" value="{{ old('email') }}">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="password" class="form-control form-control-user" name="password"
                                    placeholder="Mật khẩu">
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-sm-6">
                                <input type="password" class="form-control form-control-user"
                                    name="password_confirmation" placeholder="Nhập lại mật khẩu">
                            </div>
                        </div>

                        <div class="form-group">
                            <select name="roles" class="form-control">
                                <option value="student">Học sinh</option>
                                <option value="teacher">Giáo viên</option>
                            </select>
                        </div>

                        <input type="submit" class="btn btn-primary btn-user btn-block" value="Đăng ký tài khoản">

                        <hr>
                        <div class="text-center rounded-3">
                            <a href="{{ route('auth.google') }}" class="btn btn-primary btn-user btn-block ">
                                <i class="fab fa-google fa-fw"></i> Đăng nhập bằng Google
                            </a>
                        </div>
                    </form>

                    <hr>
                    <div class="text-center">
                        <a class="small" href="#">Quên mật khẩu?</a>
                    </div>
                    <div class="text-center">
                        <a class="small" href="{{ route('user.login') }}">Đã có tài khoản? Đăng nhập!</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/js/sb-admin-2.min.js') }}"></script>

</body>

</html>
