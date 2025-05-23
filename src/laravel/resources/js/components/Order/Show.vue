<script>
export default {
    name: "Show",

    data() {
        return {
            order: null,
        }
    },

    mounted() {
        this.getOrder()
    },

    methods: {
        getOrder() {
            axios.get(`/api/v1/orders/${this.$route.params.id}`)
                .then(res => {
                    this.order = res.data.data
                })
                .catch(error => {
                    alert(error.response?.data?.message ?? 'Ошибка при получении заказа')
                });
        },
        goToPage(page) {
            this.getOrders(page)
        },
    }
}
</script>

<template>
    <template v-if="order">
        <div class="order-container">
            <div class="order-header">
                <div class="order-title-status">
                    <h1>Заказ № {{ order.id }}</h1>
                    <span class="order-status">{{ order.status }}</span>
                </div>
                <router-link :to="{ name: 'order.index' }">Перейти в Мои заказы</router-link>
            </div>


            <div class="order-details">
                <div class="product-list">
                    <div
                        class="product-item"
                        v-for="(product, index) in order.products"
                        :key="index"
                    >
                        <img :src="product.preview_image_url" alt="Товар"/>
                        <div class="product-info">
                            <span class="product-name">{{ product.name }}</span>
                            <span style="color: #666; font-size: 0.8rem;">{{ product.variant_name }}</span>
                            <span style="font-size: 0.8rem;">{{ product.price }} ₽ × {{
                                    product.qty
                                }} шт</span>
                        </div>
                    </div>
                </div>

                <div class="order-summary">
                    <div>Сумма {{ order.total }} ₽</div>

                    <div class="order-item">
                        <div class="label">Доставка</div>
                        {{ order.delivery_cost }} ₽
                    </div>

                    <div class="order-item">
                        <div class="label">Адрес</div>
                        {{ order.address.city }}, {{ order.address.street }}, {{ order.address.house }}
                    </div>

                    <div class="order-item">
                        <div class="label">Время заказа</div>
                        {{ order.created_at }}
                    </div>
                </div>
            </div>
        </div>
    </template>
</template>

<style scoped>
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.order-title-status {
    display: flex;
    align-items: center;
    gap: 12px; /* расстояние между h1 и статусом */
}

.order-title-status h1 {
    font-size: 24px;
    margin: 0;
    line-height: 1.2;
}

.order-status {
    padding: 4px 10px;
    margin-left: 10px;
    font-size: 0.8rem;
    border-radius: 20px;
    background-color: #f0f0f0;
    color: #333;
    font-weight: bold;
}

.order-header a {
    font-size: 14px;
    color: #007bff;
    text-decoration: none;
}

.order-header a:hover {
    text-decoration: underline;
}

.order-container {
    max-width: 800px;
    padding: 32px;
    background-color: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    font-family: sans-serif;
}

.order-details {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.product-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.product-item {
    display: flex;
    gap: 16px;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding-bottom: 16px;
}

.product-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.product-name {
    font-weight: bold;
    color: #222;
}

.order-summary {
    background-color: #f9f9f9;
    padding: 15px;
}

.label {
    font-size: 0.8rem;
    color: #666;
    padding-bottom: 8px;
}

.order-item {
    background-color: #f9f9f9;
    margin-top: 20px;
    font-size: 0.9rem;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    color: #111;
}
</style>

