<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useCartStore } from '../../stores/cart'
import { useProductStore } from '../../stores/products'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()
const productStore = useProductStore()

const showMobileMenu = ref(false)
const searchQuery = ref('')
const activeDropdown = ref(null)
const activeSubDropdown = ref(null)

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/products', query: { search: searchQuery.value } })
    searchQuery.value = ''
  }
}

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

      <form @submit.prevent="handleSearch" class="search-form">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search shoes..." 
          class="search-input"
        />
        <button type="submit" class="search-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
          </svg>
        </button>
      </form>

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

.search-form {
  flex: 1;
  display: flex;
  max-width: 400px;
}

.search-input {
  flex: 1;
  padding: 0.5rem 1rem;
  border: 1px solid var(--gray-300);
  border-right: none;
  border-radius: 0.5rem 0 0 0.5rem;
  font-size: 0.875rem;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
}

.search-btn {
  padding: 0.5rem 1rem;
  background: var(--primary);
  color: white;
  border: 1px solid var(--primary);
  border-radius: 0 0.5rem 0.5rem 0;
  cursor: pointer;
}

.search-btn svg {
  width: 20px;
  height: 20px;
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
  
  .search-form {
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
