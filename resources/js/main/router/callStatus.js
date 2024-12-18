import CallStatusIndex from '../views/call-status/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/call-status',
                component: CallStatusIndex,
                name: 'admin.call_status.index',
                meta: {
                    requireAuth: true,
                    menuParent: "call_status",
                    menuKey: "call_status",
                    permission: "call_status_view"
                }
            },
        ]

    }
]
