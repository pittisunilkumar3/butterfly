import FormIndex from '../views/forms/forms/index.vue';
import FormFieldNameIndex from '../views/forms/form-field-names/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/forms',
                component: FormIndex,
                name: 'admin.forms.index',
                meta: {
                    requireAuth: true,
                    menuParent: "forms",
                    menuKey: "forms",
                    permission: "forms_view"
                }
            },
            {
                path: '/admin/form-field-names',
                component: FormFieldNameIndex,
                name: 'admin.form_field_names.index',
                meta: {
                    requireAuth: true,
                    menuParent: "form_field_names",
                    menuKey: "form_field_names",
                    permission: "form_field_names_view"
                }
            },
        ]

    }
]
