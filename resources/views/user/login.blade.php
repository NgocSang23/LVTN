<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Đăng nhập</title>

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

        .login-container {
            width: 100%;
            max-width: 900px;
            /* Tăng max-width để chứa cả ảnh và form */
            margin: 0 auto;
        }

        .card-login {
            width: 100%;
            margin: 0;
            display: flex;
            /* Sử dụng flexbox cho card */
            flex-direction: row;
            /* Sắp xếp theo chiều ngang */
        }

        .img-container {
            width: 50%;
            /* Chiều rộng cho ảnh */
            display: flex;
            justify-content: center;
            /* Căn giữa theo chiều ngang */
            align-items: center;
            /* Căn giữa theo chiều dọc */
        }

        .bg-login-image {
            max-width: 100%;
            /* Đảm bảo ảnh không vượt quá kích thước container */
            max-height: 100%;
            object-fit: cover;
            /* Giữ nguyên tỉ lệ và lấp đầy container */
        }

        .card-body {
            width: 50%;
            /* Chiều rộng cho form */
            padding: 2rem;
        }

        input.form-control:focus {
            outline: none;
            box-shadow: none;
            border-color: none;
        }

        /* Các style khác giữ nguyên */
    </style>
</head>

<body>
    @if (Session::has('success'))
        <div class="shadow-lg p-2 move-from-top js-div-dissappear"
            style="width: 18rem; display:flex; text-align:center">
            <i class="fas fa-check p-2 bg-success text-white rounded-circle pe-2 mx-2"></i>{{ Session::get('success') }}
        </div>
    @endif
    @if (Session::has('error'))
        <div class="shadow-lg p-2 move-from-top js-div-dissappear"
            style="width: 18rem; display:flex; text-align:center">
            <i class="fas fa-times p-2 bg-danger text-white rounded-circle pe-2 mx-2"></i>{{ Session::get('error') }}
        </div>
    @endif
    <div class="login-container">
        <div class="card o-hidden border-0 shadow-lg card-login">
            <div class="img-container">
                <img src="{{ asset('assets/img/study.jpg') }}" alt="Login Image" class="bg-login-image">
            </div>
            <div class="card-body p-0">
                <div class="p-5">
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4">Đăng nhập</h1>
                    </div>

                    <form class="user" method="POST" action="{{ route('user.post_login') }}">
                        @csrf
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" name="login"
                                value="{{ old('login') }}" placeholder="Nhập email hoặc tên đăng nhập...">
                            @error('login')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <input type="password" class="form-control form-control-user" name="password"
                                placeholder="Nhập mật khẩu...">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox small">
                                <input type="checkbox" class="custom-control-input" name="rememberme" id="customCheck">
                                <label class="custom-control-label" for="customCheck">Nhớ tôi</label>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-primary btn-user btn-block" value="Đăng nhập">
                        <hr>
                        <div class="text-center rounded-3">
                            <a href="{{ route('auth.google') }}" class="btn btn-primary btn-user btn-block ">
                                <i class="fab fa-google fa-fw"></i> Đăng nhập bằng Google
                            </a>
                        </div>
                    </form>
                    <hr>
                    <div class="text-center">
                        <a class="small" href="">Quên mật khẩu?</a>
                    </div>
                    <div class="text-center">
                        <a class="small" href="{{ route('user.register') }}">Tạo tài khoản</a>
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

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/message.css') }}" />
@endsection

@section('js')
    @if (Session::has('success'))
        <script src="{{ asset('assets/js/message.js') }}"></script>
    @endif
@endsection