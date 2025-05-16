import { ref } from 'vue'
import axios from 'axios'

const cartTotalPrice = ref(0)
const cartProducts = ref([])

async function fetchCart() {
    const response = await axios.get('/api/v1/cart')
    cartTotalPrice.value = response.data.meta.totalPrice
    cartProducts.value = response.data.data
}

async function addToCart(variantId) {
    try {
        const response = await axios.post('/api/v1/cart', { variantId: variantId })
        cartTotalPrice.value = response.data.meta.totalPrice

        const product = cartProducts.value.find(p => p.variant_id === variantId)

        if (product) {
            product.qty += 1
        } else {
            await fetchCart()
        }
    } catch (error) {
        if (error.response && error.response.status === 422) {
            const message = error.response.data.message || 'Ошибка при добавлении в корзину.'
            // Покажи сообщение пользователю — всплывашка, алерт, toast, и т.д.
            alert(message) // или использовать кастомный UI компонент
        } else {
            console.error('Неизвестная ошибка:', error)
            alert('Произошла неизвестная ошибка при добавлении товара.')
        }
    }
}

async function deleteFromCart(variantId) {
    const response = await axios.delete('/api/v1/cart', { data: { variantId } })
    cartTotalPrice.value = response.data.meta.totalPrice

    const index = cartProducts.value.findIndex(p => p.variant_id === variantId)

    if (index !== -1) {
        if (cartProducts.value[index].qty > 1) {
            cartProducts.value[index].qty -= 1
        } else {
            cartProducts.value.splice(index, 1)
        }
    }
}

async function clearCart() {
    const response = await axios.delete('/api/v1/cart/clear')
    cartTotalPrice.value = 0
    cartProducts.value = []
}

function resetCartLocally() {
    cartTotalPrice.value = 0
    cartProducts.value = []
}

export function useCart() {
    return {
        cartProducts,
        cartTotalPrice,
        fetchCart,
        addToCart,
        deleteFromCart,
        clearCart,
        resetCartLocally,
    }
}
