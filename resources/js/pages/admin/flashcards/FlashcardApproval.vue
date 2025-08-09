<template>
    <div class="container mt-4">
        <h4 class="text-center mb-4 text-dark">
            📝 Kiểm duyệt bộ flashcard công khai
        </h4>

        <!-- Tabs lọc trạng thái -->
        <ul class="nav nav-tabs mb-3 justify-content-center">
            <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ active: filterStatus === 'pending' }"
                    @click="setFilter('pending')"
                >
                    ⏳ Chờ duyệt
                </button>
            </li>
            <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ active: filterStatus === 'approved' }"
                    @click="setFilter('approved')"
                >
                    ✅ Đã duyệt
                </button>
            </li>
        </ul>

        <!-- Loading -->
        <div v-if="isLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Đang tải danh sách bộ flashcard...</p>
        </div>

        <!-- Danh sách -->
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
                        <th>Tiêu đề</th>
                        <th>Mô tả</th>
                        <th>Người tạo</th>
                        <th v-if="filterStatus === 'pending'">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(card, index) in paginatedFlashcards"
                        :key="card.id"
                    >
                        <td>{{ (currentPage - 1) * perPage + index + 1 }}</td>
                        <td>{{ card.title }}</td>
                        <td>{{ card.description }}</td>
                        <td>{{ card.author }}</td>
                        <td v-if="filterStatus === 'pending'">
                            <button
                                class="btn btn-sm btn-outline-primary me-2"
                                @click="openDetailModal(card.id)"
                            >
                                👁 Xem
                            </button>
                            <button
                                class="btn btn-sm btn-outline-success"
                                @click="approveFlashcard(card.id)"
                            >
                                ✅ Duyệt
                            </button>
                            <button
                                class="btn btn-sm btn-outline-danger ms-2"
                                @click="openDeleteModal(card)"
                            >
                                🗑️ Xoá
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Nếu không có flashcard nào -->
            <div
                v-if="!paginatedFlashcards.length && !isLoading"
                class="text-center py-4 text-muted"
            >
                Không có bộ flashcard nào thuộc trạng thái này.
            </div>

            <Pagination
                :total-pages="totalPages"
                :current-page="currentPage"
                @page-change="changePage"
            />
        </div>

        <!-- Modal xác nhận xoá -->
        <div
            class="modal fade"
            id="deleteConfirmModal"
            tabindex="-1"
            aria-labelledby="deleteConfirmModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteConfirmModalLabel">
                            Xác nhận xoá
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
                            Bạn có chắc chắn muốn xoá bộ flashcard
                            <strong class="text-danger"
                                >"{{ selectedCard?.title }}"</strong
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
                            @click="confirmDelete"
                        >
                            Xác nhận xoá
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast thông báo -->
        <div
            v-if="toastMessage"
            :class="[
                'toast align-items-center text-white border-0 position-fixed bottom-0 end-0 m-4 show',
                toastSuccess ? 'bg-success' : 'bg-danger',
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

        <!-- Modal xem chi tiết -->
        <div class="modal fade" id="detailModal" tabindex="-1">
            <div
                class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"
            >
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-card-list me-2"></i> Chi tiết bộ
                            flashcard
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>

                    <div class="modal-body" v-if="detailCard">
                        <!-- Thông tin chung -->
                        <div class="mb-4">
                            <h4 class="fw-semibold text-primary">
                                {{ detailCard.title }}
                            </h4>
                            <p class="text-muted">
                                {{ detailCard.description || "Không có mô tả" }}
                            </p>

                            <div class="row">
                                <div class="col-md-6">
                                    <p>
                                        <strong>Môn học:</strong>
                                        {{
                                            detailCard.subject ||
                                            "Chưa xác định"
                                        }}
                                    </p>
                                    <p>
                                        <strong>Chủ đề:</strong>
                                        {{
                                            detailCard.topic || "Chưa xác định"
                                        }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p>
                                        <strong>Người tạo:</strong>
                                        {{ detailCard.author || "Không rõ" }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <!-- Danh sách câu hỏi -->
                        <div
                            v-for="(c, idx) in detailCard.cards"
                            :key="idx"
                            class="mb-4 p-3 border rounded bg-light"
                        >
                            <p class="mb-2">
                                <strong>Câu hỏi {{ idx + 1 }}:</strong>
                                {{ c.question }}
                            </p>
                            <ul class="list-unstyled">
                                <li
                                    v-for="(a, ai) in c.answers"
                                    :key="ai"
                                    class="mb-1"
                                >
                                    <i
                                        class="bi bi-circle-fill me-1 text-secondary"
                                        style="font-size: 0.6rem"
                                    ></i>
                                    {{ a.content }}
                                    <span
                                        v-if="a.is_correct"
                                        class="badge bg-success ms-2"
                                        >Đúng</span
                                    >
                                </li>
                            </ul>
                            <div v-if="c.image" class="mt-2">
                                <img
                                    :src="c.image"
                                    class="img-thumbnail"
                                    style="max-width: 200px"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                        >
                            <i class="bi bi-x-circle"></i> Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
// Import component Pagination từ đường dẫn tương đối.
// Component này có thể được sử dụng để hiển thị các nút phân trang.
import Pagination from "../../../components/Pagination.vue";
// Import toàn bộ gói JavaScript của Bootstrap, bao gồm các plugin như modal, toast, v.v.
// Điều này cần thiết để sử dụng các tính năng UI của Bootstrap.
import "bootstrap/dist/js/bootstrap.bundle.min.js";

// Khai báo một đối tượng export mặc định, đây là cấu trúc của một Vue component.
export default {
    // Tên của component, hữu ích cho việc debug và quản lý component.
    name: "FlashcardModeration",
    // Đăng ký các component con được sử dụng trong component này.
    // Ở đây, chúng ta đăng ký component Pagination đã được import.
    components: { Pagination },

    // Hàm `data` trả về một đối tượng chứa các dữ liệu (state) của component.
    data() {
        return {
            flashcards: [], // Mảng chứa tất cả các flashcard được lấy từ API.
            currentPage: 1, // Số trang hiện tại đang hiển thị trong phân trang. Mặc định là trang 1.
            perPage: 8, // Số lượng flashcard hiển thị trên mỗi trang.
            isLoading: false, // Cờ hiệu cho biết trạng thái tải dữ liệu (true: đang tải, false: đã tải xong).
            toastMessage: "", // Nội dung tin nhắn sẽ hiển thị trong Toast (thông báo nhỏ).
            toastSuccess: true, // Cờ hiệu xác định loại thông báo Toast (true: thành công - màu xanh, false: thất bại - màu đỏ).
            selectedCard: null, // Lưu trữ flashcard đang được chọn để thực hiện hành động (ví dụ: xóa).
            filterStatus: "pending", // Trạng thái lọc flashcard hiện tại: 'pending' (chờ duyệt) hoặc 'approved' (đã duyệt).
            searchQuery: "", // Truy vấn tìm kiếm.
            detailCard: null,
        };
    },

    // Đối tượng `computed` chứa các thuộc tính được tính toán dựa trên dữ liệu hiện có.
    // Các thuộc tính này sẽ tự động cập nhật khi dữ liệu phụ thuộc thay đổi.
    computed: {
        totalPages() {
            // Tính tổng số trang cần thiết dựa trên tổng số flashcard và số flashcard trên mỗi trang.
            // Math.ceil() đảm bảo rằng chúng ta luôn có đủ trang, kể cả khi có flashcard lẻ.
            return Math.ceil(this.flashcards.length / this.perPage);
        },
        filteredFlashcards() {
            if (!this.searchQuery) return this.flashcards;
            const q = this.searchQuery.toLowerCase();
            return this.flashcards.filter(
                (card) =>
                    card.title.toLowerCase().includes(q) ||
                    card.description.toLowerCase().includes(q) ||
                    (card.author && card.author.toLowerCase().includes(q))
            );
        },
        paginatedFlashcards() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredFlashcards.slice(start, start + this.perPage);
        },
    },

    // Đối tượng `methods` chứa các hàm (phương thức) của component.
    methods: {
        changePage(page) {
            // Phương thức này được gọi khi người dùng muốn chuyển trang.
            // Kiểm tra xem số trang mới có hợp lệ không (trong khoảng từ 1 đến tổng số trang).
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page; // Cập nhật trang hiện tại.
            }
        },

        setFilter(status) {
            // Phương thức này được gọi khi người dùng thay đổi bộ lọc trạng thái flashcard.
            this.filterStatus = status; // Cập nhật trạng thái lọc.
            this.fetchFlashcards(); // Gọi lại API để lấy flashcard theo trạng thái mới.
        },

        async fetchFlashcards() {
            // Phương thức bất đồng bộ để gọi API lấy danh sách flashcard.
            this.isLoading = true; // Đặt cờ `isLoading` thành true để hiển thị trạng thái tải.
            try {
                // Lấy token xác thực của admin từ Local Storage.
                const token = localStorage.getItem("admin_token");
                // Gửi yêu cầu GET đến API `/api/admin/flashcards` với tham số `status` để lọc.
                const response = await fetch(
                    `/api/admin/flashcards?status=${this.filterStatus}`,
                    {
                        headers: {
                            // Gửi token xác thực trong header Authorization.
                            Authorization: `Bearer ${token}`,
                            // Yêu cầu phản hồi dưới dạng JSON.
                            Accept: "application/json",
                        },
                    }
                );

                // Kiểm tra nếu phản hồi không thành công (ví dụ: mã trạng thái 4xx hoặc 5xx).
                if (!response.ok) throw new Error("Lỗi khi tải dữ liệu.");
                // Chuyển đổi phản hồi JSON thành đối tượng JavaScript và gán vào `flashcards`.
                this.flashcards = await response.json();
            } catch (error) {
                // Xử lý lỗi nếu có vấn đề trong quá trình gọi API.
                console.error("❌ Lỗi:", error); // Ghi lỗi ra console.
                this.toastMessage = "Không thể tải dữ liệu flashcard."; // Đặt thông báo lỗi.
                this.toastSuccess = false; // Đặt cờ `toastSuccess` thành false để hiển thị toast màu đỏ.
            } finally {
                // Khối `finally` luôn được thực thi sau `try` hoặc `catch`.
                this.isLoading = false; // Đặt cờ `isLoading` về false.
                // Đặt hẹn giờ để ẩn thông báo toast sau 3 giây.
                setTimeout(() => (this.toastMessage = ""), 3000);
            }
        },

        async approveFlashcard(id) {
            // Phương thức bất đồng bộ để duyệt một flashcard.
            try {
                const token = localStorage.getItem("admin_token"); // Lấy token.
                // Gửi yêu cầu POST đến API để duyệt flashcard với ID cụ thể.
                const res = await fetch(`/api/admin/flashcards/${id}/approve`, {
                    method: "POST", // Phương thức HTTP là POST.
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                });

                const data = await res.json(); // Lấy phản hồi JSON từ server.
                this.toastMessage = data.message || "✅ Đã duyệt flashcard."; // Đặt thông báo thành công.
                this.toastSuccess = true; // Đặt cờ `toastSuccess` thành true.

                // Lọc bỏ flashcard đã duyệt khỏi danh sách hiển thị ngay lập tức.
                this.flashcards = this.flashcards.filter(
                    (card) => card.id !== id
                );
            } catch (err) {
                // Xử lý lỗi nếu duyệt flashcard thất bại.
                this.toastMessage = "❌ Duyệt flashcard thất bại.";
                this.toastSuccess = false;
            } finally {
                // Đặt hẹn giờ để ẩn thông báo toast sau 3 giây.
                setTimeout(() => (this.toastMessage = ""), 3000);
            }
        },

        openDeleteModal(card) {
            // Phương thức để mở modal xác nhận xóa flashcard.
            this.selectedCard = card; // Lưu flashcard được chọn vào `selectedCard`.
            // Tạo một đối tượng Modal của Bootstrap và hiển thị nó.
            const modal = new bootstrap.Modal(
                document.getElementById("deleteConfirmModal")
            );
            modal.show();
        },

        async confirmDelete() {
            // Phương thức bất đồng bộ để xác nhận và thực hiện việc xóa flashcard.
            try {
                const token = localStorage.getItem("admin_token"); // Lấy token.
                // Gửi yêu cầu DELETE đến API để xóa flashcard với ID của `selectedCard`.
                const res = await fetch(
                    `/api/admin/flashcards/${this.selectedCard.id}`,
                    {
                        method: "DELETE", // Phương thức HTTP là DELETE.
                        headers: {
                            Authorization: `Bearer ${token}`,
                            Accept: "application/json",
                        },
                    }
                );

                const data = await res.json(); // Lấy phản hồi JSON từ server.
                this.toastMessage = data.message || "🗑️ Đã xoá flashcard."; // Đặt thông báo thành công.
                this.toastSuccess = true; // Đặt cờ `toastSuccess` thành true.

                // Lọc bỏ flashcard đã xóa khỏi danh sách hiển thị.
                this.flashcards = this.flashcards.filter(
                    (card) => card.id !== this.selectedCard.id
                );
                this.selectedCard = null; // Đặt lại `selectedCard` về null.

                // Đóng modal xác nhận xóa.
                const modalEl = document.getElementById("deleteConfirmModal");
                const modalInstance = bootstrap.Modal.getInstance(modalEl); // Lấy instance của modal.
                modalInstance.hide(); // Ẩn modal.
            } catch (err) {
                // Xử lý lỗi nếu xóa flashcard thất bại.
                this.toastMessage = "❌ Xoá flashcard thất bại.";
                this.toastSuccess = false;
            } finally {
                // Đặt hẹn giờ để ẩn thông báo toast sau 3 giây.
                setTimeout(() => (this.toastMessage = ""), 3000);
            }
        },

        async openDetailModal(id) {
            try {
                const token = localStorage.getItem("admin_token");
                const res = await fetch(`/api/admin/flashcards/${id}/detail`, {
                    headers: { Authorization: `Bearer ${token}` },
                });
                if (!res.ok) throw new Error();
                this.detailCard = await res.json();

                const modal = new bootstrap.Modal(
                    document.getElementById("detailModal")
                );
                modal.show();
            } catch (e) {
                this.toastMessage = "Không thể tải chi tiết flashcard.";
                this.toastSuccess = false;
            }
        },
    },

    // Lifecycle hook `mounted` được gọi sau khi component được gắn vào DOM.
    mounted() {
        // Khi component được mount, gọi phương thức `fetchFlashcards` để tải dữ liệu ban đầu.
        this.fetchFlashcards();
    },
};
</script>

<style scoped>
.toast {
    z-index: 2000;
}
</style>
