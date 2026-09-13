<template>
  <div class="page-wrapper" style="padding: 2rem; text-align: center;">
    <div class="spinner" style="margin: 0 auto 1rem;" />
    <p style="color: #6b7280;">নিয়োগ আবেদনে নিয়ে যাওয়া হচ্ছে...</p>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
const route = useRoute()
navigateTo(`/hr/recruitments/${route.params.id}/applications`)
</script>
