<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const categories = ref([])
const loading = ref(true)
const showModal = ref(false)
const editingCategory = ref(null)
const form = ref({ 
  name: '', 
  description: '', 
  image: '', 
  show_in_navbar: true, 
  navbar_order: 0, 
  parent_id: null 
})
const error = ref('')

const parentCategories = computed(() => {
  return categories.value.filter(c => !c.parent_id && (!editingCategory.value || c.id !== editingCategory.value.id))
})

const fetchCategories = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/admin/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const openModal = (category = null) => {
  editingCategory.value = category
  form.value = category ? { 
    ...category,
    show_in_navbar: category.show_in_navbar ?? true,
    navbar_order: category.navbar_order ?? 0,
    parent_id: category.parent_id ?? null
  } : { 
    name: '', 
    description: '', 
    image: '',
    show_in_navbar: true,
    navbar_order: 0,
    parent_id: null
  }
  error.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingCategory.value = null
}

const handleSubmit = async () => {
  error.value = ''
  try {
    if (editingCategory.value) {
      await axios.put(`/api/admin/categories/${editingCategory.value.id}`, form.value)
    } else {
      await axios.post('/api/admin/categories', form.value)
    }
    closeModal()
    await fetchCategories()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save category'
  }
}

const deleteCategory = async (id) => {
  if (confirm('Are you sure? Products in this category cannot be deleted.')) {
    try {
      await axios.delete(`/api/admin/categories/${id}`)
      await fetchCategories()
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete category')
    }
  }
}

onMounted(fetchCategories)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Categories</h1>
        <p class="page-subtitle">Organize your products into categories</p>
      </div>
      <button @click="openModal()" class="btn primary">
        <span>+</span> Add Category
      </button>
    </div>

    <div v-if="loading" class="loading"><div class="spinner"></div></div>

    <div v-else class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Parent</th>
            <th>Navbar</th>
            <th>Order</th>
            <th>Products</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td>
              <div class="category-cell">
                <img v-if="category.image" :src="category.image" :alt="category.name" />
                <div v-else class="category-placeholder">📁</div>
                <span class="category-name" :class="{ 'sub-category': category.parent_id }">
                  {{ category.parent_id ? '└─ ' : '' }}{{ category.name }}
                </span>
              </div>
            </td>
            <td><code class="slug">{{ category.slug }}</code></td>
            <td>
              <span v-if="category.parent" class="badge gray">{{ category.parent?.name }}</span>
              <span v-else class="badge gray">—</span>
            </td>
            <td>
              <span v-if="category.show_in_navbar" class="badge green">✓ Visible</span>
              <span v-else class="badge gray">Hidden</span>
            </td>
            <td>{{ category.navbar_order }}</td>
            <td><span class="badge blue">{{ category.products_count }} products</span></td>
            <td class="text-right">
              <div class="actions">
                <button @click="openModal(category)" class="action-btn edit">✏️</button>
                <button @click="deleteCategory(category.id)" class="action-btn delete">🗑️</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ editingCategory ? 'Edit Category' : 'Add Category' }}</h3>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        <form @submit.prevent="handleSubmit">
          <div class="modal-body">
            <div v-if="error" class="error-msg">{{ error }}</div>
            <div class="form-group">
              <label>Name *</label>
              <input v-model="form.name" type="text" required />
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="form.description" rows="3"></textarea>
            </div>
            <div class="form-group">
              <label>Image URL</label>
              <input v-model="form.image" type="url" />
            </div>
            
            <div class="form-divider">
              <span>Navbar Settings</span>
            </div>
            
            <div class="form-group">
              <label>Parent Category</label>
              <select v-model="form.parent_id">
                <option :value="null">None (Top Level)</option>
                <option v-for="cat in parentCategories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="checkbox-label">
                  <input type="checkbox" v-model="form.show_in_navbar" />
                  <span>Show in Navbar</span>
                </label>
              </div>
              <div class="form-group">
                <label>Navbar Order</label>
                <input v-model.number="form.navbar_order" type="number" min="0" />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn secondary">Cancel</button>
            <button type="submit" class="btn primary">{{ editingCategory ? 'Update' : 'Create' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page { padding: 0; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 700; color: #111827; }
.page-subtitle { color: #6b7280; margin-top: 4px; }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 10px; border: none; cursor: pointer; }
.btn.primary { background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; }
.btn.secondary { background: #f3f4f6; color: #374151; }
.card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.loading { display: flex; justify-content: center; padding: 80px; }
.spinner { width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.table { width: 100%; border-collapse: collapse; }
.table th { text-align: left; padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; background: #f9fafb; }
.table td { padding: 16px 24px; border-bottom: 1px solid #f3f4f6; }
.table tr:hover td { background: #f9fafb; }
.text-right { text-align: right; }
.category-cell { display: flex; align-items: center; gap: 12px; }
.category-cell img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; }
.category-placeholder { width: 40px; height: 40px; border-radius: 8px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.category-name { font-weight: 600; color: #111827; }
.slug { background: #f3f4f6; padding: 4px 8px; border-radius: 6px; font-size: 12px; color: #6b7280; }
.badge { display: inline-block; padding: 4px 12px; font-size: 12px; font-weight: 600; border-radius: 9999px; }
.badge.blue { background: #dbeafe; color: #2563eb; }
.badge.green { background: #dcfce7; color: #16a34a; }
.badge.gray { background: #f3f4f6; color: #6b7280; }
.sub-category { color: #6b7280; font-weight: 500; }
.actions { display: flex; gap: 4px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border: none; background: none; border-radius: 8px; cursor: pointer; font-size: 16px; }
.action-btn:hover { background: #f3f4f6; }
.action-btn.delete:hover { background: #fee2e2; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; border-radius: 16px; width: 100%; max-width: 550px; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
.modal-header { padding: 20px 24px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { font-size: 18px; font-weight: 600; color: #111827; }
.close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #9ca3af; }
.modal-body { padding: 24px; max-height: 70vh; overflow-y: auto; }
.modal-footer { padding: 16px 24px; border-top: 1px solid #f3f4f6; display: flex; justify-content: flex-end; gap: 12px; }
.error-msg { background: #fef2f2; color: #dc2626; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-weight: 500; margin-bottom: 6px; color: #374151; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; }
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.form-divider { margin: 20px 0; padding-top: 20px; border-top: 1px solid #e5e7eb; }
.form-divider span { background: white; padding: 0 10px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.checkbox-label { display: flex !important; align-items: center; gap: 8px; cursor: pointer; }
.checkbox-label input { width: auto !important; }
</style>
