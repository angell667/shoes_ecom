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
  <div id="app">
    <!-- Admin Layout -->
    <template v-if="isAdminRoute">
      <AdminLayout />
    </template>
    
    <!-- Customer Layout -->
    <template v-else>
      <AppHeader />
      <main class="main-content">
        <router-view />
      </main>
      <AppFooter />
    </template>
  </div>
</template>

<style scoped>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
}
</style>
