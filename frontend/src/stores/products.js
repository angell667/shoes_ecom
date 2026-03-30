import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useProductStore = defineStore('products', () => {
  const products = ref([])
  const categories = ref([])
  const navbarCategories = ref([])
  const featuredProducts = ref([])
  const newArrivals = ref([])
  const currentProduct = ref(null)
  const brands = ref([])
  const loading = ref(false)
  const pagination = ref({})

  async function fetchProducts(params = {}) {
    loading.value = true
    try {
      const response = await axios.get('/api/products', { params })
      products.value = response.data.data
      pagination.value = {
        currentPage: response.data.current_page,
        lastPage: response.data.last_page,
        total: response.data.total,
        perPage: response.data.per_page
      }
    } catch (error) {
      console.error('Fetch products error:', error)
    } finally {
      loading.value = false
    }
  }

  async function fetchProduct(slug) {
    loading.value = true
    try {
      const response = await axios.get(`/api/products/${slug}`)
      currentProduct.value = response.data
      return response.data
    } catch (error) {
      console.error('Fetch product error:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchFeaturedProducts() {
    try {
      const response = await axios.get('/api/products/featured')
      featuredProducts.value = response.data
    } catch (error) {
      console.error('Fetch featured products error:', error)
    }
  }

  async function fetchNewArrivals() {
    try {
      const response = await axios.get('/api/products', { params: { sort: 'newest', per_page: 8 } })
      newArrivals.value = response.data.data
    } catch (error) {
      console.error('Fetch new arrivals error:', error)
    }
  }

  async function fetchCategories() {
    try {
      const response = await axios.get('/api/categories')
      categories.value = response.data
    } catch (error) {
      console.error('Fetch categories error:', error)
    }
  }

  async function fetchNavbarCategories() {
    try {
      const response = await axios.get('/api/categories/navbar')
      navbarCategories.value = response.data
    } catch (error) {
      console.error('Fetch navbar categories error:', error)
    }
  }

  async function fetchBrands() {
    try {
      const response = await axios.get('/api/products/brands')
      brands.value = response.data
    } catch (error) {
      console.error('Fetch brands error:', error)
    }
  }

  async function fetchCategory(slug) {
    loading.value = true
    try {
      const response = await axios.get(`/api/categories/${slug}`)
      return response.data
    } catch (error) {
      console.error('Fetch category error:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  return {
    products,
    categories,
    navbarCategories,
    featuredProducts,
    newArrivals,
    currentProduct,
    brands,
    loading,
    pagination,
    fetchProducts,
    fetchProduct,
    fetchFeaturedProducts,
    fetchNewArrivals,
    fetchCategories,
    fetchNavbarCategories,
    fetchBrands,
    fetchCategory
  }
})
