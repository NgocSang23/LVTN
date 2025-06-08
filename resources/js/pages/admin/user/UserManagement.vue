<template>
    <div class="container mt-4">
        <h3>Duyệt / Xoá người dùng</h3>
        <table class="table table-hover mt-3">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(user, index) in users" :key="user.id">
                    <td>{{ index + 1 }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                        <button class="btn btn-sm btn-danger">Xoá</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: "UserManagement",
    data() {
        return {
            users: [
                { id: 1, name: "Nguyễn Văn A", email: "a@example.com" },
                { id: 2, name: "Trần Thị B", email: "b@example.com" },
            ],
        };
    },
};
</script>
