<template>
    <div class="container mt-4">
        <h4 class="text-center mb-4 text-dark">👥 Danh sách người dùng</h4>

        <div v-if="isLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Đang tải danh sách...</p>
        </div>

        <div v-else>
            <div class="mb-3 w-25 w-md-25">
                <label for="roleFilter" class="form-label"
                    >Lọc theo vai trò:</label
                >
                <select
                    v-model="roleFilter"
                    class="form-select"
                    id="roleFilter"
                >
                    <option value="">Tất cả</option>
                    <option value="student">Học sinh</option>
                    <option value="teacher">Giáo viên</option>
                </select>
            </div>

            <table class="table table-hover align-middle table-fixed">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px">#</th>
                        <th style="width: 200px">Tên</th>
                        <th style="width: 250px">Email</th>
                        <th style="width: 120px">Vai trò</th>
                        <th style="width: 180px">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in paginatedUsers" :key="user.id">
                        <td>{{ user.id }}</td>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>{{ user.roles }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <button
                                    class="btn btn-sm btn-outline-primary"
                                    @click="viewUser(user)"
                                >
                                    Chi tiết
                                </button>
                                <button
                                    v-if="user.roles === 'teacher'"
                                    class="btn btn-sm btn-outline-danger"
                                    @click="openRevokeModal(user)"
                                >
                                    Bỏ quyền
                                </button>
                            </div>
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

    <!-- Modal xác nhận bỏ quyền giáo viên -->
    <div
        class="modal fade"
        id="revokeConfirmModal"
        tabindex="-1"
        aria-labelledby="revokeConfirmModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="revokeConfirmModalLabel">
                        Xác nhận bỏ quyền
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
                        Bạn có chắc chắn muốn
                        <strong class="text-danger">bỏ quyền giáo viên</strong>
                        của người dùng
                        <span class="fw-bold text-danger">{{
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
                        class="btn btn-danger"
                        @click="revokeTeacher(selectedUser)"
                    >
                        Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xem chi tiết thông tin người dùng -->
    <div
        class="modal fade"
        id="userDetailModal"
        tabindex="-1"
        aria-labelledby="userDetailModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="userDetailModalLabel">
                        Thông tin người dùng
                    </h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <li
                            class="list-group-item d-flex justify-content-between"
                        >
                            <strong>ID:</strong>
                            <span>{{ selectedUser?.id }}</span>
                        </li>
                        <li
                            class="list-group-item d-flex justify-content-between"
                        >
                            <strong>Họ tên:</strong>
                            <span>{{ selectedUser?.name }}</span>
                        </li>
                        <li
                            class="list-group-item d-flex justify-content-between"
                        >
                            <strong>Email:</strong>
                            <span>{{ selectedUser?.email }}</span>
                        </li>
                        <li
                            class="list-group-item d-flex justify-content-between"
                        >
                            <strong>Vai trò:</strong>
                            <span>
                                {{
                                    Array.isArray(selectedUser?.roles)
                                        ? selectedUser.roles.join(", ")
                                        : selectedUser?.roles
                                }}
                            </span>
                        </li>
                        <li
                            v-if="selectedUser?.created_at"
                            class="list-group-item d-flex justify-content-between"
                        >
                            <strong>Ngày tạo:</strong>
                            <span>{{
                                new Date(
                                    selectedUser.created_at
                                ).toLocaleString()
                            }}</span>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer justify-content-center">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div
        v-if="toastMessage"
        :class="[
            'toast align-items-center text-white border-0 position-fixed bottom-0 end-0 m-4 show',
            toastType === 'success' ? 'bg-success' : 'bg-danger',
        ]"
        role="alert"
    >
        <div class="d-flex">
            <div class="toast-body">{{ toastMessage }}</div>
            <button
                type="button"
                class="btn-close btn-close-white me-2 m-auto"
                @click="toastMessage = ''"
            ></button>
        </div>
    </div>
</template>

<script>
import axios from "axios"; // Import thư viện Axios để thực hiện các yêu cầu HTTP (gọi API)
import Pagination from "../../../components/Pagination.vue"; // Import component phân trang tùy chỉnh

export default {
    name: "UserManagement", // Định nghĩa tên của component Vue

    components: {
        Pagination, // Đăng ký component Pagination để có thể sử dụng trong template
    },

    data() {
        return {
            users: [], // Mảng chứa danh sách tất cả người dùng được lấy từ API
            currentPage: 1, // Biến lưu trữ số trang hiện tại của phân trang
            perPage: 8, // Biến lưu trữ số lượng người dùng hiển thị trên mỗi trang
            selectedUser: null, // Biến lưu trữ thông tin người dùng được chọn để xem chi tiết hoặc bỏ quyền
            isLoading: false, // Biến cờ cho biết dữ liệu có đang được tải hay không (dùng để hiển thị spinner loading)
            roleFilter: "", // Biến lưu trữ giá trị của bộ lọc vai trò (có thể là "student", "teacher", hoặc rỗng để hiển thị tất cả)
            toastMessage: "", // Biến lưu trữ nội dung thông báo (toast)
            toastType: "success", // Biến lưu trữ loại thông báo (success hoặc error), dùng để thay đổi màu của toast
        };
    },

    computed: {
        // Computed property này trả về danh sách người dùng đã được lọc theo vai trò
        filteredUsers() {
            if (!this.roleFilter) return this.users; // Nếu roleFilter rỗng, trả về toàn bộ danh sách người dùng
            return this.users.filter((u) => u.roles === this.roleFilter); // Lọc người dùng theo giá trị của roleFilter
        },

        // Computed property này trả về danh sách người dùng hiển thị ở trang hiện tại sau khi đã lọc
        paginatedUsers() {
            const start = (this.currentPage - 1) * this.perPage; // Tính toán chỉ số bắt đầu của mảng để phân trang
            return this.filteredUsers.slice(start, start + this.perPage); // Cắt mảng filteredUsers để lấy dữ liệu cho trang hiện tại
        },

        // Computed property này tính toán tổng số trang cần có dựa trên danh sách người dùng đã lọc và số lượng người dùng mỗi trang
        totalPages() {
            return Math.ceil(this.filteredUsers.length / this.perPage); // Làm tròn lên để có tổng số trang
        },
    },

    methods: {
        // Phương thức được gọi khi component Pagination phát ra sự kiện "page-change"
        changePage(page) {
            // Kiểm tra xem số trang mới có hợp lệ không (nằm trong khoảng từ 1 đến totalPages)
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page; // Cập nhật trang hiện tại
            }
        },

        // Phương thức bất đồng bộ để gọi API lấy danh sách người dùng
        async fetchUsers() {
            this.isLoading = true; // Bật cờ loading để hiển thị spinner "Đang tải..."
            try {
                const token = localStorage.getItem("admin_token"); // Lấy token xác thực từ Local Storage
                if (!token) throw new Error("Không tìm thấy token quản trị"); // Ném lỗi nếu không tìm thấy token

                // Gửi yêu cầu GET đến API để lấy danh sách người dùng (ban đầu là pending, nhưng có thể thay đổi để lấy tất cả)
                const response = await axios.get("/api/admin/users/pending", {
                    headers: {
                        Authorization: `Bearer ${token}`, // Gắn token vào header Authorization để xác thực
                    },
                });

                this.users = response.data; // Cập nhật biến 'users' với dữ liệu trả về từ API
            } catch (error) {
                console.error("❌ Lỗi khi tải danh sách:", error); // Ghi lỗi ra console nếu có vấn đề khi gọi API
            } finally {
                this.isLoading = false; // Tắt cờ loading dù yêu cầu API thành công hay thất bại
            }
        },

        // Phương thức bất đồng bộ để thực hiện việc bỏ quyền giáo viên cho một người dùng
        async revokeTeacher(user) {
            try {
                const token = localStorage.getItem("admin_token"); // Lấy token xác thực

                // Gửi yêu cầu POST đến API để bỏ quyền giáo viên cho người dùng cụ thể
                await axios.post(
                    `/api/admin/users/${user.id}/revoke-teacher`, // URL API với ID của người dùng
                    {}, // Body yêu cầu rỗng (nếu API không yêu cầu dữ liệu cụ thể trong body)
                    {
                        headers: {
                            Authorization: `Bearer ${token}`, // Gắn token vào header Authorization để xác thực
                        },
                    }
                );

                // Sau khi bỏ quyền thành công, ẩn modal xác nhận
                const modalEl = document.getElementById("revokeConfirmModal");
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                modalInstance.hide();

                this.selectedUser = null; // Đặt lại người dùng đã chọn về null
                this.fetchUsers(); // Gọi lại fetchUsers để làm mới danh sách người dùng và cập nhật vai trò

                this.toastType = "success"; // Đặt loại thông báo là thành công
                this.toastMessage = "✅ Đã bỏ quyền giáo viên."; // Đặt nội dung thông báo thành công
            } catch (error) {
                console.error("❌ Lỗi khi bỏ quyền:", error); // Ghi lỗi ra console nếu có vấn đề
                this.toastType = "error"; // Đặt loại thông báo là thất bại
                this.toastMessage = "❌ Bỏ quyền thất bại."; // Đặt nội dung thông báo thất bại
            } finally {
                // Sau 3 giây, tự động ẩn Toast thông báo
                setTimeout(() => {
                    this.toastMessage = "";
                }, 3000);
            }
        },

        // Phương thức mở modal xem chi tiết thông tin người dùng
        viewUser(user) {
            this.selectedUser = user; // Gán người dùng được chọn vào biến selectedUser

            // Khởi tạo và hiển thị modal "Thông tin người dùng" sử dụng JavaScript của Bootstrap
            const modal = new window.bootstrap.Modal(
                document.getElementById("userDetailModal")
            );
            modal.show(); // Hiển thị modal
        },

        // Phương thức mở modal xác nhận bỏ quyền giáo viên
        openRevokeModal(user) {
            this.selectedUser = user; // Gán người dùng được chọn vào biến selectedUser
            // Khởi tạo và hiển thị modal "Xác nhận bỏ quyền" sử dụng JavaScript của Bootstrap
            const modal = new window.bootstrap.Modal(
                document.getElementById("revokeConfirmModal")
            );
            modal.show();
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

.table-fixed {
    table-layout: fixed;
    width: 100%;
    word-wrap: break-word;
}

.table-fixed th,
.table-fixed td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
