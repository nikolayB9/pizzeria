<script>
export default {
    name: "Index",

    data() {
        return {
            orders: [],
            meta: [],
        }
    },

    watch: {
        $route(to, from) {
        },
    },

    mounted() {
        this.getOrders()
    },

    methods: {
        getOrders(page = null) {
            const url = page !== null
                ? `/api/v1/orders?page=${page}`
                : `/api/v1/orders`;

            axios.get(url)
                .then(res => {
                    this.orders = res.data.data
                    this.meta = res.data.meta
                })
                .catch(error => {
                    alert(error.response?.data?.message ?? 'Ошибка при получении заказов')
                });
        },
        goToPage(page) {
            this.getOrders(page)
        },
    }
}
</script>

<template>
    <h1>Мои заказы</h1>

    <div v-if="orders">
        <div v-for="order in orders" class="order-card">

            <!-- Дата и статус -->
            <div class="order-header">
                <div>{{ order.created_at }}</div>
                <div class="order-status">{{ order.status }}</div>
            </div>

            <!-- Адрес -->
            <div class="order-label">Доставка</div>
            <div class="order-address">
                {{ order.address.city }}, {{ order.address.street }}, {{ order.address.house }}
            </div>

            <!-- Превью товаров -->
            <div class="order-products">
                <template v-for="(preview, index) in order.product_previews" :key="index">
                    <img :src="preview.url" alt="Товар">
                </template>
            </div>

            <!-- Сумма -->
            <div class="order-total">
                Сумма: {{ order.total }} ₽
            </div>
        </div>
    </div>

    <div v-if="meta" class="pagination-controls"
         style="margin-top: 20px; display: flex; justify-content: center; gap: 12px;">
        <button
            :disabled="!meta.prev_page_url"
            @click="goToPage(meta.current_page - 1)"
            style="padding: 8px 16px; border: none; background-color: #eee; cursor: pointer;"
        >
            ⬅ Назад
        </button>

        <button
            :disabled="!meta.next_page_url"
            @click="goToPage(meta.current_page + 1)"
            style="padding: 8px 16px; border: none; background-color: #eee; cursor: pointer;"
        >
            Вперёд ➡
        </button>
    </div>

</template>

<style scoped>
.order-card {
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 24px;
    background-color: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.order-header {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 10px;
}

.order-status {
    padding: 4px 10px;
    margin-left: 20px;
    font-size: 0.8rem;
    border-radius: 20px;
    background-color: #f0f0f0;
    color: #333;
    font-weight: bold;
}

.order-label {
    font-size: 0.8rem;
    color: #888;
    margin-bottom: 4px;
}

.order-address {
    font-size: 0.9rem;
    color: #333;
    margin-bottom: 16px;
}

.order-products {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.order-products img {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.order-total {
    font-size: 0.9rem;
    font-weight: bold;
    color: #222;
}
</style>

