<script>
import {useCart} from '@/composables/useCart'

export default {
    name: 'App',

    setup() {
        const {cartTotalPrice, fetchCart} = useCart()
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
        this.fetchCart()
    },

    watch: {
        $route(to, from) {
            this.getToken()
            this.getUser()
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
    <header class="navbar">
        <div class="navbar-left">
            <router-link class="nav-link" :to="{ name: 'product.index' }">Главная</router-link>
            <router-link v-if="!token" class="nav-link" :to="{ name: 'user.login' }">Войти</router-link>
            <router-link v-if="!token" class="nav-link" :to="{ name: 'user.register' }">Регистрация</router-link>
        </div>

        <div class="navbar-right" v-if="token">
            <span class="username" v-if="user">{{ user.name }}</span>
            <a href="#" class="nav-link" @click.prevent="logout">Выйти</a>
        </div>

        <div class="navbar-cart" v-if="cartTotalPrice">
            <router-link class="nav-link cart-link" :to="{ name: 'cart.index'}">
                🛒 {{ cartTotalPrice }} ₽
            </router-link>
        </div>
    </header>

    <main class="content">
        <router-view></router-view>
    </main>
</template>


<style scoped>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
    padding: 1rem 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    flex-wrap: wrap;
    gap: 1rem;
}

.navbar-left,
.navbar-right,
.navbar-cart {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.nav-link {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: color 0.2s;
}

.nav-link:hover {
    color: #007bff;
}

.cart-link {
    font-weight: bold;
    color: #28a745;
}

.username {
    font-weight: 600;
    color: #555;
}

.content {
    padding: 2rem;
}
</style>


