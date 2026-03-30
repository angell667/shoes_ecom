<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

const order = ref(null)
const loading = ref(true)
const processing = ref(false)
const error = ref('')
const paymentStatus = ref('pending') // pending, processing, success, failed
const paypalConfig = ref(null)

// Stripe elements
const stripeCardElement = ref(null)
const stripeLoaded = ref(false)

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price)
}

const fetchOrder = async () => {
  try {
    const response = await axios.get(`/api/orders/${route.params.id}`)
    order.value = response.data
    
    // Check if already paid
    if (order.value.payment_status === 'completed') {
      paymentStatus.value = 'success'
    }
  } catch (err) {
    error.value = 'Failed to load order'
  } finally {
    loading.value = false
  }
}

const fetchPayPalConfig = async () => {
  try {
    const response = await axios.get('/api/payments/paypal/config')
    paypalConfig.value = response.data
  } catch (err) {
    console.error('Failed to fetch PayPal config:', err)
  }
}

// Initialize Stripe
const initStripe = async () => {
  if (!window.Stripe) {
    // Load Stripe.js dynamically
    const script = document.createElement('script')
    script.src = 'https://js.stripe.com/v3/'
    script.onload = () => {
      setupStripe()
    }
    document.head.appendChild(script)
  } else {
    setupStripe()
  }
}

const setupStripe = async () => {
  try {
    const response = await axios.post('/api/payments/create-intent', {
      order_id: order.value.id
    })
    
    const stripeKey = import.meta.env.VITE_STRIPE_KEY || 'pk_test_your_stripe_publishable_key'
    const stripe = Stripe(stripeKey)
    const elements = stripe.elements()
    
    const cardStyle = {
      base: {
        color: '#32325d',
        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
        fontSmoothing: 'antialiased',
        fontSize: '16px',
        '::placeholder': {
          color: '#aab7c4'
        }
      },
      invalid: {
        color: '#fa755a',
        iconColor: '#fa755a'
      }
    }
    
    stripeCardElement.value = elements.create('card', { style: cardStyle })
    stripeCardElement.value.mount('#card-element')
    
    stripeLoaded.value = true
    
    // Store stripe instance for later use
    window.stripeInstance = stripe
    window.stripeClientSecret = response.data.client_secret
    window.stripeCardElement = stripeCardElement.value
  } catch (err) {
    console.error('Failed to initialize Stripe:', err)
    error.value = 'Failed to initialize payment. Please try again.'
  }
}

// Process Stripe payment
const processStripePayment = async () => {
  processing.value = true
  error.value = ''
  paymentStatus.value = 'processing'
  
  try {
    const stripe = window.stripeInstance
    const clientSecret = window.stripeClientSecret
    
    const { error: stripeError, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
      payment_method: {
        card: window.stripeCardElement,
        billing_details: {
          name: order.value.user?.name || 'Customer',
          email: order.value.user?.email || ''
        }
      }
    })
    
    if (stripeError) {
      error.value = stripeError.message
      paymentStatus.value = 'failed'
    } else if (paymentIntent.status === 'succeeded') {
      paymentStatus.value = 'success'
      // Update order status
      await axios.put(`/api/orders/${order.value.id}/status`, { status: 'processing' })
      setTimeout(() => {
        router.push(`/orders/${order.value.id}`)
      }, 2000)
    }
  } catch (err) {
    error.value = 'Payment failed. Please try again.'
    paymentStatus.value = 'failed'
  } finally {
    processing.value = false
  }
}

// Process PayPal payment
const processPayPalPayment = async () => {
  processing.value = true
  error.value = ''
  paymentStatus.value = 'processing'
  
  try {
    // Create PayPal order
    const response = await axios.post('/api/payments/paypal/create-order', {
      order_id: order.value.id
    })
    
    const { approval_url, order_id } = response.data
    
    // Store order_id in sessionStorage for capture later
    sessionStorage.setItem('paypal_order_id', order_id)
    sessionStorage.setItem('original_order_id', order.value.id)
    
    // Redirect to PayPal
    window.location.href = approval_url
  } catch (err) {
    error.value = 'Failed to initialize PayPal payment'
    paymentStatus.value = 'failed'
    processing.value = false
  }
}

// Handle PayPal return (capture payment)
const handlePayPalReturn = async () => {
  const paypalOrderId = sessionStorage.getItem('paypal_order_id')
  const originalOrderId = sessionStorage.getItem('original_order_id')
  const payerId = route.query.PayerID
  const paymentId = route.query.paymentId
  
  if (paypalOrderId && originalOrderId && payerId) {
    processing.value = true
    paymentStatus.value = 'processing'
    
    try {
      const response = await axios.post('/api/payments/paypal/capture', {
        order_id: originalOrderId,
        paypal_order_id: paypalOrderId
      })
      
      if (response.data.status === 'completed') {
        paymentStatus.value = 'success'
        sessionStorage.removeItem('paypal_order_id')
        sessionStorage.removeItem('original_order_id')
        
        setTimeout(() => {
          router.push(`/orders/${originalOrderId}`)
        }, 2000)
      }
    } catch (err) {
      error.value = 'Failed to capture PayPal payment'
      paymentStatus.value = 'failed'
    } finally {
      processing.value = false
    }
  }
}

