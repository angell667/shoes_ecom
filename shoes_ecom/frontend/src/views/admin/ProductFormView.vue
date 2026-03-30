<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const categories = ref([])
const error = ref('')

const form = ref({
  name: '', category_id: '', description: '', price: '', sale_price: '',
  image: '', brand: '', stock: '', is_featured: false, is_active: true
})

onMounted(async () => {
  try {
    const response = await axios.get('/api/admin/categories')
    categories.value = response.data
    if (isEdit.value) {
      const productResponse = await axios.get(`/api/admin/products/${route.params.id}`)
      const product = productResponse.data
      form.value = {
        name: product.name, category_id: product.category_id, description: product.description || '',
        price: product.price, sale_price: product.sale_price || '', image: product.image || '',
        brand: product.brand || '', stock: product.stock, is_featured: product.is_featured, is_active: product.is_active
      }
    }
  } catch (err) {
    error.value = 'Failed to load data'
  }
})

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  try {
    if (isEdit.value) {
      await axios.put(`/api/admin/products/${route.params.id}`, form.value)
    } else {
      await axios.post('/api/admin/products', form.value)
    }
    router.push('/admin/products')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save product'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <router-link to="/admin/products" class="back-link">← Back to Products</router-link>
    
    <div class="page-header">
      <h1 class="page-title">{{ isEdit ? 'Edit Product' : 'Add Product' }}</h1>
      <p class="page-subtitle">{{ isEdit ? 'Update product details' : 'Create a new product' }}</p>
    </div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="handleSubmit">
          <div v-if="error" class="error-msg">{{ error }}</div>
          
          <div class="form-grid">
            <div class="form-group">
              <label>Product Name *</label>
              <input v-model="form.name" type="text" required />
            </div>
            <div class="form-group">
              <label>Category *</label>
              <select v-model="form.category_id" required>
                <option value="">Select category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Price *</label>
              <div class="input-prefix">
                <span>$</span>
                <input v-model="form.price" type="number" step="0.01" required />
              </div>
            </div>
            <div class="form-group">
              <label>Sale Price</label>
              <div class="input-prefix">
                <span>$</span>
                <input v-model="form.sale_price" type="number" step="0.01" />
              </div>
            </div>
            <div class="form-group">
              <label>Brand</label>
              <input v-model="form.brand" type="text" />
            </div>
            <div class="form-group">
              <label>Stock *</label>
              <input v-model="form.stock" type="number" required />
            </div>
          </div>

          <div class="form-group full">
            <label>Description</label>
            <textarea v-model="form.description" rows="4"></textarea>
          </div>

          <div class="form-group full">
            <label>Image URL</label>
            <input v-model="form.image" type="url" placeholder="https://example.com/image.jpg" />
          </div>

          <div class="checkboxes">
            <label class="checkbox-label">
              <input v-model="form.is_featured" type="checkbox" />
              <span>Featured Product</span>
            </label>
            <label class="checkbox-label">
              <input v-model="form.is_active" type="checkbox" />
              <span>Active</span>
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" :disabled="loading" class="btn primary">
              {{ loading ? 'Saving...' : (isEdit ? 'Update Product' : 'Create Product') }}
            </button>
            <router-link to="/admin/products" class="btn secondary">Cancel</router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page { padding: 0; }
.back-link { display: inline-flex; align-items: center; gap: 8px; color: #6b7280; text-decoration: none; margin-bottom: 20px; font-weight: 500; }
.back-link:hover { color: #111827; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 700; color: #111827; }
.page-subtitle { color: #6b7280; margin-top: 4px; }
.card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 900px; }
.card-body { padding: 32px; }
.error-msg { background: #fef2f2; color: #dc2626; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
.form-group { margin-bottom: 24px; }
.form-group.full { grid-column: span 2; }
.form-group label { display: block; font-weight: 500; margin-bottom: 8px; color: #374151; }
.form-group input, .form-group select, .form-group textarea {
  width: 100%; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; background: white;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
  outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.input-prefix { position: relative; }
.input-prefix span { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #6b7280; }
.input-prefix input { padding-left: 32px; }
.checkboxes { display: flex; gap: 32px; margin-bottom: 32px; }
.checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500; color: #374151; }
.checkbox-label input { width: 20px; height: 20px; border-radius: 6px; }
.form-actions { display: flex; gap: 16px; margin-top: 8px; }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; font-size: 14px; font-weight: 600; border-radius: 10px; border: none; cursor: pointer; text-decoration: none; }
.btn.primary { background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; }
.btn.primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn.secondary { background: #f3f4f6; color: #374151; }
@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .form-group.full { grid-column: span 1; } }
</style>
