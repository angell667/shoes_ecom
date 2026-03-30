<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useOrderStore } from '../stores/orders'
import { onMounted } from 'vue'

const authStore = useAuthStore()
const orderStore = useOrderStore()

const form = ref({
  name: '',
  email: '',
  phone: '',
  address: ''
})

const loading = ref(false)
const success = ref('')
const error = ref('')

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

const updateProfile = async () => {
  loading.value = true
  success.value = ''
  error.value = ''
  
  try {
    await authStore.updateProfile(form.value)
    success.value = 'Profile updated successfully!'
  } catch (err) {
    error.value = err.message || 'Failed to update profile'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (authStore.user) {
    form.value = {
      name: authStore.user.name || '',
      email: authStore.user.email || '',
      phone: authStore.user.phone || '',
      address: authStore.user.address || ''
    }
  }
  orderStore.fetchOrders()
})
</script>

<template>
  <div class="profile-page">
    <div class="container">
      <h1 class="page-title">My Profile</h1>

      <div class="profile-layout">
        <div class="profile-form-section">
          <div class="form-card">
            <h2>Account Information</h2>

            <div v-if="success" class="success-message">
              {{ success }}
            </div>
            
            <div v-if="error" class="error-message">
              {{ error }}
            </div>

            <form @submit.prevent="updateProfile" class="profile-form">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <input 
                  v-model="form.name"
                  type="text" 
                  class="form-input"
                  required
                />
              </div>

              <div class="form-group">
                <label class="form-label">Email</label>
                <input 
                  v-model="form.email"
                  type="email" 
                  class="form-input"
                  required
                />
              </div>

              <div class="form-group">
                <label class="form-label">Phone</label>
                <input 
                  v-model="form.phone"
                  type="tel" 
                  class="form-input"
                />
              </div>

              <div class="form-group">
                <label class="form-label">Address</label>
                <textarea 
                  v-model="form.address"
                  class="form-input"
                  rows="3"
                ></textarea>
              </div>

              <button type="submit" :disabled="loading" class="btn btn-primary">
                {{ loading ? 'Saving...' : 'Save Changes' }}
              </button>
            </form>
          </div>
        </div>

        <div class="profile-sidebar">
          <div class="sidebar-card">
            <h3>Recent Orders</h3>
            <div v-if="orderStore.orders.length === 0" class="no-orders">
              <p>No orders yet</p>
              <router-link to="/products" class="btn btn-outline btn-sm">Start Shopping</router-link>
            </div>
            <div v-else class="recent-orders">
              <div v-for="order in orderStore.orders.slice(0, 3)" :key="order.id" class="recent-order">
                <div class="order-info">
                  <span class="order-number">#{{ order.order_number }}</span>
                  <span class="order-date">{{ formatDate(order.created_at) }}</span>
                </div>
                <div class="order-meta">
                  <span class="order-items">{{ order.items?.length }} items</span>
                  <span class="order-total">{{ formatPrice(order.total) }}</span>
                </div>
              </div>
              <router-link to="/orders" class="view-all-link">
                View All Orders
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="5" y1="12" x2="19" y2="12"/>
                  <polyline points="12 5 19 12 12 19"/>
                </svg>
              </router-link>
            </div>
          </div>

          <div class="sidebar-card">
            <h3>Account Stats</h3>
            <div class="stats-grid">
              <div class="stat">
                <span class="stat-value">{{ orderStore.orders.length }}</span>
                <span class="stat-label">Total Orders</span>
              </div>
              <div class="stat">
                <span class="stat-value">
                  {{ formatPrice(orderStore.orders.reduce((sum, o) => sum + o.total, 0)) }}
                </span>
                <span class="stat-label">Total Spent</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-page {
  padding: 2rem 0 4rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 2rem;
}

.profile-layout {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 2rem;
  align-items: start;
}

.form-card,
.sidebar-card {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
}

.form-card h2,
.sidebar-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.success-message {
  background: rgba(34, 197, 94, 0.1);
  color: var(--success);
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.profile-form .form-group {
  margin-bottom: 1.25rem;
}

.profile-form .btn {
  margin-top: 0.5rem;
}

.profile-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.no-orders {
  text-align: center;
  padding: 1.5rem;
  color: var(--gray-500);
}

.no-orders p {
  margin-bottom: 1rem;
}

.recent-orders {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.recent-order {
  padding: 1rem;
  background: var(--gray-50);
  border-radius: 0.75rem;
}

.order-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.order-number {
  font-weight: 600;
  color: var(--gray-800);
}

.order-date {
  font-size: 0.75rem;
  color: var(--gray-500);
}

.order-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
}

.order-items {
  color: var(--gray-500);
}

.order-total {
  font-weight: 600;
  color: var(--gray-800);
}

.view-all-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  color: var(--primary);
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 0.5rem;
  transition: background 0.2s;
}

.view-all-link:hover {
  background: rgba(37, 99, 235, 0.1);
}

.view-all-link svg {
  width: 16px;
  height: 16px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.stat {
  text-align: center;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: 0.75rem;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gray-900);
}

.stat-label {
  font-size: 0.75rem;
  color: var(--gray-500);
}

@media (max-width: 1024px) {
  .profile-layout {
    grid-template-columns: 1fr;
  }
}
</style>
