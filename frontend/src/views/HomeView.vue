<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useProductStore } from '../stores/products'
import ProductCard from '../components/product/ProductCard.vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import Lenis from 'lenis'

gsap.registerPlugin(ScrollTrigger)

const productStore = useProductStore()
const email = ref('')
let lenis = null

onMounted(async () => {
  try {
    await Promise.all([
      productStore.fetchFeaturedProducts(),
      productStore.fetchCategories(),
      productStore.fetchNewArrivals()
    ])
  } catch (error) {
    console.error('Initial fetch error:', error)
  }

  // Initialize Lenis
  lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smoothWheel: true
  })

  lenis.on('scroll', ScrollTrigger.update)

  gsap.ticker.add((time) => {
    lenis.raf(time * 1000)
  })

  gsap.ticker.lagSmoothing(0)

  await nextTick()

  // Hero Animations
  const tl = gsap.timeline()
  tl.from('.hero-title span', {
    y: 100,
    opacity: 0,
    duration: 1,
    stagger: 0.2,
    ease: 'power4.out'
  })
  .from('.hero-subtitle', {
    y: 30,
    opacity: 0,
    duration: 0.8,
    ease: 'power3.out'
  }, '-=0.5')
  .from('.hero-actions', {
    y: 30,
    opacity: 0,
    duration: 0.8,
    ease: 'power3.out'
  }, '-=0.6')
  .from('.hero-image-wrapper', {
    scale: 0.8,
    opacity: 0,
    duration: 1.2,
    ease: 'power4.out'
  }, '-=1')

  // Reveal Animations
  const reveals = gsap.utils.toArray('.reveal')
  reveals.forEach((el) => {
    gsap.fromTo(el, 
      { opacity: 0, y: 50 },
      {
        opacity: 1,
        y: 0,
        duration: 1.2,
        ease: 'power3.out',
        scrollTrigger: {
          trigger: el,
          start: 'top 90%',
          toggleActions: 'play none none none'
        }
      }
    )
  })

  // Parallax Images
  gsap.utils.toArray('.parallax-img').forEach((img) => {
    gsap.to(img, {
      yPercent: 20,
      ease: 'none',
      scrollTrigger: {
        trigger: img,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true
      }
    })
  })

  // Horizontal Marquee Animation
  gsap.to('.marquee-content', {
    xPercent: -50,
    ease: 'none',
    duration: 20,
    repeat: -1
  })

  // Flagship Hover Animations
  const cities = gsap.utils.toArray('.city-item')
  cities.forEach(city => {
    const img = city.querySelector('.city-img-popup')
    city.addEventListener('mouseenter', () => {
      gsap.to(img, { opacity: 1, scale: 1, y: -20, duration: 0.4, ease: 'back.out(1.7)' })
    })
    city.addEventListener('mouseleave', () => {
      gsap.to(img, { opacity: 0, scale: 0.8, y: 0, duration: 0.3, ease: 'power2.in' })
    })
  })

  ScrollTrigger.refresh()
})

onUnmounted(() => {
  if (lenis) lenis.destroy()
  ScrollTrigger.getAll().forEach(t => t.kill())
  gsap.ticker.remove()
})

const subscribeNewsletter = () => {
  alert('Thank you for subscribing! Email: ' + email.value)
  email.value = ''
}
</script>

