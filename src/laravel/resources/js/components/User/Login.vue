<script>
import {useCart} from '@/composables/useCart'

export default {
    name: "Login",

    data() {
        return {
            email: null,
            password: null,
            errors: [],
        }
    },

    setup() {
        const {fetchCart} = useCart()
        return {fetchCart}
    },

    computed: {
        isDisabled() {
            return this.email && this.password
        }
    },

    methods: {
        login() {
            axios.get('/sanctum/csrf-cookie')
                .then(response => {
                    axios.post('api/v1/login', {
                        email: this.email,
                        password: this.password,
                    })
                        .then(res => {
                            localStorage.setItem('x_xsrf_token', res.config.headers['X-XSRF-TOKEN'])
                            this.fetchCart()
                            this.$router.go(-1)
                        })
                        .catch(err => {
                            console.error('Ошибка входа', err);
                        })
                })
        },
    }
}
</script>

<template>
    <div class="login-container">
        <h1>Войти</h1>

        <input type="email"
               v-model="email"
               required
               placeholder="Введите email">

        <input type="password"
               v-model="password"
               required
               placeholder="Введите пароль">

        <input @click.prevent="login"
               :disabled="!isDisabled"
               type="submit"
               value="Войти">
    </div>
</template>

<style scoped>
.login-container {
    max-width: 400px;
    margin: 80px auto;
    padding: 30px;
    border-radius: 12px;
    background-color: #f9f9f9;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    gap: 16px;
}

h1 {
    text-align: center;
    margin-bottom: 10px;
    font-size: 24px;
}

input[type="email"],
input[type="password"],
input[type="submit"] {
    padding: 12px;
    font-size: 16px;
    border-radius: 8px;
    border: 1px solid #ccc;
    transition: border-color 0.2s, box-shadow 0.2s;
}

input[type="email"]:focus,
input[type="password"]:focus {
    outline: none;
    border-color: #66afe9;
    box-shadow: 0 0 6px rgba(102, 175, 233, 0.6);
}

input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    font-weight: bold;
    cursor: pointer;
    border: none;
}

input[type="submit"]:disabled {
    background-color: #9e9e9e;
    cursor: not-allowed;
}
</style>

