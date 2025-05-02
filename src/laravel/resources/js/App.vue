<script>
export default {
    name: 'App',
}
</script>
<template>
    <div>
        <div>
            <router-link :to="{ name: 'product.index' }">Главная</router-link>
            <router-link :to="{ name: 'user.login' }">Войти</router-link>
        </div>
        <div>Корзина: ₽</div>
        <router-view></router-view>
    </div>
</template>
<style scoped>

</style>
