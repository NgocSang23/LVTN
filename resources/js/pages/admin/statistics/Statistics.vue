<template>
    <div class="container mt-4">
        <h3 class="mb-4 text-center text-dark">📊 Thống kê hệ thống</h3>

        <div v-if="isLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-2 text-muted">Đang tải dữ liệu thống kê...</p>
        </div>

        <div
            v-else-if="error"
            class="alert alert-danger text-center"
            role="alert"
        >
            <i class="fas fa-exclamation-circle me-2"></i> {{ error }}
        </div>

        <div v-else class="row g-3">
            <div
                class="col-sm-6 col-md-4"
                v-for="stat in stats"
                :key="stat.label"
            >
                <div class="card border shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3 text-muted">
                            <i :class="[stat.icon, 'fa-lg']"></i>
                        </div>
                        <div>
                            <p class="mb-1 text-muted small">
                                {{ stat.label }}
                            </p>
                            <h5 class="mb-0 fw-bold">
                                {{ stat.value.toLocaleString() }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ Đã thêm: Biểu đồ tần suất -->
        <h5 class="mt-5 text-center text-dark">
            📈 Tần suất ôn tập (7 ngày gần nhất)
        </h5>
        <div class="card p-3 shadow-sm">
            <canvas id="reviewChart"></canvas>
        </div>
    </div>
</template>

<script>
import axios from "axios"; // Import thư viện Axios để thực hiện các yêu cầu HTTP (gọi API)
import Chart from "chart.js/auto"; // ✅ Đã thêm: Import Chart.js

export default {
    name: "Statistics", // Đặt tên cho component Vue này là "Statistics"

    data() {
        // Hàm `data` trả về một đối tượng chứa các dữ liệu reactive của component.
        // Bất kỳ sự thay đổi nào đối với các thuộc tính trong đối tượng này sẽ kích hoạt việc cập nhật giao diện.
        return {
            stats: [], // Một mảng rỗng để lưu trữ dữ liệu thống kê nhận được từ API. Mỗi phần tử sẽ là một đối tượng { label, value, icon }.
            isLoading: true, // Một biến boolean dùng làm cờ để theo dõi trạng thái tải dữ liệu. Ban đầu là `true` để hiển thị trạng thái loading khi component được tạo.
            error: null, // Một biến để lưu trữ thông báo lỗi nếu có vấn đề xảy ra trong quá trình gọi API. Ban đầu là `null`.
            reviewFrequency: [], // ✅ Đã thêm: Dữ liệu tần suất review
            chartInstance: null, // ✅ Đã thêm: Chart instance
        };
    },

    methods: {
        // Khối `methods` chứa các hàm (phương thức) mà component có thể thực hiện.
        async fetchStatistics() {
            // Đây là một phương thức bất đồng bộ (async) để tải dữ liệu thống kê từ API.
            // Sử dụng `async/await` giúp code dễ đọc và quản lý các thao tác bất đồng bộ hơn.

            // Reset trạng thái lỗi và loading khi bắt đầu fetch dữ liệu mới.
            this.isLoading = true; // Bắt đầu quá trình tải, đặt `isLoading` thành `true` để hiển thị spinner hoặc thông báo loading.
            this.error = null; // Đặt lại biến `error` về `null` để xóa bất kỳ thông báo lỗi nào từ lần trước.

            try {
                // Khối `try` chứa code có khả năng gây ra lỗi.
                const token = localStorage.getItem("admin_token"); // Lấy token xác thực (chẳng hạn như JWT) từ Local Storage của trình duyệt. Token này thường được yêu cầu để truy cập các API được bảo vệ.

                if (!token) {
                    // Nếu không tìm thấy token trong Local Storage, ném một lỗi.
                    throw new Error(
                        "Không có token xác thực. Vui lòng đăng nhập lại."
                    );
                }

                // Gửi yêu cầu GET đến endpoint API "/api/admin/statistics/overview".
                // Yêu cầu này sẽ lấy dữ liệu tổng quan về thống kê hệ thống từ backend.
                const res = await axios.get("/api/admin/statistics/overview", {
                    headers: {
                        // Thiết lập các header cho yêu cầu HTTP.
                        Authorization: `Bearer ${token}`, // Thêm token vào header `Authorization` với tiền tố "Bearer" để xác thực yêu cầu.
                    },
                });

                const data = res.data; // Trích xuất dữ liệu thực tế từ phản hồi của API (response body).

                // Cập nhật mảng `stats` với dữ liệu nhận được.
                // Mỗi đối tượng trong mảng `stats` biểu diễn một chỉ số thống kê cụ thể.
                this.stats = [
                    {
                        label: "Tổng người dùng", // Nhãn hiển thị cho chỉ số này.
                        value: data.users || 0, // Giá trị của chỉ số. Sử dụng `|| 0` để đảm bảo giá trị là 0 nếu `data.users` là null/undefined.
                        icon: "fas fa-users", // Class của icon Font Awesome tương ứng.
                    },
                    {
                        label: "Số lượt ôn tập",
                        value: data.totalReviews || 0,
                        icon: "fas fa-book-reader",
                    },
                    {
                        label: "Tổng flashcard",
                        value: data.cards || 0,
                        icon: "fas fa-clone",
                    },
                    {
                        label: "Bộ flashcard chia sẻ",
                        value: data.flashcard_sets || 0,
                        icon: "fas fa-layer-group",
                    },
                    {
                        label: "Flashcard trong bộ",
                        value: data.cards_in_sets || 0,
                        icon: "fas fa-box",
                    },
                    {
                        label: "Flashcard chưa trong bộ",
                        value: data.cards_not_in_sets || 0,
                        icon: "fas fa-box-open",
                    },
                ];

                // ✅ Đã thêm: Gọi API lấy dữ liệu tần suất review
                const freqRes = await axios.get(
                    "/api/admin/statistics/review-frequency",
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    }
                );
                this.reviewFrequency = freqRes.data || [];
                this.renderReviewChart();
            } catch (err) {
                // Khối `catch` sẽ được thực thi nếu có bất kỳ lỗi nào xảy ra trong khối `try`.
                console.error("Lỗi khi lấy thống kê:", err); // Ghi log lỗi ra console để debug.

                // Đặt thông báo lỗi mặc định cho người dùng.
                this.error = "Không thể tải dữ liệu. Vui lòng thử lại sau.";

                // Kiểm tra cụ thể lỗi 401 (Unauthorized) để đưa ra thông báo phù hợp hơn.
                if (err.response?.status === 401) {
                    // `err.response` chứa thông tin phản hồi từ server nếu lỗi là do HTTP request.
                    // `?.status` là cú pháp optional chaining, đảm bảo không lỗi nếu `response` không tồn tại.
                    this.error =
                        "Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.";
                    // Trong ứng dụng thực tế, bạn có thể chuyển hướng người dùng đến trang đăng nhập ở đây.
                }
            } finally {
                // Khối `finally` luôn được thực thi sau `try` và `catch`, bất kể có lỗi hay không.
                this.isLoading = false; // Đặt `isLoading` thành `false` để ẩn spinner loading sau khi thao tác fetch hoàn tất.
            }
        },

        // ✅ Đã thêm: Hàm vẽ biểu đồ bằng Chart.js
        renderReviewChart() {
            // Lấy phần tử canvas có id là "reviewChart" từ DOM để vẽ biểu đồ lên đó
            const ctx = document.getElementById("reviewChart");

            // Nếu đã có biểu đồ (Chart instance) trước đó thì huỷ nó đi để tránh bị vẽ chồng biểu đồ cũ
            if (this.chartInstance) {
                this.chartInstance.destroy(); // Huỷ biểu đồ hiện tại
            }

            // Tạo mảng nhãn trục X từ dữ liệu reviewFrequency, mỗi phần tử là một ngày (yyyy-mm-dd)
            const labels = this.reviewFrequency.map((d) => d.date);

            // Tạo mảng dữ liệu trục Y là số lượt ôn tập ứng với từng ngày
            const data = this.reviewFrequency.map((d) => d.count);

            // Tạo biểu đồ mới và gán vào `chartInstance` để có thể quản lý hoặc huỷ lần sau
            this.chartInstance = new Chart(ctx, {
                type: "line", // Kiểu biểu đồ là đường (line chart)
                data: {
                    labels, // Trục X là danh sách ngày
                    datasets: [
                        {
                            label: "Lượt ôn tập", // Tên đường biểu đồ (hiện trong chú thích)
                            data, // Dữ liệu tương ứng theo ngày
                            backgroundColor: "rgba(54, 162, 235, 0.2)", // Màu nền dưới đường biểu đồ (fill)
                            borderColor: "rgba(54, 162, 235, 1)", // Màu đường biểu đồ
                            borderWidth: 2, // Độ dày đường kẻ
                            fill: true, // Có tô nền phía dưới đường không
                            tension: 0.4, // Độ cong của đường (0: gấp khúc, gần 1: cong mềm mại)
                        },
                    ],
                },
                options: {
                    responsive: true, // Biểu đồ phản hồi tốt khi thay đổi kích thước màn hình
                    maintainAspectRatio: false, // Không giữ nguyên tỉ lệ khung (cho phép chỉnh chiều cao)
                    scales: {
                        y: {
                            beginAtZero: true, // Bắt đầu trục Y từ 0
                            ticks: { stepSize: 1 }, // Bước nhảy của trục Y là 1 đơn vị (lượt ôn tập)
                        },
                    },
                },
            });
        },
    },

    mounted() {
        // `mounted` là một lifecycle hook của Vue, được gọi một lần sau khi component được gắn vào DOM (tức là đã hiển thị trên trình duyệt).
        // Đây là nơi lý tưởng để thực hiện các thao tác tải dữ liệu ban đầu.
        this.fetchStatistics(); // Gọi phương thức `fetchStatistics` để bắt đầu tải dữ liệu thống kê ngay khi component được tải.
    },
};
</script>

<style scoped>
.card {
    transition: none;
    border-radius: 10px;
}
.card .fa-lg {
    font-size: 1.5rem;
}
canvas {
    width: 100% !important;
    height: 300px !important;
}
/* ✅ Đã thêm: Chiều cao cho canvas */
</style>
