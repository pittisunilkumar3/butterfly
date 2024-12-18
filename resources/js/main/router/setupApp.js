import SetupAdminApp from "../views/SetupAdminApp.vue";

export default [
    {
        path: '/admin/setup',
        component: SetupAdminApp,
        name: 'admin.setup_app.index',
        meta: {
            requireAuth: true,
            menuParent: "",
            menuKey: "setup_company",
        }

    }
]