// Process Cash on Delivery
const processCODPayment = async () => {
  processing.value = true
  error.value = ''
  
  try {
    // For COD, just update the order status to processing
    await axios.put(`/api/orders/${order.value.id}/status`, { status: 'processing' })
    paymentStatus.value = 'success'
    
    setTimeout(() => {
      router.push(`/orders/${order.value.id}`)
    }, 2000)
  } catch (err) {
    error.value = 'Failed to process order'
  } finally {
    processing.value = false
  }
}

onMounted(async () => {
  await fetchOrder()
  await fetchPayPalConfig()
  
  // Check if this is a PayPal return
  if (route.query.PayerID && route.query.paymentId) {
    await handlePayPalReturn()
  } else if (order.value?.payment_method === 'credit_card') {
    await initStripe()
  }
})
</script>

<template>
  <div class="payment-page">
    <div class="container">
      <!-- Loading -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading order details...</p>
      </div>

      <!-- Payment Success -->
      <div v-else-if="paymentStatus === 'success'" class="payment-result success">
        <div class="result-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h1>Payment Successful!</h1>
        <p>Thank you for your order. Your payment has been processed.</p>
        <p class="order-number">Order #{{ order?.order_number }}</p>
        <p>Redirecting to order details...</p>
      </div>

      <!-- Payment Failed -->
      <div v-else-if="paymentStatus === 'failed'" class="payment-result failed">
        <div class="result-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h1>Payment Failed</h1>
        <p class="error-message">{{ error }}</p>
        <button @click="router.push('/checkout')" class="btn-primary">Try Again</button>
      </div>

      <!-- Payment Form -->
      <div v-else class="payment-container">
        <h1>Complete Payment</h1>
        
        <div v-if="error" class="error-alert">{{ error }}</div>

        <div class="payment-layout">
          <!-- Payment Methods -->
          <div class="payment-methods">
            <!-- Credit Card / Stripe -->
            <div v-if="order?.payment_method === 'credit_card'" class="payment-section">
              <h2>💳 Credit Card Payment</h2>
              <p class="payment-desc">Enter your card details below to complete the payment.</p>
              
              <div class="card-form">
                <div id="card-element" class="card-element"></div>
                <div id="card-errors" class="card-errors"></div>
              </div>
              
              <button 
                @click="processStripePayment" 
                :disabled="processing || !stripeLoaded"
                class="btn-pay"
              >
                <span v-if="processing">
                  <span class="spinner-small"></span>
                  Processing...
                </span>
                <span v-else>Pay {{ formatPrice(order?.total) }}</span>
              </button>
              
              <div class="security-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Your payment is secured by Stripe</span>
              </div>
            </div>

            <!-- PayPal -->
            <div v-if="order?.payment_method === 'paypal'" class="payment-section">
              <h2>🅿️ PayPal Payment</h2>
              <p class="payment-desc">Click the button below to pay securely with PayPal.</p>
              
              <button 
                @click="processPayPalPayment" 
                :disabled="processing"
                class="btn-paypal"
              >
                <span v-if="processing">
                  <span class="spinner-small"></span>
                  Redirecting to PayPal...
                </span>
                <span v-else>
                  <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                    <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.93 4.778-4.005 7.201-9.138 7.201h-2.19a.563.563 0 0 0-.556.479l-1.187 7.527h-.506l-.24 1.516a.56.56 0 0 0 .554.647h3.882c.46 0 .85-.334.922-.788l.038-.2.728-4.62.047-.258a.933.933 0 0 1 .921-.788h.58c3.76 0 6.705-1.528 7.565-5.946.36-1.847.174-3.388-.777-4.471z"/>
                  </svg>
                  Pay with PayPal
                </span>
              </button>
              
              <div class="security-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Buyer protection included with PayPal</span>
              </div>
            </div>

            <!-- Cash on Delivery -->
            <div v-if="order?.payment_method === 'cod'" class="payment-section">
              <h2>💵 Cash on Delivery</h2>
              <p class="payment-desc">Pay when your order is delivered to your doorstep.</p>
              
              <div class="cod-info">
                <div class="cod-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Pay with cash or card when delivered</span>
                </div>
                <div class="cod-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>No online payment required</span>
                </div>
              </div>
              
              <button 
                @click="processCODPayment" 
                :disabled="processing"
                class="btn-cod"
              >
                <span v-if="processing">
                  <span class="spinner-small"></span>
                  Processing...
                </span>
                <span v-else>Place Order - Pay on Delivery</span>
              </button>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="order-summary">
            <h3>Order Summary</h3>
            <div class="summary-content">
              <div class="summary-row">
                <span>Order Number</span>
                <span>{{ order?.order_number }}</span>
              </div>
              <div class="summary-row">
                <span>Subtotal</span>
                <span>{{ formatPrice(order?.subtotal) }}</span>
              </div>
              <div class="summary-row">
                <span>Shipping</span>
                <span>{{ order?.shipping === 0 ? 'Free' : formatPrice(order?.shipping) }}</span>
              </div>
              <div class="summary-row">
                <span>Tax</span>
                <span>{{ formatPrice(order?.tax) }}</span>
              </div>
              <div v-if="order?.discount > 0" class="summary-row discount">
                <span>Discount</span>
                <span>-{{ formatPrice(order?.discount) }}</span>
              </div>
              <div class="summary-total">
                <span>Total</span>
                <span>{{ formatPrice(order?.total) }}</span>
              </div>
            </div>

            <div class="order-items">
              <h4>Items</h4>
              <div v-for="item in order?.items" :key="item.id" class="order-item">
                <img :src="item.product?.image || 'https://via.placeholder.com/50'" :alt="item.product_name" />
                <div class="item-details">
                  <span class="item-name">{{ item.product_name }}</span>
                  <span class="item-meta">Qty: {{ item.quantity }} | Size: {{ item.size || 'N/A' }}</span>
                </div>
                <span class="item-price">{{ formatPrice(item.price * item.quantity) }}</span>
              </div>
            </div>

            <div class="shipping-info">
              <h4>Shipping To</h4>
              <p>{{ order?.shipping_address }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.payment-page {
  min-height: 100vh;
  background: #f5f7fa;
  padding: 40px 20px;
}

.container {
  max-width: 1000px;
  margin: 0 auto;
}

.loading-container {
  text-align: center;
  padding: 80px 20px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

.spinner-small {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.payment-result {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.payment-result.success .result-icon {
  color: #16a34a;
  margin-bottom: 24px;
}

.payment-result.failed .result-icon {
  color: #dc2626;
  margin-bottom: 24px;
}

.payment-result h1 {
  font-size: 28px;
  margin-bottom: 12px;
  color: #111827;
}

.payment-result p {
  color: #6b7280;
  margin-bottom: 8px;
}

.order-number {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.error-message {
  color: #dc2626;
  margin: 16px 0;
}

.error-alert {
  background: #fee2e2;
  color: #dc2626;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 24px;
}

.payment-container h1 {
  font-size: 28px;
  margin-bottom: 24px;
  color: #111827;
}

.payment-layout {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 24px;
}

@media (max-width: 768px) {
  .payment-layout {
    grid-template-columns: 1fr;
  }
}

.payment-section {
  background: white;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.payment-section h2 {
  font-size: 20px;
  margin-bottom: 8px;
  color: #111827;
}

.payment-desc {
  color: #6b7280;
  margin-bottom: 24px;
}

.card-form {
  margin-bottom: 24px;
}

.card-element {
  padding: 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #f9fafb;
}

.card-errors {
  color: #dc2626;
  font-size: 14px;
  margin-top: 8px;
}

.btn-pay, .btn-paypal, .btn-cod, .btn-primary {
  width: 100%;
  padding: 16px 24px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-pay {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-pay:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-paypal {
  background: #0070ba;
  color: white;
  gap: 12px;
}

.btn-paypal:hover:not(:disabled) {
  background: #003087;
}

.btn-cod {
  background: #16a34a;
  color: white;
}

.btn-cod:hover:not(:disabled) {
  background: #15803d;
}

.btn-primary {
  background: #111827;
  color: white;
  width: auto;
  padding: 12px 24px;
}

.btn-pay:disabled, .btn-paypal:disabled, .btn-cod:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.security-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
  color: #6b7280;
  font-size: 14px;
}

.cod-info {
  margin-bottom: 24px;
}

.cod-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 0;
  color: #374151;
}

.cod-item svg {
  color: #16a34a;
}

.order-summary {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  height: fit-content;
  position: sticky;
  top: 20px;
}

.order-summary h3 {
  font-size: 18px;
  margin-bottom: 20px;
  color: #111827;
}

.summary-content {
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 16px;
  margin-bottom: 16px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  color: #6b7280;
}

.summary-row.discount {
  color: #16a34a;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin-top: 12px;
}

.order-items h4, .shipping-info h4 {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 12px;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
}

.order-item img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
}

.item-details {
  flex: 1;
}

.item-name {
  display: block;
  font-size: 14px;
  color: #111827;
}

.item-meta {
  font-size: 12px;
  color: #6b7280;
}

.item-price {
  font-weight: 500;
  color: #111827;
}

.shipping-info {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.shipping-info p {
  color: #374151;
  font-size: 14px;
  line-height: 1.5;
}
</style>