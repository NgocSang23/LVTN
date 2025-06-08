const admin = [
    {
        path: '/admin',
        name: 'admin',
        component: () => import('../layouts/Admin.vue'),
        children: [
            {
                path: 'user',
                name: 'admin-user',
                component: () => import('../pages/admin/user/UserManagement.vue'),
            },
            {
                path: 'assign-role',
                name: 'admin-assign-role',
                component: () => import('../pages/admin/user/AssignRole.vue'),
            },
            // {
            //     path: 'flashcards',
            //     name: 'admin-flashcards',
            //     component: () => import('../pages/admin/flashcard/FlashcardApproval.vue'),
            // },
            // {
            //     path: 'statistics',
            //     name: 'admin-statistics',
            //     component: () => import('../pages/admin/statistics/Statistics.vue'),
            // },
            // {
            //     path: 'notifications',
            //     name: 'admin-notifications',
            //     component: () => import('../pages/admin/notification/Notifications.vue'),
            // }
        ]
    }
];


export default admin