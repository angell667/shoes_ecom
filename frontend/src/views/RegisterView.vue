<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import gsap from 'gsap'

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

onMounted(() => {
  gsap.from('.auth-card-modern', { opacity: 0, y: 40, duration: 1, ease: 'power4.out' })
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
      <!-- Image Side -->
      <div class="lg:col-span-5 hidden lg:block relative bg-gray-50">
        <img src="https://images.unsplash.com/photo-1556906781-9a412961c28c?w=1000&q=90" class="w-full h-full object-cover" alt="Register" />
        <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
        <div class="absolute bottom-20 left-12 right-12 text-black">
          <h2 class="text-5xl font-black uppercase tracking-tighter leading-none mb-4">Elite<br/>Membership.</h2>
          <p class="text-gray-500 font-semibold text-lg">Join the radical movement.</p>
        </div>
      </div>

      <!-- Form Side -->
      <div class="lg:col-span-7 p-12 md:p-20 bg-white text-black">
        <div class="mb-12">
          <div class="inline-block px-4 py-1.5 rounded-full bg-purple-50 text-purple-600 text-[10px] font-black uppercase tracking-[0.2em] mb-6">New Era</div>
          <h1 class="text-6xl font-black uppercase tracking-tighter mb-4">Create<br/><span class="text-gray-300 italic">Account.</span></h1>
          <p class="text-gray-400 font-medium text-lg">Start your elite journey today.</p>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-8 max-w-lg">
          <div v-if="errors.general" class="p-5 bg-red-50 border border-red-100 rounded-2xl text-red-600 text-sm font-bold flex items-center gap-3">
            <div class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></div>
            {{ errors.general }}
          </div>

          <div class="grid gap-8">
            <div class="space-y-3">
              <label class="text-[11px] font-black uppercase tracking-[0.3em] text-gray-400 ml-1">Full Name</label>
              <input 
                v-model="form.name"
                type="text" 
                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-8 py-5 text-black focus:outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all placeholder:text-gray-300 font-semibold text-lg" 
                :class="{ '!border-red-500/50': errors.name }"
                placeholder="JOHN DOE"
                required
              />
              <span v-if="errors.name" class="text-[10px] text-red-500 font-bold ml-4 uppercase">{{ errors.name[0] }}</span>
            </div>

            <div class="space-y-3">
              <label class="text-[11px] font-black uppercase tracking-[0.3em] text-gray-400 ml-1">Email Identity</label>
              <input 
                v-model="form.email"
                type="email" 
                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-8 py-5 text-black focus:outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all placeholder:text-gray-300 font-semibold text-lg" 
                :class="{ '!border-red-500/50': errors.email }"
                placeholder="YOUR@EMAIL.COM"
                required
              />
              <span v-if="errors.email" class="text-[10px] text-red-500 font-bold ml-4 uppercase">{{ errors.email[0] }}</span>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <label class="text-[11px] font-black uppercase tracking-[0.3em] text-gray-400 ml-1">Password</label>
                <input 
                  v-model="form.password"
                  type="password" 
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-8 py-5 text-black focus:outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all placeholder:text-gray-300 font-semibold text-lg" 
                  :class="{ '!border-red-500/50': errors.password }"
                  placeholder="••••••••"
                  required
                />
              </div>
              <div class="space-y-3">
                <label class="text-[11px] font-black uppercase tracking-[0.3em] text-gray-400 ml-1">Confirm</label>
                <input 
                  v-model="form.password_confirmation"
                  type="password" 
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-8 py-5 text-black focus:outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all placeholder:text-gray-300 font-semibold text-lg" 
                  placeholder="••••••••"
                  required
                />
              </div>
            </div>
            <span v-if="errors.password" class="text-[10px] text-red-500 font-bold ml-4 uppercase">{{ errors.password[0] }}</span>
          </div>

          <div class="pt-4">
            <label class="flex items-start gap-4 cursor-pointer group">
              <div class="relative w-6 h-6 mt-1 shrink-0">
                <input type="checkbox" class="peer hidden" required />
                <div class="w-full h-full bg-gray-100 border border-gray-200 rounded-lg peer-checked:bg-black peer-checked:border-black transition-all"></div>
                <svg class="absolute top-1.5 left-1.5 w-3 h-3 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-xs font-bold text-gray-400 group-hover:text-black transition-colors leading-relaxed uppercase tracking-widest">
                I AGREE TO THE <a href="#" class="text-black underline underline-offset-4">TERMS</a> AND <a href="#" class="text-black underline underline-offset-4">PRIVACY POLICY</a>.
              </span>
            </label>
          </div>

          <button type="submit" :disabled="loading" class="w-full bg-black text-white py-6 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all duration-500 disabled:opacity-50 mt-4 shadow-xl shadow-black/10">
            {{ loading ? 'Creating...' : 'Create Account' }}
          </button>
        </form>

        <div class="mt-16 pt-8 border-t border-gray-50 max-w-lg">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Already a member? <router-link to="/login" class="text-black hover:text-blue-500 ml-2 transition-colors border-b-2 border-black">Sign In</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>
