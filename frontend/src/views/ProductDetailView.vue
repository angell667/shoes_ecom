<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '../stores/products'
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const cartStore = useCartStore()
const authStore = useAuthStore()

const quantity = ref(1)
const selectedSize = ref('')
const selectedColor = ref('')

const product = computed(() => productStore.currentProduct)

const sizes = ['7', '7.5', '8', '8.5', '9', '9.5', '10', '10.5', '11', '11.5', '12']
const colors = ['Black', 'White', 'Red', 'Blue', 'Gray']

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price)
}

const addToCart = async () => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  
  try {
    await cartStore.addToCart(
      product.value.id, 
      quantity.value, 
      selectedSize.value || null, 
      selectedColor.value || null
    )
    alert('Added to cart!')
  } catch (error) {
    alert(error.message || 'Failed to add to cart')
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) quantity.value--
}

const increaseQuantity = () => {
  if (quantity.value < product.value.stock) quantity.value++
}

onMounted(async () => {
  await productStore.fetchProduct(route.params.slug)
})
</script>

<template>
  <div class="product-detail" v-if="product">
    <div class="container">
      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/">Home</router-link>
        <span>/</span>
        <router-link to="/products">Products</router-link>
        <span>/</span>
        <router-link :to="`/category/${product.category?.slug}`">{{ product.category?.name }}</router-link>
        <span>/</span>
        <span class="current">{{ product.name }}</span>
      </nav>

      <div class="product-layout">
        <!-- Product Images -->
        <div class="product-images">
          <div class="main-image">
            <img :src="product.image || 'https://via.placeholder.com/600x600?text=No+Image'" :alt="product.name" />
            <div class="image-badges">
              <span v-if="product.is_featured" class="badge badge-featured">Featured</span>
              <span v-if="product.sale_price" class="badge badge-sale">
                -{{ product.discount_percentage }}%
              </span>
            </div>
          </div>
          <div class="thumbnail-images" v-if="product.images && product.images.length">
            <img 
              v-for="(img, index) in product.images" 
              :key="index" 
              :src="img" 
              :alt="`${product.name} ${index + 1}`"
            />
          </div>
        </div>

        <!-- Product Info -->
        <div class="product-info">
          <span class="product-category">{{ product.category?.name }}</span>
          <h1 class="product-name">{{ product.name }}</h1>
          
          <div class="product-meta">
            <span v-if="product.brand" class="brand">Brand: {{ product.brand }}</span>
            <span class="sku">SKU: {{ product.sku }}</span>
          </div>

          <div class="product-price">
            <span class="price-current">{{ formatPrice(product.effective_price) }}</span>
            <span v-if="product.sale_price" class="price-old">{{ formatPrice(product.price) }}</span>
            <span v-if="product.sale_price" class="discount-badge">
              Save {{ formatPrice(product.price - product.effective_price) }}
            </span>
          </div>

          <div class="product-description" v-if="product.description">
            <h3>Description</h3>
            <p>{{ product.description }}</p>
          </div>

          <!-- Size Selection -->
          <div class="option-section">
            <h3>Select Size</h3>
            <div class="size-options">
              <button 
                v-for="size in sizes" 
                :key="size"
                @click="selectedSize = size"
                :class="['size-btn', { active: selectedSize === size }]"
              >
                {{ size }}
              </button>
            </div>
          </div>

          <!-- Color Selection -->
          <div class="option-section">
            <h3>Select Color</h3>
            <div class="color-options">
              <button 
                v-for="color in colors" 
                :key="color"
                @click="selectedColor = color"
                :class="['color-btn', { active: selectedColor === color }]"
              >
                {{ color }}
              </button>
            </div>
          </div>

          <!-- Quantity -->
          <div class="option-section">
            <h3>Quantity</h3>
            <div class="quantity-selector">
              <button @click="decreaseQuantity" class="qty-btn">-</button>
              <span class="qty-value">{{ quantity }}</span>
              <button @click="increaseQuantity" class="qty-btn">+</button>
            </div>
          </div>

          <!-- Stock Status -->
          <div class="stock-status">
            <span v-if="product.stock > 10" class="in-stock">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              In Stock
            </span>
            <span v-else-if="product.stock > 0" class="low-stock">
              Only {{ product.stock }} left in stock!
            </span>
            <span v-else class="out-stock">Out of Stock</span>
          </div>

          <!-- Add to Cart -->
          <div class="product-actions">
            <button 
              @click="addToCart" 
              :disabled="product.stock === 0"
              class="btn btn-primary btn-lg add-to-cart"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
              </svg>
              {{ product.stock > 0 ? 'Add to Cart' : 'Out of Stock' }}
            </button>
            <button class="btn btn-outline btn-lg wishlist-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
              </svg>
            </button>
          </div>

          <!-- Features -->
          <div class="product-features">
            <div class="feature">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
              </svg>
              <span>Free shipping on orders over $100</span>
            </div>
            <div class="feature">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 4 23 10 17 10"></polyline>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
              </svg>
              <span>30-day easy returns</span>
            </div>
            <div class="feature">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <span>Secure payment</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="loading">
    <div class="container">
      <div class="loading-skeleton">
        <div class="skeleton" style="height: 500px;"></div>
        <div class="loading-info">
          <div class="skeleton" style="height: 30px; width: 100px;"></div>
          <div class="skeleton" style="height: 40px; width: 80%; margin-top: 1rem;"></div>
          <div class="skeleton" style="height: 24px; width: 150px; margin-top: 1rem;"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.product-detail {
  padding: 2rem 0 4rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
  font-size: 0.875rem;
}

