<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const stats = ref(null)
const loading = ref(true)
const activeTab = ref('overview')

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
    cancelled: { bg: '#fee2e2', color: '#dc2626' },
    completed: { bg: '#dcfce7', color: '#16a34a' },
    failed: { bg: '#fee2e2', color: '#dc2626' },
    refunded: { bg: '#f3e8ff', color: '#9333ea' }
  }
  return badges[status] || { bg: '#f3f4f6', color: '#6b7280' }
}

// Computed properties for stats
const totalRevenue = computed(() => stats.value?.stats?.total_revenue || 0)
const monthlyRevenue = computed(() => stats.value?.stats?.monthly_revenue || 0)
const totalOrders = computed(() => stats.value?.stats?.total_orders || 0)
const totalProducts = computed(() => stats.value?.stats?.total_products || 0)
const totalUsers = computed(() => stats.value?.stats?.total_users || 0)
const totalReviews = computed(() => stats.value?.stats?.total_reviews || 0)
const totalCoupons = computed(() => stats.value?.stats?.total_coupons || 0)
const averageRating = computed(() => stats.value?.stats?.average_rating || 0)
const totalStockValue = computed(() => stats.value?.stats?.total_stock_value || 0)

const orderCompletionRate = computed(() => {
  if (!stats.value?.stats) return 0
  const completed = (stats.value.stats.completed_orders || 0) + (stats.value.stats.shipped_orders || 0)
  const total = stats.value.stats.total_orders || 1
  return Math.round((completed / total) * 100)
})

onMounted(async () => {
  try {
    const response = await axios.get('/api/admin/dashboard')
    stats.value = response.data
    console.log('Dashboard data:', stats.value)
  } catch (error) {
    console.error('Dashboard error:', error)
  } finally {
    loading.value = false
  }
})

