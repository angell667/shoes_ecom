<script setup>
import { onMounted } from 'vue'
import { useOrderStore } from '../stores/orders'

const orderStore = useOrderStore()

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'badge-warning',
    processing: 'badge-primary',
    shipped: 'badge-primary',
    delivered: 'badge-success',
    cancelled: 'badge-danger'
  }
  return classes[status] || 'badge-secondary'
}

onMounted(() => {
  orderStore.fetchOrders()
})
</script>

<template>
  <div class="orders-page">
    <div class="container">
      <h1 class="page-title">My Orders</h1>

      <div v-if="orderStore.loading" class="loading-state">
        <div class="skeleton" style="height: 120px; margin-bottom: 1rem;" v-for="i in 3" :key="i"></div>
      </div>

      <div v-else-if="orderStore.orders.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
          <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
          <line x1="12" y1="22.08" x2="12" y2="12"/>
        </svg>
        <h2>No orders yet</h2>
        <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
        <router-link to="/products" class="btn btn-primary btn-lg">Start Shopping</router-link>
      </div>

      <div v-else class="orders-list">
        <div v-for="order in orderStore.orders" :key="order.id" class="order-card">
          <div class="order-header">
            <div class="order-info">
              <h3>Order #{{ order.order_number }}</h3>
              <span class="order-date">Placed on {{ formatDate(order.created_at) }}</span>
            </div>
            <span :class="['badge', getStatusClass(order.status)]">
              {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
            </span>
          </div>

          <div class="order-items">
            <div v-for="item in order.items?.slice(0, 3)" :key="item.id" class="order-item">
              <img :src="item.product?.image || 'https://via.placeholder.com/60'" :alt="item.product_name" />
              <div class="item-info">
                <span class="item-name">{{ item.product_name }}</span>
                <span class="item-meta">Qty: {{ item.quantity }}</span>
              </div>
            </div>
            <span v-if="order.items?.length > 3" class="more-items">
              +{{ order.items.length - 3 }} more items
            </span>
          </div>

          <div class="order-footer">
            <div class="order-total">
              <span>Total:</span>
              <strong>{{ formatPrice(order.total) }}</strong>
            </div>
            <router-link :to="`/orders/${order.id}`" class="btn btn-outline btn-sm">
              View Details
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.orders-page {
  padding: 2rem 0 4rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 2rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 1rem;
}

.empty-state svg {
  width: 80px;
  height: 80px;
  color: var(--gray-300);
  margin-bottom: 1.5rem;
}

.empty-state h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: var(--gray-500);
  margin-bottom: 2rem;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.order-card {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-100);
}

.order-info h3 {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.order-date {
  font-size: 0.875rem;
  color: var(--gray-500);
}

.order-items {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  overflow-x: auto;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}

.order-item img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 0.5rem;
}

.item-name {
  display: block;
  font-weight: 500;
  font-size: 0.875rem;
  white-space: nowrap;
}

.item-meta {
  font-size: 0.75rem;
  color: var(--gray-500);
}

.more-items {
  font-size: 0.875rem;
  color: var(--gray-500);
  white-space: nowrap;
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: var(--gray-50);
}

.order-total span {
  color: var(--gray-500);
  font-size: 0.875rem;
}

.order-total strong {
  font-size: 1.125rem;
  color: var(--gray-900);
  margin-left: 0.5rem;
}

@media (max-width: 640px) {
  .order-header {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .order-footer {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
}
</style>
