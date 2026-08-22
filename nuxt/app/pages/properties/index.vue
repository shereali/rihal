<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <h1>সম্পত্তি ও সম্পদ</h1>
        <p class="page-subtitle">মাদ্রাসার জমি, ভবন, যানবাহন, সরঞ্জাম — তালিকা, মূল্য ও অবস্থান</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/properties/create" class="btn btn-primary">
          <icon name="plus" /> নতুন সম্পত্তি
        </NuxtLink>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner" />
      <p>সম্পত্তি লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!properties.length" class="empty-state">
      <div class="empty-icon"><icon name="building" /></div>
      <h3>কোনো সম্পত্তি নেই</h3>
      <p>নতুন সম্পত্তি যোগ করে শুরু করুন</p>
      <NuxtLink to="/properties/create" class="btn btn-primary">প্রথম সম্পত্তি যোগ করুন</NuxtLink>
    </div>

    <div v-else class="properties-grid">
      <article v-for="prop in properties" :key="prop.id" class="property-card">
        <div class="property-card-header">
          <div class="property-type-badge" :class="prop.status?.toLowerCase().replace(/[^a-z]/g, '-') || 'type-default'">
            {{ prop.status || 'অন্যান্য' }}
          </div>
          <div class="property-value-badge" v-if="prop.current_market_value">
            <icon name="money" />
            {{ prop.current_market_value.toLocaleString('bn-BD') }} টাকা
          </div>
        </div>
        <h3 class="property-name">{{ prop.property_name_bn || prop.property_name_en }}</h3>
        <p v-if="prop.location_address_bn" class="property-address">{{ prop.location_address_bn }}</p>
        <div class="property-attributes">
          <div class="attr-item">
            <icon name="tag" />
            <span>{{ prop.property_type || 'নির্ধারিত নয়' }}</span>
          </div>
          <div class="attr-item" v-if="prop.acquired_date">
            <icon name="calendar" />
            <span>{{ formatDate(prop.acquired_date) }}</span>
          </div>
        </div>
        <div class="property-footer">
          <NuxtLink :to="`/properties/${prop.id}`" class="view-link">
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
const properties = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const r = await api.get('/properties')
    properties.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error('Failed to load properties:', e)
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

.properties-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.property-card {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.property-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.property-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.8rem 1rem;
  background: rgba(0,0,0,0.02);
  border-bottom: 1px solid var(--color-border-light);
}

.property-type-badge {
  padding: 0.18rem 0.55rem;
  border-radius: 99px;
  font-size: 0.65rem;
  font-weight: 600;
  white-space: nowrap;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.property-type-badge.owned {
  background: #e6f4ec;
  color: #19724a;
}

.property-type-badge.rented {
  background: #fff0e4;
  color: #a05c35;
}

.property-type-badge.under_maintenance {
  background: #fef3e2;
  color: #a07035;
}

.property-type-badge.completed {
  background: #e3f2fa;
  color: #1a5276;
}

.property-type-badge.donated {
  background: #f0eafb;
  color: #7857a9;
}

.property-type-badge.type-default {
  background: #f0f0f0;
  color: #666;
}

.property-value-badge {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.2rem 0.5rem;
  background: rgba(212, 175, 55, 0.15);
  border-radius: 6px;
  font-size: 0.7rem;
  color: var(--color-primary-700);
  font-weight: 600;
  font-family: var(--font-bn);
}

.property-value-badge icon {
  width: 13px;
  height: 13px;
}

.property-name {
  padding: 0.8rem 1rem 0.4rem;
  font-family: var(--font-bn);
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--color-text);
  margin: 0;
  line-height: 1.3;
}

.property-address {
  padding: 0 1rem 0.4rem;
  font-family: var(--font-bn);
  font-size: 0.75rem;
  color: var(--color-text-muted);
  margin: 0;
  line-height: 1.4;
}

.property-attributes {
  padding: 0.4rem 1rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.attr-item {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-family: var(--font-bn);
  font-size: 0.72rem;
  color: var(--color-text-muted);
}

.attr-item icon {
  width: 12px;
  height: 12px;
}

.property-footer {
  padding: 0.5rem 1rem 0.7rem;
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