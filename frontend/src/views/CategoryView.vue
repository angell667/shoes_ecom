<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useProductStore } from '../stores/products'
import ProductCard from '../components/product/ProductCard.vue'

const route = useRoute()
const productStore = useProductStore()

const category = ref(null)
const selectedSort = ref('newest')
const currentPage = ref(1)

const fetchCategoryData = async () => {
  category.value = await productStore.fetchCategory(route.params.slug)
}

onMounted(fetchCategoryData)

watch(() => route.params.slug, fetchCategoryData)

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price)
}
</script>

<template>
  <div class="category-page">
    <div class="container">
      <div class="page-header" v-if="category">
        <h1>{{ category.name }}</h1>
        <p>{{ category.description || `Browse our ${category.name} collection` }}</p>
      </div>

      <div class="products-header">
        <span class="results-count">
          {{ category?.products?.length || 0 }} products
        </span>
        <div class="sort-select">
          <label>Sort by:</label>
          <select v-model="selectedSort" class="form-input">
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
          </div>
        </div>
      </div>

      <div v-else-if="category?.products?.length > 0" class="products-grid">
        <ProductCard 
          v-for="product in category.products" 
          :key="product.id" 
          :product="product"
        />
      </div>

      <div v-else class="no-results">
        <h3>No products in this category</h3>
        <p>Check back later for new arrivals!</p>
        <router-link to="/products" class="btn btn-primary mt-4">Browse All Products</router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.category-page {
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
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.loading-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
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

.no-results h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
}

.no-results p {
  color: var(--gray-500);
}

@media (max-width: 1024px) {
  .products-grid,
  .loading-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .products-grid,
  .loading-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .products-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}

@media (max-width: 640px) {
  .products-grid,
  .loading-grid {
    grid-template-columns: 1fr;
  }
}
</style>
