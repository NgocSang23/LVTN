<template>
    <div class="container mt-4">
        <h3>Duyệt / Xoá người dùng</h3>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in paginatedUsers" :key="user.id">
                    <td>{{ user.id }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                        <button class="btn btn-sm btn-danger">Xoá</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Phân trang cố định dưới phải -->
        <Pagination
            :total-pages="totalPages"
            :current-page="currentPage"
            @page-change="changePage"
        />
    </div>
</template>

<script>
import Pagination from "../../../components/Pagination.vue";

export default {
    name: "UserManagement",
    components: {
        Pagination,
    },
    data() {
        return {
            users: [
                { id: 1, name: "Nguyễn Văn A", email: "a@example.com" },
                { id: 2, name: "Trần Thị B", email: "b@example.com" },
                { id: 3, name: "Lê Văn C", email: "c@example.com" },
                { id: 4, name: "Phạm Văn D", email: "d@example.com" },
                { id: 5, name: "Đỗ Thị E", email: "e@example.com" },
                { id: 6, name: "Lý Văn F", email: "f@example.com" },
                { id: 7, name: "Ngô Thị G", email: "g@example.com" },
                { id: 8, name: "Hoàng Văn H", email: "h@example.com" },
                { id: 9, name: "Trần Thị I", email: "i@example.com" },
                { id: 10, name: "Lê Văn J", email: "j@example.com" },
            ],
            currentPage: 1,
            perPage: 8,
        };
    },
    computed: {
        totalPages() {
            return Math.ceil(this.users.length / this.perPage);
        },
        paginatedUsers() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.users.slice(start, start + this.perPage);
        },
    },
    methods: {
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
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
