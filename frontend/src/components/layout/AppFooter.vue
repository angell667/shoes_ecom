<script setup>
</script>

<template>
  <footer class="bg-black text-white pt-24 pb-12 overflow-hidden relative">
    <!-- Abstract Shape -->
    <div class="absolute top-0 right-0 w-1/3 h-full bg-blue-600/10 blur-[120px] rounded-full translate-x-1/2"></div>
    
    <div class="container relative z-10">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16 mb-20">
        <div class="space-y-8">
          <router-link to="/" class="flex items-center gap-2 group">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-black">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
              </svg>
            </div>
            <span class="text-3xl font-black tracking-tighter uppercase italic">KICKS.</span>
          </router-link>
          <p class="text-gray-400 text-lg leading-relaxed max-w-xs">
            Pushing the boundaries of footwear design. Elevating your stride since 2026.
          </p>
          <div class="flex gap-4">
            <a v-for="i in 4" :key="i" href="#" class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center hover:bg-white hover:text-black transition-all duration-500">
              <span class="text-xs font-black uppercase tracking-widest">S{{i}}</span>
            </a>
          </div>
        </div>

        <div>
          <h4 class="text-xs font-black uppercase tracking-[0.3em] text-gray-500 mb-10">Navigation</h4>
          <ul class="space-y-6">
            <li v-for="link in ['Home', 'Shop All', 'Collections', 'About Us']" :key="link">
              <a href="#" class="text-xl font-bold hover:text-blue-500 transition-colors">{{ link }}</a>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="text-xs font-black uppercase tracking-[0.3em] text-gray-500 mb-10">Support</h4>
          <ul class="space-y-6">
            <li v-for="link in ['Shipping', 'Returns', 'Privacy', 'Contact']" :key="link">
              <a href="#" class="text-xl font-bold hover:text-blue-500 transition-colors">{{ link }}</a>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="text-xs font-black uppercase tracking-[0.3em] text-gray-500 mb-10">Office</h4>
          <p class="text-xl font-bold leading-relaxed">
            123 Sneaker Plaza,<br />
            Design District,<br />
            New York, NY 10001
          </p>
          <p class="mt-8 text-blue-500 font-bold text-xl underline underline-offset-8">hello@kicks.io</p>
        </div>
      </div>

      <div class="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-8">
        <p class="text-gray-500 font-medium tracking-tight">
          &copy; 2026 KICKS GLOBAL. ALL RIGHTS RESERVED.
        </p>
        <div class="flex gap-12 text-xs font-black uppercase tracking-widest text-gray-500">
          <a href="#" class="hover:text-white transition-colors">Terms</a>
          <a href="#" class="hover:text-white transition-colors">Privacy</a>
          <a href="#" class="hover:text-white transition-colors">Cookies</a>
        </div>
      </div>
    </div>
  </footer>
</template>
