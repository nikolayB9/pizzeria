<script>
import {useCart} from '@/composables/useCart'

export default {
    name: 'App',

    setup() {
        const {cartTotalPrice, fetchCart, resetCartLocally} = useCart()
        return {cartTotalPrice, fetchCart, resetCartLocally}
    },

    data() {
        return {
            token: null,
            userName: null,
            showDropdown: false,
        }
    },

    mounted() {
        this.getToken()
        this.getUserPreview()
        this.fetchCart()
    },

    watch: {
        $route(to, from) {
            this.getToken()
            this.getUserPreview()
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
                    this.resetCartLocally()
                    this.token = null
                    this.$router.push({name: 'user.login'})
                })
        },
        getUserPreview() {
            if (this.token) {
                axios.get('/api/v1/profile/preview')
                    .then(res => {
                        this.userName = res.data.data.name
                    })
            }
        },
        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
        },
        closeDropdown() {
            setTimeout(() => {
                this.showDropdown = false;
            }, 150);
        },
    }
}
</script>
<template>
    <header class="navbar">
        <div class="navbar-left">
            <router-link class="nav-link" :to="{ name: 'main.index' }">Главная</router-link>
            <template v-if="token">
                <a href="#" class="nav-link" @click.prevent="logout">Выйти</a>
                <div class="user-menu" @click="toggleDropdown" @blur="closeDropdown" tabindex="0">
                    <span class="username" v-if="userName">{{ userName }}</span>

                    <div class="dropdown" v-if="showDropdown">
                        <router-link :to="{ name: 'user.show' }">Профиль</router-link>
                        <router-link :to="{ name: 'order.index' }">Мои заказы</router-link>
                        <router-link :to="{ name: 'address.index' }">Адреса доставки</router-link>
                    </div>
                </div>
            </template>
            <router-link v-if="!token" class="nav-link" :to="{ name: 'user.login' }">Войти</router-link>
            <router-link v-if="!token" class="nav-link" :to="{ name: 'user.register' }">Регистрация</router-link>
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
.user-menu {
    position: relative;
    display: inline-block;
    cursor: pointer;
    outline: none;
}

.username {
    font-weight: bold;
    padding: 6px 12px;
    background-color: #f0f0f0;
    border-radius: 6px;
    user-select: none;
}

.dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 6px;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    min-width: 200px;
    z-index: 1000;
    padding: 8px 0;
}

.dropdown a {
    display: block;
    padding: 8px 16px;
    text-decoration: none;
    color: #333;
    font-size: 0.9rem;
}

.dropdown a:hover {
    background-color: #f7f7f7;
}

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


