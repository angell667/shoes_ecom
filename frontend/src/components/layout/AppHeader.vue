<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useCartStore } from '../../stores/cart'
import { useProductStore } from '../../stores/products'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()
const productStore = useProductStore()

const showMobileMenu = ref(false)
const searchQuery = ref('')
const searchResults = ref([])
const isSearching = ref(false)
const showSearchDropdown = ref(false)
const searchInputRef = ref(null)
const activeDropdown = ref(null)
const activeSubDropdown = ref(null)
const selectedIndex = ref(-1)

let debounceTimer = null

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price)
}

const searchProducts = async (query) => {
  if (query.length < 2) {
    searchResults.value = []
    showSearchDropdown.value = false
    return
  }

  isSearching.value = true
  try {
    const response = await axios.get('/api/products/search', { params: { q: query } })
    searchResults.value = response.data
    showSearchDropdown.value = true
    selectedIndex.value = -1
  } catch (error) {
    console.error('Search error:', error)
    searchResults.value = []
  } finally {
    isSearching.value = false
  }
}

const handleSearchInput = (e) => {
  const query = e.target.value
  searchQuery.value = query
  
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    searchProducts(query)
  }, 200)
}

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    showSearchDropdown.value = false
    router.push({ path: '/products', query: { search: searchQuery.value } })
  }
}

const selectProduct = (product) => {
  showSearchDropdown.value = false
  searchQuery.value = ''
  searchResults.value = []
  router.push(`/products/${product.slug}`)
}

const handleKeyDown = (e) => {
  if (!showSearchDropdown.value || searchResults.value.length === 0) {
    if (e.key === 'Enter') {
      handleSearch()
    }
    return
  }

  switch (e.key) {
    case 'ArrowDown':
      e.preventDefault()
      selectedIndex.value = Math.min(selectedIndex.value + 1, searchResults.value.length - 1)
      break
    case 'ArrowUp':
      e.preventDefault()
      selectedIndex.value = Math.max(selectedIndex.value - 1, -1)
      break
    case 'Enter':
      e.preventDefault()
      if (selectedIndex.value >= 0) {
        selectProduct(searchResults.value[selectedIndex.value])
      } else {
        handleSearch()
      }
      break
    case 'Escape':
      showSearchDropdown.value = false
      selectedIndex.value = -1
      break
  }
}

const closeSearchDropdown = (e) => {
  if (!e.target.closest('.search-container')) {
    showSearchDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeSearchDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeSearchDropdown)
  clearTimeout(debounceTimer)
})

const handleLogout = async () => {
  await authStore.logout()
  cartStore.items = []
  cartStore.count = 0
  router.push('/')
}

const toggleDropdown = (id) => {
  activeDropdown.value = activeDropdown.value === id ? null : id
  activeSubDropdown.value = null
}

const toggleSubDropdown = (id) => {
  activeSubDropdown.value = activeSubDropdown.value === id ? null : id
}

const closeDropdowns = () => {
  activeDropdown.value = null
  activeSubDropdown.value = null
}
</script>

