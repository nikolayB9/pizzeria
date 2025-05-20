<script>
import {useCart} from '@/composables/useCart'
import axios from "axios";

export default {
    name: "Index",

    data() {
        return {
            token: null,
            checkoutData: null,
            summaryData: null,
            address: null,
        }
    },

    setup() {
        const {cartProducts, fetchCart} = useCart()
        return {cartProducts, fetchCart}
    },

    mounted() {
        this.fetchCart()
        this.getToken()
        this.getCheckoutData()
        this.getSummaryData()
    },

    watch: {
        $route(to, from) {
            this.fetchCart()
            this.getToken()
            this.getCheckoutData()
            this.getSummaryData()
        }
    },

    methods: {
        getToken() {
            this.token = localStorage.getItem('x_xsrf_token')
        },
        getCheckoutData() {
            axios.get('/api/v1/checkout/user-data')
                .then(res => {
                    this.checkoutData = res.data.data
                    this.address = this.checkoutData.address
                })
        },
        getSummaryData() {
            axios.get('/api/v1/checkout/summary-data')
                .then(res => {
                    this.summaryData = res.data.data
                })
        },
        storeOrder() {
            if (!this.address) {
                alert('Добавьте адрес доставки');
                return;
            }
            axios.post('/api/v1/orders')
                .then(res => {
                    console.log(res)
                })
                .catch(error => {
                    alert(error.response?.data?.message ?? 'Ошибка при оформлении заказа')
                })
        },
        goToAddresses() {
            this.$router.push({name: 'address.index', query: {fromCheckout: '1'}});
        }
    },

}
</script>

<template>
    <h1>Оформление заказа</h1>

    <div v-if="token && cartProducts && checkoutData && summaryData" class="checkout-container">
        <!-- Данные пользователя -->
        <div class="user-info">
            <div><span class="label">Имя:</span> {{ checkoutData.name }}</div>
            <div><span class="label">Email:</span> {{ checkoutData.email }}</div>
            <div><span class="label">Номер телефона:</span> {{ checkoutData.phone_number }}</div>

            <div class="address-section">
  <span v-if="address">
    {{ address.city }}, {{ address.street }}, {{ address.house }}
  </span>
                <span v-else>Добавьте адрес доставки</span>
                <button class="edit-btn" @click="goToAddresses" title="Редактировать адрес">✏️</button>
            </div>
        </div>

        <!-- Основной блок -->
        <div class="main-block">
            <!-- Список товаров -->
            <ul class="cart-list">
                <li v-for="product in cartProducts" :key="product.id" class="cart-item">
                    <img :src="product.preview_image_url" alt="preview"/>
                    <div class="cart-info">
                        <div class="cart-name">{{ product.name }}</div>
                        <div class="cart-variant">{{ product.variant_name }}</div>
                        <div class="cart-price">{{ product.price }} ₽</div>
                        <div class="cart-qty">{{ product.qty }} шт</div>
                    </div>
                </li>
            </ul>

            <!-- Итог и кнопки -->
            <div class="summary">
                <div>Общая стоимость товаров: {{ summaryData.cart_total }} ₽</div>
                <div>Стоимость доставки: {{ summaryData.delivery_cost }} ₽</div>
                <div class="summary-total">Итого: {{ summaryData.total }} ₽</div>

                <div class="actions">
                    <button class="btn primary" @click.prevent="storeOrder">Оформить заказ</button>
                    <router-link class="btn secondary" :to="{ name: 'cart.index' }">Перейти в корзину</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.checkout-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.user-info {
    background: #f3f7fb;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    line-height: 1.6;
    font-size: 1rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.user-info .label {
    font-weight: 600;
    margin-right: 0.3rem;
    color: #555;
}

.address-section {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.8rem;
    font-size: 1rem;
}

.edit-btn {
    background: transparent;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.1rem;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s, color 0.2s;
}

.edit-btn:hover {
    transform: scale(1.2);
    color: #007bff;
}

.main-block {
    display: flex;
    flex-direction: row;
    gap: 2rem;
    flex-wrap: wrap;
}

.cart-list {
    flex: 2;
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.cart-item {
    display: flex;
    gap: 1rem;
    background-color: #f9f9f9;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}

.cart-item img {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    object-fit: cover;
}

.cart-info {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.cart-name {
    font-size: 1rem;
}

.cart-variant {
    color: #777;
    font-size: 0.9rem;
}

.cart-price {
    margin-top: 0.2rem;
    font-weight: 500;
}

.cart-qty {
    font-size: 0.9rem;
    color: #555;
}

.summary {
    flex: 1;
    background: #eef3f7;
    padding: 1.2rem;
    border-radius: 10px;
    min-width: 280px;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    justify-content: space-between;
    height: fit-content;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.summary-total {
    font-weight: bold;
    font-size: 1.2rem;
    margin-top: 0.5rem;
}

.actions {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.btn {
    display: inline-block;
    text-align: center;
    padding: 0.7rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.3s ease;
    cursor: pointer;
    font-size: 0.95rem;
}

.btn.primary {
    background-color: #ff6b00;
    color: white;
    padding: 0.6rem 1.2rem;
    border: none; /* убирает чёрную границу */
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s ease;
}

.btn.primary:hover {
    background-color: #e65a00;
}

.btn.secondary {
    background-color: #007bff;
    color: white;
}

.btn.secondary:hover {
    background-color: #0056b3;
}

</style>

