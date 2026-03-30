<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const users = ref([])
const loading = ref(true)
const search = ref('')

const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })

const fetchUsers = async () => {
  loading.value = true
  try {
    const params = {}
    if (search.value) params.search = search.value
    const response = await axios.get('/api/admin/users', { params })
    users.value = response.data.data
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchUsers)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Users</h1>
        <p class="page-subtitle">Manage all registered users</p>
      </div>
    </div>

    <div class="card filter-card">
      <div class="search-box">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input v-model="search" type="text" placeholder="Search users by name or email..." @input="fetchUsers" />
      </div>
    </div>

    <div v-if="loading" class="loading"><div class="spinner"></div></div>

    <div v-else class="card">
      <table class="table">
        <thead>
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Orders</th>
            <th>Joined</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>
              <div class="user-cell">
                <div class="avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
                <span class="user-name">{{ user.name }}</span>
              </div>
            </td>
            <td>{{ user.email }}</td>
            <td>{{ user.phone || '-' }}</td>
            <td><span class="badge gray">{{ user.orders_count }} orders</span></td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>
              <span class="badge" :class="user.is_admin ? 'blue' : 'gray'">
                {{ user.is_admin ? 'Admin' : 'Customer' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.page { padding: 0; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 700; color: #111827; }
.page-subtitle { color: #6b7280; margin-top: 4px; }
.card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.filter-card { padding: 20px 24px; margin-bottom: 24px; }
.search-box { position: relative; max-width: 400px; }
.search-box svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.search-box input { width: 100%; padding: 12px 14px 12px 44px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; }
.search-box input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.loading { display: flex; justify-content: center; padding: 80px; }
.spinner { width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.table { width: 100%; border-collapse: collapse; }
.table th { text-align: left; padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; background: #f9fafb; }
.table td { padding: 16px 24px; border-bottom: 1px solid #f3f4f6; }
.table tr:hover td { background: #f9fafb; }
.user-cell { display: flex; align-items: center; gap: 12px; }
.avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px; }
.user-name { font-weight: 600; color: #111827; }
.badge { display: inline-block; padding: 4px 12px; font-size: 12px; font-weight: 600; border-radius: 9999px; }
.badge.blue { background: #dbeafe; color: #2563eb; }
.badge.gray { background: #f3f4f6; color: #6b7280; }
</style>
