<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const payments = ref([])
const stats = ref(null)
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const selectedStatus = ref('')
const selectedProvider = ref('')

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2
  }).format(price)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusBadge = (status) => {
  const badges = {
    completed: { bg: '#dcfce7', color: '#16a34a', label: 'Completed' },
    pending: { bg: '#fef3c7', color: '#d97706', label: 'Pending' },
    failed: { bg: '#fee2e2', color: '#dc2626', label: 'Failed' },
    refunded: { bg: '#f3e8ff', color: '#9333ea', label: 'Refunded' },
    partially_refunded: { bg: '#e0e7ff', color: '#4f46e5', label: 'Partial Refund' },
    cancelled: { bg: '#f3f4f6', color: '#6b7280', label: 'Cancelled' }
  }
  return badges[status] || { bg: '#f3f4f6', color: '#6b7280', label: status }
}

const getProviderBadge = (provider) => {
  const badges = {
    stripe: { bg: '#e0e7ff', color: '#4f46e5', label: 'Stripe' },
    paypal: { bg: '#dbeafe', color: '#2563eb', label: 'PayPal' }
  }
  return badges[provider] || { bg: '#f3f4f6', color: '#6b7280', label: provider }
}

const fetchPayments = async (page = 1) => {
  try {
    let url = `/api/admin/payments?page=${page}`
    if (selectedStatus.value) url += `&status=${selectedStatus.value}`
    if (selectedProvider.value) url += `&provider=${selectedProvider.value}`
    
    const response = await axios.get(url)
    payments.value = response.data.data
    currentPage.value = response.data.current_page
    totalPages.value = response.data.last_page
  } catch (error) {
    console.error('Error fetching payments:', error)
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/admin/dashboard')
    stats.value = response.data.stats
  } catch (error) {
    console.error('Error fetching payment stats:', error)
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchPayments(page)
  }
}

const filterByStatus = (status) => {
  selectedStatus.value = status
  fetchPayments(1)
}

const filterByProvider = (provider) => {
  selectedProvider.value = provider
  fetchPayments(1)
}

const clearFilters = () => {
  selectedStatus.value = ''
  selectedProvider.value = ''
  fetchPayments(1)
}

