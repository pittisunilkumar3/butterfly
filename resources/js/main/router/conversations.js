import ConversationIndex from '../views/conversations/index.vue';
import ConversationShow from '../views/conversations/Show.vue';

export default [
    {
        path: '/admin/conversations',
        component: ConversationIndex,
        name: 'admin.conversations.index',
        meta: {
            requireAuth: true,
            menuParent: "communication",
            menuKey: "conversations",
            permissions: ['*'] // Allow all authenticated users
        }
    },
    {
        path: '/admin/conversations/:id',
        component: ConversationShow,
        name: 'admin.conversations.show',
        meta: {
            requireAuth: true,
            menuParent: "communication",
            menuKey: "conversations",
            permissions: ['*']
        }
    }
]
