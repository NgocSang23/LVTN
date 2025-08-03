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
    <form id="instantSearchForm" method="POST"
        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        @csrf
        <div class="input-group">
            <input name="search" id="instantSearchInput" type="text" class="form-control bg-light border-0 small"
                placeholder="Học phần, sách giáo khoa, câu hỏi" aria-label="Search" aria-describedby="basic-addon2">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" onclick="submitInstantSearch()">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
        <div id="search-suggestions" class="position-absolute bg-white shadow p-2 rounded w-100"
            style="z-index: 9999; display: none;"></div>
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
                @can('teacher')
                    <a class="dropdown-item" href="{{ route('classrooms.create') }}">Tạo lớp học</a>
                    <a class="dropdown-item select-option"
                        href="{{ auth()->check() ? route('flashcard_multiple_choice.create') : route('user.login') }}"
                        data-value="multiple_choice">Tạo bài kiểm tra
                    </a>
                @endcan
            </div>
        </li>

        <li class="nav-item dropdown no-arrow mx-1">
            @php
                $notifications = \App\Models\Notification::where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
                $unread = $notifications->where('is_read', false)->count();
            @endphp
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw" style="color: rgb(220, 220, 34);"></i>
                @if ($unread > 0)
                    <span class="badge badge-danger badge-counter">{{ $unread }}</span>
                @endif
            </a>

            <!-- Dropdown - Thông báo -->
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="alertsDropdown"
                style="min-width: 300px;" id="notificationDropdown">
                <h6 class="dropdown-header">🔔 Thông báo</h6>

                @foreach ($notifications as $note)
                    <div class="dropdown-item d-flex align-items-start justify-content-between small text-wrap">
                        <a href="{{ $note->url ?? '#' }}" class="d-flex text-decoration-none text-dark">
                            <div class="me-2">
                                <div class="icon-circle bg-primary text-white">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                            </div>
                            <div>
                                <div class="small text-gray-500">{{ $note->created_at->format('d/m/Y H:i') }}</div>
                                <span class="{{ $note->is_read ? '' : 'fw-bold' }}">
                                    {!! \Illuminate\Support\Str::limit($note->title, 50) !!}
                                </span>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('notifications.delete', $note->id) }}" class="ms-2">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger p-0" title="Xoá">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </form>
                    </div>
                @endforeach

                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center small text-gray-500" href="{{ route('user.notifications') }}">
                    Xem tất cả thông báo
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
                    <a class="dropdown-item" href="{{ route('user.history') }}">
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

<script>
    function submitInstantSearch() {
        const keyword = document.getElementById('instantSearchInput').value.trim();
        if (!keyword) return;

        fetch("{{ route('search.instant') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    search: keyword
                })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('searchResults').innerHTML = data.html;
            })
            .catch(() => {
                document.getElementById('searchResults').innerHTML =
                    '<p class="text-danger">Có lỗi xảy ra khi tìm kiếm.</p>';
            });
    }

    function fetchNotifications() {
        fetch('/api/notifications/latest')
            .then(res => res.json())
            .then(data => {
                const dropdown = document.querySelector('#notificationDropdown');
                if (dropdown) dropdown.innerHTML = data.html;

                const badge = document.querySelector('.badge-counter');
                if (badge) {
                    badge.textContent = data.unread;
                    badge.style.display = data.unread > 0 ? 'inline-block' : 'none';
                }
            });
    }

    // Gọi mỗi 5 giây
    setInterval(fetchNotifications, 5000);
    fetchNotifications(); // Gọi lần đầu
</script>
