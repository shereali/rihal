<template>
  <div class="page-wrapper" style="padding: 2rem; text-align: center;">
    <div class="spinner" style="margin: 0 auto 1rem;" />
    <p style="color: #6b7280;">ডিভাইসের তথ্যে নিয়ে যাওয়া হচ্ছে...</p>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
const route = useRoute()
navigateTo(`/digital-attendance/devices/${route.params.id}/edit`)
</script>
