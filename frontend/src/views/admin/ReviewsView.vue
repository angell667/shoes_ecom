<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const reviews = ref([])
const loading = ref(true)
const filter = ref('all')
const currentPage = ref(1)
const totalPages = ref(1)

const fetchReviews = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: 20
    }
    if (filter.value !== 'all') {
      params.status = filter.value
    }
    const response = await axios.get('/api/admin/reviews', { params })
    reviews.value = response.data.reviews.data
    totalPages.value = response.data.reviews.last_page
  } catch (error) {
    console.error('Error fetching reviews:', error)
  } finally {
    loading.value = false
  }
}

const approveReview = async (reviewId) => {
  try {
    await axios.patch(`/api/admin/reviews/${reviewId}/approve`)
    fetchReviews()
  } catch (error) {
    console.error('Error approving review:', error)
  }
}

const rejectReview = async (reviewId) => {
  try {
    await axios.patch(`/api/admin/reviews/${reviewId}/reject`)
    fetchReviews()
  } catch (error) {
    console.error('Error rejecting review:', error)
  }
}

const deleteReview = async (reviewId) => {
  if (!confirm('Are you sure you want to delete this review?')) return
  try {
    await axios.delete(`/api/admin/reviews/${reviewId}`)
    fetchReviews()
  } catch (error) {
    console.error('Error deleting review:', error)
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const changePage = (page) => {
  currentPage.value = page
  fetchReviews()
}

onMounted(fetchReviews)
</script>

<template>
  <div class="admin-page">
    <div class="page-header">
      <h1>Review Management</h1>
      <p class="page-subtitle">Moderate customer reviews</p>
    </div>

    <!-- Filters -->
    <div class="filters">
      <button :class="['filter-btn', { active: filter === 'all' }]" @click="filter = 'all'; currentPage = 1; fetchReviews()">
        All Reviews
      </button>
      <button :class="['filter-btn', { active: filter === 'pending' }]" @click="filter = 'pending'; currentPage = 1; fetchReviews()">
        Pending Approval
      </button>
      <button :class="['filter-btn', { active: filter === 'approved' }]" @click="filter = 'approved'; currentPage = 1; fetchReviews()">
        Approved
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading reviews...</p>
    </div>

    <!-- Reviews List -->
    <div v-else class="reviews-grid">
      <div v-for="review in reviews" :key="review.id" class="review-card">
        <div class="review-header">
          <div class="review-rating">
            <span v-for="n in 5" :key="n" :class="{ filled: n <= review.rating }">★</span>
          </div>
          <span :class="['status-badge', review.is_approved ? 'approved' : 'pending']">
            {{ review.is_approved ? 'Approved' : 'Pending' }}
          </span>
        </div>
        
        <div class="review-product">
          <img :src="review.product?.image || 'https://via.placeholder.com/60'" :alt="review.product?.name" />
          <div>
            <p class="product-name">{{ review.product?.name }}</p>
            <p class="review-date">{{ formatDate(review.created_at) }}</p>
          </div>
        </div>

        <h4 v-if="review.title" class="review-title">{{ review.title }}</h4>
        <p v-if="review.comment" class="review-text">{{ review.comment }}</p>

        <div class="review-meta">
          <div class="author-info">
            <div class="avatar">{{ review.user?.name?.charAt(0) || 'U' }}</div>
            <div>
              <p class="author-name">{{ review.user?.name }}</p>
              <p class="verified-badge" v-if="review.is_verified_purchase">✓ Verified Purchase</p>
            </div>
          </div>
          <div class="helpful-count">
            👍 {{ review.helpful_count }} found helpful
          </div>
        </div>

        <div class="review-actions">
          <button v-if="!review.is_approved" @click="approveReview(review.id)" class="btn-approve">
            ✓ Approve
          </button>
          <button v-if="review.is_approved" @click="rejectReview(review.id)" class="btn-reject">
            ✗ Reject
          </button>
          <button @click="deleteReview(review.id)" class="btn-delete">
            🗑️ Delete
          </button>
        </div>
      </div>

      <div v-if="reviews.length === 0" class="empty-state">
        <p>📝 No reviews found</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination">
      <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">Previous</button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">Next</button>
    </div>
  </div>
</template>

<style scoped>
.admin-page { padding: 0; }

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
}

.page-subtitle {
  color: #6b7280;
  margin-top: 4px;
}

.filters {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 10px 20px;
  border: none;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #6b7280;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: all 0.2s;
}

.filter-btn:hover {
  background: #f3f4f6;
}

.filter-btn.active {
  background: #3b82f6;
  color: white;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 60px;
  color: #6b7280;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin { to { transform: rotate(360deg); } }

.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 20px;
}

.review-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.review-rating {
  font-size: 20px;
  color: #d1d5db;
}

.review-rating .filled {
  color: #fbbf24;
}

.status-badge {
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 9999px;
}

.status-badge.approved {
  background: #dcfce7;
  color: #16a34a;
}

.status-badge.pending {
  background: #fef3c7;
  color: #d97706;
}

.review-product {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.review-product img {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  object-fit: cover;
}

.product-name {
  font-weight: 600;
  color: #111827;
  font-size: 14px;
}

.review-date {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
}

.review-title {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 8px;
}

.review-text {
  color: #374151;
  font-size: 14px;
  line-height: 1.6;
  margin-bottom: 16px;
}

.review-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f3f4f6;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.author-name {
  font-weight: 600;
  color: #111827;
  font-size: 14px;
}

.verified-badge {
  font-size: 12px;
  color: #16a34a;
}

.helpful-count {
  font-size: 12px;
  color: #6b7280;
}

.review-actions {
  display: flex;
  gap: 8px;
}

.btn-approve, .btn-reject, .btn-delete {
  flex: 1;
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-approve {
  background: #dcfce7;
  color: #16a34a;
}

.btn-approve:hover {
  background: #bbf7d0;
}

.btn-reject {
  background: #fef3c7;
  color: #d97706;
}

.btn-reject:hover {
  background: #fde68a;
}

.btn-delete {
  background: #fee2e2;
  color: #dc2626;
}

.btn-delete:hover {
  background: #fecaca;
}

.empty-state {
  text-align: center;
  padding: 60px;
  color: #6b7280;
  background: white;
  border-radius: 16px;
  grid-column: 1 / -1;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 24px;
  padding: 16px;
}

.pagination button {
  padding: 8px 16px;
  border: none;
  background: #3b82f6;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
}

.pagination button:disabled {
  background: #e5e7eb;
  color: #9ca3af;
  cursor: not-allowed;
}

.pagination span {
  color: #6b7280;
  font-size: 14px;
}

@media (max-width: 768px) {
  .reviews-grid {
    grid-template-columns: 1fr;
  }
  
  .review-actions {
    flex-direction: column;
  }
}
</style>