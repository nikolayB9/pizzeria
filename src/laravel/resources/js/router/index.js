import { createWebHistory, createRouter } from 'vue-router'

const routes = [
    {
        path: '/',
        component: () => import('../components/Product/Index.vue'),
        name: 'product.index'
    },
    {
        path: '/product/:productSlug',
        component: () => import('../components/Product/Show.vue'),
        name: 'product.show'
    },
    {
        path: '/login',
        component: () => import('../components/User/Login.vue'),
        name: 'user.login'
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router
