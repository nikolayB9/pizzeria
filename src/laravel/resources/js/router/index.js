import { createWebHistory, createRouter } from 'vue-router'

const routes = [
    {
        path: '/',
        component: () => import('../components/Main/Index.vue'),
        name: 'main.index'
    },
    {
        path: '/category/:categorySlug',
        component: () => import('../components/Main/Index.vue'),
        name: 'category.products'
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
    {
        path: '/register',
        component: () => import('../components/User/Register.vue'),
        name: 'user.register'
    },
    {
        path: '/cart',
        component: () => import('../components/Cart/Index.vue'),
        name: 'cart.index'
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router
