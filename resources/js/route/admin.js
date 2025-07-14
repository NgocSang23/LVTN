const admin = [
    {
        path: "/admin",
        name: "admin",
        component: () => import("../layouts/Admin.vue"),
        redirect: () => {
            const isLoggedIn =
                localStorage.getItem("admin_logged_in") === "true";
            return isLoggedIn ? "/admin/user" : "/admin/login";
        },
        children: [
            {
                path: "user",
                name: "admin-user",
                component: () =>
                    import("../pages/admin/user/UserManagement.vue"),
            },
            {
                path: "assign-role",
                name: "admin-assign-role",
                component: () => import("../pages/admin/user/AssignRole.vue"),
            },
            {
                path: "flashcards",
                name: "admin-flashcards",
                component: () =>
                    import("../pages/admin/flashcards/FlashcardApproval.vue"),
            },
            {
                path: "statistics",
                name: "admin-statistics",
                component: () =>
                    import("../pages/admin/statistics/Statistics.vue"),
            },
            {
                path: "notifications",
                name: "admin-notifications",
                component: () =>
                    import("../pages/admin/notification/Notifications.vue"),
            },
        ],
    },
    {
        path: "/admin/login",
        name: "admin-login",
        component: () => import("../pages/admin/login/Login.vue"),
    },
];

export default admin;
