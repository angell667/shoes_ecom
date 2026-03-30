<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const order = ref(null)
const loading = ref(true)

const formatPrice = (price) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price)
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })

const getStatusClass = (status) => {
  const classes = { pending: 'amber', processing: 'blue', shipped: 'purple', delivered: 'green', cancelled: 'red' }
  return classes[status] || 'gray'
}

const updateStatus = async (status) => {
  try {
    const response = await axios.patch(`/api/admin/orders/${order.value.id}/status`, { status })
    order.value = response.data
  } catch (err) {
    alert('Failed to update status')
  }
}

onMounted(async () => {
  try {
    const response = await axios.get(`/api/admin/orders/${route.params.id}`)
    order.value = response.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="page">
    <router-link to="/admin/orders" class="back-link">← Back to Orders</router-link>
    
    <div v-if="loading" class="loading"><div class="spinner"></div></div>

    <template v-else-if="order">
      <div class="page-header">
        <div>
          <h1 class="page-title">Order #{{ order.order_number }}</h1>
          <p class="page-subtitle">{{ formatDate(order.created_at) }}</p>
        </div>
        <span class="badge large" :class="getStatusClass(order.status)">{{ order.status }}</span>
      </div>

      <div class="content-grid">
        <div class="card">
          <div class="card-header"><h2>Order Items</h2></div>
          <div class="card-body">
            <div class="items-list">
              <div v-for="item in order.items" :key="item.id" class="item-row">
                <img :src="item.product?.image || 'https://via.placeholder.com/64'" :alt="item.product_name" />
                <div class="item-info">
                  <p class="item-name">{{ item.product_name }}</p>
                  <p class="item-meta">
                    <span v-if="item.size">Size: {{ item.size }}</span>
                    <span v-if="item.color"> | Color: {{ item.color }}</span>
                  </p>
                </div>
                <div class="item-total">
                  <p class="item-price">{{ formatPrice(item.price * item.quantity) }}</p>
                  <p class="item-qty">x{{ item.quantity }}</p>
                </div>
              </div>
            </div>
            <div class="totals">
              <div class="total-row"><span>Subtotal</span><span>{{ formatPrice(order.subtotal) }}</span></div>
              <div class="total-row"><span>Shipping</span><span>{{ order.shipping === 0 ? 'Free' : formatPrice(order.shipping) }}</span></div>
              <div class="total-row"><span>Tax</span><span>{{ formatPrice(order.tax) }}</span></div>
              <div class="total-row final"><span>Total</span><span>{{ formatPrice(order.total) }}</span></div>
            </div>
          </div>
        </div>

        <div class="sidebar">
          <div class="card">
            <div class="card-header"><h3>Customer</h3></div>
            <div class="card-body">
              <div class="customer-info">
                <div class="avatar">{{ order.user?.name?.charAt(0) || 'U' }}</div>
                <div>
                  <p class="customer-name">{{ order.user?.name }}</p>
                  <p class="customer-email">{{ order.user?.email }}</p>
                </div>
              </div>
              <p class="customer-phone">{{ order.user?.phone || 'No phone' }}</p>
            </div>
          </div>

          <div class="card">
            <div class="card-header"><h3>Shipping Address</h3></div>
            <div class="card-body">
              <p class="address">{{ order.shipping_address }}</p>
            </div>
          </div>

          <div class="card">
            <div class="card-header"><h3>Payment</h3></div>
            <div class="card-body">
              <div class="payment-row">
                <span>Method</span>
                <span class="payment-method">{{ order.payment_method?.replace('_', ' ') }}</span>
              </div>
              <div class="payment-row">
                <span>Status</span>
                <span class="badge" :class="order.payment_status === 'paid' ? 'green' : 'amber'">{{ order.payment_status }}</span>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header"><h3>Update Status</h3></div>
            <div class="card-body">
              <select :value="order.status" @change="updateStatus($event.target.value)" class="select">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.page { padding: 0; }
.back-link { display: inline-flex; align-items: center; gap: 8px; color: #6b7280; text-decoration: none; margin-bottom: 20px; font-weight: 500; }
.back-link:hover { color: #111827; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 700; color: #111827; }
.page-subtitle { color: #6b7280; margin-top: 4px; }
.badge { display: inline-block; padding: 4px 12px; font-size: 12px; font-weight: 600; border-radius: 9999px; text-transform: capitalize; }
.badge.large { font-size: 14px; padding: 8px 20px; }
.badge.green { background: #dcfce7; color: #16a34a; }
.badge.amber { background: #fef3c7; color: #d97706; }
.badge.blue { background: #dbeafe; color: #2563eb; }
.badge.purple { background: #f3e8ff; color: #9333ea; }
.badge.red { background: #fee2e2; color: #dc2626; }
.loading { display: flex; justify-content: center; padding: 80px; }
.spinner { width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.content-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px; align-items: start; }
.card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 20px; }
.card-header { padding: 20px 24px; border-bottom: 1px solid #f3f4f6; }
.card-header h2, .card-header h3 { font-size: 16px; font-weight: 600; color: #111827; }
.card-body { padding: 24px; }
.items-list { margin-bottom: 20px; }
.item-row { display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid #f3f4f6; }
.item-row:last-child { border-bottom: none; }
.item-row img { width: 64px; height: 64px; border-radius: 10px; object-fit: cover; }
.item-info { flex: 1; }
.item-name { font-weight: 600; color: #111827; }
.item-meta { font-size: 13px; color: #6b7280; }
.item-total { text-align: right; }
.item-price { font-weight: 600; color: #111827; }
.item-qty { font-size: 13px; color: #6b7280; }
.totals { padding-top: 20px; border-top: 1px solid #e5e7eb; }
.total-row { display: flex; justify-content: space-between; padding: 8px 0; color: #6b7280; }
.total-row.final { font-size: 18px; font-weight: 700; color: #111827; padding-top: 16px; border-top: 1px solid #e5e7eb; margin-top: 8px; }
.sidebar { display: flex; flex-direction: column; gap: 20px; }
.customer-info { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 18px; }
.customer-name { font-weight: 600; color: #111827; }
.customer-email { font-size: 13px; color: #6b7280; }
.customer-phone { color: #6b7280; }
.address { color: #6b7280; white-space: pre-line; }
.payment-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.payment-row:last-child { margin-bottom: 0; }
.payment-method { font-weight: 600; color: #111827; text-transform: capitalize; }
.select { width: 100%; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; background: white; }
@media (max-width: 1024px) { .content-grid { grid-template-columns: 1fr; } }
</style>
