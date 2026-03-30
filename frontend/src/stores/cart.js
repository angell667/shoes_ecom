import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { useAuthStore } from './auth'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])
  const total = ref(0)
  const count = ref(0)
  const loading = ref(false)

  const authStore = useAuthStore()

  const isEmpty = computed(() => items.value.length === 0)

  async function fetchCart() {
    if (!authStore.isAuthenticated) return
    loading.value = true
    try {
      const response = await axios.get('/api/cart')
      items.value = response.data.items
      total.value = response.data.total
      count.value = response.data.count
    } catch (error) {
      console.error('Fetch cart error:', error)
    } finally {
      loading.value = false
    }
  }

  async function addToCart(productId, quantity = 1, size = null, color = null) {
    if (!authStore.isAuthenticated) {
      throw new Error('Please login to add items to cart')
    }
    loading.value = true
    try {
      const response = await axios.post('/api/cart/add', {
        product_id: productId,
        quantity,
        size,
        color
      })
      await fetchCart()
      return response.data
    } catch (error) {
      throw error.response?.data || error
    } finally {
      loading.value = false
    }
  }

  async function updateQuantity(cartItemId, quantity) {
    loading.value = true
    try {
      const response = await axios.put(`/api/cart/${cartItemId}`, { quantity })
      await fetchCart()
      return response.data
    } catch (error) {
      throw error.response?.data || error
    } finally {
      loading.value = false
    }
  }

  async function removeFromCart(cartItemId) {
    loading.value = true
    try {
      await axios.delete(`/api/cart/${cartItemId}`)
      await fetchCart()
    } catch (error) {
      throw error.response?.data || error
    } finally {
      loading.value = false
    }
  }

  async function clearCart() {
    loading.value = true
    try {
      await axios.delete('/api/cart')
      items.value = []
      total.value = 0
      count.value = 0
    } catch (error) {
      throw error.response?.data || error
    } finally {
      loading.value = false
    }
  }

  return {
    items,
    total,
    count,
    loading,
    isEmpty,
    fetchCart,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart
  }
})
