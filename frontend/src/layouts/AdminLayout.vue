<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import gsap from 'gsap'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const sidebarOpen = ref(true)

const menuItems = [
  { path: '/admin', name: 'Dashboard', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
  { path: '/admin/products', name: 'Products', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
  { path: '/admin/categories', name: 'Categories', icon: 'M7 7h.01M7 11h.01M7 15h.01M10 7h10M10 11h10M10 15h10' },
  { path: '/admin/orders', name: 'Orders', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
  { path: '/admin/payments', name: 'Payments', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
  { path: '/admin/users', name: 'Users', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
  { path: '/admin/reviews', name: 'Reviews', icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' },
  { path: '/admin/inventory', name: 'Inventory', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
  { path: '/admin/payment-settings', name: 'Settings', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
]

const isActive = (path) => {
  if (path === '/admin') return route.path === '/admin'
  return route.path.startsWith(path)
}

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  gsap.from('.sidebar', { x: -100, opacity: 0, duration: 0.8, ease: 'power3.out' })
  gsap.from('.admin-main-content', { opacity: 0, y: 20, duration: 0.8, delay: 0.2, ease: 'power3.out' })
})
</script>

<template>
  <div class="admin-layout flex min-h-screen bg-[#f8fafc]">
    <!-- Sidebar -->
    <aside 
      class="sidebar fixed inset-y-0 left-0 z-50 w-72 bg-white/80 backdrop-blur-xl border-r border-gray-200/50 transition-all duration-300 ease-in-out transform"
      :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
    >
      <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="p-8">
          <router-link to="/admin" class="flex items-center gap-3 group">
            <div class="w-10 h-10 bg-black rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
            <span class="text-xl font-bold tracking-tight text-gray-900">ShoeAdmin</span>
          </router-link>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
          <router-link
            v-for="item in menuItems"
            :key="item.path"
            :to="item.path"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
            :class="isActive(item.path) ? 'bg-black text-white shadow-md' : 'text-gray-600 hover:bg-gray-100'"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
            </svg>
            <span class="font-medium">{{ item.name }}</span>
            <div v-if="isActive(item.path)" class="ml-auto w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
          </router-link>
        </nav>

        <!-- User -->
        <div class="p-4 mt-auto border-t border-gray-100">
          <div class="p-4 rounded-2xl bg-gray-50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold">
              {{ authStore.user?.name?.charAt(0)?.toUpperCase() }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900 truncate">{{ authStore.user?.name }}</p>
              <p class="text-xs text-gray-500 truncate">{{ authStore.user?.email }}</p>
            </div>
            <button @click="logout" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main 
      class="flex-1 transition-all duration-300 ease-in-out"
      :class="{ 'ml-72': sidebarOpen, 'ml-0': !sidebarOpen }"
    >
      <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-gray-200/50 px-8 py-4 flex items-center justify-between">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <div class="flex items-center gap-4">
          <router-link to="/" class="text-sm font-medium text-gray-500 hover:text-black transition-colors">
            View Storefront
          </router-link>
          <div class="w-px h-6 bg-gray-200"></div>
          <button class="relative p-2 text-gray-400 hover:text-black">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="absolute top-2 right-2 w-2 h-2 bg-blue-500 rounded-full"></span>
          </button>
        </div>
      </header>

      <div class="admin-main-content p-8">
        <router-view />
      </div>
    </main>
  </div>
</template>

<style>
.admin-layout {
  font-family: 'Inter', system-ui, sans-serif;
}

/* Custom scrollbar for sidebar */
nav::-webkit-scrollbar {
  width: 4px;
}
nav::-webkit-scrollbar-track {
  background: transparent;
}
nav::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
</style>
