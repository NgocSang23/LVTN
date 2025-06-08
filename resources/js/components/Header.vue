<template>
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
      <!-- Sidebar Toggle (Topbar) -->
      <button
        id="sidebarToggleTop"
        class="btn btn-secondary d-md-none rounded-circle me-3"
      >
        <i class="fa fa-bars"></i>
      </button>

      <!-- Topbar Search -->
      <form
        @submit.prevent="submitSearch"
        class="d-none d-sm-inline-block form-inline ms-3 my-2 my-md-0 mw-100 navbar-search position-relative"
      >
        <div class="input-group">
          <input
            v-model="search"
            type="text"
            class="form-control bg-light border-0 small"
            placeholder="Tìm kiếm người dùng, flashcard, thống kê..."
          />
          <div class="input-group-append">
            <button class="btn btn-secondary" type="submit">
              <i class="fas fa-search fa-sm"></i>
            </button>
          </div>
        </div>
        <div
          id="search-suggestions"
          class="position-absolute bg-white shadow p-2 rounded w-100"
          style="z-index: 9999"
          v-show="showSuggestions"
        >
          <div v-for="suggestion in suggestions" :key="suggestion.id">
            <a href="#" class="d-block py-1">{{ suggestion.text }}</a>
          </div>
        </div>
      </form>

      <!-- Topbar Navbar -->
      <ul class="navbar-nav ms-auto align-items-center">
        <!-- Thống kê nhanh -->
        <li class="nav-item mx-2">
          <a class="nav-link" href="/admin/statistics">
            <i class="fa-solid fa-chart-line text-secondary"></i>
          </a>
        </li>

        <!-- Thông báo -->
        <li class="nav-item dropdown no-arrow mx-2">
          <a
            class="nav-link dropdown-toggle position-relative"
            href="#"
            data-bs-toggle="dropdown"
          >
            <i class="fa-solid fa-bell text-warning"></i>
            <span
              class="position-absolute start-100 translate-middle badge rounded-pill bg-danger"
              style="font-size: 0.6rem"
              >3</span
            >
          </a>
          <div class="dropdown-menu dropdown-menu-end shadow">
            <h6 class="dropdown-header">Thông báo</h6>
            <a class="dropdown-item" href="#">
              <i class="fa-solid fa-wrench me-2 text-muted"></i> Bảo trì hệ thống lúc 22h
            </a>
            <a class="dropdown-item" href="#">
              <i class="fa-solid fa-star me-2 text-muted"></i> Tính năng mới được cập nhật
            </a>
          </div>
        </li>

        <!-- User -->
        <li class="nav-item dropdown no-arrow">
          <template v-if="isLoggedIn">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <span class="me-2 d-none d-lg-inline text-gray-600 small">{{ user.name }}</span>
              <img
                class="img-profile rounded-circle"
                :src="user.image || defaultImage"
                width="32"
                height="32"
              />
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow">
              <a class="dropdown-item" href="/profile">
                <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                Thông tin cá nhân
              </a>
              <a class="dropdown-item" href="/admin/settings">
                <i class="fa-solid fa-cog fa-sm fa-fw me-2 text-gray-400"></i>
                Cài đặt quản trị
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="/logout">
                <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                Đăng xuất
              </a>
            </div>
          </template>
          <template v-else>
            <a class="nav-link text-primary fw-bold" href="/login">
              <i class="fas fa-sign-in-alt fa-sm fa-fw me-2 text-primary"></i>
              Đăng nhập
            </a>
          </template>
        </li>
      </ul>
    </nav>
  </template>

  <script>
  export default {
    name: "AdminHeader",
    data() {
      return {
        search: "",
        suggestions: [],
        showSuggestions: false,
        user: {
          name: "Admin",
          image: "", // link ảnh từ API nếu có
        },
        defaultImage: "/assets/img/undraw_profile.svg",
        isLoggedIn: true, // true giả định, kết nối với auth thực tế
      };
    },
    methods: {
      submitSearch() {
        this.$router.push({
          name: "search",
          query: { search: this.search },
        });
      },
    },
  };
  </script>

  <style scoped>
  #search-suggestions {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-top: none;
  }
  #search-suggestions a:hover {
    background-color: #f8f9fa;
  }
  .img-profile {
    object-fit: cover;
  }
  </style>
