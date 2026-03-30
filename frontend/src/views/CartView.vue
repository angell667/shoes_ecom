<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../stores/cart'

const router = useRouter()
const cartStore = useCartStore()

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price)
}

const updateQuantity = async (item, delta) => {
  const newQty = item.quantity + delta
  if (newQty < 1) return
  await cartStore.updateQuantity(item.id, newQty)
}

const removeItem = async (itemId) => {
  if (confirm('Remove this item from cart?')) {
    await cartStore.removeFromCart(itemId)
  }
}

const proceedToCheckout = () => {
  router.push('/checkout')
}

onMounted(() => {
  cartStore.fetchCart()
})
</script>

<template>
  <div class="cart-page">
    <div class="container">
      <h1 class="page-title">Shopping Cart</h1>

      <div v-if="cartStore.isEmpty && !cartStore.loading" class="empty-cart">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"/>
          <circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't added anything to your cart yet.</p>
        <router-link to="/products" class="btn btn-primary btn-lg">Start Shopping</router-link>
      </div>

      <div v-else class="cart-layout">
        <div class="cart-items">
          <div v-for="item in cartStore.items" :key="item.id" class="cart-item">
            <div class="item-image">
              <img :src="item.product?.image || 'https://via.placeholder.com/120'" :alt="item.product?.name" />
            </div>
            <div class="item-details">
              <router-link :to="`/products/${item.product?.slug}`" class="item-name">
                {{ item.product?.name }}
              </router-link>
              <div class="item-meta">
                <span v-if="item.size">Size: {{ item.size }}</span>
                <span v-if="item.color">Color: {{ item.color }}</span>
              </div>
              <div class="item-price">{{ formatPrice(item.product?.effective_price) }}</div>
            </div>
            <div class="item-quantity">
              <button @click="updateQuantity(item, -1)" class="qty-btn">-</button>
              <span class="qty-value">{{ item.quantity }}</span>
              <button @click="updateQuantity(item, 1)" class="qty-btn">+</button>
            </div>
            <div class="item-total">
              {{ formatPrice(item.product?.effective_price * item.quantity) }}
            </div>
            <button @click="removeItem(item.id)" class="remove-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="cart-summary">
          <h3>Order Summary</h3>
          <div class="summary-row">
            <span>Subtotal ({{ cartStore.count }} items)</span>
            <span>{{ formatPrice(cartStore.total) }}</span>
          </div>
          <div class="summary-row">
            <span>Shipping</span>
            <span>{{ cartStore.total >= 100 ? 'Free' : formatPrice(10) }}</span>
          </div>
          <div class="summary-row">
            <span>Estimated Tax</span>
            <span>{{ formatPrice(cartStore.total * 0.1) }}</span>
          </div>
          <div class="summary-total">
            <span>Total</span>
            <span>{{ formatPrice(cartStore.total + (cartStore.total >= 100 ? 0 : 10) + cartStore.total * 0.1) }}</span>
          </div>
          
          <button @click="proceedToCheckout" class="btn btn-primary btn-lg w-full checkout-btn">
            Proceed to Checkout
          </button>
          
          <div class="cart-features">
            <div class="feature">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <span>Secure checkout</span>
            </div>
            <div class="feature">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 4 23 10 17 10"></polyline>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
              </svg>
              <span>30-day returns</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cart-page {
  padding: 2rem 0 4rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 2rem;
}

.empty-cart {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 1rem;
}

.empty-cart svg {
  width: 80px;
  height: 80px;
  color: var(--gray-300);
  margin-bottom: 1.5rem;
}

.empty-cart h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
}

.empty-cart p {
  color: var(--gray-500);
  margin-bottom: 2rem;
}

.cart-layout {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 2rem;
  align-items: start;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cart-item {
  display: grid;
  grid-template-columns: 100px 1fr auto auto auto;
  gap: 1.5rem;
  align-items: center;
  padding: 1.5rem;
  background: white;
  border-radius: 1rem;
}

.item-image {
  width: 100px;
  height: 100px;
  border-radius: 0.5rem;
  overflow: hidden;
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-details {
  min-width: 0;
}

.item-name {
  display: block;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 0.5rem;
}

.item-name:hover {
  color: var(--primary);
}

.item-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.875rem;
  color: var(--gray-500);
  margin-bottom: 0.25rem;
}

.item-price {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-900);
}

.item-quantity {
  display: flex;
  align-items: center;
  border: 1px solid var(--gray-300);
  border-radius: 0.5rem;
  overflow: hidden;
}

.qty-btn {
  width: 36px;
  height: 36px;
  background: var(--gray-100);
  border: none;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s;
}

.qty-btn:hover {
  background: var(--gray-200);
}

.qty-value {
  width: 40px;
  text-align: center;
  font-weight: 600;
}

.item-total {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--gray-900);
  min-width: 100px;
  text-align: right;
}

.remove-btn {
  padding: 0.5rem;
  background: none;
  border: none;
  color: var(--gray-400);
  cursor: pointer;
  transition: color 0.2s;
}

.remove-btn:hover {
  color: var(--danger);
}

.remove-btn svg {
  width: 20px;
  height: 20px;
}

.cart-summary {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  position: sticky;
  top: 100px;
}

.cart-summary h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--gray-200);
  color: var(--gray-600);
}

.summary-total {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  font-size: 1.25rem;
  font-weight: 700;
}

.checkout-btn {
  margin-top: 1.5rem;
}

.cart-features {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--gray-200);
}

.feature {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: var(--gray-500);
  margin-bottom: 0.5rem;
}

.feature svg {
  width: 18px;
  height: 18px;
  color: var(--primary);
}

@media (max-width: 1024px) {
  .cart-layout {
    grid-template-columns: 1fr;
  }
  
  .cart-summary {
    position: static;
  }
}

@media (max-width: 768px) {
  .cart-item {
    grid-template-columns: 80px 1fr;
    gap: 1rem;
  }
  
  .item-quantity,
  .item-total,
  .remove-btn {
    grid-column: 2;
  }
  
  .item-total {
    text-align: left;
  }
}
</style>
