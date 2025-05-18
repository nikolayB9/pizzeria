<script>
export default {
    name: "Register",

    data() {
        return {
            name: null,
            phone_number: null,
            email: null,
            password: null,
            password_confirmation: null,
            birth_date: null,
            errors: [],
        }
    },

    computed: {
        isDisabled() {
            return this.name && this.phone_number && this.email && this.password && this.password_confirmation
        }
    },

    methods: {
        register() {
            axios.get('/sanctum/csrf-cookie')
                .then(response => {
                    axios.post('api/v1/register', {
                        name: this.name,
                        phone_number: this.phone_number,
                        email: this.email,
                        birth_date: this.birth_date,
                        password: this.password,
                        password_confirmation: this.password_confirmation,
                    })
                        .then(res => {
                            localStorage.setItem('x_xsrf_token', res.config.headers['X-XSRF-TOKEN'])
                            this.$router.go(-1)
                            if (res.data.meta.cart_merge === false) {
                                alert('Не удалось перенести корзину после входа. Пожалуйста, проверьте её содержимое.')
                            }
                        })
                        .catch(err => {
                            console.error('Ошибка регистрации', err);
                        })
                })
        },
    }
}
</script>

<template>
    <div class="login-container">
        <h1>Регистрация</h1>

        <input type="text"
               v-model="name"
               required
               placeholder="Введите имя">

        <input type="text"
               v-model="phone_number"
               required
               placeholder="Номер телефона: +79091234567">

        <input type="email"
               v-model="email"
               required
               placeholder="Введите email">

        <input type="text"
               v-model="birth_date"
               placeholder="Дата рождения: 1990-01-01">

        <input type="password"
               v-model="password"
               required
               placeholder="Введите пароль">

        <input type="password"
               v-model="password_confirmation"
               required
               placeholder="Подтвердите пароль">

        <input @click.prevent="register"
               :disabled="!isDisabled"
               type="submit"
               value="Зарегистрироваться">
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
input[type="text"],
input[type="password"],
input[type="submit"] {
    padding: 12px;
    font-size: 16px;
    border-radius: 8px;
    border: 1px solid #ccc;
    transition: border-color 0.2s, box-shadow 0.2s;
}

input[type="email"]:focus,
input[type="text"]:focus,
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

