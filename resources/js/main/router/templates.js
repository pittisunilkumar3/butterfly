import TemplateIndex from '../views/templates/index.vue';

export default [
    {
        path: '/admin',
        children: [
            {
                path: '/admin/templates',
                component: TemplateIndex,
                name: 'admin.templates.index',
                meta: {
                    requireAuth: true,
                    menuParent: "communication",
                    menuKey: "templates",
                }
            }
        ]
    }
]