.breadcrumb a {
  color: var(--gray-500);
  transition: color 0.2s;
}

.breadcrumb a:hover {
  color: var(--primary);
}

.breadcrumb span {
  color: var(--gray-400);
}

.breadcrumb .current {
  color: var(--gray-700);
}

.product-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
}

.product-images {
  position: sticky;
  top: 100px;
  height: fit-content;
}

.main-image {
  position: relative;
  background: var(--gray-100);
  border-radius: 1rem;
  overflow: hidden;
  margin-bottom: 1rem;
}

.main-image img {
  width: 100%;
  height: 500px;
  object-fit: cover;
}

.image-badges {
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

.thumbnail-images {
  display: flex;
  gap: 0.75rem;
}

.thumbnail-images img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 0.5rem;
  cursor: pointer;
  border: 2px solid transparent;
  transition: border-color 0.2s;
}

.thumbnail-images img:hover {
  border-color: var(--primary);
}

.product-info {
  padding: 1rem 0;
}

.product-category {
  display: inline-block;
  font-size: 0.875rem;
  color: var(--primary);
  background: rgba(37, 99, 235, 0.1);
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  margin-bottom: 1rem;
}

.product-name {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 1rem;
  line-height: 1.3;
}

.product-meta {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
  color: var(--gray-500);
  font-size: 0.875rem;
}

.product-price {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.price-current {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
}

.price-old {
  font-size: 1.25rem;
  color: var(--gray-400);
  text-decoration: line-through;
}

.discount-badge {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 600;
}

.product-description {
  margin-bottom: 2rem;
}

.product-description h3 {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
}

.product-description p {
  color: var(--gray-600);
  line-height: 1.8;
}

.option-section {
  margin-bottom: 1.5rem;
}

.option-section h3 {
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
}

.size-options {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.size-btn {
  min-width: 48px;
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid var(--gray-300);
  border-radius: 0.5rem;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.size-btn:hover {
  border-color: var(--primary);
}

.size-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: white;
}

.color-options {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.color-btn {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid var(--gray-300);
  border-radius: 0.5rem;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.color-btn:hover {
  border-color: var(--primary);
}

.color-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: white;
}

.quantity-selector {
  display: inline-flex;
  align-items: center;
  border: 1px solid var(--gray-300);
  border-radius: 0.5rem;
  overflow: hidden;
}

.qty-btn {
  width: 40px;
  height: 40px;
  background: var(--gray-100);
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  transition: background 0.2s;
}

.qty-btn:hover {
  background: var(--gray-200);
}

.qty-value {
  width: 60px;
  text-align: center;
  font-weight: 600;
}

.stock-status {
  margin-bottom: 1.5rem;
}

.in-stock {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--success);
  font-weight: 500;
}

.in-stock svg {
  width: 18px;
  height: 18px;
}

.low-stock {
  color: var(--warning);
  font-weight: 500;
}

.out-stock {
  color: var(--danger);
  font-weight: 500;
}

.product-actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.add-to-cart {
  flex: 1;
}

.add-to-cart svg {
  width: 20px;
  height: 20px;
}

.wishlist-btn {
  padding: 1rem;
}

.wishlist-btn svg {
  width: 24px;
  height: 24px;
}

.product-features {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1.5rem;
  background: var(--gray-100);
  border-radius: 0.75rem;
}

.feature {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: var(--gray-600);
}

.feature svg {
  width: 20px;
  height: 20px;
  color: var(--primary);
}

.loading {
  padding: 4rem 0;
}

.loading-skeleton {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
}

.loading-info {
  padding-top: 2rem;
}

@media (max-width: 1024px) {
  .product-layout {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  
  .product-images {
    position: static;
  }
  
  .main-image img {
    height: 400px;
  }
}

@media (max-width: 640px) {
  .main-image img {
    height: 300px;
  }
  
  .product-name {
    font-size: 1.5rem;
  }
  
  .product-actions {
    flex-direction: column;
  }
}
</style>