<template>
  <header class="header">
    <div class="container header-content">
      <router-link to="/" class="logo">
        <svg class="logo-icon" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
        <span class="logo-text">ShoeStore</span>
      </router-link>

      <nav class="nav-desktop">
        <router-link to="/" class="nav-link">Home</router-link>
        <router-link to="/products" class="nav-link">Products</router-link>
        
        <!-- Categories Dropdown -->
        <div 
          class="nav-dropdown"
          @mouseenter="toggleDropdown('categories')"
          @mouseleave="closeDropdowns"
        >
          <span class="nav-link">
            Categories
            <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </span>
          <div class="dropdown-menu" v-show="activeDropdown === 'categories'">
            <div 
              v-for="category in productStore.navbarCategories" 
              :key="category.id"
              class="dropdown-item-wrapper"
              @mouseenter="category.children?.length && toggleSubDropdown(category.id)"
              @mouseleave="activeSubDropdown === category.id && (activeSubDropdown = null)"
            >
              <router-link 
                :to="`/category/${category.slug}`"
                class="dropdown-item"
              >
                {{ category.name }}
                <svg v-if="category.children?.length" class="item-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
              </router-link>
              
              <!-- Subcategories Dropdown -->
              <div 
                v-if="category.children?.length"
                class="sub-dropdown-menu"
                v-show="activeSubDropdown === category.id"
              >
                <router-link 
                  v-for="sub in category.children" 
                  :key="sub.id"
                  :to="`/category/${sub.slug}`"
                  class="dropdown-item"
                >
                  {{ sub.name }}
                </router-link>
              </div>
            </div>
            
            <router-link 
              v-if="productStore.navbarCategories.length === 0"
              to="/products"
              class="dropdown-item"
            >
              Browse All Products
            </router-link>
          </div>
        </div>
        
        <!-- Brands Dropdown -->
        <div 
          class="nav-dropdown"
          @mouseenter="toggleDropdown('brands')"
          @mouseleave="closeDropdowns"
        >
          <span class="nav-link">
            Brands
            <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </span>
          <div class="dropdown-menu" v-show="activeDropdown === 'brands'">
            <router-link 
              v-for="brand in ['Nike', 'Adidas', 'New Balance', 'Puma', 'Vans', 'Converse', 'Asics']" 
              :key="brand"
              :to="`/products?brand=${brand}`"
              class="dropdown-item"
            >
              {{ brand }}
            </router-link>
          </div>
        </div>
      </nav>

      <div class="search-container">
        <form @submit.prevent="handleSearch" class="search-form">
          <input 
            ref="searchInputRef"
            :value="searchQuery"
            @input="handleSearchInput"
            @keydown="handleKeyDown"
            @focus="searchResults.length && (showSearchDropdown = true)"
            type="text" 
            placeholder="Search shoes, brands..." 
            class="search-input"
            autocomplete="off"
          />
          <button type="submit" class="search-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <path d="M21 21l-4.35-4.35"/>
            </svg>
          </button>
        </form>

        <!-- Search Dropdown -->
        <div v-show="showSearchDropdown" class="search-dropdown">
          <div v-if="isSearching" class="search-loading">
            <div class="search-spinner"></div>
            <span>Searching...</span>
          </div>
          
          <div v-else-if="searchResults.length > 0" class="search-results">
            <router-link 
              v-for="(product, index) in searchResults" 
              :key="product.id"
              :to="`/products/${product.slug}`"
              class="search-result-item"
              :class="{ 'active': index === selectedIndex }"
              @click="showSearchDropdown = false"
            >
              <img :src="product.image || 'https://via.placeholder.com/60'" :alt="product.name" class="result-image" />
              <div class="result-info">
                <span class="result-brand">{{ product.brand }}</span>
                <span class="result-name">{{ product.name }}</span>
                <span class="result-price">
                  <span v-if="product.sale_price" class="price-old">{{ formatPrice(product.price) }}</span>
                  <span class="price-current">{{ formatPrice(product.effective_price) }}</span>
                </span>
              </div>
            </router-link>
            
            <button @click="handleSearch" class="search-view-all">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
              </svg>
              Search for "{{ searchQuery }}"
            </button>
          </div>
          
          <div v-else-if="searchQuery.length >= 2" class="search-no-results">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
              <circle cx="11" cy="11" r="8"/>
              <path d="M21 21l-4.35-4.35"/>
            </svg>
            <span>No products found for "{{ searchQuery }}"</span>
          </div>
        </div>
      </div>

      <div class="header-actions">
        <router-link to="/cart" class="cart-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="21" r="1"/>
            <circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          <span v-if="cartStore.count > 0" class="cart-badge">{{ cartStore.count }}</span>
        </router-link>

        <template v-if="authStore.isAuthenticated">
          <div class="user-dropdown">
            <button class="user-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <span>{{ authStore.user?.name }}</span>
            </button>
            <div class="dropdown-menu">
              <router-link to="/profile" class="dropdown-item">Profile</router-link>
              <router-link to="/orders" class="dropdown-item">Orders</router-link>
              <router-link v-if="authStore.isAdmin" to="/admin" class="dropdown-item admin-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                  <rect x="3" y="3" width="7" height="7"/>
                  <rect x="14" y="3" width="7" height="7"/>
                  <rect x="14" y="14" width="7" height="7"/>
                  <rect x="3" y="14" width="7" height="7"/>
                </svg>
                Admin Panel
              </router-link>
              <button @click="handleLogout" class="dropdown-item logout-btn">Logout</button>
            </div>
          </div>
        </template>
        <template v-else>
          <router-link to="/login" class="btn btn-outline btn-sm">Login</router-link>
          <router-link to="/register" class="btn btn-primary btn-sm">Register</router-link>
        </template>

        <button class="mobile-menu-btn" @click="showMobileMenu = !showMobileMenu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 12h18M3 6h18M3 18h18"/>
          </svg>
        </button>
      </div>
    </div>

    <div v-if="showMobileMenu" class="mobile-menu">
      <div class="mobile-search">
        <input 
          v-model="searchQuery"
          @keyup.enter="() => { handleSearch(); showMobileMenu = false; }"
          type="text" 
          placeholder="Search products..." 
          class="mobile-search-input"
        />
        <button @click="() => { handleSearch(); showMobileMenu = false; }" class="mobile-search-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
          </svg>
        </button>
      </div>
      <router-link to="/" class="mobile-nav-link" @click="showMobileMenu = false">Home</router-link>
      <router-link to="/products" class="mobile-nav-link" @click="showMobileMenu = false">Products</router-link>
      <div class="mobile-categories">
        <span class="mobile-category-title">Categories</span>
        <template v-for="category in productStore.navbarCategories" :key="category.id">
          <router-link 
            :to="`/category/${category.slug}`"
            class="mobile-nav-link"
            @click="showMobileMenu = false"
          >
            {{ category.name }}
          </router-link>
          <router-link 
            v-for="sub in category.children" 
            :key="sub.id"
            :to="`/category/${sub.slug}`"
            class="mobile-nav-link mobile-sub-link"
            @click="showMobileMenu = false"
          >
            {{ sub.name }}
          </router-link>
        </template>
      </div>
      <div class="mobile-categories">
        <span class="mobile-category-title">Brands</span>
        <router-link 
          v-for="brand in ['Nike', 'Adidas', 'New Balance', 'Puma', 'Vans', 'Converse']" 
          :key="brand"
          :to="`/products?brand=${brand}`"
          class="mobile-nav-link"
          @click="showMobileMenu = false"
        >
          {{ brand }}
        </router-link>
      </div>
    </div>
  </header>
