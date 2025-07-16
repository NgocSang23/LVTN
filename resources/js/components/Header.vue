<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow mb-4">
        <div class="container-fluid">
            <!-- Sidebar Toggle Button (for mobile) -->
            <button
                class="btn btn-outline-secondary d-lg-none me-3"
                @click="$emit('toggle-sidebar')"
            >
                <i class="fas fa-bars"></i>
            </button>

            <!-- Search Form -->
            <form
                class="d-none d-md-flex align-items-center w-50 position-relative"
                @submit.prevent="submitSearch"
            >
                <input
                    v-model="search"
                    class="form-control form-control-sm w-100"
                    type="search"
                    placeholder="Tìm kiếm người dùng, flashcard, thống kê..."
                />
                <button
                    class="btn btn-sm btn-secondary position-absolute end-0"
                    type="submit"
                >
                    <i class="fas fa-search"></i>
                </button>
                <div
                    v-if="showSuggestions"
                    class="position-absolute bg-white border shadow rounded mt-1 w-100 z-3"
                >
                    <a
                        v-for="suggestion in suggestions"
                        :key="suggestion.id"
                        href="#"
                        class="dropdown-item"
                    >
                        {{ suggestion.text }}
                    </a>
                </div>
            </form>

            <!-- Right Nav Section -->
            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Quick stats -->
                <li class="nav-item mx-2">
                    <router-link class="nav-link" to="/admin/statistics">
                        <i class="fa-solid fa-chart-line text-secondary"></i>
                    </router-link>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown mx-2">
                    <a
                        class="nav-link dropdown-toggle position-relative"
                        href="#"
                        role="button"
                        data-bs-toggle="dropdown"
                    >
                        <i class="fa-solid fa-bell text-warning"></i>
                        <span
                            v-if="notifications.length"
                            class="badge bg-danger position-absolute top-10 start-100 translate-middle rounded-pill"
                            style="font-size: 0.6rem"
                        >
                            {{ notifications.length }}
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">Thông báo từ Admin</h6>
                        </li>

                        <li v-for="notify in notifications" :key="notify.id">
                            <a class="dropdown-item text-wrap" href="#">
                                <i
                                    class="fa-regular fa-circle-dot me-2 text-muted"
                                ></i>
                                <strong>{{ notify.title }}</strong>
                                <div class="small text-muted">
                                    {{ notify.message }}
                                </div>
                            </a>
                        </li>

                        <li
                            v-if="!notifications.length"
                            class="dropdown-item text-muted text-center"
                        >
                            Không có thông báo nào.
                        </li>

                        <li v-if="notifications.length > 0">
                            <hr class="dropdown-divider" />
                            <button
                                class="dropdown-item text-danger text-center"
                                @click="deleteAllNotifications"
                            >
                                🗑 Xoá tất cả thông báo
                            </button>
                        </li>
                    </ul>
                </li>

                <!-- User Profile -->
                <li class="nav-item dropdown">
                    <a
                        v-if="isLoggedIn"
                        class="nav-link dropdown-toggle"
                        href="#"
                        role="button"
                        data-bs-toggle="dropdown"
                    >
                        <span
                            class="me-2 d-none d-lg-inline text-gray-600 small"
                            >{{ user.email }}</span
                        >
                        <img
                            class="rounded-circle"
                            :src="user.image || defaultImage"
                            width="32"
                            height="32"
                            alt="profile"
                        />
                    </a>
                    <ul
                        v-if="isLoggedIn"
                        class="dropdown-menu dropdown-menu-end"
                    >
                        <li>
                            <router-link class="dropdown-item" to="/profile">
                                <i
                                    class="fas fa-user fa-sm fa-fw me-2 text-gray-400"
                                ></i>
                                Thông tin cá nhân
                            </router-link>
                        </li>
                        <li>
                            <router-link
                                class="dropdown-item"
                                to="/admin/settings"
                            >
                                <i
                                    class="fa-solid fa-cog fa-sm fa-fw me-2 text-gray-400"
                                ></i>
                                Cài đặt quản trị
                            </router-link>
                        </li>
                        <li><hr class="dropdown-divider" /></li>
                        <li>
                            <a
                                class="dropdown-item"
                                href="#"
                                @click.prevent="logout"
                            >
                                <i
                                    class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"
                                ></i>
                                Đăng xuất
                            </a>
                        </li>
                    </ul>
                    <a
                        v-else
                        class="nav-link text-primary fw-bold"
                        href="#"
                        @click.prevent="login"
                    >
                        <i
                            class="fas fa-sign-in-alt fa-sm fa-fw me-2 text-primary"
                        ></i>
                        Đăng nhập
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
// Import đối tượng Dropdown từ Bootstrap (để sử dụng API JavaScript cho dropdown Bootstrap 5)
import { Dropdown } from "bootstrap";

