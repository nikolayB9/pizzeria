<script>
export default {
    name: "Index",

    mounted() {
        this.getProducts()
    },

    data() {
      return {
          products: [],
      }
    },

    methods: {
        getProducts() {
            axios.get('api/v1/products')
                .then(res => {
                    this.products = res.data.data
                    console.log(this.products)
                })
                .catch(err => {
                    console.error('Ошибка загрузки продуктов', err);
                });
        }
    }
}
</script>

<template>
    <div v-if="products">
        <ul class="menu">
            <li v-for="product in products" :key="product.id">
                <router-link :to="{ name: 'product.show', params: {product_slug: product.slug} }">
                    <img :src="product.preview_image_url" alt="preview">
                    <div class="product-info">
                        <h3>{{ product.name }}</h3>
                        <p class="description">{{ product.description }}</p>
                        <p class="price">
                            <span v-if="product.has_multiple_variants">от</span> {{ product.min_price }} ₽
                        </p>
                    </div>
                </router-link>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.menu {
    list-style: none;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.menu a {
    text-decoration: none;
    color: black;
}

.menu li {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    padding: 12px;
    width: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: box-shadow 0.2s ease;
}

.menu li:hover {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.menu img {
    width: 100%;
    height: auto;
    border-radius: 4px;
    margin-bottom: 8px;
}

.product-info h3 {
    margin: 4px 0;
    font-size: 16px;
}

.description {
    font-size: 14px;
    color: #666;
    margin: 4px 0;
}

.price {
    font-weight: bold;
    margin-top: 6px;
}
</style>

