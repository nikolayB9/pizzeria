<script>
export default {
    name: "Index",

    data() {
        return {
            marketingCategories: [],
            productCategories: [],
            products: [],
            activeCategorySlug: null,
        }
    },

    watch: {
        $route(to, from) {
            this.setActiveCategoryFromRoute();
            this.getCategories();
        },
        activeCategorySlug(newSlug) {
            if (newSlug) {
                this.getProducts(newSlug);
            }
        }
    },

    mounted() {
        this.setActiveCategoryFromRoute()
        this.getCategories()
    },

    methods: {
        setActiveCategoryFromRoute() {
            const slug = this.$route.params.categorySlug;
            this.activeCategorySlug = slug ?? null;
        },
        getCategories() {
            axios.get('/api/v1/categories')
                .then(res => {
                    const categories = res.data.data

                    this.marketingCategories = categories.filter(
                        category => category.type_slug === 'marketing'
                    );

                    this.productCategories = categories.filter(
                        category => category.type_slug === 'product_type'
                    );

                    if (!this.activeCategorySlug && this.productCategories.length > 0) {
                        this.activeCategorySlug = this.productCategories[0].slug;
                    }
                })
                .catch(err => {
                    console.error('Ошибка загрузки категорий', err);
                });
        },
        getProducts(categorySlug) {
            axios.get(`/api/v1/products/category/${categorySlug}`)
                .then(res => {
                    this.products = res.data.data
                })
                .catch(err => {
                    console.error('Ошибка загрузки продуктов', err);
                });
        },
        isCategoryActive(slug) {
            return this.activeCategorySlug === slug ||
                (!this.activeCategorySlug && this.productCategories.length > 0 && slug === this.productCategories[0].slug);
        }
    }
}
</script>

<template>
    <div>
        <!-- Маркетинговые категории -->
        <div v-if="marketingCategories" class="category-row marketing-row">
            <router-link
                :to="{ name: 'main.index' }"
                class="category-link"
                :class="{ active: !activeCategorySlug }"
                active-class="active"
            >
                Все
            </router-link>
            <template v-for="markCategory in marketingCategories" :key="markCategory.id">
                <router-link
                    :to="{ name: 'category.products', params: { categorySlug: markCategory.slug } }"
                    class="category-link"
                    active-class="active"
                >
                    {{ markCategory.name }}
                </router-link>
            </template>
        </div>

        <!-- Продуктовые категории -->
        <div v-if="productCategories" class="category-row product-row">
            <template v-for="prodCategory in productCategories" :key="prodCategory.id">
                <router-link
                    :to="{ name: 'category.products', params: { categorySlug: prodCategory.slug } }"
                    class="category-link"
                    active-class="active"
                    :class="{ active: isCategoryActive(prodCategory.slug) }"
                >
                    {{ prodCategory.name }}
                </router-link>
            </template>
        </div>
    </div>

    <div v-if="products">
        <ul class="menu">
            <li v-for="product in products" :key="product.id">
                <router-link :to="{ name: 'product.show', params: {productSlug: product.slug} }">
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
.category-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px 12px;
    margin-bottom: 16px;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

.marketing-row {
    background: linear-gradient(90deg, #f9f9f9, #f1f1f1);
}

.product-row {
    background: linear-gradient(90deg, #eef6ff, #dceeff);
}

.category-link {
    padding: 6px 14px;
    border-radius: 20px;
    background-color: white;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.category-link:hover {
    background-color: #f0f0f0;
}

.category-link.active {
    background-color: #007bff;
    color: white;
    font-weight: bold;
    border-color: #007bff;
}


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