export default {
    name: "AdminHeader", // Đặt tên cho component là AdminHeader

    data() {
        return {
            search: "", // Chuỗi tìm kiếm được nhập vào ô tìm kiếm
            suggestions: [], // Mảng chứa danh sách gợi ý (ví dụ: người dùng, flashcard...)
            showSuggestions: false, // Cờ để điều khiển hiển thị/ẩn khung gợi ý
            user: {
                email: "", // Email của admin đăng nhập
                image: "", // Ảnh đại diện của admin
            },
            defaultImage: "/assets/img/undraw_profile.svg", // Ảnh mặc định nếu không có ảnh user
            isLoggedIn: false, // Biến kiểm tra trạng thái đăng nhập của admin
            notifications: [],
        };
    },

    created() {
        // Hàm này được gọi khi component được khởi tạo (chưa gắn vào DOM)

        const saved = localStorage.getItem("admin"); // Lấy thông tin admin từ localStorage (nếu có)
        if (saved) {
            try {
                const admin = JSON.parse(saved); // Parse JSON thành đối tượng JavaScript
                // Gán thông tin user từ dữ liệu lưu
                this.user = {
                    email: admin.email || "admin@example.com", // Nếu không có email thì dùng mặc định
                    image: admin.image || "", // Nếu không có ảnh thì để trống
                };
                this.isLoggedIn = true; // Đánh dấu đã đăng nhập
            } catch (e) {
                console.error("Lỗi khi đọc thông tin admin", e); // Ghi log nếu lỗi khi parse JSON
            }
        }
    },

    methods: {
        // Hàm xử lý khi người dùng nhấn tìm kiếm
        submitSearch() {
            // Chuyển hướng đến route "search" và truyền query tìm kiếm
            this.$router.push({
                name: "search",
                query: { search: this.search },
            });
        },

        async loadNotifications() {
            const token = localStorage.getItem("admin_token");
            try {
                const res = await axios.get("/api/admin/notifications", {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                this.notifications = res.data.slice(0, 3); // lấy 3 cái mới nhất
            } catch (err) {
                console.error("Lỗi khi lấy thông báo admin:", err);
            }
        },

        async deleteAllNotifications() {
            const token = localStorage.getItem("admin_token");
            try {
                await axios.delete("/api/admin/notifications", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.notifications = [];
            } catch (err) {
                console.error("❌ Lỗi khi xoá tất cả thông báo:", err);
            }
        },

        // Hàm xử lý khi người dùng nhấn đăng xuất
        logout() {
            axios
                .post("admin/logout", {}, { withCredentials: true }) // Gửi yêu cầu POST đăng xuất
                .then(() => {
                    // Xoá thông tin đăng nhập khỏi localStorage
                    localStorage.removeItem("admin");
                    localStorage.removeItem("admin_logged_in");
                    this.$router.push("/admin/login"); // Chuyển về trang đăng nhập
                })
                .catch((err) => {
                    console.error("Đăng xuất thất bại:", err); // Ghi log nếu có lỗi
                });
        },

        // Hàm xử lý khi người dùng nhấn "Đăng nhập"
        login() {
            this.$router.push("/admin/login"); // Chuyển hướng về trang đăng nhập
        },
    },

    mounted() {
        // Hàm này chạy khi component đã được gắn vào DOM

        // Tìm tất cả các phần tử có thuộc tính data-bs-toggle="dropdown"
        const dropdownTriggerList = document.querySelectorAll(
            '[data-bs-toggle="dropdown"]'
        );

        // Với mỗi phần tử đó, khởi tạo dropdown bằng Bootstrap
        // Điều này đảm bảo dropdown hoạt động đúng trong môi trường Vue (không cần jQuery)
        dropdownTriggerList.forEach((dropdownTriggerEl) => {
            new Dropdown(dropdownTriggerEl); // Tạo dropdown Bootstrap bằng JS
        });

        this.loadNotifications();
    },
};
</script>

<style scoped>
.z-3 {
    z-index: 1050;
}

.dropdown-toggle::after {
    display: none !important;
}
</style>
