<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: ''
})

const errors = ref({})
const loading = ref(false)

const handleRegister = async () => {
  errors.value = {}
  loading.value = true
  
  try {
    await authStore.register(form.value)
    router.push('/')
  } catch (err) {
    if (err.errors) {
      errors.value = err.errors
    } else {
      errors.value = { general: err.message || 'Registration failed' }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <h1>Create Account</h1>
          <p>Join us and start shopping for premium shoes</p>
        </div>

        <form @submit.prevent="handleRegister" class="auth-form">
          <div v-if="errors.general" class="error-message">
            {{ errors.general }}
          </div>

          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input 
              v-model="form.name"
              type="text" 
              class="form-input" 
              :class="{ 'input-error': errors.name }"
              placeholder="Enter your full name"
              required
            />
            <span v-if="errors.name" class="field-error">{{ errors.name[0] }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Email</label>
            <input 
              v-model="form.email"
              type="email" 
              class="form-input" 
              :class="{ 'input-error': errors.email }"
              placeholder="Enter your email"
              required
            />
            <span v-if="errors.email" class="field-error">{{ errors.email[0] }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Phone (Optional)</label>
            <input 
              v-model="form.phone"
              type="tel" 
              class="form-input" 
              placeholder="Enter your phone number"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Password</label>
            <input 
              v-model="form.password"
              type="password" 
              class="form-input" 
              :class="{ 'input-error': errors.password }"
              placeholder="Create a password"
              required
              minlength="8"
            />
            <span v-if="errors.password" class="field-error">{{ errors.password[0] }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input 
              v-model="form.password_confirmation"
              type="password" 
              class="form-input" 
              placeholder="Confirm your password"
              required
            />
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <input type="checkbox" required />
              <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
            </label>
          </div>

          <button type="submit" :disabled="loading" class="btn btn-primary btn-lg w-full">
            {{ loading ? 'Creating Account...' : 'Create Account' }}
          </button>
        </form>

        <div class="auth-footer">
          <p>Already have an account? <router-link to="/login">Sign in</router-link></p>
        </div>
      </div>

      <div class="auth-image">
        <img src="https://images.unsplash.com/photo-1556906781-9a412961c28c?w=600&h=800&fit=crop" alt="Register" />
        <div class="image-overlay">
          <h2>Join ShoeStore</h2>
          <p>Get exclusive access to new arrivals and special offers</p>
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

.input-error {
  border-color: var(--danger) !important;
}

.field-error {
  display: block;
  color: var(--danger);
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.form-options {
  margin-bottom: 1.5rem;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.875rem;
  color: var(--gray-600);
  line-height: 1.4;
}

.checkbox-label input {
  width: 16px;
  height: 16px;
  margin-top: 2px;
  accent-color: var(--primary);
}

.checkbox-label a {
  color: var(--primary);
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
