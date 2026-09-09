<template>
  <div class="detail-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1 v-if="expense">{{ expense.description_bn }}</h1>
        <p v-else class="text-muted">ব্যয় লোড হচ্ছে...</p>
      </div>
      <span v-if="expense" class="badge" :class="expense.is_paid ? 'badge-success' : 'badge-warning'">
        {{ expense.is_paid ? 'পরিশোধিত' : 'অপরিশোধিত' }}
      </span>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="expense" class="detail-grid">
      <div class="card">
        <h3>ব্যয়ের বিবরণ</h3>
        <dl class="info-list">
          <div><dt>বিষয়</dt><dd>{{ expense.description_bn }}</dd></div>
          <div v-if="expense.description_en"><dt>বিষয় (ইং)</dt><dd>{{ expense.description_en }}</dd></div>
          <div><dt>বিভাগ</dt><dd>{{ expense.category || 'অন্যান্য' }}</dd></div>
          <div><dt>পরিমাণ</dt><dd>৳{{ expense.amount }}</dd></div>
          <div><dt>তারিখ</dt><dd>{{ formatDate(expense.transaction_date) }}</dd></div>
          <div><dt>পদ্ধতি</dt><dd>{{ expense.payment_method || 'নগদ' }}</dd></div>
          <div><dt>প্রদাতা</dt><dd>{{ expense.vendor?.name_bn || expense.payee_name || '-' }}</dd></div>
          <div><dt>অনুমোদিত</dt><dd>{{ expense.is_approved ? 'হ্যাঁ' : 'না' }}</dd></div>
          <div><dt>পরিশোধিত</dt><dd>{{ expense.is_paid ? 'হ্যাঁ' : 'না' }}</dd></div>
          <div v-if="expense.notes"><dt>মন্তব্য</dt><dd>{{ expense.notes }}</dd></div>
          <div><dt>তৈরির তারিখ</dt><dd>{{ formatDate(expense.created_at) }}</dd></div>
        </dl>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const route = useRoute()
const api = useApiClient()
const { isAuthenticated } = useAuth()

const expense = ref<any>(null)
const error = ref('')

const formatDate = (d: string | null | undefined) =>
  d ? new Date(d).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'

async function load() {
  error.value = ''
  try {
    const res = await api.get(`/finance/expenses/${route.params.id}`)
    expense.value = res.data.data
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ব্যয় লোড করা যায়নি'
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
.alert-error { background: #fce4e4; color: var(--color-error); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.badge { padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-family: 'Noto Sans Bengali', sans-serif; }
.badge-success { background: #e8f5e9; color: var(--color-success); }
.badge-warning { background: #fff3e0; color: #e65100; }
.text-muted { color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
</style>
