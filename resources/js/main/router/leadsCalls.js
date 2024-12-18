import LeadIndex from '../views/leads-calls/leads/index.vue';
import CallLogs from '../views/leads-calls/call-logs/index.vue';
import LeadNotes from '../views/leads-calls/notes/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/leads',
                component: LeadIndex,
                name: 'admin.leads.index',
                meta: {
                    requireAuth: true,
                    menuParent: "leads",
                    menuKey: "leads",
                }
            },
            {
                path: '/admin/call-logs',
                component: CallLogs,
                name: 'admin.call_logs.index',
                meta: {
                    requireAuth: true,
                    menuParent: "leads",
                    menuKey: "call_logs",
                }
            },
            {
                path: '/admin/notes',
                component: LeadNotes,
                name: 'admin.lead_notes.index',
                meta: {
                    requireAuth: true,
                    menuParent: "leads",
                    menuKey: "lead_notes",
                }
            },
        ]

    }
]
