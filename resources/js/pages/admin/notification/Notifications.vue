<template>
    <div class="container mt-5">
        <h3 class="text-center text-primary fw-bold mb-4">
            📨 Gửi thông báo hệ thống
        </h3>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 p-4">
                    <form @submit.prevent="sendNotification">
                        <div class="mb-3">
                            <label class="form-label text-dark fw-semibold"
                                >Tiêu đề</label
                            >
                            <input
                                type="text"
                                class="form-control rounded-3"
                                v-model="title"
                                placeholder="Nhập tiêu đề"
                                required
                            />
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark fw-semibold"
                                >Nội dung</label
                            >
                            <textarea
                                class="form-control rounded-3"
                                rows="5"
                                v-model="message"
                                placeholder="Nhập nội dung chi tiết"
                                required
                            ></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-paper-plane me-2"></i> Gửi
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-5">
                    <div
                        class="d-flex justify-content-between align-items-center mb-2"
                    >
                        <h5 class="text-dark fw-semibold m-0">
                            📋 Danh sách thông báo đã gửi
                        </h5>
                        <button
                            class="btn btn-sm btn-outline-danger"
                            @click="deleteAllNotifications"
                            :disabled="notifications.length === 0"
                        >
                            <i class="fas fa-trash-alt me-1"></i> Xoá tất cả
                        </button>
                    </div>

                    <ul class="list-group shadow-sm rounded-3 overflow-hidden">
                        <li
                            v-for="notification in notifications"
                            :key="notification.id"
                            class="list-group-item d-flex justify-content-between align-items-start"
                        >
                            <div>
                                <div class="fw-semibold">
                                    {{ notification.title }}
                                </div>
                                <div class="text-muted small">
                                    {{ notification.message }}
                                </div>
                                <div class="text-muted small text-end">
                                    🕒 {{ formatDate(notification.created_at) }}
                                </div>
                            </div>
                            <button
                                class="btn btn-sm btn-outline-danger ms-2"
                                @click="deleteNotification(notification.id)"
                                title="Xoá"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </li>
                        <li
                            v-if="notifications.length === 0"
                            class="list-group-item text-center text-muted"
                        >
                            Không có thông báo nào.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ✅ Toast -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div
                ref="toast"
                class="toast align-items-center text-white bg-success border-0"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
            >
                <div class="d-flex">
                    <div class="toast-body">{{ toastMessage }}</div>
                    <button
                        type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"
                        aria-label="Close"
                    ></button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios"; // Gọi HTTP request
import { Toast } from "bootstrap"; // Import Toast từ Bootstrap 5

export default {
    name: "SendNotification",
    data() {
        return {
            title: "", // Tiêu đề thông báo
            message: "", // Nội dung thông báo
            toastMessage: "", // Nội dung hiển thị trong Toast
            notifications: [], // Danh sách các thông báo đã gửi
        };
    },
    methods: {
        // Gửi thông báo mới
        async sendNotification() {
            try {
                const token = localStorage.getItem("admin_token"); // Lấy token xác thực
                const res = await axios.post(
                    "/api/admin/notifications", // Gửi POST đến API
                    {
                        title: this.title,
                        message: this.message,
                    },
                    { headers: { Authorization: `Bearer ${token}` } }
                );

                this.toastMessage = res.data.message || "✅ Đã gửi thông báo"; // Gán nội dung cho Toast
                this.title = "";
                this.message = "";
                this.showToast(); // Hiển thị Toast
                this.loadNotifications(); // Reload lại danh sách thông báo
            } catch (err) {
                alert("❌ Không thể gửi thông báo.");
            }
        },

        // Tải danh sách thông báo từ API
        async loadNotifications() {
            const token = localStorage.getItem("admin_token");
            const res = await axios.get("/api/admin/notifications", {
                headers: { Authorization: `Bearer ${token}` },
            });
            this.notifications = res.data || [];
        },

        // Xoá thông báo theo id
        async deleteNotification(id) {
            const token = localStorage.getItem("admin_token");
            try {
                await axios.delete(`/api/admin/notifications/${id}`, {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.toastMessage = "🗑️ Đã xoá thông báo";
                this.showToast(); // Hiện Toast
                this.loadNotifications(); // Cập nhật danh sách
            } catch (err) {
                alert("❌ Không thể xoá thông báo.");
            }
        },

        // Xoá tất cả thông báo
        async deleteAllNotifications() {
            const token = localStorage.getItem("admin_token");
            try {
                await axios.delete("/api/admin/notifications", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.toastMessage = "🗑️ Đã xoá tất cả thông báo";
                this.showToast();
                this.loadNotifications();
            } catch (err) {
                alert("❌ Không thể xoá tất cả.");
            }
        },

        // Hiển thị Toast bootstrap
        showToast() {
            const toast = new Toast(this.$refs.toast); // Tạo toast từ phần tử ref="toast"
            toast.show();
        },

        // Định dạng ngày giờ cho tiếng Việt
        formatDate(dateStr) {
            const d = new Date(dateStr);
            return d.toLocaleString("vi-VN");
        },
    },

    // Khi component mount thì tự động tải danh sách thông báo
    mounted() {
        this.loadNotifications();
    },
};
</script>

<style scoped>
.card {
    border-radius: 10px;
}
textarea {
    resize: vertical;
}
.toast-container {
    z-index: 2000;
}
</style>