onMounted(async () => {
  try {
    await Promise.all([fetchPayments(), fetchStats()])
  } catch (error) {
    console.error('Error loading payments data:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="payments-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Payment Management</h1>
        <p class="page-subtitle">View and manage all payment transactions</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading payments...</p>
    </div>

    <template v-else>
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-label">Total Payments</p>
              <p class="stat-value">{{ stats?.total_payments || 0 }}</p>
            </div>
            <div class="stat-icon blue">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="stat-card success">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-label">Completed</p>
              <p class="stat-value">{{ stats?.completed_payments || 0 }}</p>
            </div>
            <div class="stat-icon green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="stat-card warning">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-label">Pending</p>
              <p class="stat-value">{{ stats?.pending_payments || 0 }}</p>
            </div>
            <div class="stat-icon amber">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="stat-card danger">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-label">Failed</p>
              <p class="stat-value">{{ stats?.failed_payments || 0 }}</p>
            </div>
            <div class="stat-icon red">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="stat-card info">
          <div class="stat-content">
            <div class="stat-info">
              <p class="stat-label">Refunded</p>
              <p class="stat-value">{{ stats?.refunded_payments || 0 }}</p>
            </div>
            <div class="stat-icon purple">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-header">
          <h3>Filters</h3>
          <button @click="clearFilters" class="btn-clear">Clear All</button>
        </div>
        <div class="filters-row">
          <div class="filter-group">
            <label>Status</label>
            <div class="filter-buttons">
              <button 
                :class="['filter-btn', { active: selectedStatus === '' }]"
                @click="filterByStatus('')"
              >All</button>
              <button 
                :class="['filter-btn', { active: selectedStatus === 'completed' }]"
                @click="filterByStatus('completed')"
              >Completed</button>
              <button 
                :class="['filter-btn', { active: selectedStatus === 'pending' }]"
                @click="filterByStatus('pending')"
              >Pending</button>
              <button 
                :class="['filter-btn', { active: selectedStatus === 'failed' }]"
                @click="filterByStatus('failed')"
              >Failed</button>
              <button 
                :class="['filter-btn', { active: selectedStatus === 'refunded' }]"
                @click="filterByStatus('refunded')"
              >Refunded</button>
            </div>
          </div>
          <div class="filter-group">
            <label>Provider</label>
            <div class="filter-buttons">
              <button 
                :class="['filter-btn', { active: selectedProvider === '' }]"
                @click="filterByProvider('')"
              >All</button>
              <button 
                :class="['filter-btn stripe', { active: selectedProvider === 'stripe' }]"
                @click="filterByProvider('stripe')"
              >
                <span class="provider-icon">💳</span> Stripe
              </button>
              <button 
                :class="['filter-btn paypal', { active: selectedProvider === 'paypal' }]"
                @click="filterByProvider('paypal')"
              >
                <span class="provider-icon">🅿️</span> PayPal
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Payments Table -->
      <div class="card">
        <div class="card-header">
          <h2>All Payments</h2>
          <span class="pagination-info">Page {{ currentPage }} of {{ totalPages }}</span>
        </div>
        <div class="card-body">
          <div v-if="payments.length === 0" class="empty-state">
            <div class="empty-icon">💳</div>
            <p>No payments found</p>
          </div>
          <div v-else class="table-container">
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
                <tr v-for="payment in payments" :key="payment.id">
                  <td>
                    <span class="mono">{{ payment.transaction_id }}</span>
                  </td>
                  <td>
                    <router-link :to="`/admin/orders/${payment.order?.id}`" class="order-link">
                      #{{ payment.order?.order_number }}
                    </router-link>
                  </td>
                  <td>{{ payment.order?.user?.name || 'N/A' }}</td>
                  <td class="font-bold">{{ formatPrice(payment.amount) }}</td>
                  <td>
                    <span :class="['provider-badge', payment.provider]">
                      {{ getProviderBadge(payment.provider).label }}
                    </span>
                  </td>
                  <td>
                    <span 
                      class="status-badge"
                      :style="{ backgroundColor: getStatusBadge(payment.status).bg, color: getStatusBadge(payment.status).color }"
                    >
                      {{ getStatusBadge(payment.status).label }}
                    </span>
                  </td>
                  <td>{{ formatDate(payment.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="pagination">
            <button 
              @click="changePage(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="page-btn"
            >
              Previous
            </button>
            <span class="page-info">{{ currentPage }} / {{ totalPages }}</span>
            <button 
              @click="changePage(currentPage + 1)" 
              :disabled="currentPage === totalPages"
              class="page-btn"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- Payment Providers Info -->
      <div class="providers-info">
        <div class="provider-card stripe">
          <div class="provider-header">
            <h3>💳 Stripe</h3>
            <span class="status-active">Active</span>
          </div>
          <p>Credit/debit card payments processed securely through Stripe.</p>
          <ul>
            <li>Supports Visa, Mastercard, American Express</li>
            <li>3D Secure authentication</li>
            <li>Real-time payment status updates</li>
          </ul>
        </div>
        <div class="provider-card paypal">
          <div class="provider-header">
            <h3>🅿️ PayPal</h3>
            <span class="status-active">Active</span>
          </div>
          <p>PayPal account and card payments via PayPal checkout.</p>
          <ul>
            <li>PayPal account payments</li>
            <li>Credit/debit cards via PayPal</li>
            <li>Buyer protection included</li>
          </ul>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.payments-page {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-title {
  font-size: 24px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.page-subtitle {
  color: #6b7280;
  margin: 4px 0 0 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-card.success { border-left: 4px solid #16a34a; }
.stat-card.warning { border-left: 4px solid #d97706; }
.stat-card.danger { border-left: 4px solid #dc2626; }
.stat-card.info { border-left: 4px solid #9333ea; }

.stat-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.stat-info { flex: 1; }

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #111827;
  margin: 4px 0 0 0;
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
.stat-icon.amber { background: #fef3c7; color: #d97706; }
.stat-icon.red { background: #fee2e2; color: #dc2626; }
.stat-icon.purple { background: #f3e8ff; color: #9333ea; }

.filters-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.filters-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.btn-clear {
  background: none;
  border: none;
  color: #2563eb;
  cursor: pointer;
  font-size: 14px;
}

.filters-row {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
}

.filter-group label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 8px;
}

.filter-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.filter-btn:hover {
  background: #f3f4f6;
}

.filter-btn.active {
  background: #111827;
  color: white;
  border-color: #111827;
}

.filter-btn.stripe.active { background: #4f46e5; border-color: #4f46e5; }
.filter-btn.paypal.active { background: #2563eb; border-color: #2563eb; }

.provider-icon {
  margin-right: 4px;
}

.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.card-header h2 {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  color: #111827;
}

.card-body {
  padding: 20px;
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.data-table th {
  background: #f9fafb;
  font-weight: 500;
  font-size: 13px;
  color: #6b7280;
}

.data-table tbody tr:hover {
  background: #f9fafb;
}

.mono {
  font-family: monospace;
  font-size: 13px;
}

.font-bold {
  font-weight: 600;
}

.order-link {
  color: #2563eb;
  text-decoration: none;
}

.order-link:hover {
  text-decoration: underline;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.provider-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.provider-badge.stripe { background: #e0e7ff; color: #4f46e5; }
.provider-badge.paypal { background: #dbeafe; color: #2563eb; }

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.page-btn {
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 6px;
  cursor: pointer;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #6b7280;
  font-size: 14px;
}

.pagination-info {
  color: #6b7280;
  font-size: 14px;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 12px;
}

.providers-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.provider-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.provider-card.stripe {
  border-top: 4px solid #4f46e5;
}

.provider-card.paypal {
  border-top: 4px solid #2563eb;
}

.provider-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.provider-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.status-active {
  background: #dcfce7;
  color: #16a34a;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.provider-card p {
  color: #6b7280;
  margin: 0 0 12px 0;
}

.provider-card ul {
  margin: 0;
  padding-left: 20px;
  color: #6b7280;
  font-size: 14px;
}

.provider-card li {
  margin-bottom: 4px;
}

.loading-container {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #111827;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>