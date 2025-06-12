<script>
import {useCart} from '@/composables/useCart'

export default {
    name: "Index",

    data() {
        return {
            token: null,
        }
    },

    setup() {
        const {fetchCart} = useCart()
        return {fetchCart}
    },

    mounted() {
        this.fetchCart()
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
    },

}
</script>

<template>
    <h1>Заказ успешно оплачен</h1>
    <div v-if="token">
        <router-link :to="{ name: 'order.index' }">Перейти в Мои заказы</router-link>
    </div>
</template>

<style scoped>

</style>

