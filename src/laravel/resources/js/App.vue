<script>
import {useCart} from '@/composables/useCart'

export default {
    name: 'App',

    setup() {
        const {cartTotalPrice, fetchCart} = useCart()
        fetchCart()
        return {cartTotalPrice, fetchCart}
    },

    data() {
        return {
            token: null,
            user: null,
        }
    },

    mounted() {
        this.getToken()
        this.getUser()
    },

    watch: {
        $route(to, from) {
            this.getToken()
            this.getUser()
            this.fetchCart()
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
        getUser() {
            if (this.token) {
                axios.get('/api/v1/user')
                    .then(res => {
                        this.user = res.data.data
                    })
            }
        }
    }
}
</script>
<template>
    <div v-if="!token">
        <router-link :to="{ name: 'user.login' }">Войти</router-link>
        <router-link :to="{ name: 'user.register' }">Регистрация</router-link>
    </div>
    <div v-if="token">
        <div v-if="user">{{ user.name }}</div>
        <a href="#" @click.prevent="logout">Выйти</a>
    </div>

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
