<script>
import {useCart} from '@/composables/useCart'

export default {
    name: "Show",

    setup() {
        const {addToCart} = useCart()
        return {addToCart}
    },

    mounted() {
        this.getProduct()
    },

    data() {
        return {
            product: null,
        }
    },

    methods: {
        getProduct() {
            axios.get(`/api/v1/products/${this.$route.params.productSlug}`)
                .then(res => {
                    this.product = res.data.data
                })
        },
    }
}
</script>

<template>
    <div v-if="product" class="product-detail">
        <h2 class="product-name">{{ product.name }}</h2>
        <img :src="product.detail_image_url" alt="Product image" class="product-image">
        <p class="product-description">{{ product.description }}</p>

        <!-- Несколько вариантов -->
        <div v-if="product.variants.length > 1" class="variant-list">
            <ul>
                <li v-for="variant in product.variants" :key="variant.id" class="variant-item">
                    <span class="variant-name">{{ variant.name }}</span>
                    <span v-if="variant.old_price" class="old-price">{{ variant.old_price }} ₽</span>
                    <button @click.prevent="addToCart(variant.id)">
                        + {{ variant.price }} ₽
                    </button>
                </li>
            </ul>
        </div>

        <!-- Один вариант -->
        <div v-else class="single-variant">
            <template v-for="variant in product.variants" :key="variant.id">
                <span class="variant-name">{{ variant.name }}</span>
                <span v-if="variant.old_price" class="old-price">{{ variant.old_price }} ₽</span>
                <button @click.prevent="addToCart(variant.id)">
                    + {{ variant.price }} ₽
                </button>
            </template>
        </div>
    </div>
</template>

<style scoped>
.product-detail {
    max-width: 500px;
    padding: 1rem;
    font-family: sans-serif;
}

.product-name {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.product-image {
    width: 200px;
    height: auto;
    display: block;
    margin-bottom: 1rem;
}

.product-description {
    margin-bottom: 1rem;
    color: #555;
}

.variant-list ul {
    list-style: none;
    padding: 0;
}

.variant-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.single-variant {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.variant-name {
    font-weight: bold;
}

.old-price {
    text-decoration: line-through;
    color: #888;
}

button {
    background-color: #4caf50;
    color: white;
    border: none;
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
}
</style>


