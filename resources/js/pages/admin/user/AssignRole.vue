<template>
    <div class="container mt-4">
        <h4 class="text-center mb-4 text-dark">🎓 Gán quyền giáo viên</h4>

        <div v-if="isLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Đang tải danh sách người dùng...</p>
        </div>

        <div v-else>
            <div class="mb-3 w-50">
                <input
                    v-model="searchQuery"
                    type="text"
                    class="form-control"
                    placeholder="🔍 Tìm kiếm theo tên hoặc email..."
                />
            </div>

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in paginatedUsers" :key="user.id">
                        <td>{{ user.id }}</td>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>
                            {{
                                Array.isArray(user.roles)
                                    ? user.roles.join(", ")
                                    : user.roles
                            }}
                        </td>
                        <td>
                            <button
                                class="btn btn-sm btn-outline-primary"
                                @click="openAssignModal(user)"
                            >
                                Gán quyền
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <Pagination
                :total-pages="totalPages"
                :current-page="currentPage"
                @page-change="changePage"
            />
        </div>
    </div>

    <!-- Modal xác nhận gán quyền giáo viên -->
    <div
        class="modal fade"
        id="assignConfirmModal"
        tabindex="-1"
        aria-labelledby="assignConfirmModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="assignConfirmModalLabel">
                        Xác nhận gán quyền
                    </h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body text-center">
                    <p>
                        Bạn có chắc chắn muốn gán quyền
                        <strong>giáo viên</strong> cho người dùng
                        <span class="text-primary fw-bold">{{
                            selectedUser?.name
                        }}</span
                        >?
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="confirmAssign"
                    >
                        Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div
        v-if="assignMessage"
        :class="[
            'toast align-items-center text-white border-0 position-fixed bottom-0 end-0 m-4 show',
            assignSuccess ? 'bg-success' : 'bg-danger',
        ]"
        role="alert"
    >
        <div class="d-flex">
            <div class="toast-body">{{ assignMessage }}</div>
            <button
                type="button"
                class="btn-close btn-close-white me-2 m-auto"
                @click="assignMessage = ''"
            ></button>
        </div>
    </div>
</template>

<script>
import axios from "axios"; // Import thư viện Axios để thực hiện các yêu cầu HTTP (gọi API)
import Pagination from "../../../components/Pagination.vue"; // Import component phân trang tùy chỉnh
import "bootstrap/dist/js/bootstrap.bundle.min.js"; // Import file JS của Bootstrap để sử dụng các thành phần như Modal, Toast

