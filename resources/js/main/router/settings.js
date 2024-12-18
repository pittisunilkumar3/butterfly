import CompanyEdit from '../views/settings/company/Edit.vue';
import ProfileEdit from '../views/settings/profile/Edit.vue';
import Langs from '../views/settings/translations/langs/index.vue';
import Roles from '../views/settings/roles/index.vue';
import Currencies from '../views/settings/currency/index.vue';
import CommonAdminSettings from "./common/adminSettings";

export default [
    {
        path: '/admin/settings/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: 'company',
                component: CompanyEdit,
                name: 'admin.settings.company.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "company",
                    permission: "companies_edit"
                }
            },
            {
                path: 'profile',
                component: ProfileEdit,
                name: 'admin.settings.profile.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "profile",
                }
            },
            {
                path: 'langs',
                component: Langs,
                name: 'admin.settings.langs.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "translations",
                    permission: "translations_view"
                }
            },
            {
                path: 'roles',
                component: Roles,
                name: 'admin.settings.roles.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "roles",
                    permission: "roles_view"
                }
            },
            {
                path: 'currencies',
                component: Currencies,
                name: 'admin.settings.currencies.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "currencies",
                    permission: "currencies_view"
                }
            },

            ...CommonAdminSettings,
        ]

    }
]
