const { defineNuxtConfig } = require('nuxt/config')

module.exports = defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  future: {
    compatibilityVersion: 4,
  },
  devtools: {
    enabled: true,
  },
  css: ['~/assets/css/main.css'],
  app: {
    head: {
      htmlAttrs: { lang: 'bn' },
      bodyAttrs: { class: 'font-bn' },
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap' },
      ],
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'রিহাল — মাদ্রাসা ব্যবস্থাপনা প্ল্যাটফর্ম' },
        { name: 'theme-color', content: '#145032' },
        { name: 'apple-mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-status-bar-style', content: 'black-translucent' },
      ],
      title: 'রিহাল — মাদ্রাসা ব্যবস্থাপনা',
    },
  },
  // PWA manifest
  manifest: {
    name: 'রিহাল — মাদ্রাসা ব্যবস্থাপনা',
    short_name: 'রিহাল',
    description: 'মাদ্রাসা ব্যবস্থাপনার জন্য সম্পূর্ণ ডিজিটাল প্ল্যাটফর্ম',
    theme_color: '#145032',
    background_color: '#145032',
    display: 'standalone',
    orientation: 'portrait-primary',
    start_url: '/',
    icons: [
      {
        src: 'https://ui-avatars.com/api/?name=Rihal&background=145032&color=ffffff&size=192',
        sizes: '192x192',
        type: 'image/png',
      },
      {
        src: 'https://ui-avatars.com/api/?name=Rihal&background=145032&color=ffffff&size=512',
        sizes: '512x512',
        type: 'image/png',
      },
    ],
  },
  ssr: true,
  modules: ['@pinia/nuxt', '@vueuse/nuxt'],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1',
      appName: 'Rihal',
    },
  },
})
