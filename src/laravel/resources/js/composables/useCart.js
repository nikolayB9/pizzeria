import { ref } from 'vue'
import axios from 'axios'

const cartTotalPrice = ref(0)
const cartProducts = ref([])

async function fetchCart() {
    const response = await axios.get('/api/v1/cart')
    cartTotalPrice.value = response.data.totalPrice
    cartProducts.value = response.data.cartProducts
}

async function addToCart(variantId) {
    const response = await axios.post('/api/v1/cart', { variantId })
    cartTotalPrice.value = response.data.totalPrice

    const product = cartProducts.value.find(p => p.variant_id === variantId)

    if (product) {
        product.qty += 1
    } else {
        // Если товара ещё нет в списке, можно перезапросить корзину
        await fetchCart()
    }
}

async function deleteFromCart(variantId) {
    const response = await axios.delete('/api/v1/cart', { data: { variantId } })
    cartTotalPrice.value = response.data.totalPrice

    const index = cartProducts.value.findIndex(p => p.variant_id === variantId)

    if (index !== -1) {
        if (cartProducts.value[index].qty > 1) {
            cartProducts.value[index].qty -= 1
        } else {
            cartProducts.value.splice(index, 1)
        }
    }
}

export function useCart() {
    return {
        cartProducts,
        cartTotalPrice,
        fetchCart,
        addToCart,
        deleteFromCart,
    }
}
