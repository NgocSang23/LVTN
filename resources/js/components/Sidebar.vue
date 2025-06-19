<template>
    <ul
        class="navbar-nav bg-secondary sidebar sidebar-dark accordion"
        id="accordionSidebar"
    >
        <!-- Logo -->
        <router-link
            class="sidebar-brand d-flex align-items-center justify-content-center text-white"
            to="/dashboard"
        >
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="sidebar-brand-text mx-3">Admin Panel</div>
        </router-link>

        <hr class="sidebar-divider my-0 border-light" />

        <!-- Dashboard -->
        <li class="nav-item active">
            <router-link
                class="nav-link text-white"
                to="/dashboard"
                active-class="bg-primary"
            >
                <i class="fa-solid fa-house"></i>
                <span>Bảng điều khiển</span>
            </router-link>
        </li>

        <!-- Quản lý người dùng -->
        <hr class="sidebar-divider border-light" />
        <div class="sidebar-heading text-light">Quản lý người dùng</div>

        <li class="nav-item">
            <router-link
                class="nav-link text-white"
                to="/admin/user"
                active-class="bg-primary"
            >
                <i class="fa-solid fa-users"></i>
                <span>Duyệt / Xoá người dùng</span>
            </router-link>
        </li>

        <li class="nav-item">
            <router-link
                class="nav-link text-white"
                to="/admin/assign-role"
                active-class="bg-primary"
            >
                <i class="fa-solid fa-user-shield"></i>
                <span>Gán quyền giáo viên</span>
            </router-link>
        </li>

        <!-- Quản lý flashcard -->
        <hr class="sidebar-divider border-light" />
        <div class="sidebar-heading text-light">Quản lý nội dung</div>

        <li class="nav-item">
            <router-link
                class="nav-link text-white"
                to="/admin/flashcards"
                active-class="bg-primary"
            >
                <i class="fa-solid fa-layer-group"></i>
                <span>Kiểm duyệt flashcard</span>
            </router-link>
        </li>

        <!-- Thống kê -->
        <hr class="sidebar-divider border-light" />
        <div class="sidebar-heading text-light">Thống kê hệ thống</div>

        <li class="nav-item">
            <router-link
                class="nav-link text-white"
                to="/admin/statistics"
                active-class="bg-primary"
            >
                <i class="fa-solid fa-chart-line"></i>
                <span>Phân tích sử dụng</span>
            </router-link>
        </li>

        <!-- Gửi thông báo -->
        <hr class="sidebar-divider border-light" />
        <div class="sidebar-heading text-light">Thông báo hệ thống</div>

        <li class="nav-item">
            <router-link
                class="nav-link text-white"
                to="/admin/notifications"
                active-class="bg-primary"
            >
                <i class="fa-solid fa-bullhorn"></i>
                <span>Gửi thông báo</span>
            </router-link>
        </li>

        <!-- Toggle Sidebar -->
        <div class="text-center d-none d-md-inline mt-3">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>
</template>

<script>
export default {
    name: "Sidebar",
};
</script>

<style scoped>
.sidebar {
    width: 250px;
}

.nav-link:hover {
    background-color: #5a6268;
    color: white !important;
}

.router-link-active {
    background-color: #495057 !important;
    color: white !important;
}

.nav-link i {
    color: white;
}

.sidebar-heading {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    padding-left: 1rem;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    color: white;
}

.sidebar-divider {
    border-top: 1px solid rgba(255, 255, 255, 0.3);
}
</style>