export default {
    name: "AssignRole", // Định nghĩa tên của component Vue
    components: {
        Pagination, // Đăng ký component Pagination để có thể sử dụng trong template
    },
    data() {
        return {
            users: [], // Mảng chứa danh sách người dùng được tải từ API
            currentPage: 1, // Biến lưu trữ số trang hiện tại của phân trang
            perPage: 8, // Biến lưu trữ số lượng người dùng hiển thị trên mỗi trang
            isLoading: false, // Biến cờ cho biết dữ liệu có đang được tải hay không (dùng để hiển thị spinner loading)
            selectedUser: null, // Biến lưu trữ thông tin người dùng được chọn để gán quyền (khi modal hiển thị)
            assignMessage: "", // Biến lưu trữ nội dung thông báo (thành công/thất bại) cho Toast
            assignSuccess: true, // Biến cờ cho biết thông báo là thành công (true) hay thất bại (false), dùng để thay đổi màu Toast
            searchQuery: "", // Biến lưu trữ truy vấn tìm kiếm người dùng
        };
    },
    computed: {
        // Computed property này tính toán tổng số trang dựa trên tổng số người dùng và số lượng người dùng mỗi trang
        totalPages() {
            return Math.ceil(this.users.length / this.perPage);
        },
        // Computed property này trả về danh sách người dùng cho trang hiện tại
        filteredUsers() {
            if (!this.searchQuery) return this.users;
            const q = this.searchQuery.toLowerCase();
            return this.users.filter(
                (user) =>
                    user.name.toLowerCase().includes(q) ||
                    user.email.toLowerCase().includes(q)
            );
        },
        paginatedUsers() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredUsers.slice(start, start + this.perPage);
        },
    },
    methods: {
        // Phương thức được gọi khi trang phân trang thay đổi
        changePage(page) {
            // Kiểm tra xem số trang mới có hợp lệ không
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page; // Cập nhật trang hiện tại
            }
        },

        // Phương thức bất đồng bộ để gọi API lấy danh sách người dùng
        async fetchUsers() {
            this.isLoading = true; // Bật cờ loading để hiển thị spinner
            try {
                const token = localStorage.getItem("admin_token"); // Lấy token xác thực từ Local Storage
                if (!token) throw new Error("Không tìm thấy token quản trị"); // Ném lỗi nếu không tìm thấy token

                // Gửi yêu cầu GET đến API để lấy danh sách người dùng (học sinh)
                const response = await axios.get("/api/admin/users/students", {
                    headers: {
                        Authorization: `Bearer ${token}`, // Gắn token vào header Authorization để xác thực
                    },
                });

                this.users = response.data; // Cập nhật dữ liệu người dùng với dữ liệu từ API
            } catch (error) {
                console.error("❌ Lỗi khi tải danh sách:", error); // Ghi lỗi ra console nếu có vấn đề khi gọi API
            } finally {
                this.isLoading = false; // Tắt cờ loading dù thành công hay thất bại
            }
        },

        // Phương thức mở modal xác nhận gán quyền giáo viên
        openAssignModal(user) {
            this.selectedUser = user; // Lưu người dùng được chọn vào biến selectedUser

            // Khởi tạo và hiển thị modal sử dụng JavaScript của Bootstrap
            const modal = new bootstrap.Modal(
                document.getElementById("assignConfirmModal")
            );
            modal.show();
        },

        // Phương thức bất đồng bộ để xác nhận gán quyền giáo viên
        async confirmAssign() {
            try {
                const token = localStorage.getItem("admin_token"); // Lấy token xác thực
                // Gửi yêu cầu POST đến API để gán quyền giáo viên cho người dùng đã chọn
                await axios.post(
                    `/api/admin/users/${this.selectedUser.id}/assign-teacher`, // URL API với ID người dùng
                    {}, // Body rỗng vì có thể không cần dữ liệu cụ thể trong body cho hành động này
                    {
                        headers: {
                            Authorization: `Bearer ${token}`, // Gắn token vào header Authorization
                        },
                    }
                );

                // Sau khi gán quyền thành công, ẩn modal
                const modalEl = document.getElementById("assignConfirmModal");
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                modalInstance.hide();

                this.selectedUser = null; // Đặt lại selectedUser về null
                this.fetchUsers(); // Gọi lại fetchUsers để làm mới danh sách người dùng sau khi gán quyền

                this.assignSuccess = true; // Đặt trạng thái thông báo là thành công
                this.assignMessage =
                    "✅ Đã gán quyền giáo viên cho người dùng."; // Đặt nội dung thông báo thành công
            } catch (error) {
                console.error("❌ Lỗi khi gán quyền:", error); // Ghi lỗi ra console
                this.assignSuccess = false; // Đặt trạng thái thông báo là thất bại
                this.assignMessage = "❌ Gán quyền thất bại."; // Đặt nội dung thông báo thất bại
            } finally {
                // Sau 3 giây, tự động ẩn Toast thông báo
                setTimeout(() => {
                    this.assignMessage = "";
                }, 3000);
            }
        },
    },
    mounted() {
        // Lifecycle hook: được gọi sau khi component được gắn vào DOM
        // Lấy CSRF cookie (cần cho Laravel Sanctum) trước khi fetch dữ liệu người dùng
        axios
            .get("/sanctum/csrf-cookie", { withCredentials: true })
            .then(() => {
                this.fetchUsers(); // Sau khi lấy cookie, gọi phương thức fetchUsers để tải danh sách
            });
    },
};
</script>

<style scoped>
.pagination-fixed {
    position: fixed;
    bottom: 80px;
    right: 20px;
    z-index: 1000;
    background-color: #fff;
    padding: 8px 12px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
</style>
