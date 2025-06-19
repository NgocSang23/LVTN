<template>
    <div class="container mt-4">
        <h3>Gán quyền giáo viên</h3>
        <table class="table table-bordered mt-3">
            <thead>
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
                    <td>{{ user.role }}</td>
                    <td>
                        <button class="btn btn-sm btn-success">
                            Gán giáo viên
                        </button>
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
    name: "AssignRole",
    components: {
        Pagination,
    },
    data() {
        return {
            users: [
                {
                    id: 1,
                    name: "Lê Văn C",
                    email: "c@example.com",
                    role: "Học sinh",
                },
                {
                    id: 2,
                    name: "Nguyễn Văn A",
                    email: "a@example.com",
                    role: "Học sinh",
                },
                {
                    id: 3,
                    name: "Trần Thị B",
                    email: "b@example.com",
                    role: "Học sinh",
                },
                {
                    id: 4,
                    name: "Phạm Văn D",
                    email: "d@example.com",
                    role: "Học sinh",
                },
                {
                    id: 5,
                    name: "Đỗ Thị E",
                    email: "e@example.com",
                    role: "Học sinh",
                },
                {
                    id: 6,
                    name: "Lý Văn F",
                    email: "f@example.com",
                    role: "Học sinh",
                },
                {
                    id: 7,
                    name: "Ngô Thị G",
                    email: "g@example.com",
                    role: "Học sinh",
                },
                {
                    id: 8,
                    name: "Hoàng Văn H",
                    email: "h@example.com",
                    role: "Học sinh",
                },
                {
                    id: 9,
                    name: "Lê Văn C",
                    email: "c@example.com",
                    role: "Học sinh",
                },
                {
                    id: 10,
                    name: "Nguyễn Văn A",
                    email: "a@example.com",
                    role: "Học sinh",
                },
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
