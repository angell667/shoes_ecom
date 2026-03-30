<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useOrderStore } from '../stores/orders'

const route = useRoute()
const router = useRouter()
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
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
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

onMounted(async () => {
  await orderStore.fetchOrder(route.params.id)
})
</script>

<template>
  <div class="order-detail-page">
    <div class="container">
      <button @click="router.push('/orders')" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="19" y1="12" x2="5" y2="12"/>
          <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back to Orders
      </button>

      <div v-if="orderStore.loading" class="loading-state">
        <div class="skeleton" style="height: 200px;"></div>
      </div>

      <div v-else-if="orderStore.currentOrder" class="order-detail">
        <div class="order-header">
          <div class="header-left">
            <h1>Order #{{ orderStore.currentOrder.order_number }}</h1>
            <span class="order-date">Placed on {{ formatDate(orderStore.currentOrder.created_at) }}</span>
          </div>
          <span :class="['badge', 'badge-lg', getStatusClass(orderStore.currentOrder.status)]">
            {{ orderStore.currentOrder.status.charAt(0).toUpperCase() + orderStore.currentOrder.status.slice(1) }}
          </span>
        </div>

        <div class="order-content">
          <div class="order-items-section">
            <h2>Order Items</h2>
            <div class="items-list">
              <div v-for="item in orderStore.currentOrder.items" :key="item.id" class="order-item">
                <img :src="item.product?.image || 'https://via.placeholder.com/80'" :alt="item.product_name" />
                <div class="item-details">
                  <router-link :to="`/products/${item.product?.slug}`" class="item-name">
                    {{ item.product_name }}
                  </router-link>
                  <div class="item-meta">
                    <span v-if="item.size">Size: {{ item.size }}</span>
                    <span v-if="item.color">Color: {{ item.color }}</span>
                  </div>
                  <div class="item-price">{{ formatPrice(item.price) }} x {{ item.quantity }}</div>
                </div>
                <div class="item-total">
                  {{ formatPrice(item.price * item.quantity) }}
                </div>
              </div>
            </div>
          </div>

          <div class="order-sidebar">
            <div class="summary-card">
              <h3>Order Summary</h3>
              <div class="summary-row">
                <span>Subtotal</span>
                <span>{{ formatPrice(orderStore.currentOrder.subtotal) }}</span>
              </div>
              <div class="summary-row">
                <span>Shipping</span>
                <span>{{ orderStore.currentOrder.shipping === 0 ? 'Free' : formatPrice(orderStore.currentOrder.shipping) }}</span>
              </div>
              <div class="summary-row">
                <span>Tax</span>
                <span>{{ formatPrice(orderStore.currentOrder.tax) }}</span>
              </div>
              <div class="summary-total">
                <span>Total</span>
                <span>{{ formatPrice(orderStore.currentOrder.total) }}</span>
              </div>
            </div>

            <div class="info-card">
              <h3>Shipping Address</h3>
              <p>{{ orderStore.currentOrder.shipping_address }}</p>
            </div>

            <div class="info-card">
              <h3>Payment Method</h3>
              <p>{{ orderStore.currentOrder.payment_method?.replace('_', ' ') }}</p>
              <span :class="['badge', orderStore.currentOrder.payment_status === 'paid' ? 'badge-success' : 'badge-warning']">
                {{ orderStore.currentOrder.payment_status }}
              </span>
            </div>

            <div v-if="orderStore.currentOrder.notes" class="info-card">
              <h3>Order Notes</h3>
              <p>{{ orderStore.currentOrder.notes }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.order-detail-page {
  padding: 2rem 0 4rem;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  margin-bottom: 1.5rem;
  background: none;
  border: none;
  color: var(--gray-600);
  font-size: 0.875rem;
  cursor: pointer;
  transition: color 0.2s;
}

.back-btn:hover {
  color: var(--primary);
}

.back-btn svg {
  width: 18px;
  height: 18px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.header-left h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 0.25rem;
}

.order-date {
  color: var(--gray-500);
  font-size: 0.875rem;
}

.badge-lg {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.order-content {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 2rem;
  align-items: start;
}

.order-items-section {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
}

.order-items-section h2 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.order-item {
  display: grid;
  grid-template-columns: 80px 1fr auto;
  gap: 1.5rem;
  align-items: center;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: 0.75rem;
}

.order-item img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 0.5rem;
}

.item-name {
  display: block;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 0.5rem;
}

.item-name:hover {
  color: var(--primary);
}

.item-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.875rem;
  color: var(--gray-500);
  margin-bottom: 0.25rem;
}

.item-price {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.item-total {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--gray-900);
}

.order-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.summary-card,
.info-card {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
}

.summary-card h3,
.info-card h3 {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  font-size: 0.875rem;
  color: var(--gray-600);
}

.summary-total {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  margin-top: 0.5rem;
  border-top: 1px solid var(--gray-200);
  font-size: 1.25rem;
  font-weight: 700;
}

.info-card p {
  color: var(--gray-600);
  line-height: 1.6;
}

.info-card .badge {
  margin-top: 0.5rem;
}

@media (max-width: 1024px) {
  .order-content {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .order-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .order-item {
    grid-template-columns: 60px 1fr;
    gap: 1rem;
  }
  
  .item-total {
    grid-column: 2;
  }
}
</style>
