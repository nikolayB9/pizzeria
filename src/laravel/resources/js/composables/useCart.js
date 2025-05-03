import { ref } from 'vue'
import axios from 'axios'

const cartTotalPrice = ref(0)

async function fetchCart() {
    const response = await axios.get('/api/v1/cart')
    cartTotalPrice.value = response.data.totalPrice
}

async function addToCart(variantId) {
    const response = await axios.post('/api/v1/cart', { variantId })
    cartTotalPrice.value = response.data.totalPrice
}

export function useCart() {
    return {
        cartTotalPrice,
        fetchCart,
        addToCart,
    }
}
