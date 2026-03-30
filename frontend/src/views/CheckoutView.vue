<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../stores/cart'
import { useOrderStore } from '../stores/orders'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const cartStore = useCartStore()
const orderStore = useOrderStore()
const authStore = useAuthStore()

const form = ref({
  shipping_address: '',
  payment_method: 'credit_card',
  notes: ''
})

const loading = ref(false)
const error = ref('')

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price)
}

const subtotal = computed(() => cartStore.total)
const shipping = computed(() => subtotal.value >= 100 ? 0 : 10)
const tax = computed(() => subtotal.value * 0.1)
const total = computed(() => subtotal.value + shipping.value + tax.value)

const placeOrder = async () => {
  if (!form.value.shipping_address.trim()) {
    error.value = 'Please enter a shipping address'
    return
  }
  
  error.value = ''
  loading.value = true
  
  try {
    await orderStore.createOrder({
      shipping_address: form.value.shipping_address,
      payment_method: form.value.payment_method,
      notes: form.value.notes
    })
    
    await cartStore.fetchCart()
    router.push(`/orders/${orderStore.currentOrder.id}`)
  } catch (err) {
    error.value = err.message || 'Failed to place order'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (cartStore.isEmpty) {
    router.push('/cart')
  }
  if (authStore.user?.address) {
    form.value.shipping_address = authStore.user.address
  }
})
</script>

<template>
  <div class="checkout-page">
    <div class="container">
      <h1 class="page-title">Checkout</h1>

      <div class="checkout-layout">
        <div class="checkout-form">
          <div v-if="error" class="error-message">
            {{ error }}
          </div>

          <!-- Shipping Address -->
          <div class="checkout-section">
            <h2>
              <span class="step-number">1</span>
              Shipping Address
            </h2>
            <div class="form-group">
              <label class="form-label">Full Address</label>
              <textarea 
                v-model="form.shipping_address"
                class="form-input"
                rows="4"
                placeholder="Enter your complete shipping address including street, city, state, and zip code"
                required
              ></textarea>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="checkout-section">
            <h2>
              <span class="step-number">2</span>
              Payment Method
            </h2>
            <div class="payment-options">
              <label class="payment-option" :class="{ active: form.payment_method === 'credit_card' }">
                <input type="radio" v-model="form.payment_method" value="credit_card" />
                <div class="payment-content">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                  </svg>
                  <div>
                    <span class="payment-name">Credit Card</span>
                    <span class="payment-desc">Visa, Mastercard, Amex</span>
                  </div>
                </div>
              </label>
              <label class="payment-option" :class="{ active: form.payment_method === 'paypal' }">
                <input type="radio" v-model="form.payment_method" value="paypal" />
                <div class="payment-content">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.93 4.778-4.005 7.201-9.138 7.201h-2.19a.563.563 0 0 0-.556.479l-1.187 7.527h-.506l-.24 1.516a.56.56 0 0 0 .554.647h3.882c.46 0 .85-.334.922-.788l.038-.2.728-4.62.047-.258a.933.933 0 0 1 .921-.788h.58c3.76 0 6.705-1.528 7.565-5.946.36-1.847.174-3.388-.777-4.471z"/>
                  </svg>
                  <div>
                    <span class="payment-name">PayPal</span>
                    <span class="payment-desc">Pay with your PayPal account</span>
                  </div>
                </div>
              </label>
              <label class="payment-option" :class="{ active: form.payment_method === 'cod' }">
                <input type="radio" v-model="form.payment_method" value="cod" />
                <div class="payment-content">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                  </svg>
                  <div>
                    <span class="payment-name">Cash on Delivery</span>
                    <span class="payment-desc">Pay when you receive</span>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Order Notes -->
          <div class="checkout-section">
            <h2>
              <span class="step-number">3</span>
              Order Notes (Optional)
            </h2>
            <div class="form-group">
              <textarea 
                v-model="form.notes"
                class="form-input"
                rows="3"
                placeholder="Add any special instructions for your order..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
          <h3>Order Summary</h3>
          
          <div class="summary-items">
            <div v-for="item in cartStore.items" :key="item.id" class="summary-item">
              <img :src="item.product?.image || 'https://via.placeholder.com/60'" :alt="item.product?.name" />
              <div class="item-info">
                <span class="item-name">{{ item.product?.name }}</span>
                <span class="item-qty">Qty: {{ item.quantity }}</span>
              </div>
              <span class="item-price">{{ formatPrice(item.product?.effective_price * item.quantity) }}</span>
            </div>
          </div>

          <div class="summary-totals">
            <div class="summary-row">
              <span>Subtotal</span>
              <span>{{ formatPrice(subtotal) }}</span>
            </div>
            <div class="summary-row">
              <span>Shipping</span>
              <span>{{ shipping === 0 ? 'Free' : formatPrice(shipping) }}</span>
            </div>
            <div class="summary-row">
              <span>Tax (10%)</span>
              <span>{{ formatPrice(tax) }}</span>
            </div>
            <div class="summary-total">
              <span>Total</span>
              <span>{{ formatPrice(total) }}</span>
            </div>
          </div>

          <button 
            @click="placeOrder" 
            :disabled="loading || cartStore.isEmpty"
            class="btn btn-primary btn-lg w-full place-order-btn"
          >
            {{ loading ? 'Placing Order...' : 'Place Order' }}
          </button>

          <p class="secure-text">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            Your payment information is secure
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.checkout-page {
  padding: 2rem 0 4rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 2rem;
}

.checkout-layout {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
  align-items: start;
}

.checkout-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
  padding: 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.checkout-section {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
}

.checkout-section h2 {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.step-number {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: var(--primary);
  color: white;
  border-radius: 50%;
  font-size: 0.875rem;
  font-weight: 700;
}

.payment-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.payment-option {
  display: block;
  cursor: pointer;
}

.payment-option input {
  display: none;
}

.payment-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 2px solid var(--gray-200);
  border-radius: 0.75rem;
  transition: all 0.2s;
}

.payment-option.active .payment-content {
  border-color: var(--primary);
  background: rgba(37, 99, 235, 0.05);
}

.payment-content svg {
  width: 32px;
  height: 32px;
  color: var(--gray-600);
}

.payment-option.active .payment-content svg {
  color: var(--primary);
}

.payment-name {
  display: block;
  font-weight: 600;
  color: var(--gray-800);
}

.payment-desc {
  display: block;
  font-size: 0.875rem;
  color: var(--gray-500);
}

.order-summary {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  position: sticky;
  top: 100px;
}

.order-summary h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.summary-items {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.summary-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.summary-item img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 0.5rem;
}

.item-info {
  flex: 1;
  min-width: 0;
}

.item-name {
  display: block;
  font-weight: 500;
  font-size: 0.875rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-qty {
  font-size: 0.75rem;
  color: var(--gray-500);
}

.item-price {
  font-weight: 600;
  font-size: 0.875rem;
}

.summary-totals {
  margin-bottom: 1.5rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  font-size: 0.875rem;
  color: var(--gray-600);
}

.summary-total {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  margin-top: 0.5rem;
  border-top: 1px solid var(--gray-200);
  font-size: 1.25rem;
  font-weight: 700;
}

.place-order-btn {
  margin-bottom: 1rem;
}

.secure-text {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: var(--gray-500);
}

.secure-text svg {
  width: 16px;
  height: 16px;
}

@media (max-width: 1024px) {
  .checkout-layout {
    grid-template-columns: 1fr;
  }
  
  .order-summary {
    position: static;
    order: -1;
  }
}
</style>
