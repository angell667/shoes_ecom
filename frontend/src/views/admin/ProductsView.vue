<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const products = ref([])
const categories = ref([])
const loading = ref(true)
const search = ref('')
const selectedCategory = ref('')

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price)
}

const fetchProducts = async () => {
  loading.value = true
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (selectedCategory.value) params.category = selectedCategory.value
    const response = await axios.get('/api/admin/products', { params })
    products.value = response.data.data
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/admin/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Error:', error)
  }
}

const deleteProduct = async (id) => {
  if (confirm('Are you sure you want to delete this product?')) {
    try {
      await axios.delete(`/api/admin/products/${id}`)
      await fetchProducts()
    } catch (error) {
      alert('Failed to delete product')
    }
  }
}

const toggleFeatured = async (id) => {
  try {
    await axios.patch(`/api/admin/products/${id}/toggle-featured`)
    await fetchProducts()
  } catch (error) {
    alert('Failed to update')
  }
}

const toggleActive = async (id) => {
  try {
    await axios.patch(`/api/admin/products/${id}/toggle-active`)
    await fetchProducts()
  } catch (error) {
    alert('Failed to update')
  }
}

onMounted(() => {
  fetchProducts()
  fetchCategories()
})
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Products</h1>
        <p class="page-subtitle">Manage your product catalog</p>
      </div>
      <router-link to="/admin/products/new" class="btn primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Add Product
      </router-link>
    </div>

    <div class="card filter-card">
      <div class="filter-row">
        <div class="search-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input v-model="search" type="text" placeholder="Search products..." @input="fetchProducts" />
        </div>
        <select v-model="selectedCategory" class="select" @change="fetchProducts">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
    </div>

    <div v-else-if="products.length === 0" class="card empty">
      <div class="empty-icon">📦</div>
      <h3>No products found</h3>
      <p>Get started by adding your first product</p>
      <router-link to="/admin/products/new" class="btn primary">Add Product</router-link>
    </div>

    <div v-else class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>
              <div class="product-cell">
                <img :src="product.image || 'https://via.placeholder.com/48'" :alt="product.name" />
                <div>
                  <p class="product-name">{{ product.name }}</p>
                  <p class="product-brand">{{ product.brand }}</p>
                </div>
              </div>
            </td>
            <td><span class="badge gray">{{ product.category?.name || 'N/A' }}</span></td>
            <td>
              <p class="price">{{ formatPrice(product.effective_price || product.price) }}</p>
              <p v-if="product.sale_price" class="price-old">{{ formatPrice(product.price) }}</p>
            </td>
            <td>
              <span class="badge" :class="product.stock < 10 ? 'amber' : 'green'">
                {{ product.stock }} in stock
              </span>
            </td>
            <td>
              <span class="badge" :class="product.is_active ? 'green' : 'red'">
                {{ product.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="text-right">
              <div class="actions">
                <button @click="toggleFeatured(product.id)" class="action-btn" :class="{ star: product.is_featured }" title="Toggle Featured">⭐</button>
                <button @click="toggleActive(product.id)" class="action-btn" title="Toggle Active">👁️</button>
                <router-link :to="`/admin/products/${product.id}/edit`" class="action-btn edit">✏️</router-link>
                <button @click="deleteProduct(product.id)" class="action-btn delete">🗑️</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.page { padding: 0; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
.page-title { font-size: 28px; font-weight: 700; color: #111827; }
.page-subtitle { color: #6b7280; margin-top: 4px; }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 10px; border: none; cursor: pointer; text-decoration: none; transition: all 0.2s; }
.btn.primary { background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
.btn.primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4); }
.card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.filter-card { padding: 20px 24px; margin-bottom: 24px; }
.filter-row { display: flex; gap: 16px; flex-wrap: wrap; }
.search-box { position: relative; flex: 1; min-width: 200px; max-width: 400px; }
.search-box svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.search-box input { width: 100%; padding: 12px 14px 12px 44px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.2s; }
.search-box input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.select { padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; min-width: 180px; background: white; cursor: pointer; }
.select:focus { outline: none; border-color: #3b82f6; }
.loading { display: flex; justify-content: center; padding: 80px; }
.spinner { width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty { padding: 80px; text-align: center; }
.empty-icon { font-size: 64px; margin-bottom: 16px; }
.empty h3 { font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 8px; }
.empty p { color: #6b7280; margin-bottom: 24px; }
.table { width: 100%; border-collapse: collapse; }
.table th { text-align: left; padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; background: #f9fafb; }
.table td { padding: 16px 24px; border-bottom: 1px solid #f3f4f6; }
.table tr:hover td { background: #f9fafb; }
.text-right { text-align: right; }
.product-cell { display: flex; align-items: center; gap: 12px; }
.product-cell img { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; }
.product-name { font-weight: 600; color: #111827; }
.product-brand { font-size: 13px; color: #6b7280; }
.price { font-weight: 600; color: #111827; }
.price-old { font-size: 12px; color: #9ca3af; text-decoration: line-through; }
.badge { display: inline-block; padding: 4px 12px; font-size: 12px; font-weight: 600; border-radius: 9999px; }
.badge.green { background: #dcfce7; color: #16a34a; }
.badge.amber { background: #fef3c7; color: #d97706; }
.badge.red { background: #fee2e2; color: #dc2626; }
.badge.gray { background: #f3f4f6; color: #6b7280; }
.badge.blue { background: #dbeafe; color: #2563eb; }
.actions { display: flex; gap: 4px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border: none; background: none; border-radius: 8px; cursor: pointer; font-size: 16px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.action-btn:hover { background: #f3f4f6; }
.action-btn.star { color: #f59e0b; }
.action-btn.edit:hover { background: #dbeafe; }
.action-btn.delete:hover { background: #fee2e2; }
</style>
