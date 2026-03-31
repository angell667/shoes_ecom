<script setup>
import { useCartStore } from '../../stores/cart'
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()

const addToCart = async () => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  try {
    await cartStore.addToCart(props.product.id, 1)
  } catch (error) {
    console.error('Failed to add to cart:', error)
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price)
}
</script>

<template>
  <div class="group relative flex flex-col bg-white rounded-[2rem] overflow-hidden transition-all duration-500 hover:shadow-2xl border border-gray-100">
    <!-- Image Area -->
    <div class="relative aspect-square overflow-hidden bg-gray-50">
      <router-link :to="`/products/${product.slug}`" class="block w-full h-full">
        <img 
          :src="product.image || 'https://via.placeholder.com/600x600?text=No+Image'" 
          :alt="product.name"
          class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
        />
      </router-link>
      
      <!-- Badges -->
      <div class="absolute top-6 left-6 flex flex-col gap-2 pointer-events-none">
        <span v-if="product.sale_price" class="px-4 py-1.5 bg-red-600 text-white text-xs font-black uppercase tracking-widest rounded-full">
          -{{ product.discount_percentage }}%
        </span>
        <span v-if="product.is_featured" class="px-4 py-1.5 bg-black text-white text-xs font-black uppercase tracking-widest rounded-full">
          Hot
        </span>
      </div>

      <!-- Quick Actions -->
      <div class="absolute inset-x-6 bottom-6 translate-y-20 group-hover:translate-y-0 transition-transform duration-500 ease-out flex gap-2">
        <button 
          @click.prevent="addToCart"
          class="flex-1 bg-black text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-wider hover:bg-gray-800 transition-colors flex items-center justify-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add to Cart
        </button>
      </div>
    </div>

    <!-- Info Area -->
    <div class="p-8 flex-1 flex flex-col">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">{{ product.brand || 'Original' }}</span>
        <div class="flex gap-1">
          <div v-for="i in 5" :key="i" class="w-1.5 h-1.5 rounded-full bg-gray-200"></div>
        </div>
      </div>
      
      <router-link 
        :to="`/products/${product.slug}`" 
        class="text-xl font-bold text-black group-hover:text-blue-600 transition-colors line-clamp-1 leading-tight mb-4"
      >
        {{ product.name }}
      </router-link>

      <div class="mt-auto flex items-center justify-between">
        <div class="flex flex-col">
          <span v-if="product.sale_price" class="text-xs text-gray-400 line-through font-medium">{{ formatPrice(product.price) }}</span>
          <span class="text-2xl font-black text-black tracking-tighter">{{ formatPrice(product.effective_price) }}</span>
        </div>
        
        <div class="h-10 w-10 rounded-full border border-gray-100 flex items-center justify-center group-hover:bg-black group-hover:text-white transition-all duration-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>
