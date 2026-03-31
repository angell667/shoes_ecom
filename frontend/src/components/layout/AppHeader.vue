<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useCartStore } from '../../stores/cart'
import { useProductStore } from '../../stores/products'
import axios from 'axios'
import gsap from 'gsap'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const cartStore = useCartStore()
const productStore = useProductStore()

const isScrolled = ref(false)
const showSearch = ref(false)
const searchQuery = ref('')
const searchResults = ref([])
const isSearching = ref(false)
const activeDropdown = ref(null)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20
}

const toggleSearch = () => {
  showSearch.value = !showSearch.value
  if (showSearch.value) {
    gsap.fromTo('.search-overlay', { y: -100, opacity: 0 }, { y: 0, opacity: 1, duration: 0.5, ease: 'power3.out' })
  }
}

const searchProducts = async (query) => {
  if (query.length < 2) {
    searchResults.value = []
    return
  }
  isSearching.value = true
  try {
    const response = await axios.get('/api/products/search', { params: { q: query } })
    searchResults.value = response.data
  } catch (error) {
    console.error('Search error:', error)
  } finally {
    isSearching.value = false
  }
}

let debounceTimer = null
watch(searchQuery, (newVal) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => searchProducts(newVal), 300)
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/')
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  clearTimeout(debounceTimer)
})
</script>

<template>
  <header 
    class="fixed top-0 left-0 right-0 z-[100] transition-all duration-500"
    :class="[isScrolled ? 'py-4' : 'py-8']"
  >
    <div class="container">
      <nav 
        class="glass rounded-[2rem] px-8 py-4 flex items-center justify-between shadow-lg border border-white/20"
      >
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2 group">
          <div class="w-10 h-10 bg-black rounded-full flex items-center justify-center text-white group-hover:rotate-[360deg] transition-transform duration-700">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
          </div>
          <span class="text-2xl font-black tracking-tighter text-black uppercase italic">KICKS.</span>
        </router-link>

        <!-- Desktop Nav -->
        <div class="hidden lg:flex items-center gap-10">
          <router-link to="/" class="nav-link-modern">Home</router-link>
          <router-link to="/products" class="nav-link-modern">Shop All</router-link>
          
          <div class="relative group">
            <button class="nav-link-modern flex items-center gap-1">
              Collections
              <svg class="w-4 h-4 group-hover:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div class="absolute top-full left-0 mt-4 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 translate-y-2 group-hover:translate-y-0">
              <div class="glass min-w-[240px] rounded-2xl p-4 shadow-2xl border border-white/20 overflow-hidden">
                <router-link 
                  v-for="cat in productStore.navbarCategories" 
                  :key="cat.id"
                  :to="`/category/${cat.slug}`"
                  class="block px-4 py-3 rounded-xl hover:bg-black hover:text-white transition-colors text-sm font-bold uppercase tracking-wider"
                >
                  {{ cat.name }}
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-4">
          <button @click="toggleSearch" class="p-3 hover:bg-black/5 rounded-full transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
          </button>

          <router-link to="/cart" class="relative p-3 hover:bg-black/5 rounded-full transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span v-if="cartStore.count > 0" class="absolute top-2 right-2 w-5 h-5 bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center rounded-full">
              {{ cartStore.count }}
            </span>
          </router-link>

          <div v-if="authStore.isAuthenticated" class="relative group">
            <button class="flex items-center gap-2 p-1.5 pr-4 bg-black rounded-full text-white hover:bg-gray-800 transition-colors">
              <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center font-bold">
                {{ authStore.user?.name?.charAt(0) }}
              </div>
              <span class="text-xs font-bold uppercase tracking-widest">{{ authStore.user?.name?.split(' ')[0] || 'User' }}</span>
            </button>
            <div class="absolute top-full right-0 mt-4 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 translate-y-2 group-hover:translate-y-0">
              <div class="glass min-w-[200px] rounded-2xl p-2 shadow-2xl border border-white/20">
                <router-link to="/profile" class="block px-4 py-3 rounded-xl hover:bg-gray-100 text-sm font-bold">Profile</router-link>
                <router-link v-if="authStore.isAdmin" to="/admin" class="block px-4 py-3 rounded-xl hover:bg-blue-50 text-blue-600 text-sm font-bold">Admin Portal</router-link>
                <hr class="my-2 border-white/20" />
                <button @click="handleLogout" class="w-full text-left px-4 py-3 rounded-xl hover:bg-red-50 text-red-600 text-sm font-bold">Logout</button>
              </div>
            </div>
          </div>
          <router-link v-else to="/login" class="btn btn-primary !py-2.5 !px-6 !text-xs !rounded-full uppercase tracking-widest font-black">
            Login
          </router-link>
        </div>
      </nav>
    </div>

    <!-- Modern Search Overlay -->
    <div v-if="showSearch" class="search-overlay fixed inset-0 z-[110] bg-white/95 backdrop-blur-2xl">
      <div class="container max-w-4xl pt-32">
        <div class="flex items-center justify-between border-b-4 border-black pb-4">
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Type your search..." 
            class="w-full bg-transparent text-5xl md:text-7xl font-black text-black placeholder:text-gray-200 focus:outline-none uppercase tracking-tighter"
            autofocus
          />
          <button @click="toggleSearch" class="p-4 hover:rotate-90 transition-transform duration-500">
            <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
              <path d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 overflow-y-auto max-h-[60vh] pb-20">
          <div v-if="isSearching" class="col-span-full py-20 text-center">
            <div class="inline-block w-12 h-12 border-4 border-black border-t-transparent rounded-full animate-spin"></div>
          </div>
          <template v-else-if="searchResults.length">
            <router-link 
              v-for="product in searchResults" 
              :key="product.id"
              :to="`/products/${product.slug}`"
              @click="toggleSearch"
              class="group flex items-center gap-6 p-4 rounded-3xl hover:bg-gray-50 transition-colors"
            >
              <img :src="product.image" class="w-24 h-24 object-cover rounded-2xl shadow-lg" />
              <div>
                <p class="text-xs font-black text-gray-400 uppercase">{{ product.brand }}</p>
                <h4 class="text-lg font-bold text-black group-hover:text-blue-600 line-clamp-1">{{ product.name }}</h4>
                <p class="text-xl font-black">${{ product.effective_price }}</p>
              </div>
            </router-link>
          </template>
          <div v-else-if="searchQuery.length > 2" class="col-span-full py-20 text-center text-gray-400">
            <p class="text-2xl font-bold">No results found for "{{ searchQuery }}"</p>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.nav-link-modern {
  @apply text-sm font-black uppercase tracking-widest text-gray-500 hover:text-black transition-colors relative after:content-[''] after:absolute after:bottom-[-4px] after:left-0 after:w-0 after:h-[2px] after:bg-black after:transition-all hover:after:w-full;
}

.router-link-active.nav-link-modern {
  @apply text-black after:w-full;
}
</style>
