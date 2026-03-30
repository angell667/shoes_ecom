<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const stats = ref(null)
const logs = ref([])
const lowStockProducts = ref([])
const outOfStockProducts = ref([])
const loading = ref(true)
const activeTab = ref('overview')
const currentPage = ref(1)
const totalPages = ref(1)
const selectedProduct = ref(null)
const adjustModal = ref(false)
const adjustForm = ref({
  product_id: '',
  new_stock: 0,
  notes: ''
})

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
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTypeBadge = (type) => {
  const badges = {
    sale: { bg: '#fee2e2', color: '#dc2626', label: 'Sale' },
    restock: { bg: '#dcfce7', color: '#16a34a', label: 'Restock' },
    adjustment: { bg: '#dbeafe', color: '#2563eb', label: 'Adjustment' },
    return: { bg: '#e0e7ff', color: '#4f46e5', label: 'Return' },
    damage: { bg: '#fef3c7', color: '#d97706', label: 'Damage' },
    theft: { bg: '#fee2e2', color: '#dc2626', label: 'Theft' }
  }
  return badges[type] || { bg: '#f3f4f6', color: '#6b7280', label: type }
}

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/admin/inventory/stats')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching inventory stats:', error)
  }
}

const fetchLogs = async (page = 1) => {
  try {
    const response = await axios.get(`/api/admin/inventory/logs?page=${page}`)
    logs.value = response.data.data
    currentPage.value = response.data.current_page
    totalPages.value = response.data.last_page
  } catch (error) {
    console.error('Error fetching inventory logs:', error)
  }
}

const fetchLowStock = async () => {
  try {
    const response = await axios.get('/api/admin/inventory/low-stock?threshold=10')
    lowStockProducts.value = response.data.data
  } catch (error) {
    console.error('Error fetching low stock products:', error)
  }
}

const fetchOutOfStock = async () => {
  try {
    const response = await axios.get('/api/admin/inventory/out-of-stock')
    outOfStockProducts.value = response.data.data
  } catch (error) {
    console.error('Error fetching out of stock products:', error)
  }
}

const openAdjustModal = (product = null) => {
  if (product) {
    adjustForm.value = {
      product_id: product.id,
      new_stock: product.stock,
      notes: ''
    }
  } else {
    adjustForm.value = {
      product_id: '',
      new_stock: 0,
      notes: ''
    }
  }
  adjustModal.value = true
}

const submitAdjustment = async () => {
  try {
    await axios.post('/api/admin/inventory/adjust', adjustForm.value)
    adjustModal.value = false
    await Promise.all([fetchStats(), fetchLogs(), fetchLowStock(), fetchOutOfStock()])
    alert('Inventory adjusted successfully')
  } catch (error) {
    console.error('Error adjusting inventory:', error)
    alert(error.response?.data?.message || 'Failed to adjust inventory')
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchLogs(page)
  }
}

onMounted(async () => {
  try {
    await Promise.all([fetchStats(), fetchLogs(), fetchLowStock(), fetchOutOfStock()])
  } catch (error) {
    console.error('Error loading inventory data:', error)
  } finally {
    loading.value = false
  }
})

const tabs = [
  { id: 'overview', label: 'Overview', icon: '📊' },
  { id: 'logs', label: 'Activity Logs', icon: '📋' },
  { id: 'low-stock', label: 'Low Stock', icon: '⚠️' },
  { id: 'out-of-stock', label: 'Out of Stock', icon: '🚫' }
]
</script>

