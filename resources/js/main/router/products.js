import Product from '../views/product/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/product',
                component: Product,
                name: 'admin.products.index',
                meta: {
                    requireAuth: true,
                    menuParent: "product",
                    menuKey: route => "products",
                }
            }
        ]

    }
]
