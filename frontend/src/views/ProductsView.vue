<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '../stores/products'
import ProductCard from '../components/product/ProductCard.vue'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()

const selectedCategory = ref(route.query.category || '')
const selectedBrand = ref(route.query.brand || '')
const selectedSort = ref(route.query.sort || 'newest')
const searchQuery = ref(route.query.search || '')
const currentPage = ref(1)

const fetchProducts = async () => {
  const params = {
    page: currentPage.value,
    sort: selectedSort.value,
  }
  
  if (selectedCategory.value) params.category = selectedCategory.value
  if (selectedBrand.value) params.brand = selectedBrand.value
  if (searchQuery.value) params.search = searchQuery.value
  
  await productStore.fetchProducts(params)
}

const applyFilters = () => {
  currentPage.value = 1
  const query = {}
  if (selectedCategory.value) query.category = selectedCategory.value
  if (selectedBrand.value) query.brand = selectedBrand.value
  if (selectedSort.value !== 'newest') query.sort = selectedSort.value
  if (searchQuery.value) query.search = searchQuery.value
  
  router.push({ path: '/products', query })
  fetchProducts()
}

const changePage = (page) => {
  currentPage.value = page
  fetchProducts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  await productStore.fetchCategories()
  await productStore.fetchBrands()
  await fetchProducts()
})

watch(() => route.query, () => {
  selectedCategory.value = route.query.category || ''
  selectedBrand.value = route.query.brand || ''
  selectedSort.value = route.query.sort || 'newest'
  searchQuery.value = route.query.search || ''
  fetchProducts()
})
</script>

<template>
  <div class="products-page">
    <div class="container">
      <div class="page-header">
        <h1>All Products</h1>
        <p>Browse our complete collection of premium shoes</p>
      </div>

      <div class="products-layout">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
          <div class="filter-section">
            <h3>Search</h3>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search products..."
              class="form-input"
              @keyup.enter="applyFilters"
            />
          </div>

          <div class="filter-section">
            <h3>Categories</h3>
            <div class="filter-options">
              <label class="filter-option">
                <input 
                  type="radio" 
                  v-model="selectedCategory" 
                  value=""
                  @change="applyFilters"
                />
                <span>All Categories</span>
              </label>
              <label 
                v-for="category in productStore.categories" 
                :key="category.id"
                class="filter-option"
              >
                <input 
                  type="radio" 
                  v-model="selectedCategory" 
                  :value="category.id"
                  @change="applyFilters"
                />
                <span>{{ category.name }} ({{ category.products_count }})</span>
              </label>
            </div>
          </div>

          <div class="filter-section">
            <h3>Brands</h3>
            <div class="filter-options">
              <label class="filter-option">
                <input 
                  type="radio" 
                  v-model="selectedBrand" 
                  value=""
                  @change="applyFilters"
                />
                <span>All Brands</span>
              </label>
              <label 
                v-for="brand in productStore.brands" 
                :key="brand"
                class="filter-option"
              >
                <input 
                  type="radio" 
                  v-model="selectedBrand" 
                  :value="brand"
                  @change="applyFilters"
                />
                <span>{{ brand }}</span>
              </label>
            </div>
          </div>

          <button @click="applyFilters" class="btn btn-primary w-full">Apply Filters</button>
        </aside>

        <!-- Products Grid -->
        <div class="products-main">
          <div class="products-header">
            <span class="results-count">
              {{ productStore.pagination.total || 0 }} products found
            </span>
            <div class="sort-select">
              <label>Sort by:</label>
              <select v-model="selectedSort" @change="applyFilters" class="form-input">
                <option value="newest">Newest</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
              </select>
            </div>
          </div>

          <div v-if="productStore.loading" class="loading-grid">
            <div v-for="i in 8" :key="i" class="skeleton-card">
              <div class="skeleton" style="height: 250px;"></div>
              <div class="skeleton-body">
                <div class="skeleton" style="height: 16px; width: 60%;"></div>
                <div class="skeleton" style="height: 20px; width: 80%; margin-top: 8px;"></div>
                <div class="skeleton" style="height: 16px; width: 40%; margin-top: 8px;"></div>
              </div>
            </div>
          </div>

          <div v-else-if="productStore.products.length > 0" class="products-grid">
            <ProductCard 
              v-for="product in productStore.products" 
              :key="product.id" 
              :product="product"
            />
          </div>

          <div v-else class="no-results">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <path d="M21 21l-4.35-4.35"/>
            </svg>
            <h3>No products found</h3>
            <p>Try adjusting your filters or search query</p>
          </div>

          <!-- Pagination -->
          <div v-if="productStore.pagination.lastPage > 1" class="pagination">
            <button 
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="page-btn"
            >
              Previous
            </button>
            
            <div class="page-numbers">
              <button 
                v-for="page in productStore.pagination.lastPage" 
                :key="page"
                @click="changePage(page)"
                :class="['page-btn', { active: currentPage === page }]"
              >
                {{ page }}
              </button>
            </div>
            
            <button 
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === productStore.pagination.lastPage"
              class="page-btn"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.products-page {
  padding: 2rem 0 4rem;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 0.5rem;
}

.page-header p {
  color: var(--gray-500);
  font-size: 1.125rem;
}

.products-layout {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 2rem;
}

.filters-sidebar {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  height: fit-content;
  position: sticky;
  top: 100px;
}

.filter-section {
  margin-bottom: 1.5rem;
}

.filter-section h3 {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.filter-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.5rem;
  transition: background 0.2s;
}

.filter-option:hover {
  background: var(--gray-100);
}

.filter-option input {
  width: 16px;
  height: 16px;
  accent-color: var(--primary);
}

.filter-option span {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.products-main {
  min-width: 0;
}

.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.results-count {
  color: var(--gray-500);
  font-size: 0.875rem;
}

.sort-select {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sort-select label {
  color: var(--gray-500);
  font-size: 0.875rem;
}

.sort-select .form-input {
  width: auto;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.loading-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.skeleton-card {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
}

.skeleton-body {
  padding: 1rem;
}

.no-results {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 1rem;
}

.no-results svg {
  width: 64px;
  height: 64px;
  color: var(--gray-300);
  margin-bottom: 1rem;
}

.no-results h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
}

.no-results p {
  color: var(--gray-500);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  margin-top: 3rem;
}

.page-numbers {
  display: flex;
  gap: 0.25rem;
}

.page-btn {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid var(--gray-300);
  border-radius: 0.5rem;
  color: var(--gray-700);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}

.page-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: white;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 1024px) {
  .products-layout {
    grid-template-columns: 1fr;
  }
  
  .filters-sidebar {
    position: static;
  }
  
  .products-grid,
  .loading-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .products-grid,
  .loading-grid {
    grid-template-columns: 1fr;
  }
  
  .products-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}
</style>
