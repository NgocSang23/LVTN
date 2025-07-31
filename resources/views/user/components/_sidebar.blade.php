<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('user.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Studying For Exams</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('user.dashboard') }}">
            <i class="fa-solid fa-house"></i>
            <span>Trang chủ</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('user.library') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('user.library') }}">
            <i class="fa-solid fa-book"></i>
            <span>Thư viện của bạn</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('user.history') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('user.history') }}">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Kết quả học tập</span>
        </a>
    </li>

    @can('teacher')
        <li class="nav-item {{ request()->routeIs('classrooms.index') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('classrooms.index') }}">
                <i class="fas fa-chalkboard-teacher"></i> <span>Lớp học của tôi</span>
            </a>
        </li>
    @endcan

    @can('student')
        <li class="nav-item {{ request()->routeIs('classrooms.joinForm') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('classrooms.joinForm') }}">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tham gia lớp học</span>
            </a>
        </li>
        <li class="nav-item {{ request()->routeIs('classrooms.my') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('classrooms.my') }}">
                <i class="fas fa-users"></i>
                <span>Lớp học của tôi</span>
            </a>
        </li>
    @endcan

    <li class="nav-item {{ request()->routeIs('user.notifications') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('user.notifications') }}">
            <i class="fa-solid fa-bell"></i>
            <span>Thông báo</span>
            @php
                $unread = auth()->user()?->notifications()->where('is_read', false)->count();
            @endphp
            @if ($unread)
                <span class="badge bg-danger ms-1">{{ $unread }}</span>
            @endif
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Bắt đầu tại đây
    </div>

    <li class="nav-item {{ request()->routeIs('flashcard_define_essay.create') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('flashcard_define_essay.create') }}">
            <i class="fas fa-fw fa-cog"></i>
            <span>Khái niệm / Tự luận</span>
        </a>
    </li>

    @can('teacher')
        <li class="nav-item {{ request()->routeIs('flashcard_multiple_choice.create') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('flashcard_multiple_choice.create') }}">
                <i class="fas fa-fw fa-cog"></i>
                <span>Trắc nghiệm</span>
            </a>
        </li>
    @endcan

    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Lời giải chuyên gia</span>
        </a>
    </li>

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
