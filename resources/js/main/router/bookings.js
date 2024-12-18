import LeadFollowUp from '../views/bookings/lead-follow-up/index.vue';
import SalesmanBooking from '../views/bookings/salesman-booking/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/bookings/lead-follow-up',
                component: LeadFollowUp,
                name: 'admin.bookings.lead_follow_up.index',
                meta: {
                    requireAuth: true,
                    menuParent: "lead_follow_up",
                    menuKey: "lead_follow_up",
                }
            },
            {
                path: '/admin/bookings/salesman-bookings',
                component: SalesmanBooking,
                name: 'admin.bookings.salesman_bookings.index',
                meta: {
                    requireAuth: true,
                    menuParent: "salesmans",
                    menuKey: "salesman_bookings",
                }
            },
        ]

    }
]
