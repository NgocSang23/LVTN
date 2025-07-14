<template>
    <div
        class="d-flex justify-content-center align-items-center min-vh-100 bg-light"
    >
        <div class="card p-4 shadow" style="min-width: 350px">
            <h3 class="text-center mb-3">Đăng nhập Quản trị</h3>
            <input
                v-model="email"
                placeholder="Email"
                class="form-control mb-2"
                autocomplete="email"
            />
            <input
                v-model="password"
                type="password"
                placeholder="Mật khẩu"
                class="form-control mb-3"
                autocomplete="current-password"
            />
            <button
                class="btn btn-primary w-100"
                @click="login"
                :disabled="loading"
                type="submit"
            >
                <span v-if="loading">Đang đăng nhập...</span>
                <span v-else>Đăng nhập</span>
            </button>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            email: "",
            password: "",
            loading: false,
        };
    },
    methods: {
        async login() {
            this.loading = true;
            try {
                await axios.get("/sanctum/csrf-cookie");

                const response = await axios.post("/admin/login", {
                    email: this.email,
                    password: this.password,
                });

                const user = response.data.user;
                const token = response.data.token;

                // ✅ Lưu cả token + user
                localStorage.setItem("admin_token", token);
                localStorage.setItem("admin_logged_in", "true");
                localStorage.setItem("admin", JSON.stringify(user)); // 👈 Quan trọng

                this.$router.push("/admin");
            } catch (error) {
                alert(
                    "❌ Đăng nhập thất bại: " +
                        (error.response?.data?.message || error.message)
                );
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
