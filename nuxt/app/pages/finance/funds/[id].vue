<template>
  <div class="detail-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1 v-if="fund">{{ fund.name_bn }}</h1>
        <p v-else class="text-muted">ফান্ড লোড হচ্ছে...</p>
      </div>
      <span v-if="fund" class="badge" :class="fund.is_active ? 'badge-success' : 'badge-secondary'">
        {{ fund.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
      </span>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="fund" class="detail-grid">
      <div class="card">
        <h3>ফান্ডের তথ্য</h3>
        <dl class="info-list">
          <div><dt>নাম (বাংলা)</dt><dd>{{ fund.name_bn }}</dd></div>
          <div v-if="fund.name_en"><dt>নাম (ইংরেজি)</dt><dd>{{ fund.name_en }}</dd></div>
          <div><dt>ধরণ</dt><dd>{{ fund.type || 'রাশনির্দিষ্ট' }}</dd></div>
          <div><dt>লক্ষ্যমাত্রা</dt><dd>৳{{ fund.target_amount || 0 }}</dd></div>
          <div><dt>সংগ্রহিত</dt><dd>৳{{ fund.collected_amount || 0 }}</dd></div>
          <div><dt>বর্তমান ব্যালেন্স</dt><dd>৳{{ fund.balance || 0 }}</dd></div>
          <div v-if="fund.description_bn"><dt>বিবরণ</dt><dd>{{ fund.description_bn }}</dd></div>
          <div v-if="fund.description"><dt>বিবরণ (ইং)</dt><dd>{{ fund.description }}</dd></div>
          <div v-if="fund.zakat_eligible_balance"><dt>জাকাত যোগ্য ব্যালেন্স</dt><dd>৳{{ fund.zakat_eligible_balance }}</dd></div>
          <div><dt>তৈরির তারিখ</dt><dd>{{ formatDate(fund.created_at) }}</dd></div>
        </dl>
      </div>

      <div class="card">
        <h3>অগ্রগতি</h3>
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
        </div>
        <p class="text-muted">{{ progressPercent }}% সংগ্রহিত (৳{{ fund.collected_amount || 0 }} / ৳{{ fund.target_amount || 0 }})</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const route = useRoute()
const api = useApiClient()
const { isAuthenticated } = useAuth()

const fund = ref<any>(null)
const error = ref('')

const progressPercent = computed(() => {
  if (!fund.value?.target_amount) return 0
  const pct = ((fund.value.collected_amount || 0) / fund.value.target_amount) * 100
  return Math.min(100, Math.round(pct))
})

const formatDate = (d: string | null | undefined) =>
  d ? new Date(d).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'

async function load() {
  error.value = ''
  try {
    const res = await api.get(`/finance/funds/${route.params.id}`)
    fund.value = res.data.data
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ফান্ড লোড করা যায়নি'
  }
}

if (isAuthenticated.value) onMounted(load)
</script>

<style scoped>
.detail-page { max-width: 960px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.header-left h1 { margin: 0.5rem 0 0; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; padding: 1.25rem; }
.card h3 { margin: 0 0 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.info-list div { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--color-border-light); }
.info-list dt { color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.info-list dd { font-weight: 600; margin: 0; }
.progress-bar { height: 12px; background: var(--color-border-light); border-radius: 6px; overflow: hidden; margin-bottom: 0.5rem; }
.progress-fill { height: 100%; background: var(--color-primary); transition: width 0.3s; }
.alert-error { background: #fce4e4; color: var(--color-error); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.badge { padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-family: 'Noto Sans Bengali', sans-serif; }
.badge-success { background: #e8f5e9; color: var(--color-success); }
.badge-secondary { background: #eee; color: var(--color-text-light); }
.text-muted { color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
</style>
