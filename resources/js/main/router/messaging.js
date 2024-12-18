import EmailTemplateIndex from '../views/messaging/email-templates/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/email-templates',
                component: EmailTemplateIndex,
                name: 'admin.email_templates.index',
                meta: {
                    requireAuth: true,
                    menuParent: "messaging",
                    menuKey: "email_templates",
                    permission: "email_templates_view"
                }
            },
        ]

    }
]