<template>
  <div class="home-modern bg-white">
    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center pt-20 overflow-hidden">
      <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] select-none pointer-events-none">
        <h2 class="text-[30vw] font-black uppercase tracking-tighter italic">KICKS</h2>
      </div>

      <div class="container relative z-10 grid lg:grid-cols-2 gap-20 items-center">
        <div class="hero-text">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-blue-600 text-xs font-black uppercase tracking-widest mb-8">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
            </span>
            New S/S 2026 Collection
          </div>
          <h1 class="hero-title text-8xl md:text-9xl font-black leading-[0.85] tracking-tighter text-black uppercase">
            <span class="block overflow-hidden"><span class="block">Fast</span></span>
            <span class="block overflow-hidden"><span class="block text-gray-200">Beyond</span></span>
            <span class="block overflow-hidden"><span class="block">Limits</span></span>
          </h1>
          <p class="hero-subtitle mt-10 text-xl text-gray-500 leading-relaxed max-w-lg font-medium">
            Discover the fusion of radical design and elite performance. Engineered for those who never stand still.
          </p>
          <div class="hero-actions mt-12 flex flex-wrap gap-6">
            <router-link to="/products" class="group flex items-center gap-4 bg-black text-white px-10 py-5 rounded-full font-black uppercase tracking-widest hover:bg-blue-600 transition-all duration-500">
              Shop Now
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </router-link>
            <router-link to="/products" class="px-10 py-5 border-2 border-black text-black rounded-full font-black uppercase tracking-widest hover:bg-black hover:text-white transition-all duration-500">
              Lookbook
            </router-link>
          </div>
        </div>
        
        <div class="hero-image-wrapper relative">
          <div class="absolute -top-20 -right-20 w-80 h-80 bg-blue-100 rounded-full blur-[100px] opacity-50"></div>
          <div class="relative z-10 aspect-[4/5] overflow-hidden rounded-[3rem] shadow-2xl">
            <img 
              src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1200&q=90" 
              alt="Elite Shoe" 
              class="w-full h-full object-cover"
            />
          </div>
          <div class="absolute -bottom-10 -left-10 glass p-8 rounded-[2rem] shadow-2xl border border-white/50 hidden md:block">
            <div class="flex gap-8">
              <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Weight</p>
                <p class="text-2xl font-black">180g</p>
              </div>
              <div class="w-px h-10 bg-gray-200"></div>
              <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Support</p>
                <p class="text-2xl font-black">Max</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 1. Cinematic Vision Section (New) -->
    <section class="relative h-[80vh] flex items-center justify-center overflow-hidden bg-black mt-20">
      <img src="https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=1600&q=90" class="absolute inset-0 w-full h-full object-cover opacity-60 parallax-img" alt="Cinematic" />
      <div class="absolute inset-0 bg-gradient-to-b from-transparent via-black/20 to-black"></div>
      <div class="container relative z-10 text-center">
        <h2 class="reveal text-[12vw] font-black text-white leading-none uppercase tracking-tighter mix-blend-difference">A New<br/>Reality.</h2>
        <button class="reveal mt-10 w-24 h-24 rounded-full border-2 border-white/30 flex items-center justify-center group hover:bg-white transition-all duration-500">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white group-hover:text-black" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        </button>
      </div>
    </section>

    <!-- Marquee Section -->
    <section class="py-12 bg-black overflow-hidden select-none">
      <div class="marquee flex whitespace-nowrap">
        <div class="marquee-content flex gap-20 py-4">
          <span v-for="i in 10" :key="i" class="text-6xl font-black text-white/20 uppercase italic tracking-tighter">
            * Performance Tech * Elite Style * Radical Design *
          </span>
        </div>
      </div>
    </section>

    <!-- Categories Modern -->
    <section class="py-32 bg-gray-50">
      <div class="container">
        <div class="reveal flex flex-col items-center text-center mb-20">
          <h2 class="text-6xl md:text-7xl font-black uppercase tracking-tighter">Collections</h2>
          <div class="w-20 h-2 bg-black mt-6"></div>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
          <router-link 
            v-for="(category, index) in productStore.categories.slice(0, 3)" 
            :key="category.id"
            :to="`/category/${category.slug}`"
            class="reveal group relative aspect-[3/4] rounded-[2.5rem] overflow-hidden shadow-xl bg-white"
          >
            <img :src="category.image || 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=800&q=80'" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
            <div class="absolute inset-0 p-10 flex flex-col justify-end">
              <span class="text-xs font-black text-blue-500 uppercase tracking-[0.3em] mb-4">Explore</span>
              <h3 class="text-4xl font-black text-white uppercase tracking-tighter mb-2">{{ category.name }}</h3>
              <p class="text-white/60 font-medium tracking-tight">Browse {{ category.products_count }} Products</p>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- 2. KICKS Studio / Customization (New) -->
    <section class="py-32 bg-white relative overflow-hidden">
      <div class="container">
        <div class="reveal grid lg:grid-cols-2 gap-20 items-center">
          <div class="order-2 lg:order-1 relative h-[600px] bg-gray-100 rounded-[4rem] p-12 flex items-center justify-center">
            <!-- Floating Shoe Parts (Visual Metaphor) -->
            <div class="absolute top-20 left-20 w-32 h-32 bg-white rounded-2xl shadow-xl rotate-12 flex items-center justify-center p-4">
              <span class="text-[10px] font-black uppercase text-gray-400">Sole Tech</span>
            </div>
            <div class="absolute bottom-20 right-20 w-32 h-32 bg-white rounded-2xl shadow-xl -rotate-12 flex items-center justify-center p-4">
              <span class="text-[10px] font-black uppercase text-gray-400">Fabric Lab</span>
            </div>
            <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?w=800&q=90" class="relative z-10 w-full rounded-2xl rotate-[-15deg] drop-shadow-2xl" alt="Custom Shoe" />
          </div>
          <div class="order-1 lg:order-2 reveal">
            <span class="text-xs font-black text-blue-600 uppercase tracking-widest mb-4 block">KICKS Studio</span>
            <h2 class="text-7xl font-black uppercase tracking-tighter mb-8 leading-none">Built by<br/><span class="text-gray-300">You.</span></h2>
            <p class="text-xl text-gray-500 leading-relaxed mb-12 max-w-md">
              The power of design is now in your hands. Choose your materials, select your palette, and engrave your legacy.
            </p>
            <button class="px-12 py-5 bg-black text-white rounded-full font-black uppercase tracking-widest hover:bg-blue-600 transition-all duration-500">
              Start Designing
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- The Archive Section -->
    <section class="py-32 bg-gray-50 overflow-hidden">
      <div class="container">
        <div class="grid lg:grid-cols-12 gap-10 items-center">
          <div class="lg:col-span-5 reveal">
            <span class="text-xs font-black text-gray-400 uppercase tracking-[0.4em] mb-4 block">Legacy</span>
            <h2 class="text-6xl font-black uppercase tracking-tighter leading-none mb-8">The<br/><span class="text-gray-300 italic">Archive</span></h2>
            <p class="text-lg text-gray-500 font-medium leading-relaxed mb-10">A curated journey through the silhouettes that defined generations.</p>
            <router-link to="/products" class="inline-flex items-center gap-4 text-black font-black uppercase tracking-widest group">
              Explore History <div class="w-12 h-px bg-black group-hover:w-20 transition-all duration-500"></div>
            </router-link>
          </div>
          <div class="lg:col-span-7 grid grid-cols-2 gap-6 reveal">
            <div class="aspect-[4/5] rounded-[2rem] overflow-hidden mt-20"><img src="https://images.unsplash.com/photo-1552346154-21d32810aba3?w=800&q=80" class="w-full h-full object-cover parallax-img" /></div>
            <div class="aspect-[4/5] rounded-[2rem] overflow-hidden"><img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=800&q=80" class="w-full h-full object-cover parallax-img" /></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Innovation Section -->
    <section class="py-32 bg-white relative">
      <div class="container grid lg:grid-cols-2 gap-20 items-center">
        <div class="reveal">
          <h2 class="text-5xl md:text-6xl font-black uppercase tracking-tighter mb-10 leading-none">Radical<br/><span class="text-blue-600">Innovation.</span></h2>
          <div class="space-y-12">
            <div v-for="(tech, i) in ['Carbon Fiber Core', 'Reactive Foam', 'Breathable Mesh']" :key="i" class="flex gap-8 group">
              <div class="text-5xl font-black text-gray-200 group-hover:text-blue-200 transition-colors">0{{i+1}}</div>
              <div>
                <h4 class="text-xl font-black uppercase mb-2">{{ tech }}</h4>
                <p class="text-gray-500 leading-relaxed">Providing unparalleled energy return and stabilization for your most intense runs.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="reveal relative"><img src="https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=1000&q=80" class="w-full rounded-[3rem] shadow-2xl" /></div>
      </div>
    </section>

    <!-- 3. Global Flagships (New) -->
    <section class="py-32 bg-black text-white">
      <div class="container">
        <div class="reveal flex flex-col items-center text-center mb-24">
          <span class="text-blue-500 font-black uppercase tracking-widest text-xs mb-4">Global Reach</span>
          <h2 class="text-7xl font-black uppercase tracking-tighter">Flagship Spaces.</h2>
        </div>
        <div class="space-y-4">
          <div v-for="(city, i) in [
            { name: 'Tokyo', area: 'Shibuya', img: 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?w=400' },
            { name: 'Paris', area: 'Le Marais', img: 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=400' },
            { name: 'New York', area: 'SoHo', img: 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=400' },
            { name: 'London', area: 'Shoreditch', img: 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=400' }
          ]" :key="i" class="reveal city-item relative border-b border-white/10 py-10 group cursor-pointer">
            <div class="flex items-end justify-between relative z-10">
              <div class="flex items-center gap-10">
                <span class="text-xl font-black text-white/20">0{{i+1}}</span>
                <h3 class="text-6xl md:text-8xl font-black uppercase tracking-tighter group-hover:text-blue-500 transition-colors duration-500">{{ city.name }}</h3>
              </div>
              <p class="text-xl font-medium text-white/40 group-hover:text-white transition-colors">{{ city.area }}</p>
            </div>
            <!-- Dynamic Image Popup -->
            <div class="city-img-popup absolute left-[40%] top-0 w-64 h-80 pointer-events-none opacity-0 scale-80 overflow-hidden rounded-3xl shadow-2xl z-20">
              <img :src="city.img" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Eco-Impact Section -->
    <section class="py-32 bg-green-50 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-[50%] h-full bg-green-100 -skew-x-12 translate-x-1/4"></div>
      <div class="container relative z-10 grid lg:grid-cols-2 gap-20 items-center">
        <div class="reveal order-2 lg:order-1">
          <div class="grid grid-cols-2 gap-4">
            <div class="p-8 bg-white rounded-3xl shadow-sm"><p class="text-4xl font-black text-green-600 mb-2">98%</p><p class="text-xs font-bold uppercase tracking-widest text-gray-400">Recycled Polyester</p></div>
            <div class="p-8 bg-white rounded-3xl shadow-sm mt-8"><p class="text-4xl font-black text-green-600 mb-2">0</p><p class="text-xs font-bold uppercase tracking-widest text-gray-400">Waste Policy</p></div>
          </div>
        </div>
        <div class="reveal order-1 lg:order-2">
          <span class="text-xs font-black text-green-600 uppercase tracking-widest mb-4 block">Sustainability</span>
          <h2 class="text-6xl font-black uppercase tracking-tighter mb-8">Green by Design.</h2>
          <button class="btn bg-green-600 text-white px-10">Read Our Impact Report</button>
        </div>
      </div>
    </section>

    <!-- Trending Products -->
    <section class="py-32 bg-black text-white overflow-hidden">
      <div class="container">
        <div class="reveal flex flex-col md:flex-row md:items-end justify-between mb-20 gap-8">
          <div><span class="text-blue-500 font-black uppercase tracking-widest text-xs">Trending Now</span><h2 class="text-6xl font-black uppercase tracking-tighter mt-4">The Heat List.</h2></div>
          <router-link to="/products" class="text-white font-black uppercase tracking-widest text-sm underline underline-offset-8 hover:text-blue-500 transition-colors">View All Drops</router-link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
          <div v-for="(product, index) in productStore.featuredProducts" :key="product.id" class="reveal"><ProductCard :product="product" /></div>
        </div>
      </div>
    </section>

    <!-- Street Culture Mosaic -->
    <section class="py-32 bg-white">
      <div class="container">
        <div class="reveal text-center mb-20">
          <h2 class="text-6xl font-black uppercase tracking-tighter">Street Culture.</h2>
          <p class="text-gray-400 font-bold tracking-widest uppercase text-xs mt-4">#KICKSWORLDWIDE ON INSTAGRAM</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 h-[800px]">
          <div class="reveal col-span-2 row-span-2 rounded-[2rem] overflow-hidden"><img src="https://images.unsplash.com/photo-1552346154-21d32810aba3?w=1000&q=80" class="w-full h-full object-cover" /></div>
          <div class="reveal rounded-[2rem] overflow-hidden"><img src="https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=600&q=80" class="w-full h-full object-cover" /></div>
          <div class="reveal rounded-[2rem] overflow-hidden"><img src="https://images.unsplash.com/photo-1584735175315-9d5df23860e6?w=600&q=80" class="w-full h-full object-cover" /></div>
          <div class="reveal col-span-2 rounded-[2rem] overflow-hidden"><img src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=1000&q=80" class="w-full h-full object-cover" /></div>
        </div>
      </div>
    </section>

    <!-- KICKS+ App Section -->
    <section class="py-32 bg-black overflow-hidden relative">
      <div class="absolute -right-20 top-0 w-[60vw] h-full bg-blue-600 rounded-l-[10rem] opacity-20"></div>
      <div class="container relative z-10 grid lg:grid-cols-2 gap-20 items-center">
        <div class="reveal">
          <span class="text-xs font-black text-blue-500 uppercase tracking-widest mb-4 block">Tech Ecosystem</span>
          <h2 class="text-7xl font-black text-white uppercase tracking-tighter mb-8 leading-none">Your Phone,<br/>Our <span class="italic text-gray-500">Soul.</span></h2>
          <div class="flex flex-wrap gap-4">
            <button class="bg-white text-black px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center gap-3">App Store</button>
            <button class="bg-gray-900 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center gap-3 border border-gray-800">Google Play</button>
          </div>
        </div>
        <div class="reveal flex justify-center"><div class="relative w-72 h-[600px] bg-black border-[12px] border-gray-900 rounded-[3.5rem] shadow-2xl overflow-hidden"><img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80" class="w-full h-full object-cover opacity-80" /></div></div>
      </div>
    </section>

    <!-- Membership Section -->
    <section class="py-32 bg-white">
      <div class="container">
        <div class="reveal bg-gray-900 rounded-[4rem] p-12 md:p-24 relative overflow-hidden">
          <div class="absolute -right-20 -top-20 w-80 h-80 bg-blue-600 rounded-full blur-[100px] opacity-20"></div>
          <div class="grid lg:grid-cols-2 gap-20 items-center">
            <div>
              <h2 class="text-6xl font-black text-white uppercase tracking-tighter mb-8">Join the<br/><span class="text-blue-500">Elite.</span></h2>
              <button class="mt-12 px-12 py-5 bg-white text-black rounded-full font-black uppercase tracking-widest hover:bg-blue-600 transition-all">Become a Member</button>
            </div>
            <div class="relative"><div class="glass p-10 rounded-[3rem] border border-white/10 text-white transform rotate-3"><p class="text-3xl font-black tracking-[0.2em] mb-4 text-white/90">0000 0000 0000 0000</p></div></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonial Section -->
    <section class="py-32 bg-gray-50">
      <div class="container">
        <div class="reveal text-center max-w-4xl mx-auto">
          <svg class="w-16 h-16 text-blue-500 mx-auto mb-10" fill="currentColor" viewBox="0 0 32 32"><path d="M10 8v8h6v8h-8v-16h2zM22 8v8h6v8h-8v-16h2z" /></svg>
          <p class="text-3xl md:text-5xl font-black tracking-tighter leading-tight text-black">"These are the most comfortable and stylish shoes I've ever owned."</p>
          <div class="mt-12"><p class="text-lg font-black uppercase tracking-widest">Marcus Thompson</p></div>
        </div>
      </div>
    </section>

    <!-- Newsletter Modern -->
    <section class="py-32 container">
      <div class="reveal relative bg-black rounded-[4rem] p-12 md:p-32 text-center overflow-hidden shadow-2xl border border-white/5">
        <!-- Abstract Shapes for Black Theme -->
        <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-purple-600/10 rounded-full blur-[120px]"></div>

        <div class="relative z-10 max-w-3xl mx-auto">
          <h2 class="text-5xl md:text-7xl font-black text-white tracking-tighter leading-[0.9] uppercase">Don't miss<br/>the next drop.</h2>
          <p class="mt-8 text-xl text-gray-400 font-medium">Join 50k+ enthusiasts and get early access to exclusive releases.</p>
          
          <form @submit.prevent="subscribeNewsletter" class="mt-12 flex flex-col sm:flex-row gap-4 p-2 bg-white/5 border border-white/10 rounded-[2.5rem] backdrop-blur-md">
            <input 
              v-model="email" 
              type="email" 
              placeholder="YOUR@EMAIL.COM" 
              class="flex-1 bg-transparent px-8 py-5 text-white font-black uppercase tracking-widest focus:outline-none placeholder:text-gray-600"
              required
            />
            <button type="submit" class="bg-white text-black hover:bg-blue-600 hover:text-white px-12 py-5 rounded-[2rem] font-black uppercase tracking-widest transition-all duration-500">
              Join Now
            </button>
          </form>
          <p class="mt-8 text-xs text-gray-500 font-black uppercase tracking-widest">Safe & Secure. No Spam Ever.</p>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.hero-title span {
  display: inline-block;
}

.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.marquee-content span {
  -webkit-text-stroke: 1px rgba(255,255,255,0.2);
  color: transparent;
}

.city-img-popup {
  transition: opacity 0.4s ease, transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
</style>
