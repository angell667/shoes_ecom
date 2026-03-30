<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const coupons = ref([])
const loading = ref(true)
const showModal = ref(false)
const editingCoupon = ref(null)

const form = ref({
  code: '',
  type: 'percentage',
  value: '',
  minimum_order: '',
  maximum_discount: '',
  usage_limit: '',
  starts_at: '',
  expires_at: '',
  is_active: true
})

const fetchCoupons = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/admin/coupons')
    coupons.value = response.data.coupons.data
  } catch (error) {
    console.error('Error fetching coupons:', error)
  } finally {
    loading.value = false
  }
}

const openModal = (coupon = null) => {
  if (coupon) {
    editingCoupon.value = coupon
    form.value = {
      code: coupon.code,
      type: coupon.type,
      value: coupon.value,
      minimum_order: coupon.minimum_order || '',
      maximum_discount: coupon.maximum_discount || '',
      usage_limit: coupon.usage_limit || '',
      starts_at: coupon.starts_at ? coupon.starts_at.split('T')[0] : '',
      expires_at: coupon.expires_at ? coupon.expires_at.split('T')[0] : '',
      is_active: coupon.is_active
    }
  } else {
    editingCoupon.value = null
    form.value = {
      code: '',
      type: 'percentage',
      value: '',
      minimum_order: '',
      maximum_discount: '',
      usage_limit: '',
      starts_at: '',
      expires_at: '',
      is_active: true
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingCoupon.value = null
}

const saveCoupon = async () => {
  try {
    if (editingCoupon.value) {
      await axios.put(`/api/admin/coupons/${editingCoupon.value.id}`, form.value)
    } else {
      await axios.post('/api/admin/coupons', form.value)
    }
    closeModal()
    fetchCoupons()
  } catch (error) {
    console.error('Error saving coupon:', error)
    alert(error.response?.data?.message || 'Error saving coupon')
  }
}

const deleteCoupon = async (id) => {
  if (!confirm('Are you sure you want to delete this coupon?')) return
  try {
    await axios.delete(`/api/admin/coupons/${id}`)
    fetchCoupons()
  } catch (error) {
    console.error('Error deleting coupon:', error)
  }
}

const toggleCoupon = async (id) => {
  try {
    await axios.patch(`/api/admin/coupons/${id}/toggle`)
    fetchCoupons()
  } catch (error) {
    console.error('Error toggling coupon:', error)
  }
}

const generateCode = async () => {
  try {
    const response = await axios.post('/api/admin/coupons/generate-code')
    form.value.code = response.data.code
  } catch (error) {
    console.error('Error generating code:', error)
  }
}

const formatDate = (date) => {
  if (!date) return 'Never'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatValue = (coupon) => {
  return coupon.type === 'percentage' ? `${coupon.value}%` : `$${coupon.value}`
}

onMounted(fetchCoupons)
</script>

<template>
  <div class="admin-page">
    <div class="page-header">
      <div>
        <h1>Coupon Management</h1>
        <p class="page-subtitle">Create and manage discount coupons</p>
      </div>
      <button @click="openModal()" class="btn-primary">+ Create Coupon</button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading coupons...</p>
    </div>

    <!-- Coupons Table -->
    <div v-else class="card">
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Type</th>
              <th>Value</th>
              <th>Min Order</th>
              <th>Usage</th>
              <th>Valid Period</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="coupon in coupons" :key="coupon.id">
              <td class="code-cell">
                <span class="coupon-code">{{ coupon.code }}</span>
              </td>
              <td>{{ coupon.type }}</td>
              <td class="value-cell">{{ formatValue(coupon) }}</td>
              <td>{{ coupon.minimum_order ? `$${coupon.minimum_order}` : '-' }}</td>
              <td>
                {{ coupon.used_count }}
                <span v-if="coupon.usage_limit">/ {{ coupon.usage_limit }}</span>
              </td>
              <td>
                <div class="date-range">
                  <span>{{ formatDate(coupon.starts_at) }}</span>
                  <span class="date-separator">to</span>
                  <span>{{ formatDate(coupon.expires_at) }}</span>
                </div>
              </td>
              <td>
                <span :class="['status-badge', coupon.is_active ? 'active' : 'inactive']">
                  {{ coupon.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button @click="toggleCoupon(coupon.id)" class="btn-icon" :title="coupon.is_active ? 'Deactivate' : 'Activate'">
                    {{ coupon.is_active ? '⏸️' : '▶️' }}
                  </button>
                  <button @click="openModal(coupon)" class="btn-icon" title="Edit">✏️</button>
                  <button @click="deleteCoupon(coupon.id)" class="btn-icon delete" title="Delete">🗑️</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="coupons.length === 0" class="empty-state">
        <p>🏷️ No coupons found. Create your first coupon!</p>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ editingCoupon ? 'Edit Coupon' : 'Create New Coupon' }}</h2>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        <form @submit.prevent="saveCoupon" class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label>Coupon Code</label>
              <div class="input-with-btn">
                <input v-model="form.code" type="text" required placeholder="e.g., SUMMER20" />
                <button type="button" @click="generateCode" class="btn-generate">Generate</button>
              </div>
            </div>
          </div>

          <div class="form-row two-cols">
            <div class="form-group">
              <label>Discount Type</label>
              <select v-model="form.type" required>
                <option value="percentage">Percentage (%)</option>
                <option value="fixed">Fixed Amount ($)</option>
              </select>
            </div>
            <div class="form-group">
              <label>Value</label>
              <input v-model="form.value" type="number" step="0.01" min="0.01" required placeholder="e.g., 20" />
            </div>
          </div>

          <div class="form-row two-cols">
            <div class="form-group">
              <label>Minimum Order Amount</label>
              <input v-model="form.minimum_order" type="number" step="0.01" min="0" placeholder="Optional" />
            </div>
            <div class="form-group">
              <label>Maximum Discount</label>
              <input v-model="form.maximum_discount" type="number" step="0.01" min="0" placeholder="Optional (for %)" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Usage Limit</label>
              <input v-model="form.usage_limit" type="number" min="1" placeholder="Leave empty for unlimited" />
            </div>
          </div>

          <div class="form-row two-cols">
            <div class="form-group">
              <label>Start Date</label>
              <input v-model="form.starts_at" type="date" />
            </div>
            <div class="form-group">
              <label>Expiry Date</label>
              <input v-model="form.expires_at" type="date" />
            </div>
          </div>

          <div class="form-row">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.is_active" />
              <span>Active</span>
            </label>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">{{ editingCoupon ? 'Update' : 'Create' }} Coupon</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-page { padding: 0; }

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
}

.page-subtitle {
  color: #6b7280;
  margin-top: 4px;
}

.btn-primary {
  padding: 12px 24px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  padding: 12px 24px;
  background: #f3f4f6;
  color: #374151;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 60px;
  color: #6b7280;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin { to { transform: rotate(360deg); } }

.card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
  padding: 16px;
  text-align: left;
  border-bottom: 1px solid #f3f4f6;
}

.data-table th {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  background: #f9fafb;
}

.data-table tbody tr:hover {
  background: #f9fafb;
}

.code-cell {
  font-family: monospace;
}

.coupon-code {
  background: #e0e7ff;
  color: #4338ca;
  padding: 4px 12px;
  border-radius: 6px;
  font-weight: 600;
}

.value-cell {
  font-weight: 600;
  color: #16a34a;
}

.date-range {
  display: flex;
  flex-direction: column;
  font-size: 12px;
}

.date-separator {
  color: #9ca3af;
}

.status-badge {
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 9999px;
}

.status-badge.active {
  background: #dcfce7;
  color: #16a34a;
}

.status-badge.inactive {
  background: #f3f4f6;
  color: #6b7280;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-icon {
  padding: 6px 10px;
  border: none;
  background: #f3f4f6;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #e5e7eb;
}

.btn-icon.delete:hover {
  background: #fee2e2;
}

.empty-state {
  text-align: center;
  padding: 60px;
  color: #6b7280;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #f3f4f6;
}

.modal-header h2 {
  font-size: 20px;
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
  padding: 24px;
}

.form-row {
  margin-bottom: 16px;
}

.form-row.two-cols {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}

.form-group input,
.form-group select {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input-with-btn {
  display: flex;
  gap: 8px;
}

.input-with-btn input {
  flex: 1;
}

.btn-generate {
  padding: 10px 16px;
  background: #f3f4f6;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  white-space: nowrap;
}

.btn-generate:hover {
  background: #e5e7eb;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-label input {
  width: 18px;
  height: 18px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #f3f4f6;
  margin-top: 20px;
}

@media (max-width: 768px) {
  .form-row.two-cols {
    grid-template-columns: 1fr;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
}
</style>