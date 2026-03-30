<script setup>
import { ref, onMounted } from 'vue'
import { useProductStore } from '../stores/products'
import ProductCard from '../components/product/ProductCard.vue'

const productStore = useProductStore()
const email = ref('')

onMounted(async () => {
  await productStore.fetchFeaturedProducts()
  await productStore.fetchCategories()
  await productStore.fetchNewArrivals()
})

const subscribeNewsletter = () => {
  alert('Thank you for subscribing! Email: ' + email.value)
  email.value = ''
}
</script>

<template>
  <div class="home">
    <!-- Hero Section -->
    <section class="hero">
      <div class="container">
        <div class="hero-content">
          <h1 class="hero-title">Step Into <span>Style</span></h1>
          <p class="hero-subtitle">
            Discover the perfect blend of comfort and fashion with our premium shoe collection.
            From running to casual, we've got your feet covered.
          </p>
          <div class="hero-actions">
            <router-link to="/products" class="btn btn-primary btn-lg">Shop Now</router-link>
            <router-link to="/products" class="btn btn-outline btn-lg">View Collection</router-link>
          </div>
          <div class="hero-stats">
            <div class="stat">
              <span class="stat-number">500+</span>
              <span class="stat-label">Products</span>
            </div>
            <div class="stat">
              <span class="stat-number">50+</span>
              <span class="stat-label">Brands</span>
            </div>
            <div class="stat">
              <span class="stat-number">10K+</span>
              <span class="stat-label">Customers</span>
            </div>
          </div>
        </div>
        <div class="hero-image">
          <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=400&fit=crop" alt="Featured Shoe" />
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
      <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <p class="section-subtitle">Find the perfect shoes for every occasion</p>
        <div class="categories-grid">
          <router-link 
            v-for="category in productStore.categories" 
            :key="category.id"
            :to="`/category/${category.slug}`"
            class="category-card"
          >
            <div class="category-image">
              <img :src="category.image || 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=300&h=300&fit=crop'" :alt="category.name" />
            </div>
            <div class="category-info">
              <h3>{{ category.name }}</h3>
              <span class="category-count">{{ category.products_count }} Products</span>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured">
      <div class="container">
        <div class="section-header">
          <div>
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Our most popular picks for you</p>
          </div>
          <router-link to="/products" class="btn btn-outline">View All</router-link>
        </div>
        <div class="products-grid">
          <ProductCard 
            v-for="product in productStore.featuredProducts" 
            :key="product.id" 
            :product="product"
          />
        </div>
      </div>
    </section>

    <!-- Promo Banner -->
    <section class="promo-banner">
      <div class="container">
        <div class="promo-content">
          <h2>Get 20% Off Your First Order</h2>
          <p>Sign up today and receive exclusive discounts on your favorite shoes.</p>
          <router-link to="/register" class="btn btn-primary btn-lg">Sign Up Now</router-link>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="container">
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
              </svg>
            </div>
            <h3>Free Shipping</h3>
            <p>On orders over $100</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
              </svg>
            </div>
            <h3>24/7 Support</h3>
            <p>Round-the-clock assistance</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 4 23 10 17 10"></polyline>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
              </svg>
            </div>
            <h3>Easy Returns</h3>
            <p>30-day return policy</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </div>
            <h3>Secure Payment</h3>
            <p>100% secure checkout</p>
          </div>
        </div>
      </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="new-arrivals">
      <div class="container">
        <div class="section-header">
          <div>
            <h2 class="section-title">New Arrivals</h2>
            <p class="section-subtitle">Check out our latest additions</p>
          </div>
          <router-link to="/products?sort=newest" class="btn btn-outline">View All</router-link>
        </div>
        <div class="products-grid">
          <ProductCard 
            v-for="product in productStore.newArrivals" 
            :key="product.id" 
            :product="product"
          />
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
      <div class="container">
        <h2 class="section-title text-center">What Our Customers Say</h2>
        <p class="section-subtitle text-center">Don't just take our word for it</p>
        <div class="testimonials-grid">
          <div class="testimonial-card">
            <div class="testimonial-rating">
              <span v-for="n in 5" :key="n">&#9733;</span>
            </div>
            <p class="testimonial-text">"Amazing quality shoes! The Nike Air Max I bought is so comfortable and looks exactly like the pictures. Fast shipping too!"</p>
            <div class="testimonial-author">
              <div class="author-avatar">JD</div>
              <div class="author-info">
                <strong>John Doe</strong>
                <span>Verified Buyer</span>
              </div>
            </div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-rating">
              <span v-for="n in 5" :key="n">&#9733;</span>
            </div>
            <p class="testimonial-text">"Best shoe store online! Great prices, excellent customer service, and my order arrived faster than expected. Will definitely shop here again."</p>
            <div class="testimonial-author">
              <div class="author-avatar">SM</div>
              <div class="author-info">
                <strong>Sarah Miller</strong>
                <span>Verified Buyer</span>
              </div>
            </div>
          </div>
          <div class="testimonial-card">
            <div class="testimonial-rating">
              <span v-for="n in 5" :key="n">&#9733;</span>
            </div>
            <p class="testimonial-text">"The variety of brands is incredible. Found my favorite Adidas shoes that were sold out everywhere else. Highly recommend!"</p>
            <div class="testimonial-author">
              <div class="author-avatar">MJ</div>
              <div class="author-info">
                <strong>Mike Johnson</strong>
                <span>Verified Buyer</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Brands Section -->
    <section class="brands">
      <div class="container">
        <h2 class="section-title text-center">Shop by Brand</h2>
        <p class="section-subtitle text-center">We carry all the top brands</p>
        <div class="brands-grid">
          <div class="brand-card" v-for="brand in ['Nike', 'Adidas', 'New Balance', 'Puma', 'Vans', 'Converse', 'Asics', 'Jordan']" :key="brand">
            <router-link :to="`/products?brand=${brand}`" class="brand-link">
              <span class="brand-name">{{ brand }}</span>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
      <div class="container">
        <div class="newsletter-content">
          <div class="newsletter-text">
            <h2>Join Our Newsletter</h2>
            <p>Subscribe to get special offers, free giveaways, and exclusive deals.</p>
          </div>
          <form class="newsletter-form" @submit.prevent="subscribeNewsletter">
            <input type="email" v-model="email" placeholder="Enter your email" required />
            <button type="submit" class="btn btn-primary">Subscribe</button>
          </form>
        </div>
      </div>
    </section>

    <!-- Instagram Feed Section -->
    <section class="instagram">
      <div class="container">
        <h2 class="section-title text-center">Follow Us on Instagram</h2>
        <p class="section-subtitle text-center">@shoestore for daily inspiration</p>
        <div class="instagram-grid">
          <div class="instagram-item" v-for="n in 6" :key="n">
            <img :src="`https://images.unsplash.com/photo-${1542291026789 + n * 10000}?w=300&h=300&fit=crop`" alt="Instagram post" />
            <div class="instagram-overlay">
              <svg viewBox="0 0 24 24" fill="white" width="24" height="24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
      <div class="container">
        <h2 class="section-title text-center">Why Choose ShoeStore?</h2>
        <p class="section-subtitle text-center">We're committed to providing the best shopping experience</p>
        <div class="why-grid">
          <div class="why-card">
            <div class="why-number">01</div>
            <h3>Authentic Products</h3>
            <p>100% genuine products sourced directly from authorized distributors. No fakes, no imitations.</p>
          </div>
          <div class="why-card">
            <div class="why-number">02</div>
            <h3>Best Prices</h3>
            <p>We offer competitive prices and regular sales. Price match guarantee on all products.</p>
          </div>
          <div class="why-card">
            <div class="why-number">03</div>
            <h3>Expert Advice</h3>
            <p>Our team of sneaker enthusiasts is always ready to help you find the perfect pair.</p>
          </div>
          <div class="why-card">
            <div class="why-number">04</div>
            <h3>Fast Delivery</h3>
            <p>Same-day processing and express shipping options available. Track your order in real-time.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq">
      <div class="container">
        <h2 class="section-title text-center">Frequently Asked Questions</h2>
        <p class="section-subtitle text-center">Got questions? We've got answers</p>
        <div class="faq-grid">
          <div class="faq-item">
            <h4>How long does shipping take?</h4>
            <p>Standard shipping takes 3-5 business days. Express shipping is available for 1-2 day delivery.</p>
          </div>
          <div class="faq-item">
            <h4>What is your return policy?</h4>
            <p>We offer a 30-day return policy for unworn items in original packaging. Free returns on all orders.</p>
          </div>
          <div class="faq-item">
            <h4>Are the shoes authentic?</h4>
            <p>Yes! All our products are 100% authentic and sourced from authorized retailers.</p>
          </div>
          <div class="faq-item">
            <h4>How do I track my order?</h4>
            <p>Once shipped, you'll receive an email with tracking information. You can also track in your account.</p>
          </div>
        </div>
      </div>
    </section>

      </div>