<template>
  <div class="inventory-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Inventory Management</h1>
        <p class="page-subtitle">Track and manage your product inventory</p>
      </div>
      <button @click="openAdjustModal()" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Adjust Inventory
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading inventory data...</p>
    </div>

    <template v-else>
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
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-content">
              <div class="stat-info">
                <p class="stat-label">Total Products</p>
                <p class="stat-value">{{ stats?.total_products || 0 }}</p>
              </div>
              <div class="stat-icon blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
            </div>
            <div class="stat-footer">
              <span class="stat-detail">Active items in store</span>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-content">
              <div class="stat-info">
                <p class="stat-label">Total Items in Stock</p>
                <p class="stat-value">{{ stats?.total_items_in_stock || 0 }}</p>
              </div>
              <div class="stat-icon green">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="stat-footer">
              <span class="stat-success">All units across products</span>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-content">
              <div class="stat-info">
                <p class="stat-label">Stock Value</p>
                <p class="stat-value">{{ formatPrice(stats?.total_stock_value || 0) }}</p>
              </div>
              <div class="stat-icon purple">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="stat-footer">
              <span class="stat-detail">Total inventory value</span>
            </div>
          </div>

          <div class="stat-card alert">
            <div class="stat-content">
              <div class="stat-info">
                <p class="stat-label">Low Stock Items</p>
                <p class="stat-value">{{ stats?.low_stock_count || 0 }}</p>
              </div>
              <div class="stat-icon orange">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
            </div>
            <div class="stat-footer">
              <span class="stat-warning">10 or fewer items</span>
            </div>
          </div>

          <div class="stat-card danger">
            <div class="stat-content">
              <div class="stat-info">
                <p class="stat-label">Out of Stock</p>
                <p class="stat-value">{{ stats?.out_of_stock_count || 0 }}</p>
              </div>
              <div class="stat-icon red">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
              </div>
            </div>
            <div class="stat-footer">
              <span class="stat-danger">Needs restocking</span>
            </div>
          </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="card">
          <div class="card-header">
            <h2>Low Stock Products</h2>
            <button @click="activeTab = 'low-stock'" class="view-all">View All →</button>
          </div>
          <div class="card-body">
            <div v-if="lowStockProducts.length === 0" class="empty-state">
              <p>No low stock products</p>
            </div>
            <div v-else class="product-list">
              <div v-for="product in lowStockProducts.slice(0, 5)" :key="product.id" class="product-item">
                <div class="product-image">
                  <img :src="product.image || '/placeholder.jpg'" :alt="product.name" />
                </div>
                <div class="product-info">
                  <h3 class="product-name">{{ product.name }}</h3>
                  <p class="product-category">{{ product.category?.name }}</p>
                </div>
                <div class="product-stock">
                  <span class="stock-badge warning">{{ product.stock }} left</span>
                </div>
                <button @click="openAdjustModal(product)" class="btn-sm">Adjust</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Activity Logs Tab -->
      <div v-if="activeTab === 'logs'" class="tab-content">
        <div class="card">
          <div class="card-header">
            <h2>Inventory Activity Logs</h2>
            <div class="header-actions">
              <span class="pagination-info">Page {{ currentPage }} of {{ totalPages }}</span>
            </div>
          </div>
          <div class="card-body">
            <div v-if="logs.length === 0" class="empty-state">
              <p>No inventory activity recorded</p>
            </div>
            <div v-else class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Stock Before</th>
                    <th>Stock After</th>
                    <th>User</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="log in logs" :key="log.id">
                    <td>{{ formatDate(log.created_at) }}</td>
                    <td>
                      <div class="product-cell">
                        <img :src="log.product?.image || '/placeholder.jpg'" :alt="log.product?.name" class="table-thumb" />
                        <span>{{ log.product?.name }}</span>
                      </div>
                    </td>
                    <td>
                      <span :class="['type-badge', log.type]">
                        {{ getTypeBadge(log.type).label }}
                      </span>
                    </td>
                    <td :class="log.quantity > 0 ? 'text-green' : 'text-red'">
                      {{ log.quantity > 0 ? '+' : '' }}{{ log.quantity }}
                    </td>
                    <td>{{ log.stock_before }}</td>
                    <td>{{ log.stock_after }}</td>
                    <td>{{ log.user?.name || 'System' }}</td>
                    <td>{{ log.notes || '-' }}</td>
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
      </div>

      <!-- Low Stock Tab -->
      <div v-if="activeTab === 'low-stock'" class="tab-content">
        <div class="card">
          <div class="card-header">
            <h2>Low Stock Products (≤10 items)</h2>
            <span class="badge warning">{{ lowStockProducts.length }} products</span>
          </div>
          <div class="card-body">
            <div v-if="lowStockProducts.length === 0" class="empty-state">
              <div class="empty-icon">✅</div>
              <p>All products are well stocked</p>
            </div>
            <div v-else class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="product in lowStockProducts" :key="product.id">
                    <td>
                      <div class="product-cell">
                        <img :src="product.image || '/placeholder.jpg'" :alt="product.name" class="table-thumb" />
                        <span>{{ product.name }}</span>
                      </div>
                    </td>
                    <td>{{ product.category?.name }}</td>
                    <td>{{ formatPrice(product.price) }}</td>
                    <td>
                      <div class="stock-bar-container">
                        <div class="stock-bar">
                          <div class="stock-fill warning" :style="{ width: (product.stock / 10) * 100 + '%' }"></div>
                        </div>
                        <span class="stock-value">{{ product.stock }}</span>
                      </div>
                    </td>
                    <td>
                      <span class="stock-badge warning">Low Stock</span>
                    </td>
                    <td>
                      <button @click="openAdjustModal(product)" class="btn-sm primary">
                        Restock
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Out of Stock Tab -->
      <div v-if="activeTab === 'out-of-stock'" class="tab-content">
        <div class="card">
          <div class="card-header">
            <h2>Out of Stock Products</h2>
            <span class="badge danger">{{ outOfStockProducts.length }} products</span>
          </div>
          <div class="card-body">
            <div v-if="outOfStockProducts.length === 0" class="empty-state">
              <div class="empty-icon">✅</div>
              <p>All products are in stock</p>
            </div>
            <div v-else class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Last Updated</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="product in outOfStockProducts" :key="product.id">
                    <td>
                      <div class="product-cell">
                        <img :src="product.image || '/placeholder.jpg'" :alt="product.name" class="table-thumb" />
                        <span>{{ product.name }}</span>
                      </div>
                    </td>
                    <td>{{ product.category?.name }}</td>
                    <td>{{ formatPrice(product.price) }}</td>
                    <td>{{ formatDate(product.updated_at) }}</td>
                    <td>
                      <span class="stock-badge danger">Out of Stock</span>
                    </td>
                    <td>
                      <button @click="openAdjustModal(product)" class="btn-sm primary">
                        Restock
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Adjust Inventory Modal -->
    <div v-if="adjustModal" class="modal-overlay" @click.self="adjustModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Adjust Inventory</h2>
          <button @click="adjustModal = false" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Product ID</label>
            <input 
              type="text" 
              v-model="adjustForm.product_id" 
              placeholder="Enter product ID"
              class="form-input"
            />
          </div>
          <div class="form-group">
            <label>New Stock Quantity</label>
            <input 
              type="number" 
              v-model="adjustForm.new_stock" 
              min="0"
              class="form-input"
            />
          </div>
          <div class="form-group">
            <label>Notes (Optional)</label>
            <textarea 
              v-model="adjustForm.notes" 
              placeholder="Reason for adjustment..."
              class="form-textarea"
              rows="3"
            ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="adjustModal = false" class="btn-secondary">Cancel</button>
          <button @click="submitAdjustment" class="btn-primary">Save Adjustment</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.inventory-page {
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

.tabs-container {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 8px;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  background: none;
  border-radius: 8px;
  cursor: pointer;
  color: #6b7280;
  transition: all 0.2s;
}

.tab-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

.tab-btn.active {
  background: #111827;
  color: white;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-card.alert {
  border-left: 4px solid #f59e0b;
}

.stat-card.danger {
  border-left: 4px solid #ef4444;
}

.stat-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.stat-info {
  flex: 1;
}

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
.stat-icon.purple { background: #e0e7ff; color: #4f46e5; }
.stat-icon.orange { background: #ffedd5; color: #ea580c; }
.stat-icon.red { background: #fee2e2; color: #dc2626; }

.stat-footer {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-detail {
  font-size: 13px;
  color: #6b7280;
}

.stat-success { color: #16a34a; font-size: 13px; }
.stat-warning { color: #d97706; font-size: 13px; }
.stat-danger { color: #dc2626; font-size: 13px; }

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

.view-all {
  color: #2563eb;
  text-decoration: none;
  font-size: 14px;
  cursor: pointer;
}

.view-all:hover {
  text-decoration: underline;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #111827;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
}

.btn-primary:hover {
  background: #374151;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 13px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  background: #f3f4f6;
  color: #374151;
}

.btn-sm.primary {
  background: #111827;
  color: white;
}

.product-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.product-image img {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 8px;
}

.product-info {
  flex: 1;
}

.product-name {
  font-weight: 500;
  margin: 0;
  color: #111827;
}

.product-category {
  font-size: 13px;
  color: #6b7280;
  margin: 2px 0 0 0;
}

.stock-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.stock-badge.warning {
  background: #fef3c7;
  color: #d97706;
}

.stock-badge.danger {
  background: #fee2e2;
  color: #dc2626;
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

.product-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.table-thumb {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 6px;
}

.type-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.type-badge.sale { background: #fee2e2; color: #dc2626; }
.type-badge.restock { background: #dcfce7; color: #16a34a; }
.type-badge.adjustment { background: #dbeafe; color: #2563eb; }
.type-badge.return { background: #e0e7ff; color: #4f46e5; }

.text-green { color: #16a34a; font-weight: 500; }
.text-red { color: #dc2626; font-weight: 500; }

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

.stock-bar-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stock-bar {
  width: 80px;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.stock-fill {
  height: 100%;
  border-radius: 3px;
}

.stock-fill.warning {
  background: #f59e0b;
}

.stock-value {
  font-weight: 500;
  min-width: 30px;
}

.badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.badge.warning {
  background: #fef3c7;
  color: #d97706;
}

.badge.danger {
  background: #fee2e2;
  color: #dc2626;
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

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #6b7280;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #374151;
}

.form-input,
.form-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #111827;
  box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.1);
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