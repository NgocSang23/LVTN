<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <style>
        #search-suggestions {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-top: none;
        }

        #search-suggestions a:hover {
            background-color: #f8f9fa;
        }
    </style>


    <!-- Topbar Search -->
    <form action="{{ route('user.search') }}"
        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" method="get">
        @csrf
        <div class="input-group">
            <input name="search" type="text" class="form-control bg-light border-0 small"
                placeholder="Học phần, sách giáo khoa, câu hỏi" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search') }}">

            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>

        <!-- Kết quả tìm kiếm AJAX -->
        <div id="search-suggestions" class="position-absolute bg-white shadow p-2 rounded w-100" style="z-index: 9999; display: none;"></div>
    </form>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa-solid fa-plus btn btn-primary"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                <h2 class="dropdown-header">Thêm Mới</h2>
                <a class="dropdown-item select-option"
                    href="{{ auth()->check() ? route('flashcard_define_essay.create') : route('user.login') }}"
                    data-value="concept">Tạo thẻ mới
                </a>
                <a class="dropdown-item select-option"
                    href="{{ auth()->check() ? route('flashcard_multiple_choice.create') : route('user.login') }}"
                    data-value="multiple_choice">Câu hỏi trắc
                    nghiệm
                </a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            @php
                $image = asset('assets/img/undraw_profile.svg');
                if (Auth::guard('web')->check()) {
                    $user = Auth::guard('web')->user();
                    if ($user && !empty($user->image)) {
                        $image = $user->image;
                    } else {
                        $image = asset('assets/img/undraw_profile.svg');
                    }
                }
            @endphp
            @if (Auth::guard('web')->check())
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span
                        class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::guard('web')->user()->name }}</span>
                    <img class="img-profile rounded-circle" src="{{ $image }}">
                </a>
                <!-- Dropdown - User Information -->
                <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="{{ route('user.profile') }}">
                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                        Thông tin cá nhân
                    </a>
                    <a class="dropdown-item" href="{{ route('user.history_define_essay') }}">
                        <i class="fa-solid fa-clock-rotate-left fa-sm fa-fw mr-2 text-gray-400"></i>
                        Kết quả học tập
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('user.logout') }}">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Đăng xuất
                    </a>
                </div>
            @else
                <a class="nav-link text-primary bold" href="{{ route('user.login') }}">
                    <i class="fas fa-sign-in-alt fa-sm fa-fw mr-2 text-primary"></i>
                    Đăng nhập
                </a>
            @endif
        </li>
    </ul>
</nav>