</template>

<style scoped>
.header {
  background: white;
  border-bottom: 1px solid var(--gray-200);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 2rem;
  padding: 1rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  font-size: 1.5rem;
  color: var(--primary);
}

.logo-icon {
  width: 32px;
  height: 32px;
}

.nav-desktop {
  display: flex;
  gap: 1.5rem;
}

.nav-link {
  color: var(--gray-600);
  font-weight: 500;
  transition: color 0.2s;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  cursor: pointer;
}

.nav-link:hover,
.nav-link.router-link-active {
  color: var(--primary);
}

.dropdown-arrow {
  width: 16px;
  height: 16px;
  transition: transform 0.2s;
}

.nav-dropdown:hover .dropdown-arrow {
  transform: rotate(180deg);
}

.nav-dropdown {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background: white;
  border: 1px solid var(--gray-200);
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  min-width: 200px;
  padding: 0.5rem;
  z-index: 50;
}

.dropdown-item-wrapper {
  position: relative;
}

.dropdown-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  color: var(--gray-700);
  border-radius: 0.25rem;
  transition: background 0.2s;
}

.dropdown-item:hover {
  background: var(--gray-100);
}

.item-arrow {
  width: 16px;
  height: 16px;
}

.sub-dropdown-menu {
  position: absolute;
  left: 100%;
  top: 0;
  background: white;
  border: 1px solid var(--gray-200);
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  min-width: 180px;
  padding: 0.5rem;
  z-index: 60;
}

.mobile-sub-link {
  padding-left: 2.5rem !important;
  font-size: 0.875rem;
}

.search-container {
  flex: 1;
  max-width: 450px;
  position: relative;
}

.search-form {
  display: flex;
}

