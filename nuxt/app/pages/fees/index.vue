<template>
  <div class="fee-page">
    <div class="page-header">
      <div class="header-left">
        <h1>ফি সংগ্রহ</h1>
        <p class="text-muted">{{ payments?.data?.total || 0 }}টি ফি রেকর্ড</p>
      </div>
      <div class="header-actions">
        <select v-model="filter.status" class="form-select form-select-sm" @change="loadPayments">
          <option value="">সব অবস্থা</option>
          <option value="0">বকেয়া</option>
          <option value="1">পরিশোধিত</option>
        </select>
        <NuxtLink to="/fees/collect" class="btn btn-primary btn-sm"><icon name="plus" /> নতুন ফি সংগ্রহ</NuxtLink>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>
        <div v-else-if="(payments?.data?.data || []).length === 0" class="empty-state"><p>কোনো ফি রেকর্ড নেই</p></div>
        <div v-else class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>ছাত্র</th><th>ফি কাঠামো</th><th>মোট</th><th>পরিশোধিত</th>
                <th>বকেয়া</th><th>শেষ তারিখ</th><th>অবস্থা</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in (payments?.data?.data || [])" :key="p.id">
                <td>{{ p.student?.name_bn || p.student?.name_en || '-' }}</td>
                <td>{{ p.fee_structure?.name_bn || '-' }}</td>
                <td>৳{{ Number(p.total_amount).toLocaleString('bn-BD') }}</td>
                <td>৳{{ Number(p.paid_amount).toLocaleString('bn-BD') }}</td>
                <td>৳{{ Number(p.balance).toLocaleString('bn-BD') }}</td>
                <td>{{ formatDate(p.due_date) }}</td>
                <td>
                  <span class="badge" :class="p.is_fully_paid ? 'badge-success' : 'badge-warning'">
                    {{ p.is_fully_paid ? 'পরিশোধিত' : 'বকেয়া' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const payments = ref<any>(null)
const filter = ref({ status: '' })

async function loadPayments() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.set('per_page', '50')
    if (filter.value.status !== '') params.set('is_fully_paid', filter.value.status)
    const res = await api.get(`/finance/fee-payments?${params.toString()}`)
    payments.value = res.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

function formatDate(date: string | null | undefined): string {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(loadPayments)
</script>

<style scoped>
.fee-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
.header-actions { display: flex; gap: 0.5rem; align-items: center; }
.table-responsive { overflow-x: auto; }
</style>
