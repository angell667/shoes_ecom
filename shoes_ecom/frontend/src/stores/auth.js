import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.is_admin === true)

  async function login(email, password) {
    try {
      const response = await axios.post('/api/auth/login', { email, password })
      user.value = response.data.user
      token.value = response.data.token
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  }

  async function register(data) {
    try {
      const response = await axios.post('/api/auth/register', data)
      user.value = response.data.user
      token.value = response.data.token
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  }

  async function logout() {
    try {
      await axios.post('/api/auth/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
    }
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      const response = await axios.get('/api/auth/user')
      user.value = response.data
    } catch (error) {
      console.error('Fetch user error:', error)
      logout()
    }
  }

  async function updateProfile(data) {
    try {
      const response = await axios.put('/api/auth/profile', data)
      user.value = response.data.user
      return response.data
    } catch (error) {
      throw error.response?.data || error
    }
  }

  function init() {
    if (token.value) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      fetchUser()
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
    init
  }
})
