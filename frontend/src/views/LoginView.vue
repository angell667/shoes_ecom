<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'
import axios from 'axios'
import gsap from 'gsap'

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

onMounted(() => {
  gsap.from('.auth-card-modern', { opacity: 0, y: 40, duration: 1, ease: 'power4.out' })
  
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
      localStorage.setItem('token', token)
      const user = JSON.parse(decodeURIComponent(userData))
      authStore.token = token
      authStore.user = user
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      cartStore.fetchCart()
      success.value = 'Successfully signed in with Google!'
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
  <div class="min-h-screen bg-[#fcfcfc] pt-40 pb-24 flex items-center justify-center relative overflow-hidden">
    <!-- Sophisticated Light Background -->
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
      <div class="absolute top-[10%] right-[-5%] w-[500px] h-[500px] bg-blue-50 rounded-full blur-[120px] opacity-60"></div>
      <div class="absolute bottom-[10%] left-[-5%] w-[500px] h-[500px] bg-purple-50 rounded-full blur-[120px] opacity-60"></div>
    </div>

    <div class="container max-w-6xl grid lg:grid-cols-12 gap-0 bg-white rounded-[3rem] overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.08)] auth-card-modern relative z-10 border border-gray-100">
      <!-- Form Side -->
      <div class="lg:col-span-7 p-12 md:p-20 bg-white">
        <div class="mb-12">
          <div class="inline-block px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-[0.2em] mb-6">Secure Access</div>
          <h1 class="text-6xl font-black uppercase tracking-tighter mb-4 text-black">Welcome<br/><span class="text-gray-300 italic">Back.</span></h1>
          <p class="text-gray-400 font-medium text-lg">Log in to your elite account to continue.</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-10 max-w-lg">
          <div v-if="error" class="p-5 bg-red-50 border border-red-100 rounded-2xl text-red-600 text-sm font-bold flex items-center gap-3">
            <div class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></div>
            {{ error }}
          </div>
          
          <div v-if="success" class="p-5 bg-green-50 border border-green-100 rounded-2xl text-green-600 text-sm font-bold flex items-center gap-3">
            <div class="w-2 h-2 bg-green-600 rounded-full animate-pulse"></div>
            {{ success }}
          </div>

          <div class="space-y-8">
            <div class="space-y-3">
              <label class="text-[11px] font-black uppercase tracking-[0.3em] text-gray-400 ml-1">Email Identity</label>
              <input 
                v-model="email"
                type="email" 
                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-8 py-5 text-black focus:outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all placeholder:text-gray-300 font-semibold text-lg" 
                placeholder="YOUR@EMAIL.COM"
                required
              />
            </div>

            <div class="space-y-3">
              <label class="text-[11px] font-black uppercase tracking-[0.3em] text-gray-400 ml-1">Secret Password</label>
              <input 
                v-model="password"
                type="password" 
                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-8 py-5 text-black focus:outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all placeholder:text-gray-300 font-semibold text-lg" 
                placeholder="••••••••"
                required
              />
            </div>
          </div>

          <div class="flex items-center justify-between px-1">
            <label class="flex items-center gap-3 cursor-pointer group">
              <div class="relative w-6 h-6">
                <input type="checkbox" class="peer hidden" />
                <div class="w-full h-full bg-gray-100 border border-gray-200 rounded-lg peer-checked:bg-black peer-checked:border-black transition-all"></div>
                <svg class="absolute top-1.5 left-1.5 w-3 h-3 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-xs font-black uppercase tracking-widest text-gray-400 group-hover:text-black transition-colors">Keep me signed in</span>
            </label>
            <a href="#" class="text-xs font-black uppercase tracking-widest text-blue-600 hover:text-black transition-colors underline underline-offset-4">Reset?</a>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button type="submit" :disabled="loading" class="bg-black text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all duration-500 disabled:opacity-50 text-sm shadow-xl shadow-black/10">
              {{ loading ? 'Wait...' : 'Sign In' }}
            </button>
            <button 
              type="button" 
              @click="loginWithGoogle" 
              :disabled="googleLoading"
              class="bg-white border border-gray-200 text-gray-700 py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-gray-50 transition-all duration-500 flex items-center justify-center gap-3 text-sm"
            >
              <svg viewBox="0 0 24 24" width="18" height="18"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
              Google
            </button>
          </div>
        </form>

        <div class="mt-16 pt-8 border-t border-gray-50 max-w-lg">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Not a member? <router-link to="/register" class="text-black hover:text-blue-600 ml-2 transition-colors border-b-2 border-black">Create Account</router-link></p>
        </div>
      </div>

      <!-- Image Side -->
      <div class="lg:col-span-5 hidden lg:block relative bg-gray-50">
        <img src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=1000&q=90" class="w-full h-full object-cover" alt="Login" />
        <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
        <div class="absolute bottom-20 left-12 right-12 text-black">
          <h2 class="text-5xl font-black uppercase tracking-tighter leading-none mb-4">The Next<br/>Generation.</h2>
          <p class="text-gray-500 font-semibold text-lg">Enter the world of KICKS.</p>
        </div>
      </div>
    </div>
  </div>
</template>
