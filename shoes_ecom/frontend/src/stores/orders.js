import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useOrderStore = defineStore('orders', () => {
  const orders = ref([])
  const currentOrder = ref(null)
  const loading = ref(false)

  async function fetchOrders() {
    loading.value = true
    try {
      const response = await axios.get('/api/orders')
      orders.value = response.data
    } catch (error) {
      console.error('Fetch orders error:', error)
    } finally {
      loading.value = false
    }
  }

  async function fetchOrder(id) {
    loading.value = true
    try {
      const response = await axios.get(`/api/orders/${id}`)
      currentOrder.value = response.data
      return response.data
    } catch (error) {
      console.error('Fetch order error:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function createOrder(data) {
    loading.value = true
    try {
      const response = await axios.post('/api/orders', data)
      currentOrder.value = response.data.order
      return response.data
    } catch (error) {
      throw error.response?.data || error
    } finally {
      loading.value = false
    }
  }

  return {
    orders,
    currentOrder,
    loading,
    fetchOrders,
    fetchOrder,
    createOrder
  }
})
