<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <h1>পরিবহন রুট</h1>
        <p class="page-subtitle">মাদ্রাসার রুট, স্টপেজ, দূরত্ব, ভাড়া ও সময়সীমা</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/transport/routes/create" class="btn btn-primary">
          <icon name="plus" /> নতুন রুট
        </NuxtLink>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner" />
      <p>রুট লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!routes.length" class="empty-state">
      <div class="empty-icon"><icon name="bus" /></div>
      <h3>কোনো রুট নেই</h3>
      <p>নতুন রুট তৈরি করে শুরু করুন</p>
      <NuxtLink to="/transport/routes/create" class="btn btn-primary">প্রথম রুট তৈরি করুন</NuxtLink>
    </div>

    <div v-else class="routes-grid">
      <article v-for="route in routes" :key="route.id" class="route-card">
        <div class="route-header">
          <div class="route-number-badge">
            <span class="route-name-main">{{ route.route_name_bn || route.route_name_en }}</span>
          </div>
          <span class="route-status" :class="route.is_active ? 'active' : 'inactive'">
            {{ route.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>
        <div class="route-points">
          <span class="route-start">{{ route.start_point || '—' }}</span>
          <icon name="arrow-right" class="route-arrow" />
          <span class="route-end">{{ route.end_point || '—' }}</span>
        </div>
        <div class="route-details">
          <div v-if="route.distance_km" class="detail-item">
            <icon name="road" />
            <span>{{ route.distance_km.toFixed(1) }} কি.মি.</span>
          </div>
          <div v-if="route.fare" class="detail-item">
            <icon name="money" />
            <span>{{ route.fare.toLocaleString('bn-BD') }} টাকা</span>
          </div>
          <div v-if="route.start_time" class="detail-item">
            <icon name="schedule" />
            <span>{{ formatDate(route.start_time) }}</span>
          </div>
          <div v-if="route.end_time" class="detail-item">
            <icon name="schedule" />
            <span>{{ formatDate(route.end_time) }}</span>
          </div>
        </div>
        <div class="route-footer">
          <NuxtLink :to="`/transport/routes/${route.id}`" class="view-link">
            বিস্তারিত <icon name="arrow-right" />
          </NuxtLink>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const routes = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const r = await api.get('/transport/routes')
    routes.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error('Failed to load routes:', e)
  } finally {
    loading.value = false
  }
})

function formatDate(date: string | null) {
  if (!date) return '-'
  try {
    return new Date(date).toLocaleDateString('bn-BD', {
      day: 'numeric', month: 'short', year: 'numeric'
    })
  } catch {
    return '-'
  }
}
</script>

<style scoped>
.page-wrapper {
  max-width: 1300px;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

h1 {
  font-size: 1.7rem;
  color: var(--color-primary);
  font-family: var(--font-bn);
  margin: 0 0 0.3rem;
}

.page-subtitle {
  color: var(--color-text-light);
  font-family: var(--font-bn);
  margin: 0;
  font-size: 0.9rem;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.btn {
  padding: 0.6rem 1.2rem;
  border-radius: 10px;
  font-family: var(--font-bn);
  font-weight: 600;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  text-decoration: none;
  border: 1px solid transparent;
  transition: all 0.15s ease;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
}

.btn-primary:hover {
  opacity: 0.9;
  transform: translateY(-1px);
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
  padding: 4rem 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.7rem;
}

.empty-icon {
  width: 56px;
  height: 56px;
  color: var(--color-text-muted);
  margin-bottom: 0.5rem;
}

.empty-state h3 {
  font-family: var(--font-bn);
  font-size: 1.1rem;
  margin: 0;
  color: var(--color-text);
}

.empty-state p {
  color: var(--color-text-muted);
  font-family: var(--font-bn);
  margin: 0;
}

.routes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.route-card {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.route-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.route-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1rem 0.6rem;
  border-bottom: 1px solid var(--color-border-light);
}

.route-number-badge {
  display: flex;
  flex-direction: column;
}

.route-name-main {
  font-family: var(--font-bn);
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-text);
  line-height: 1.2;
}

.route-status {
  padding: 0.18rem 0.5rem;
  border-radius: 99px;
  font-size: 0.65rem;
  font-weight: 600;
  white-space: nowrap;
  flex-shrink: 0;
}

.route-status.active {
  background: #e6f4ec;
  color: #19724a;
}

.route-status.inactive {
  background: #fde8e8;
  color: #a03030;
}

.route-points {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1rem;
  border-bottom: 1px solid var(--color-border-light);
  font-family: var(--font-bn);
  font-size: 0.82rem;
}

.route-start {
  color: var(--color-text-muted);
}

.route-arrow {
  width: 14px;
  height: 14px;
  color: var(--color-primary);
}

.route-end {
  color: var(--color-text);
  font-weight: 500;
}

.route-details {
  padding: 0.6rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-family: var(--font-bn);
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.detail-item icon {
  width: 13px;
  height: 13px;
}

.route-footer {
  padding: 0.5rem 1rem 0.8rem;
}

.view-link {
  color: var(--color-primary);
  text-decoration: none;
  font-family: var(--font-bn);
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.view-link:hover {
  opacity: 0.8;
}

@media (max-width: 768px) {
  .page-header-row {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>