<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { useCartStore } from './stores/cart'
import { useProductStore } from './stores/products'
import AppHeader from './components/layout/AppHeader.vue'
import AppFooter from './components/layout/AppFooter.vue'
import AdminLayout from './layouts/AdminLayout.vue'

const route = useRoute()
const authStore = useAuthStore()
const cartStore = useCartStore()
const productStore = useProductStore()

const isAdminRoute = computed(() => route.path.startsWith('/admin'))

onMounted(async () => {
  await productStore.fetchCategories()
  await productStore.fetchNavbarCategories()
  if (authStore.isAuthenticated) {
    await cartStore.fetchCart()
  }
})
</script>

<template>
  <div id="app" class="min-h-screen flex flex-col font-sans">
    <!-- Admin Layout -->
    <template v-if="isAdminRoute">
      <AdminLayout />
    </template>
    
    <!-- Customer Layout -->
    <template v-else>
      <AppHeader />
      <main class="flex-1">
        <router-view v-slot="{ Component }">
          <transition 
            name="fade-slide" 
            mode="out-in"
          >
            <component :is="Component" :key="route.path" />
          </transition>
        </router-view>
      </main>
      <AppFooter />
    </template>
  </div>
</template>

<style>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
