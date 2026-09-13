<script setup>
definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
await navigateTo({ path: '/results', query: route.query }, { replace: true })
</script>

<template>
  <div class="p-8 text-center text-muted">
    <div class="inline-block animate-spin w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full mb-2"></div>
    <p>ফলাফল পাতায় রিডাইরেক্ট হচ্ছে...</p>
  </div>
</template>
