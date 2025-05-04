<script>
import {useCart} from '@/composables/useCart'

export default {
    name: "Index",

    setup() {
        const {cartTotalPrice, cartProducts, fetchCart, addToCart, deleteFromCart, clearCart} = useCart()
        return {cartTotalPrice, cartProducts, fetchCart, addToCart, deleteFromCart, clearCart}
    },

    mounted() {
        this.fetchCart()
    },


}
</script>

<template>
    <h1>Корзина</h1>
    <div class="cart-clear">
        <button v-if="cartTotalPrice" @click.prevent="clearCart()" class="clear-btn">
            Очистить корзину
        </button>
    </div>
    <div v-if="cartProducts">
        <ul class="cart-list">
            <li v-for="product in cartProducts" class="cart-item">
                <img :src="product.preview_image_url" alt="preview">
                <div class="cart-info">
                    <div class="cart-name">{{ product.name }}</div>
                    <div class="cart-variant">{{ product.variant_name }}</div>
                    <div class="cart-price">{{ product.price }} ₽</div>
                    <div class="cart-qty">
                        <button @click.prevent="addToCart(product.variant_id)">+</button>
                        <span>{{ product.qty }}</span>
                        <button @click.prevent="deleteFromCart(product.variant_id)">-</button>
                    </div>
                </div>
            </li>
        </ul>
        <div class="cart-total">Общая стоимость: {{ cartTotalPrice }} ₽</div>
        <div v-if="cartTotalPrice" class="checkout">
            <a href="#">Оформить заказ</a>
        </div>
    </div>
</template>

<style scoped>
.cart-clear {
    margin-top: 1rem;
    margin-bottom: 1rem;
    display: flex;
}

.clear-btn {
    background-color: #f87171;
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s ease;
}

.cart-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.cart-item {
    display: flex;
    gap: 16px;
    padding: 12px;
    border-bottom: 1px solid #ddd;
    align-items: center;
}

.cart-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.cart-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cart-name {
    font-weight: bold;
    font-size: 16px;
}

.cart-variant {
    font-size: 14px;
    color: #666;
}

.cart-price {
    color: #2c3e50;
    font-weight: 500;
}

.cart-qty {
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.cart-qty button {
    width: 28px;
    height: 28px;
    font-size: 16px;
    font-weight: bold;
    background-color: #eee;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.cart-total {
    margin-top: 16px;
    font-size: 18px;
    font-weight: bold;
}

.checkout {
    margin-top: 12px;
}

.checkout a {
    display: inline-block;
    padding: 10px 16px;
    background-color: #42b983;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}
</style>

