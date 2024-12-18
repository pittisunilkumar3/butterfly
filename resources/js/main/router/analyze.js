import AnalyticsIndex from '../views/analytics/Index.vue';
import LeadsIndex from '../views/analytics/Leads.vue';

export default [
    {
        path: '/admin/analytics',
        component: AnalyticsIndex,
        name: 'admin.analyze.index',
        meta: {
            requireAuth: true,
            menuParent: "analytics",
            menuKey: "analytics",
            permissions: ['admin', 'analytics_view']
        }
    },
    {
        path: '/admin/leads/:id',
        component: LeadsIndex,
        name: 'admin.leads.index',
        meta: {
            requireAuth: true,
            menuParent: "analytics",
            menuKey: "analytics",
            permissions: ['admin', 'analytics_view']
        }
    }
];
