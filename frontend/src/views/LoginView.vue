<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'
import axios from 'axios'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const cartStore = useCartStore()

const email = ref('')
const password = ref('')
const error = ref('')
const success = ref('')
const loading = ref(false)
const googleLoading = ref(false)

const handleLogin = async () => {
  error.value = ''
  loading.value = true
  
  try {
    await authStore.login(email.value, password.value)
    await cartStore.fetchCart()
    
    const redirect = route.query.redirect || '/'
    router.push(redirect)
  } catch (err) {
    error.value = err.message || err.email?.[0] || 'Invalid credentials'
  } finally {
    loading.value = false
  }
}

const loginWithGoogle = () => {
  googleLoading.value = true
  window.location.href = `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/api/auth/google`
}

onMounted(async () => {
  // Handle OAuth callback
  const token = route.query.token
  const userData = route.query.user
  const authError = route.query.error

  if (authError) {
    error.value = decodeURIComponent(authError)
    return
  }

  if (token && userData) {
    try {
      googleLoading.value = true
      
      // Store token and user data
      localStorage.setItem('token', token)
      const user = JSON.parse(decodeURIComponent(userData))
      
      // Update auth store
      authStore.token = token
      authStore.user = user
      
      // Set axios default header
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      
      // Fetch cart
      await cartStore.fetchCart()
      
      success.value = 'Successfully signed in with Google!'
      
      // Redirect after short delay
      setTimeout(() => {
        const redirect = route.query.redirect || '/'
        router.push(redirect)
      }, 1000)
    } catch (err) {
      error.value = 'Failed to complete Google sign in'
    } finally {
      googleLoading.value = false
    }
  }
})
</script>

<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <h1>Welcome Back</h1>
          <p>Sign in to your account to continue shopping</p>
        </div>

        <form @submit.prevent="handleLogin" class="auth-form">
          <div v-if="error" class="error-message">
            {{ error }}
          </div>
          
          <div v-if="success" class="success-message">
            {{ success }}
          </div>

          <!-- Google Login Button -->
          <button 
            type="button" 
            @click="loginWithGoogle" 
            :disabled="googleLoading"
            class="btn-google"
          >
            <svg viewBox="0 0 24 24" width="20" height="20">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            <span>{{ googleLoading ? 'Signing in...' : 'Continue with Google' }}</span>
          </button>

          <div class="divider">
            <span>or</span>
          </div>

          <div class="form-group">
            <label class="form-label">Email</label>
            <input 
              v-model="email"
              type="email" 
              class="form-input" 
              placeholder="Enter your email"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">Password</label>
            <input 
              v-model="password"
              type="password" 
              class="form-input" 
              placeholder="Enter your password"
              required
            />
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <input type="checkbox" />
              <span>Remember me</span>
            </label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" :disabled="loading" class="btn btn-primary btn-lg w-full">
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>

        <div class="auth-footer">
          <p>Don't have an account? <router-link to="/register">Sign up</router-link></p>
        </div>
      </div>

      <div class="auth-image">
        <img src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600&h=800&fit=crop" alt="Login" />
        <div class="image-overlay">
          <h2>Step Into Style</h2>
          <p>Discover premium quality shoes for every occasion</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: calc(100vh - 200px);
  display: flex;
  align-items: center;
  padding: 2rem 0;
}

.auth-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  max-width: 1000px;
  margin: 0 auto;
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.auth-card {
  padding: 3rem;
}

.auth-header {
  margin-bottom: 2rem;
}

.auth-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 0.5rem;
}

.auth-header p {
  color: var(--gray-500);
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.success-message {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.btn-google {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  background: white;
  border: 2px solid var(--gray-200);
  border-radius: 0.5rem;
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--gray-700);
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 1.5rem;
}

.btn-google:hover:not(:disabled) {
  background: var(--gray-50);
  border-color: var(--gray-300);
}

.btn-google:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.divider {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--gray-200);
}

.divider span {
  padding: 0 1rem;
  color: var(--gray-400);
  font-size: 0.875rem;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.875rem;
  color: var(--gray-600);
}

.checkbox-label input {
  width: 16px;
  height: 16px;
  accent-color: var(--primary);
}

.forgot-link {
  font-size: 0.875rem;
  color: var(--primary);
}

.forgot-link:hover {
  text-decoration: underline;
}

.auth-footer {
  text-align: center;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--gray-200);
}

.auth-footer p {
  color: var(--gray-500);
  font-size: 0.875rem;
}

.auth-footer a {
  color: var(--primary);
  font-weight: 600;
}

.auth-image {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.auth-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 2rem;
  background: linear-gradient(transparent, rgba(0,0,0,0.8));
  color: white;
}

.image-overlay h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.image-overlay p {
  opacity: 0.9;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .auth-container {
    grid-template-columns: 1fr;
  }
  
  .auth-image {
    display: none;
  }
  
  .auth-card {
    padding: 2rem;
  }
}
</style>
