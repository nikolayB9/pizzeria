<script>
export default {
    name: "Show",

    mounted() {
        this.getUser()
    },

    data() {
        return {
            user: null,
        }
    },

    methods: {
        getUser() {
            axios.get('/api/v1/profile')
                .then(res => {
                    this.user = res.data.data
                })
        },
    }
}
</script>

<template>
    <div class="profile-container" v-if="user">
        <div class="profile-header">
            <h1>Профиль</h1>
        </div>

        <div class="profile-info">
            <div><strong>Имя:</strong> {{ user.name }}</div>
            <div><strong>Email:</strong> {{ user.email }}</div>
            <div><strong>Телефон:</strong> {{ user.phone_number }}</div>
            <div><strong>Дата рождения:</strong> {{ user.birth_date }}</div>
        </div>

        <div class="profile-links">
            <router-link :to="{ name: 'order.index' }">Мои заказы</router-link>
            <router-link :to="{ name: 'address.index' }">Адреса доставки</router-link>
        </div>
    </div>
</template>

<style scoped>
.profile-container {
    max-width: 600px;
    padding: 24px;
    border: 1px solid #ddd;
    border-radius: 12px;
    background-color: #fafafa;
    font-family: Arial, sans-serif;
}

.profile-header h1 {
    margin-bottom: 20px;
    font-size: 24px;
    color: #333;
}

.profile-info > div {
    margin-bottom: 12px;
    font-size: 16px;
    color: #444;
}

.profile-info strong {
    display: inline-block;
    width: 140px;
    font-weight: 600;
    color: #222;
}

.profile-links {
    margin-top: 24px;
    display: flex;
    gap: 16px;
}

.profile-links a {
    text-decoration: none;
    color: #007bff;
    font-weight: 500;
    border-bottom: 1px solid transparent;
    transition: 0.2s;
}

.profile-links a:hover {
    border-bottom: 1px solid #007bff;
}
</style>



