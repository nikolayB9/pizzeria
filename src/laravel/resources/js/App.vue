<script>
import { useCart } from '@/composables/useCart'

export default {
    name: 'App',

    setup() {
        const { cartTotalPrice, fetchCart } = useCart()
        return { cartTotalPrice }
    },

    data() {
        return {
            token: null,
        }
    },

    mounted() {
        this.getToken()
    },

    watch: {
        $route(to, from) {
            this.getToken()
        }
    },

    methods: {
        getToken() {
            this.token = localStorage.getItem('x_xsrf_token')
        },
        logout() {
            axios.post('/api/v1/logout')
                .then(res => {
                    localStorage.removeItem('x_xsrf_token')
                    this.token = null
                    this.$router.push({name: 'user.login'})
                })
        },
    }
}
</script>
<template>
    <div v-if="!token">
        <router-link :to="{ name: 'user.login' }">Войти</router-link>
        <router-link :to="{ name: 'user.register' }">Регистрация</router-link>
    </div>
    <div v-if="token"><a href="#" @click.prevent="logout">Выйти</a></div>

    <div>
        <router-link :to="{ name: 'product.index' }">Главная</router-link>
    </div>
    <div v-if="cartTotalPrice">Корзина: {{ cartTotalPrice }} ₽</div>

    <div>
        <router-view></router-view>
    </div>
</template>
<style scoped>

</style>
