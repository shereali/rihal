<template>
  <div class="page-wrapper">
    <div class="breadcrumb">
      <NuxtLink :to="`/properties/${propertyId}`" class="breadcrumb-current">সম্পত্তির বিবরণী</NuxtLink>
      <span class="sep">/</span>
      <span class="breadcrumb-current">রক্ষণাবেক্ষণ</span>
    </div>
    <div class="page-header">
      <h1>রক্ষণাবেক্ষণের ইতিহাস</h1>
      <div class="breadcrumb-back">
        <NuxtLink :to="`/properties/${propertyId}`">
          <icon name="arrow-left" /> সম্পত্তির বিবরণীতে ফিরে যান
        </NuxtLink>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner" />
      <p>রক্ষণাবেক্ষণের তথ্য লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!maintenance.length" class="empty-state">
      <div class="empty-icon"><icon name="wrench" /></div>
      <h3>কোনো রক্ষণাবেক্ষণের রেকর্ড নেই</h3>
      <p>এখনো কোনো রক্ষণাবেক্ষণের কাজ করা হয়নি</p>
    </div>

    <div v-else class="maintenance-list">
      <article v-for="item in maintenance" :key="item.id" class="maintenance-card card">
        <div class="maintenance-header">
          <div class="maintenance-type-icon" :class="statusClass(item.status)">
            <icon :name="item.status === 'completed' ? 'check-circle' : 'clock'"/>
          </div>
          <div class="maintenance-info">
            <h3 class="maintenance-title">{{ item.title_bn || item.title_en }}</h3>
            <span class="maintenance-type-badge">{{ item.type || 'সাধারণ নবকরা' }}</span>
            <p v-if="item.description_bn" class="maintenance-description">{{ item.description_bn }}</p>
          </div>
          <div class="maintenance-meta">
            <span class="status-badge" :class="statusClass(item.status)">
              {{ statusLabel(item.status) }}
            </span>
          </div>
        </div>
        <div class="maintenance-details">
          <div class="detail-item">
            <icon name="calendar" />
            <span>শুরু: {{ formatDate(item.start_date) }}</span>
          </div>
          <div class="detail-item" v-if="item.end_date">
            <icon name="calendar-check" />
            <span>সমাপ্তি: {{ formatDate(item.end_date) }}</span>
          </div>
          <div class="detail-item" v-if="item.cost">
            <icon name="money" />
            <span>খরচ: {{ formatCurrency(item.cost) }}</span>
          </div>
          <div class="detail-item" v-if="item.technician">
            <icon name="user" />
            <span>কর্মকর্তা: {{ item.technician.name_bn || item.technician.name_en }}</span>
          </div>
          <div class="detail-item" v-if="item.notes_bn">
            <icon name="file-text" />
            <span>{{ item.notes_bn }}</span>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const propertyId = computed(() => route.params.id as string)
const maintenance = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const r = await api.get(`/properties/${propertyId.value}/maintenance`)
    maintenance.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error('Failed to load maintenance:', e)
  } finally {
    loading.value = false
  }
})

function statusClass(status) {
  const map = {
    pending: 'pending',
    in_progress: 'in-progress',
    completed: 'completed',
  }
  return map[status] || 'pending'
}

function statusLabel(status) {
  const map = {
    pending: 'মুলতুবি',
    in_progress: 'চলছে',
    completed: 'সম্পন্ন',
  }
  return map[status] || status || '-'
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('bn-BD', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

function formatCurrency(amount) {
  if (!amount) return '-'
  return 'টাকা ' + amount.toLocaleString('bn-BD', { minimumFractionDigits: 0 })
}
</script>

<style scoped>
.page-wrapper {
  max-width: 960px;
  margin: 0 auto;
  padding: 1.5rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 1rem;
  font-size: 0.82rem;
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.breadcrumb a {
  color: var(--color-primary);
  text-decoration: none;
}

.breadcrumb .sep {
  color: var(--color-text-muted);
}

.breadcrumb-back {
  margin-left: auto;
}

.breadcrumb-back a {
  color: var(--color-text-muted);
  font-size: 0.78rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.breadcrumb-back a:hover {
  color: var(--color-primary);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.2rem;
}

.page-header h1 {
  font-size: 1.4rem;
  color: var(--color-primary);
  font-family: var(--font-bn);
  margin: 0;
}

.loading-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 4rem 0;
}

.empty-state {
  text-align: center;
  padding: 3rem 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.7rem;
}

.empty-icon {
  width: 48px;
  height: 48px;
  color: var(--color-text-muted);
  margin-bottom: 0.5rem;
}

.empty-state h3 {
  font-family: var(--font-bn);
  font-size: 1.05rem;
  color: var(--color-text);
  margin: 0;
}

.empty-state p {
  color: var(--color-text-muted);
  font-family: var(--font-bn);
  margin: 0;
  font-size: 0.82rem;
}

.maintenance-list {
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
}

.maintenance-card {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.maintenance-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.maintenance-header {
  display: flex;
  align-items: flex-start;
  gap: 0.7rem;
  padding: 0.8rem 1rem;
  border-bottom: 1px solid var(--color-border-light);
}

.maintenance-type-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.maintenance-type-icon.pending {
  background: #fff0e4;
  color: #a05c35;
}

.maintenance-type-icon.in-progress {
  background: #e3f2fa;
  color: #1a5276;
}

.maintenance-type-icon.completed {
  background: #e6f4ec;
  color: #19724a;
}

.maintenance-type-icon icon {
  width: 18px;
  height: 18px;
}

.maintenance-info {
  flex: 1;
  min-width: 0;
}

.maintenance-title {
  font-family: var(--font-bn);
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--color-text);
  margin: 0 0 0.15rem;
}

.maintenance-type-badge {
  font-size: 0.65rem;
  color: var(--color-text-muted);
  background: var(--color-bg-muted);
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  font-family: var(--font-bn);
  display: inline-block;
  margin-bottom: 0.2rem;
}

.maintenance-description {
  font-family: var(--font-bn);
  font-size: 0.78rem;
  color: var(--color-text-light);
  margin: 0;
  line-height: 1.4;
}

.maintenance-meta {
  flex-shrink: 0;
}

.status-badge {
  padding: 0.2rem 0.5rem;
  border-radius: 99px;
  font-size: 0.65rem;
  font-weight: 600;
  white-space: nowrap;
}

.status-badge.pending {
  background: #fff0e4;
  color: #a05c35;
}

.status-badge.in-progress {
  background: #e3f2fa;
  color: #1a5276;
}

.status-badge.completed {
  background: #e6f4ec;
  color: #19724a;
}

.maintenance-details {
  padding: 0.6rem 1rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 1rem;
  font-family: var(--font-bn);
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.detail-item {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.detail-item icon {
  width: 12px;
  height: 12px;
}
</style>