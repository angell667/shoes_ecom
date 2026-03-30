<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const orders = ref([])
const loading = ref(true)
const statusFilter = ref('')

const formatPrice = (price) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price)
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })

const getStatusClass = (status) => {
  const classes = { pending: 'amber', processing: 'blue', shipped: 'purple', delivered: 'green', cancelled: 'red' }
  return classes[status] || 'gray'
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const params = {}
    if (statusFilter.value) params.status = statusFilter.value
    const response = await axios.get('/api/admin/orders', { params })
    orders.value = response.data.data
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const updateStatus = async (orderId, status) => {
  try {
    await axios.patch(`/api/admin/orders/${orderId}/status`, { status })
    await fetchOrders()
  } catch (error) {
    alert('Failed to update status')
  }
}

onMounted(fetchOrders)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Orders</h1>
        <p class="page-subtitle">Manage and track all orders</p>
      </div>
    </div>

    <div class="card filter-card">
      <select v-model="statusFilter" class="select" @change="fetchOrders">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="processing">Processing</option>
        <option value="shipped">Shipped</option>
        <option value="delivered">Delivered</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <div v-if="loading" class="loading"><div class="spinner"></div></div>

    <div v-else-if="orders.length === 0" class="card empty">
      <div class="empty-icon">📋</div>
      <h3>No orders found</h3>
      <p>Orders will appear here when customers make purchases</p>
    </div>

    <div v-else class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td><router-link :to="`/admin/orders/${order.id}`" class="order-link">#{{ order.order_number }}</router-link></td>
            <td>
              <div class="customer-cell">
                <div class="avatar">{{ order.user?.name?.charAt(0) || 'U' }}</div>
                <div>
                  <p class="customer-name">{{ order.user?.name || 'Guest' }}</p>
                  <p class="customer-email">{{ order.user?.email || 'N/A' }}</p>
                </div>
              </div>
            </td>
            <td>{{ formatDate(order.created_at) }}</td>
            <td><span class="badge gray">{{ order.items?.length || 0 }} items</span></td>
            <td class="price">{{ formatPrice(order.total) }}</td>
            <td><span class="badge" :class="getStatusClass(order.status)">{{ order.status }}</span></td>
            <td class="text-right">
              <select :value="order.status" @change="updateStatus(order.id, $event.target.value)" class="select-sm">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.page { padding: 0; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 700; color: #111827; }
.page-subtitle { color: #6b7280; margin-top: 4px; }
.card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.filter-card { padding: 20px 24px; margin-bottom: 24px; }
.select { padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; min-width: 180px; background: white; }
.select-sm { padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; background: white; }
.loading { display: flex; justify-content: center; padding: 80px; }
.spinner { width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty { padding: 80px; text-align: center; }
.empty-icon { font-size: 64px; margin-bottom: 16px; }
.empty h3 { font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 8px; }
.empty p { color: #6b7280; }
.table { width: 100%; border-collapse: collapse; }
.table th { text-align: left; padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; background: #f9fafb; }
.table td { padding: 16px 24px; border-bottom: 1px solid #f3f4f6; }
.table tr:hover td { background: #f9fafb; }
.text-right { text-align: right; }
.order-link { font-weight: 600; color: #3b82f6; text-decoration: none; }
.order-link:hover { text-decoration: underline; }
.customer-cell { display: flex; align-items: center; gap: 12px; }
.avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px; }
.customer-name { font-weight: 600; color: #111827; }
.customer-email { font-size: 12px; color: #6b7280; }
.price { font-weight: 600; color: #111827; }
.badge { display: inline-block; padding: 4px 12px; font-size: 12px; font-weight: 600; border-radius: 9999px; text-transform: capitalize; }
.badge.green { background: #dcfce7; color: #16a34a; }
.badge.amber { background: #fef3c7; color: #d97706; }
.badge.red { background: #fee2e2; color: #dc2626; }
.badge.blue { background: #dbeafe; color: #2563eb; }
.badge.purple { background: #f3e8ff; color: #9333ea; }
.badge.gray { background: #f3f4f6; color: #6b7280; }
</style>
