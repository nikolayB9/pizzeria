import { createWebHistory, createRouter } from 'vue-router'

const routes = [
    {
        path: '/',
        component: () => import('../components/Product/Index.vue'),
        name: 'product.index'
    },
    {
        path: '/product/:product_slug',
        component: () => import('../components/Product/Show.vue'),
        name: 'product.show'
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router