</template>

<style scoped>
.hero {
  background: linear-gradient(135deg, var(--gray-100) 0%, white 100%);
  padding: 4rem 0;
}

.hero .container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: center;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  line-height: 1.1;
  color: var(--gray-900);
  margin-bottom: 1.5rem;
}

.hero-title span {
  color: var(--primary);
}

.hero-subtitle {
  font-size: 1.25rem;
  color: var(--gray-600);
  margin-bottom: 2rem;
  max-width: 500px;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 3rem;
}

.hero-stats {
  display: flex;
  gap: 3rem;
}

.stat {
  display: flex;
  flex-direction: column;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
}

.stat-label {
  color: var(--gray-500);
  font-size: 0.875rem;
}

.hero-image {
  position: relative;
}

.hero-image img {
  border-radius: 2rem;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  transform: rotate(-5deg);
  transition: transform 0.3s;
}

.hero-image:hover img {
  transform: rotate(0deg);
}

.categories,
.featured,
.features {
  padding: 5rem 0;
}

.section-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 0.5rem;
}

.section-subtitle {
  color: var(--gray-500);
  margin-bottom: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 2rem;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.category-card {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.category-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.category-image {
  height: 200px;
  overflow: hidden;
}

.category-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.category-card:hover .category-image img {
  transform: scale(1.1);
}

.category-info {
  padding: 1.5rem;
  text-align: center;
}

.category-info h3 {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.category-count {
  color: var(--gray-500);
  font-size: 0.875rem;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.promo-banner {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  padding: 4rem 0;
  margin: 4rem 0;
}

.promo-content {
  text-align: center;
  color: white;
}

.promo-content h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.promo-content p {
  font-size: 1.125rem;
  opacity: 0.9;
  margin-bottom: 2rem;
}

.promo-content .btn {
  background: white;
  color: var(--primary);
}

.promo-content .btn:hover {
  background: var(--gray-100);
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}

.feature-card {
  text-align: center;
  padding: 2rem;
}

.feature-icon {
  width: 60px;
  height: 60px;
  margin: 0 auto 1rem;
  background: rgba(37, 99, 235, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.feature-icon svg {
  width: 28px;
  height: 28px;
  color: var(--primary);
}

.feature-card h3 {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.feature-card p {
  color: var(--gray-500);
  font-size: 0.875rem;
}

@media (max-width: 1024px) {
  .hero .container {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .hero-subtitle {
    margin: 0 auto 2rem;
  }
  
  .hero-actions {
    justify-content: center;
  }
  
  .hero-stats {
    justify-content: center;
  }
  
  .hero-image {
    display: none;
  }
  
  .categories-grid,
  .products-grid,
  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-actions {
    flex-direction: column;
  }
  
  .categories-grid,
  .products-grid,
  .features-grid {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}

/* New Arrivals Section */
.new-arrivals {
  padding: 5rem 0;
  background: var(--gray-50);
}

/* Testimonials Section */
.testimonials {
  padding: 5rem 0;
  background: white;
}

.text-center {
  text-align: center;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  margin-top: 3rem;
}

.testimonial-card {
  background: var(--gray-50);
  padding: 2rem;
  border-radius: 1rem;
}

.testimonial-rating {
  color: #fbbf24;
  font-size: 1.25rem;
  margin-bottom: 1rem;
}

.testimonial-text {
  color: var(--gray-700);
  font-size: 1rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
  font-style: italic;
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.author-avatar {
  width: 50px;
  height: 50px;
  background: var(--primary);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.author-info {
  display: flex;
  flex-direction: column;
}

.author-info strong {
  color: var(--gray-900);
}

.author-info span {
  color: var(--gray-500);
  font-size: 0.875rem;
}

/* Brands Section */
.brands {
  padding: 5rem 0;
  background: var(--gray-50);
}

.brands-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-top: 3rem;
}

.brand-card {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.brand-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.brand-link {
  text-decoration: none;
  color: inherit;
}

.brand-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--gray-800);
}

/* Newsletter Section */
.newsletter {
  padding: 5rem 0;
  background: linear-gradient(135deg, var(--gray-900) 0%, var(--gray-800) 100%);
}

.newsletter-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 3rem;
}

.newsletter-text h2 {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  margin-bottom: 0.5rem;
}

.newsletter-text p {
  color: var(--gray-400);
  font-size: 1.125rem;
}

.newsletter-form {
  display: flex;
  gap: 1rem;
  flex: 1;
  max-width: 500px;
}

.newsletter-form input {
  flex: 1;
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  border: none;
  font-size: 1rem;
}

.newsletter-form .btn {
  padding: 1rem 2rem;
  white-space: nowrap;
}

@media (max-width: 768px) {
  .testimonials-grid,
  .brands-grid,
  .why-grid,
  .faq-grid {
    grid-template-columns: 1fr;
  }
  
  .newsletter-content {
    flex-direction: column;
    text-align: center;
  }
  
  .newsletter-form {
    flex-direction: column;
    width: 100%;
  }
  
  .instagram-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Instagram Section */
.instagram {
  padding: 5rem 0;
  background: white;
}

.instagram-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 1rem;
  margin-top: 3rem;
}

.instagram-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 0.5rem;
  overflow: hidden;
}

.instagram-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.instagram-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.instagram-item:hover img {
  transform: scale(1.1);
}

.instagram-item:hover .instagram-overlay {
  opacity: 1;
}

/* Why Choose Us Section */
.why-choose-us {
  padding: 5rem 0;
  background: var(--gray-50);
}

.why-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  margin-top: 3rem;
}

.why-card {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.why-number {
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--primary);
  opacity: 0.3;
  margin-bottom: 1rem;
}

.why-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-900);
  margin-bottom: 0.75rem;
}

.why-card p {
  color: var(--gray-600);
  font-size: 0.95rem;
  line-height: 1.6;
}

/* FAQ Section */
.faq {
  padding: 5rem 0;
  background: white;
}

.faq-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
  margin-top: 3rem;
}

.faq-item {
  background: var(--gray-50);
  padding: 2rem;
  border-radius: 1rem;
}

.faq-item h4 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-900);
  margin-bottom: 0.75rem;
}

.faq-item p {
  color: var(--gray-600);
  line-height: 1.6;
}


</style>