const tabs = [
  { id: 'overview', label: 'Overview', icon: '📊' },
  { id: 'payments', label: 'Payments', icon: '💳' },
  { id: 'reviews', label: 'Reviews', icon: '⭐' },
  { id: 'coupons', label: 'Coupons', icon: '🏷️' },
  { id: 'inventory', label: 'Inventory', icon: '📦' },
  { id: 'users', label: 'Users', icon: '👥' }
]
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
      <!-- Navigation Tabs -->
      <div class="tabs-container">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :class="['tab-btn', { active: activeTab === tab.id }]"
          @click="activeTab = tab.id"
        >
          <span class="tab-icon">{{ tab.icon }}</span>
          <span class="tab-label">{{ tab.label }}</span>
        </button>
      </div>

      <!-- Overview Tab -->
      <div v-if="activeTab === 'overview'" class="tab-content">
        <!-- Stats Cards Row 1 -->
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
            <div class="stat-footer">
              <span class="stat-detail">Monthly: {{ formatPrice(monthlyRevenue) }}</span>
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
            <div class="stat-footer">
              <span class="stat-detail">Avg: {{ formatPrice(stats.stats.average_order_value) }}</span>
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
            <div class="stat-footer">
              <span class="stat-detail">Stock Value: {{ formatPrice(totalStockValue) }}</span>
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
            <div class="stat-footer">
              <span class="stat-detail">Wishlist items: {{ stats.stats.total_wishlist_items }}</span>
            </div>
          </div>
        </div>

        <!-- Stats Cards Row 2 - New Features -->
        <div class="stats-grid">
          <div class="stat-card highlight">
            <div class="stat-content">
              <div class="stat-info">
                <p class="stat-label">Reviews</p>
                <p class="stat-value">{{ totalReviews }}</p>
              </div>
              <div class="stat-icon yellow">
                ⭐
              </div>
            </div>
            <div class="stat-footer">
              <span class="stat-detail">Avg Rating: {{ averageRating }}/5</span>
              <span class="stat-badge warning">{{ stats.stats.pending_reviews }} pending</span>
            </div>
          </div>

          <div class="stat-card highlight">
            <div class="stat-content">
              <div class="stat-info">
                <p class="stat-label">Active Coupons</p>
                <p class="stat-value">{{ stats.stats.active_coupons }}</p>
              </div>
              <div class="stat-icon pink">
                🏷️
              </div>
            </div>
            <div class="stat-footer">
              <span class="stat-detail">Used: {{ stats.stats.total_coupon_usage }} times</span>
            </div>
          </div>

          <div class="stat-card highlight">
            <div class="stat-content">
              <div class="stat-info">
                <p class="stat-label">Payments</p>
                <p class="stat-value">{{ stats.stats.total_payments }}</p>
              </div>
              <div class="stat-icon teal">
                💳
              </div>
            </div>
            <div class="stat-footer">
              <span class="stat-success">{{ stats.stats.completed_payments }} completed</span>
              <span class="stat-danger">{{ stats.stats.failed_payments }} failed</span>
            </div>
          </div>

          <div class="stat-card highlight">
            <div class="stat-content">
              <div class="stat-info">
                <p class="stat-label">Inventory Alerts</p>
                <p class="stat-value">{{ stats.stats.low_stock_count + stats.stats.out_of_stock_count }}</p>
              </div>
              <div class="stat-icon red">
                📦
              </div>
            </div>
            <div class="stat-footer">
              <span class="stat-warning">{{ stats.stats.low_stock_count }} low stock</span>
              <span class="stat-danger">{{ stats.stats.out_of_stock_count }} out</span>
            </div>
          </div>
        </div>

        <!-- Order Status -->
        <div class="card">
          <div class="card-header">
            <h2>Order Status Overview</h2>
            <router-link to="/admin/orders" class="view-all">View All Orders →</router-link>
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

              <div class="status-card shipped">
                <div class="status-icon">🚚</div>
                <div class="status-info">
                  <p class="status-value">{{ stats.stats.shipped_orders }}</p>
                  <p class="status-label">Shipped</p>
                </div>
                <div class="progress-bar">
                  <div class="progress-fill purple" :style="{ width: (stats.stats.shipped_orders / (stats.stats.total_orders || 1)) * 100 + '%' }"></div>
                </div>
              </div>

              <div class="status-card completed">
                <div class="status-icon">✅</div>
                <div class="status-info">
                  <p class="status-value">{{ stats.stats.completed_orders }}</p>
                  <p class="status-label">Delivered</p>
                </div>
                <div class="progress-bar">
                  <div class="progress-fill green" :style="{ width: (stats.stats.completed_orders / (stats.stats.total_orders || 1)) * 100 + '%' }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tables Grid -->
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
              <div v-for="order in stats.recent_orders?.slice(0, 5)" :key="order.id" class="order-row">
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

          <!-- Low Stock Alert -->
          <div class="card">
            <div class="card-header">
              <div class="header-with-badge">
                <h2>⚠️ Low Stock Alert</h2>
                <span class="badge amber">{{ stats.low_stock_products?.length || 0 }} items</span>
              </div>
              <router-link to="/admin/products" class="view-all">Manage →</router-link>
            </div>
            <div class="stock-list">
              <div v-for="product in stats.low_stock_products?.slice(0, 5)" :key="product.id" class="stock-item">
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
      </div>

      <!-- Payments Tab -->
      <div v-if="activeTab === 'payments'" class="tab-content">
        <div class="stats-grid small">
          <div class="mini-stat-card">
            <p class="mini-stat-value">{{ stats.stats.total_payments }}</p>
            <p class="mini-stat-label">Total Payments</p>
          </div>
          <div class="mini-stat-card success">
            <p class="mini-stat-value">{{ stats.stats.completed_payments }}</p>
            <p class="mini-stat-label">Completed</p>
          </div>
          <div class="mini-stat-card warning">
            <p class="mini-stat-value">{{ stats.stats.pending_payments }}</p>
            <p class="mini-stat-label">Pending</p>
          </div>
          <div class="mini-stat-card danger">
            <p class="mini-stat-value">{{ stats.stats.failed_payments }}</p>
            <p class="mini-stat-label">Failed</p>
          </div>
          <div class="mini-stat-card info">
            <p class="mini-stat-value">{{ stats.stats.refunded_payments }}</p>
            <p class="mini-stat-label">Refunded</p>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2>Recent Payments</h2>
          </div>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Transaction ID</th>
                  <th>Order</th>
                  <th>Customer</th>
                  <th>Amount</th>
                  <th>Provider</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="payment in stats.recent_payments" :key="payment.id">
                  <td class="mono">{{ payment.transaction_id }}</td>
                  <td>#{{ payment.order?.order_number }}</td>
                  <td>{{ payment.order?.user?.name }}</td>
                  <td class="font-bold">{{ formatPrice(payment.amount) }}</td>
                  <td><span class="badge gray">{{ payment.provider }}</span></td>
                  <td>
                    <span class="status-badge" :style="{ backgroundColor: getStatusBadge(payment.status).bg, color: getStatusBadge(payment.status).color }">
                      {{ payment.status }}
                    </span>
                  </td>
                  <td>{{ formatDate(payment.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Reviews Tab -->
      <div v-if="activeTab === 'reviews'" class="tab-content">
        <div class="stats-grid small">
          <div class="mini-stat-card">
            <p class="mini-stat-value">{{ stats.stats.total_reviews }}</p>
            <p class="mini-stat-label">Total Reviews</p>
          </div>
          <div class="mini-stat-card success">
            <p class="mini-stat-value">{{ stats.stats.approved_reviews }}</p>
            <p class="mini-stat-label">Approved</p>
          </div>
          <div class="mini-stat-card warning">
            <p class="mini-stat-value">{{ stats.stats.pending_reviews }}</p>
            <p class="mini-stat-label">Pending Approval</p>
          </div>
          <div class="mini-stat-card rating">
            <p class="mini-stat-value">{{ stats.stats.average_rating }}/5 ⭐</p>
            <p class="mini-stat-label">Average Rating</p>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="header-with-badge">
              <h2>Pending Reviews</h2>
              <span class="badge amber">{{ stats.pending_reviews?.length || 0 }} pending</span>
            </div>
            <router-link to="/admin/reviews" class="view-all">Manage All →</router-link>
          </div>
          <div class="reviews-list">
            <div v-for="review in stats.pending_reviews" :key="review.id" class="review-card">
              <div class="review-header">
                <div class="review-rating">
                  <span v-for="n in review.rating" :key="n" class="star">⭐</span>
                </div>
                <span class="review-date">{{ formatDate(review.created_at) }}</span>
              </div>
              <div class="review-product">
                <img :src="review.product?.image || 'https://via.placeholder.com/40'" :alt="review.product?.name" class="review-product-image"/>
                <span>{{ review.product?.name }}</span>
              </div>
              <p class="review-comment" v-if="review.comment">{{ review.comment }}</p>
              <div class="review-footer">
                <span class="review-author">By: {{ review.user?.name }}</span>
                <div class="review-actions">
                  <button class="btn-approve">✓ Approve</button>
                  <button class="btn-reject">✗ Reject</button>
                </div>
              </div>
            </div>
            <div v-if="!stats.pending_reviews?.length" class="empty-state">
              <p>✅ No pending reviews</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Coupons Tab -->
      <div v-if="activeTab === 'coupons'" class="tab-content">
        <div class="stats-grid small">
          <div class="mini-stat-card">
            <p class="mini-stat-value">{{ stats.stats.total_coupons }}</p>
            <p class="mini-stat-label">Total Coupons</p>
          </div>
          <div class="mini-stat-card success">
            <p class="mini-stat-value">{{ stats.stats.active_coupons }}</p>
            <p class="mini-stat-label">Active</p>
          </div>
          <div class="mini-stat-card info">
            <p class="mini-stat-value">{{ stats.stats.used_coupons }}</p>
            <p class="mini-stat-label">Used</p>
          </div>
          <div class="mini-stat-card purple">
            <p class="mini-stat-value">{{ stats.stats.total_coupon_usage }}</p>
            <p class="mini-stat-label">Total Usage</p>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2>Recent Coupons</h2>
            <router-link to="/admin/coupons" class="view-all">Manage All →</router-link>
          </div>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Type</th>
                  <th>Value</th>
                  <th>Min Order</th>
                  <th>Usage</th>
                  <th>Status</th>
                  <th>Expires</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="coupon in stats.recent_coupons" :key="coupon.id">
                  <td class="mono font-bold">{{ coupon.code }}</td>
                  <td>{{ coupon.type }}</td>
                  <td>{{ coupon.type === 'percentage' ? coupon.value + '%' : formatPrice(coupon.value) }}</td>
                  <td>{{ coupon.minimum_order ? formatPrice(coupon.minimum_order) : '-' }}</td>
                  <td>{{ coupon.used_count }}{{ coupon.usage_limit ? '/' + coupon.usage_limit : '' }}</td>
                  <td>
                    <span :class="['status-badge', coupon.is_active ? 'active' : 'inactive']">
                      {{ coupon.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td>{{ coupon.expires_at ? formatDate(coupon.expires_at) : 'Never' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Inventory Tab -->
      <div v-if="activeTab === 'inventory'" class="tab-content">
        <div class="stats-grid small">
          <div class="mini-stat-card warning">
            <p class="mini-stat-value">{{ stats.stats.low_stock_count }}</p>
            <p class="mini-stat-label">Low Stock</p>
          </div>
          <div class="mini-stat-card danger">
            <p class="mini-stat-value">{{ stats.stats.out_of_stock_count }}</p>
            <p class="mini-stat-label">Out of Stock</p>
          </div>
          <div class="mini-stat-card success">
            <p class="mini-stat-value">{{ formatPrice(stats.stats.total_stock_value) }}</p>
            <p class="mini-stat-label">Total Stock Value</p>
          </div>
        </div>

        <div class="tables-grid">
          <!-- Low Stock Products -->
          <div class="card">
            <div class="card-header">
              <h2>⚠️ Low Stock Products</h2>
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
                  <span class="stock-count low">{{ product.stock }} left</span>
                </div>
              </div>
              <div v-if="!stats.low_stock_products?.length" class="empty-state">
                <p>✅ All products well stocked</p>
              </div>
            </div>
          </div>

          <!-- Recent Inventory Activity -->
          <div class="card">
            <div class="card-header">
              <h2>📋 Recent Inventory Activity</h2>
            </div>
            <div class="inventory-list">
              <div v-for="log in stats.recent_inventory_logs" :key="log.id" class="inventory-item">
                <div class="inventory-icon" :class="log.quantity > 0 ? 'add' : 'subtract'">
                  {{ log.quantity > 0 ? '+' : '' }}{{ log.quantity }}
                </div>
                <div class="inventory-info">
                  <p class="inventory-product">{{ log.product?.name }}</p>
                  <p class="inventory-type">{{ log.type }} • {{ log.user?.name || 'System' }}</p>
                </div>
                <div class="inventory-stock">
                  <span>{{ log.stock_before }} → {{ log.stock_after }}</span>
                </div>
              </div>
              <div v-if="!stats.recent_inventory_logs?.length" class="empty-state">
                <p>No recent inventory activity</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Tab -->
      <div v-if="activeTab === 'users'" class="tab-content">
        <div class="stats-grid small">
          <div class="mini-stat-card">
            <p class="mini-stat-value">{{ stats.stats.total_users }}</p>
            <p class="mini-stat-label">Total Customers</p>
          </div>
          <div class="mini-stat-card info">
            <p class="mini-stat-value">{{ stats.stats.total_wishlist_items }}</p>
            <p class="mini-stat-label">Wishlist Items</p>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2>Recent Customers</h2>
            <router-link to="/admin/users" class="view-all">View All →</router-link>
          </div>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Joined</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in stats.recent_users" :key="user.id">
                  <td>
                    <div class="user-cell">
                      <div class="avatar small blue">{{ user.name?.charAt(0) }}</div>
                      <span>{{ user.name }}</span>
                    </div>
                  </td>
                  <td>{{ user.email }}</td>
                  <td>{{ formatDate(user.created_at) }}</td>
                  <td>
                    <span :class="['status-badge', user.email_verified_at ? 'verified' : 'unverified']">
                      {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Top Selling Products -->
      <div v-if="activeTab === 'overview'" class="card">
        <div class="card-header">
          <h2>🏆 Top Selling Products</h2>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Total Sold</th>
                <th>Revenue</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in stats.top_selling_products" :key="item.product_id">
                <td>
                  <div class="product-cell">
                    <img :src="item.product?.image || 'https://via.placeholder.com/40'" :alt="item.product?.name" class="product-thumb"/>
                    <span>{{ item.product?.name }}</span>
                  </div>
                </td>
                <td class="font-bold">{{ item.total_sold }} units</td>
                <td class="font-bold">{{ formatPrice(item.product?.price * item.total_sold) }}</td>
              </tr>
            </tbody>
          </table>
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
  margin-bottom: 24px;
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

/* Tabs */
.tabs-container {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  background: white;
  padding: 8px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  flex-wrap: wrap;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #6b7280;
  transition: all 0.2s;
}

.tab-btn:hover {
  background: #f3f4f6;
}

.tab-btn.active {
  background: #3b82f6;
  color: white;
}

.tab-icon {
  font-size: 18px;
}

.tab-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
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

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-bottom: 24px;
}

.stats-grid.small {
  grid-template-columns: repeat(5, 1fr);
  margin-bottom: 24px;
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

.stat-card.highlight {
  border-left: 4px solid;
}

.stat-card.highlight:nth-child(1) { border-left-color: #fbbf24; }
.stat-card.highlight:nth-child(2) { border-left-color: #ec4899; }
.stat-card.highlight:nth-child(3) { border-left-color: #14b8a6; }
.stat-card.highlight:nth-child(4) { border-left-color: #ef4444; }

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

.stat-footer {
  margin-top: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
}

.stat-detail {
  color: #6b7280;
}

.stat-badge {
  padding: 2px 8px;
  border-radius: 9999px;
  font-weight: 600;
}

.stat-badge.warning {
  background: #fef3c7;
  color: #d97706;
}

.stat-success { color: #16a34a; }
.stat-warning { color: #f59e0b; }
.stat-danger { color: #dc2626; }

/* Mini Stat Cards */
.mini-stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  border-top: 4px solid #3b82f6;
}

.mini-stat-card.success { border-top-color: #16a34a; }
.mini-stat-card.warning { border-top-color: #f59e0b; }
.mini-stat-card.danger { border-top-color: #dc2626; }
.mini-stat-card.info { border-top-color: #3b82f6; }
.mini-stat-card.purple { border-top-color: #9333ea; }
.mini-stat-card.rating { border-top-color: #fbbf24; }

.mini-stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
}

.mini-stat-label {
  font-size: 13px;
  color: #6b7280;
  margin-top: 4px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.stat-icon.blue { background: #dbeafe; }
.stat-icon.green { background: #dcfce7; }
.stat-icon.purple { background: #f3e8ff; }
.stat-icon.orange { background: #ffedd5; }
.stat-icon.yellow { background: #fef3c7; }
.stat-icon.pink { background: #fce7f3; }
.stat-icon.teal { background: #ccfbf1; }
.stat-icon.red { background: #fee2e2; }

/* Card */
.card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 24px;
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
.badge.gray { background: #f3f4f6; color: #6b7280; }

.view-all {
  color: #3b82f6;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
}

.view-all:hover { text-decoration: underline; }

/* Status Grid */
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
.status-card.shipped { background: #e0e7ff; }
.status-card.completed { background: #f0fdf4; }

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
.progress-fill.purple { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
.progress-fill.green { background: linear-gradient(90deg, #22c55e, #4ade80); }

/* Tables */
.tables-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-bottom: 24px;
}

.table-container {
  overflow-x: auto;
  padding: 16px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #f3f4f6;
}

.data-table th {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
}

.data-table td {
  font-size: 14px;
  color: #374151;
}

.data-table tbody tr:hover {
  background: #f9fafb;
}

.font-bold { font-weight: 600; }
.mono { font-family: monospace; }

.user-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.product-thumb {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  object-fit: cover;
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
}

.avatar.small {
  width: 32px;
  height: 32px;
  font-size: 14px;
}

.avatar.blue { background: linear-gradient(135deg, #3b82f6, #8b5cf6); }

/* Orders List */
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
}

.order-main {
  display: flex;
  align-items: center;
  gap: 12px;
}

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
}

.status-badge.active { background: #dcfce7; color: #16a34a; }
.status-badge.inactive { background: #f3f4f6; color: #6b7280; }
.status-badge.verified { background: #dcfce7; color: #16a34a; }
.status-badge.unverified { background: #fef3c7; color: #d97706; }

/* Stock */
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

/* Reviews */
.reviews-list {
  padding: 16px 24px;
}

.review-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.review-rating {
  font-size: 16px;
}

.review-date {
  font-size: 12px;
  color: #6b7280;
}

.review-product {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.review-product-image {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  object-fit: cover;
}

.review-comment {
  color: #374151;
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: 12px;
}

.review-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.review-author {
  font-size: 12px;
  color: #6b7280;
}

.review-actions {
  display: flex;
  gap: 8px;
}

.btn-approve, .btn-reject {
  padding: 4px 12px;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.btn-approve {
  background: #dcfce7;
  color: #16a34a;
}

.btn-reject {
  background: #fee2e2;
  color: #dc2626;
}

/* Inventory */
.inventory-list {
  padding: 16px 24px;
}

.inventory-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 12px;
  margin-bottom: 12px;
}

.inventory-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
}

.inventory-icon.add {
  background: #dcfce7;
  color: #16a34a;
}

.inventory-icon.subtract {
  background: #fee2e2;
  color: #dc2626;
}

.inventory-info {
  flex: 1;
}

.inventory-product {
  font-weight: 600;
  color: #111827;
}

.inventory-type {
  font-size: 12px;
  color: #6b7280;
  text-transform: capitalize;
}

.inventory-stock {
  font-size: 13px;
  color: #6b7280;
}

/* Actions */
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

.empty-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

/* Responsive */
@media (max-width: 1200px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .stats-grid.small { grid-template-columns: repeat(3, 1fr); }
  .tables-grid { grid-template-columns: 1fr; }
  .actions-grid { grid-template-columns: repeat(3, 1fr); }
  .status-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
  .stats-grid, .stats-grid.small, .status-grid { grid-template-columns: 1fr; }
  .actions-grid { grid-template-columns: repeat(2, 1fr); }
  .tabs-container { overflow-x: auto; }
}
</style>