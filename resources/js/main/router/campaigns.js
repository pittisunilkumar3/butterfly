import CampaignIndex from '../views/campaigns/index.vue';
import CallManagerIndex from '../views/call-manager/index.vue';
import CallManagerAction from '../views/call-manager/TakeLeadAction.vue';

export default [
    {
        path: '/admin/call-manager/:id',
        component: CallManagerAction,
        name: 'admin.call_manager.take_action',
        meta: {
            requireAuth: true,
            menuParent: "call_manager",
            menuKey: "call_manager",
        }
    },
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/call-manager',
                component: CallManagerIndex,
                name: 'admin.call_manager.index',
                meta: {
                    requireAuth: true,
                    menuParent: "call_manager",
                    menuKey: "call_manager",
                }
            },
            {
                path: '/admin/campaigns',
                component: CampaignIndex,
                name: 'admin.campaigns.index',
                meta: {
                    requireAuth: true,
                    menuParent: "campaigns",
                    menuKey: "campaigns",
                    permission: "campaigns_view"
                }
            },
        ]

    }
]