.search-input {
  flex: 1;
  padding: 0.625rem 1rem;
  border: 2px solid var(--gray-200);
  border-right: none;
  border-radius: 0.5rem 0 0 0.5rem;
  font-size: 0.875rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-btn {
  padding: 0.625rem 1rem;
  background: var(--primary);
  color: white;
  border: 2px solid var(--primary);
  border-radius: 0 0.5rem 0.5rem 0;
  cursor: pointer;
  transition: background 0.2s;
}

.search-btn:hover {
  background: var(--primary-dark);
}

.search-btn svg {
  width: 20px;
  height: 20px;
}

/* Search Dropdown */
.search-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  overflow: hidden;
  z-index: 100;
  animation: dropdown-fade 0.15s ease-out;
}

@keyframes dropdown-fade {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.search-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1.5rem;
  color: var(--gray-500);
}

.search-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid var(--gray-200);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.search-results {
  max-height: 400px;
  overflow-y: auto;
}

.search-result-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1rem;
  text-decoration: none;
  transition: background 0.15s;
  border-bottom: 1px solid var(--gray-100);
}

.search-result-item:last-of-type {
  border-bottom: none;
}

.search-result-item:hover,
.search-result-item.active {
  background: var(--gray-50);
}

.result-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 0.5rem;
  background: var(--gray-100);
}

.result-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
  min-width: 0;
}

.result-brand {
  font-size: 0.7rem;
  color: var(--gray-500);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.result-name {
  font-weight: 500;
  color: var(--gray-900);
  font-size: 0.875rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.result-price {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.result-price .price-current {
  font-weight: 600;
  color: var(--primary);
  font-size: 0.875rem;
}

.result-price .price-old {
  font-size: 0.75rem;
  color: var(--gray-400);
  text-decoration: line-through;
}

.search-view-all {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.875rem;
  background: var(--gray-50);
  border: none;
  color: var(--primary);
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background 0.15s;
}

.search-view-all:hover {
  background: var(--gray-100);
}

.search-no-results {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 2rem;
  color: var(--gray-400);
  text-align: center;
}

.search-no-results span {
  font-size: 0.875rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.cart-btn {
  position: relative;
  padding: 0.5rem;
  color: var(--gray-600);
  transition: color 0.2s;
}

.cart-btn:hover {
  color: var(--primary);
}

.cart-btn svg {
  width: 24px;
  height: 24px;
}

.cart-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: var(--danger);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-dropdown {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: var(--gray-100);
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 500;
}

.user-btn svg {
  width: 20px;
  height: 20px;
}

.user-dropdown:hover .dropdown-menu {
  opacity: 1;
  visibility: visible;
}

.logout-btn {
  color: var(--danger);
  width: 100%;
  text-align: left;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 0.875rem;
}

.admin-link {
  color: #4f46e5;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.mobile-menu-btn {
  display: none;
  padding: 0.5rem;
  background: none;
  border: none;
  cursor: pointer;
}

.mobile-menu-btn svg {
  width: 24px;
  height: 24px;
}

.mobile-menu {
  display: none;
  padding: 1rem;
  border-top: 1px solid var(--gray-200);
  background: white;
}

.mobile-search {
  display: flex;
  margin-bottom: 1rem;
}

.mobile-search-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid var(--gray-200);
  border-right: none;
  border-radius: 0.5rem 0 0 0.5rem;
  font-size: 1rem;
}

.mobile-search-input:focus {
  outline: none;
  border-color: var(--primary);
}

.mobile-search-btn {
  padding: 0.75rem 1rem;
  background: var(--primary);
  color: white;
  border: 2px solid var(--primary);
  border-radius: 0 0.5rem 0.5rem 0;
  cursor: pointer;
}

.mobile-nav-link {
  display: block;
  padding: 0.75rem 1rem;
  color: var(--gray-700);
  border-radius: 0.5rem;
}

.mobile-nav-link:hover {
  background: var(--gray-100);
}

.mobile-categories {
  padding: 0.5rem 0;
}

.mobile-category-title {
  display: block;
  padding: 0.75rem 1rem;
  font-weight: 600;
  color: var(--gray-500);
  font-size: 0.75rem;
  text-transform: uppercase;
}

@media (max-width: 1024px) {
  .nav-desktop {
    display: none;
  }
  
  .search-container {
    display: none;
  }
}

@media (max-width: 768px) {
  .mobile-menu-btn {
    display: block;
  }
  
  .mobile-menu {
    display: block;
  }
  
  .user-btn span {
    display: none;
  }
}
</style>
