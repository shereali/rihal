<template>
  <div class="expenses-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>ব্যয় সমূহ</h1>
        <p class="text-muted">{{ expenses?.total || 0 }}টি ব্যয়</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/finance/expenses/create" class="btn btn-primary btn-sm"><icon name="plus" /> নতুন ব্যয়</NuxtLink>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else-if="(expenses?.data || []).length === 0" class="empty-state"><p>কোনো ব্যয় নেই</p></div>
        <table v-else class="table table-hover">
          <thead>
            <tr>
              <th>বিবরণ</th>
              <th>বিভাগ</th>
              <th>পরিমাণ (৳)</th>
              <th>তারিখ</th>
              <th>ভেন্ডর</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in (expenses?.data || [])" :key="e.id">
              <td>{{ e.description_bn || e.description || '-' }}</td>
              <td>{{ e.category || '-' }}</td>
              <td>{{ e.amount ? Number(e.amount).toLocaleString('bn-BD') : '0' }}</td>
              <td>{{ e.transaction_date || e.date || '-' }}</td>
              <td>{{ e.vendor || e.vendor_name || '-' }}</td>
            </tr>
          </tbody>
        </table>

        <div v-if="expenses?.total > 0" class="pagination">
          <button class="btn btn-sm btn-outline" :disabled="expenses?.current_page <= 1 || !expenses?.prev_page_url" @click="loadPage((expenses?.current_page || 1) - 1)">পূর্ব</button>
          <span class="page-info">পৃষ্ঠা {{ expenses?.current_page || 1 }} / {{ expenses?.last_page || 1 }}</span>
          <button class="btn btn-sm btn-outline" :disabled="!expenses?.next_page_url" @click="loadPage((expenses?.current_page || 1) + 1)">পরের</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(false)
const expenses = ref<any>(null)
const currentPage = ref(1)

async function loadExpenses(page = 1) {
  loading.value = true
  currentPage.value = page
  try {
    const r = await api.get(`/finance/expenses?per_page=50&page=${page}`)
    expenses.value = r.data
  } catch (e: any) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadPage(page: number) {
  await loadExpenses(page)
}

onMounted(() => loadExpenses())
</script>

<style scoped>
.expenses-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { margin: 0.4rem 0 0; font-family: 'Noto Sans Bengali', sans-serif; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: 'Noto Sans Bengali', sans-serif; }
.header-actions .btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.card-body { padding: 1.25rem; }
.loading-state { display: flex; justify-content: center; padding: 2rem; }
.spinner { width: 24px; height: 24px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state { padding: 2rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.table { width: 100%; border-collapse: collapse; font-family: 'Noto Sans Bengali', sans-serif; }
.table th, .table td { padding: 0.6rem 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border-light); }
.text-muted { color: var(--color-text-light); }
.pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.page-info { font-size: 0.9rem; color: var(--color-text-light); }
.btn { padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; }
.btn-sm { padding: 0.35rem 0.8rem; font-size: 0.85rem; }
.btn-primary { background: var(--color-primary); color: var(--color-text-on-primary); }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
