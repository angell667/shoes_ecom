<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const stats = ref(null)
const loading = ref(true)

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0
  }).format(price)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusBadge = (status) => {
  const badges = {
    pending: { bg: '#fef3c7', color: '#d97706' },
    processing: { bg: '#dbeafe', color: '#2563eb' },
    shipped: { bg: '#e0e7ff', color: '#4f46e5' },
    delivered: { bg: '#dcfce7', color: '#16a34a' },
    cancelled: { bg: '#fee2e2', color: '#dc2626' }
  }
  return badges[status] || { bg: '#f3f4f6', color: '#6b7280' }
}

const totalRevenue = computed(() => stats.value?.stats?.total_revenue || 0)
const totalOrders = computed(() => stats.value?.stats?.total_orders || 0)
const totalProducts = computed(() => stats.value?.stats?.total_products || 0)
const totalUsers = computed(() => stats.value?.stats?.total_users || 0)

const orderCompletionRate = computed(() => {
  if (!stats.value?.stats) return 0
  const completed = stats.value.stats.completed_orders || 0
  const total = stats.value.stats.total_orders || 1
  return Math.round((completed / total) * 100)
})

onMounted(async () => {
  try {
    const response = await axios.get('/api/admin/dashboard')
    stats.value = response.data
  } catch (error) {
    console.error('Dashboard error:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="dashboard">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back! Here's what's happening with your store.</p>
      </div>
      <div class="date-badge">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        {{ new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }) }}
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading dashboard data...</p>
    </div>

    <template v-else-if="stats">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-label">Total Revenue</p>
              <p class="stat-value">{{ formatPrice(totalRevenue) }}</p>
            </div>
            <div class="stat-icon blue">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="stat-trend positive">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            +12.5% from last month
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-label">Total Orders</p>
              <p class="stat-value">{{ totalOrders }}</p>
            </div>
            <div class="stat-icon green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
          </div>
          <div class="stat-trend positive">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            +8.2% from last month
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-label">Total Products</p>
              <p class="stat-value">{{ totalProducts }}</p>
            </div>
            <div class="stat-icon purple">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
          <div class="stat-trend neutral">
            Products in catalog
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-label">Total Users</p>
              <p class="stat-value">{{ totalUsers }}</p>
            </div>
            <div class="stat-icon orange">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
          </div>
          <div class="stat-trend positive">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            +24 new this week
          </div>
        </div>
      </div>

      <!-- Order Status -->
      <div class="card">
        <div class="card-header">
          <h2>Order Status Overview</h2>
        </div>
        <div class="card-body">
          <div class="status-grid">
            <div class="status-card pending">
              <div class="status-icon">⏳</div>
              <div class="status-info">
                <p class="status-value">{{ stats.stats.pending_orders }}</p>
                <p class="status-label">Pending</p>
              </div>
              <div class="progress-bar">
                <div class="progress-fill amber" :style="{ width: (stats.stats.pending_orders / (stats.stats.total_orders || 1)) * 100 + '%' }"></div>
              </div>
            </div>

            <div class="status-card processing">
              <div class="status-icon">⚙️</div>
              <div class="status-info">
                <p class="status-value">{{ stats.stats.processing_orders }}</p>
                <p class="status-label">Processing</p>
              </div>
              <div class="progress-bar">
                <div class="progress-fill blue" :style="{ width: (stats.stats.processing_orders / (stats.stats.total_orders || 1)) * 100 + '%' }"></div>
              </div>
            </div>

            <div class="status-card completed">
              <div class="status-icon">✅</div>
              <div class="status-info">
                <p class="status-value">{{ stats.stats.completed_orders }}</p>
                <p class="status-label">Completed</p>
              </div>
              <div class="progress-bar">
                <div class="progress-fill green" :style="{ width: (stats.stats.completed_orders / (stats.stats.total_orders || 1)) * 100 + '%' }"></div>
              </div>
            </div>

            <div class="status-card rate">
              <div class="status-icon">📊</div>
              <div class="status-info">
                <p class="status-value">{{ orderCompletionRate }}%</p>
                <p class="status-label">Completion Rate</p>
              </div>
              <div class="circular-progress">
                <svg viewBox="0 0 36 36" class="circular-chart">
                  <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                  <path class="circle" :stroke-dasharray="`${orderCompletionRate}, 100`" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tables -->
      <div class="tables-grid">
        <!-- Recent Orders -->
        <div class="card">
          <div class="card-header">
            <div class="header-with-badge">
              <h2>Recent Orders</h2>
              <span class="badge green">Live</span>
            </div>
            <router-link to="/admin/orders" class="view-all">View All →</router-link>
          </div>
          <div class="orders-list">
            <div v-for="order in stats.recent_orders" :key="order.id" class="order-row">
              <div class="order-main">
                <div class="avatar blue">{{ order.user?.name?.charAt(0) || 'U' }}</div>
                <div class="order-details">
                  <p class="order-id">#{{ order.order_number }}</p>
                  <p class="order-customer">{{ order.user?.name }}</p>
                </div>
              </div>
              <div class="order-meta">
                <p class="order-amount">{{ formatPrice(order.total) }}</p>
                <span class="status-badge" :style="{ backgroundColor: getStatusBadge(order.status).bg, color: getStatusBadge(order.status).color }">
                  {{ order.status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Low Stock -->
        <div class="card">
          <div class="card-header">
            <div class="header-with-badge">
              <h2>⚠️ Low Stock Alert</h2>
              <span class="badge amber">{{ stats.low_stock_products?.length || 0 }} items</span>
            </div>
            <router-link to="/admin/products" class="view-all">Manage →</router-link>
          </div>
          <div class="stock-list">
            <div v-for="product in stats.low_stock_products" :key="product.id" class="stock-item">
              <img :src="product.image || 'https://via.placeholder.com/48'" :alt="product.name" class="stock-image"/>
              <div class="stock-info">
                <p class="stock-name">{{ product.name }}</p>
                <p class="stock-sku">SKU: {{ product.sku }}</p>
              </div>
              <div class="stock-status">
                <div class="stock-bar-bg">
                  <div class="stock-bar-fill" :style="{ width: Math.min((product.stock / 50) * 100, 100) + '%' }"></div>
                </div>
                <span class="stock-count" :class="product.stock === 0 ? 'out' : 'low'">{{ product.stock }} left</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <div class="card-header">
          <h2>Quick Actions</h2>
        </div>
        <div class="card-body">
          <div class="actions-grid">
            <router-link to="/admin/products/new" class="action-card">
              <div class="action-icon blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <span>Add Product</span>
            </router-link>

            <router-link to="/admin/orders" class="action-card">
              <div class="action-icon green">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <span>View Orders</span>
            </router-link>

            <router-link to="/admin/categories" class="action-card">
              <div class="action-icon purple">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <span>Categories</span>
            </router-link>

            <router-link to="/admin/users" class="action-card">
              <div class="action-icon orange">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <span>Manage Users</span>
            </router-link>

            <router-link to="/" class="action-card">
              <div class="action-icon gray">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
              </div>
              <span>View Store</span>
            </router-link>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.dashboard { padding: 0; }

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  flex-wrap: wrap;
  gap: 16px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
}

.page-subtitle {
  color: #6b7280;
  margin-top: 4px;
}

.date-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: white;
  padding: 12px 20px;
  border-radius: 12px;
  font-weight: 500;
  color: #374151;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px;
  color: #6b7280;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin { to { transform: rotate(360deg); } }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.stat-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #111827;
  margin-top: 4px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon.blue { background: #dbeafe; color: #2563eb; }
.stat-icon.green { background: #dcfce7; color: #16a34a; }
.stat-icon.purple { background: #f3e8ff; color: #9333ea; }
.stat-icon.orange { background: #ffedd5; color: #ea580c; }

.stat-trend {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  margin-top: 16px;
}

.stat-trend.positive { color: #16a34a; }
.stat-trend.neutral { color: #6b7280; }

.card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 32px;
}

.card-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h2 {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.card-body {
  padding: 24px;
}

.header-with-badge {
  display: flex;
  align-items: center;
  gap: 12px;
}

.badge {
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 9999px;
}

.badge.green { background: #dcfce7; color: #16a34a; }
.badge.amber { background: #fef3c7; color: #d97706; }

.view-all {
  color: #3b82f6;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
}

.view-all:hover { text-decoration: underline; }

.status-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.status-card {
  padding: 20px;
  border-radius: 12px;
}

.status-card.pending { background: #fffbeb; }
.status-card.processing { background: #eff6ff; }
.status-card.completed { background: #f0fdf4; }
.status-card.rate { background: #faf5ff; }

.status-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

.status-value {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
}

.status-label {
  font-size: 14px;
  color: #6b7280;
}

.progress-bar {
  height: 8px;
  background: rgba(0,0,0,0.1);
  border-radius: 4px;
  margin-top: 16px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 1s ease;
}

.progress-fill.amber { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.progress-fill.blue { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
.progress-fill.green { background: linear-gradient(90deg, #22c55e, #4ade80); }

.circular-progress {
  width: 64px;
  height: 64px;
  margin-top: 12px;
}

.circular-chart {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.circle-bg {
  fill: none;
  stroke: #e5e7eb;
  stroke-width: 3.8;
}

.circle {
  fill: none;
  stroke: #9333ea;
  stroke-width: 3.8;
  stroke-linecap: round;
  animation: progress 1s ease-out forwards;
}

@keyframes progress {
  0% { stroke-dasharray: 0, 100; }
}

.tables-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 24px;
  margin-bottom: 32px;
}

.orders-list, .stock-list {
  padding: 16px 24px;
}

.order-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #f9fafb;
  border-radius: 12px;
  margin-bottom: 12px;
  transition: background 0.2s;
}

.order-row:hover { background: #f3f4f6; }

.order-main {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 16px;
}

.avatar.blue { background: linear-gradient(135deg, #3b82f6, #8b5cf6); }

.order-id {
  font-weight: 600;
  color: #111827;
}

.order-customer {
  font-size: 13px;
  color: #6b7280;
}

.order-meta {
  text-align: right;
}

.order-amount {
  font-weight: 600;
  color: #111827;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 9999px;
  text-transform: capitalize;
  margin-top: 4px;
}

.stock-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 12px;
  margin-bottom: 12px;
}

.stock-image {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
}

.stock-info {
  flex: 1;
  min-width: 0;
}

.stock-name {
  font-weight: 600;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stock-sku {
  font-size: 12px;
  color: #6b7280;
}

.stock-status {
  text-align: right;
}

.stock-bar-bg {
  width: 60px;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 4px;
}

.stock-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #f59e0b, #ef4444);
  border-radius: 3px;
}

.stock-count {
  font-size: 12px;
  font-weight: 600;
}

.stock-count.low { color: #f59e0b; }
.stock-count.out { color: #ef4444; }

.actions-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

.action-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 24px;
  background: #f9fafb;
  border-radius: 16px;
  text-decoration: none;
  transition: all 0.3s;
}

.action-card:hover {
  transform: translateY(-4px);
  background: #f3f4f6;
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.action-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.action-icon.blue { background: linear-gradient(135deg, #3b82f6, #8b5cf6); }
.action-icon.green { background: linear-gradient(135deg, #10b981, #3b82f6); }
.action-icon.purple { background: linear-gradient(135deg, #8b5cf6, #ec4899); }
.action-icon.orange { background: linear-gradient(135deg, #f59e0b, #ef4444); }
.action-icon.gray { background: linear-gradient(135deg, #6b7280, #374151); }

.action-card span {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

@media (max-width: 1200px) {
  .stats-grid, .status-grid { grid-template-columns: repeat(2, 1fr); }
  .tables-grid { grid-template-columns: 1fr; }
  .actions-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
  .stats-grid, .status-grid { grid-template-columns: 1fr; }
  .actions-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
