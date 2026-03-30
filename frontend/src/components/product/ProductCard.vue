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
    alert('Added to cart!')
  } catch (error) {
    alert(error.message || 'Failed to add to cart')
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
  <div class="product-card">
    <router-link :to="`/products/${product.slug}`" class="product-image">
      <img :src="product.image || 'https://via.placeholder.com/300x300?text=No+Image'" :alt="product.name" />
      <div class="product-badges">
        <span v-if="product.is_featured" class="badge badge-featured">Featured</span>
        <span v-if="product.sale_price" class="badge badge-sale">
          -{{ product.discount_percentage }}%
        </span>
      </div>
      <button class="quick-add" @click.prevent="addToCart">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"/>
          <circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        Add to Cart
      </button>
    </router-link>
    <div class="product-info">
      <span class="product-category">{{ product.category?.name }}</span>
      <router-link :to="`/products/${product.slug}`" class="product-name">
        {{ product.name }}
      </router-link>
      <div class="product-brand" v-if="product.brand">{{ product.brand }}</div>
      <div class="product-price">
        <span class="price-current">{{ formatPrice(product.effective_price) }}</span>
        <span v-if="product.sale_price" class="price-old">{{ formatPrice(product.price) }}</span>
      </div>
      <div v-if="product.stock < 10 && product.stock > 0" class="stock-warning">
        Only {{ product.stock }} left!
      </div>
      <div v-else-if="product.stock === 0" class="stock-out">
        Out of Stock
      </div>
    </div>
  </div>
</template>

<style scoped>
.product-card {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.product-image {
  position: relative;
  display: block;
  height: 250px;
  overflow: hidden;
  background: var(--gray-100);
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.product-card:hover .product-image img {
  transform: scale(1.1);
}

.product-badges {
  position: absolute;
  top: 1rem;
  left: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.badge-featured {
  background: var(--primary);
  color: white;
}

.badge-sale {
  background: var(--danger);
  color: white;
}

.quick-add {
  position: absolute;
  bottom: -50px;
  left: 1rem;
  right: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: bottom 0.3s, background 0.2s;
}

.quick-add:hover {
  background: var(--primary-dark);
}

.product-card:hover .quick-add {
  bottom: 1rem;
}

.quick-add svg {
  width: 18px;
  height: 18px;
}

.product-info {
  padding: 1.25rem;
}

.product-category {
  display: block;
  font-size: 0.75rem;
  color: var(--gray-500);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.product-name {
  display: block;
  font-weight: 600;
  font-size: 1rem;
  color: var(--gray-800);
  margin-bottom: 0.25rem;
  line-height: 1.4;
  transition: color 0.2s;
}

.product-name:hover {
  color: var(--primary);
}

.product-brand {
  font-size: 0.875rem;
  color: var(--gray-500);
  margin-bottom: 0.75rem;
}

.product-price {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.price-current {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--gray-900);
}

.price-old {
  font-size: 0.875rem;
  color: var(--gray-400);
  text-decoration: line-through;
}

.stock-warning {
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: var(--warning);
  font-weight: 500;
}

.stock-out {
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: var(--danger);
  font-weight: 500;
}
</style>
