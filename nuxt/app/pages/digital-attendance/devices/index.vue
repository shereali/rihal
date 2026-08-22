<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <h1>ডিজিটাল হাজিরা ডিভাইস</h1>
        <p class="page-subtitle">বায়োমেট্রিক ও আইডি কার্ড ডিভাইস ব্যবস্থাপনা — লিস্ট, যোগ, সম্পাদনা, সক্রিয়/নিষ্ক্রিয়</p>
      </div>
      <div class="header-actions">
        <NavLink to="/digital-attendance/devices/create"><Icon name="plus" /> ডিভাইস যোগ করুন</NavLink>
      </div>
    </div>
    <div class="module-stats card-grid" v-if="stats">
      <div class="stat-card"><div class="stat-icon"><Icon name="devices" /></div><div><span class="stat-value">{{ stats?.total || 0 }}</span><span class="stat-label">মোট ডিভাইস</span></div></div>
      <div class="stat-card"><div class="stat-icon stat-icon-success"><Icon name="check" /></div><div><span class="stat-value">{{ stats?.active_count || 0 }}</span><span class="stat-label">সক্রিয়</span></div></div>
      <div class="stat-card"><div class="stat-icon stat-icon-danger"><Icon name="close" /></div><div><span class="stat-value">{{ stats?.inactive_count || 0 }}</span><span class="stat-label">নিষ্ক্রিয়</span></div></div>
    </div>
    <div class="module-empty-state" v-if="!devices?.length">
      <Icon name="devices" class="empty-icon" />
      <h3>কোনো ডিভাইস নেই</h3>
      <p>হাজিরা ডিভাইস যোগ করতে নিচের বাটনে ক্লিক করুন।</p>
      <NavLink to="/digital-attendance/devices/create"><Icon name="plus" /> ডিভাইস যোগ করুন</NavLink>
    </div>
    <div v-else class="module-table-wrap card">
      <div class="module-table-toolbar">
        <div class="search-box"><Icon name="search" /><input v-model="filters.search" placeholder="ডিভাইস খুঁজুন..." /></div>
        <select v-model="filters.type" class="form-control">
          <option value="">সব ধরন</option>
          <option value="bio">বায়োমেট্রিক</option>
          <option value="gateway">গেটওয়ে</option>
          <option value="software">সফটওয়্যার</option>
        </select>
        <select v-model="filters.is_active" class="form-control">
          <option value="">সব অবস্থা</option>
          <option :value="true">সক্রিয়</option>
          <option :value="false">নিষ্ক্রিয়</option>
        </select>
      </div>
      <table class="data-table">
        <thead><tr>
          <th>নাম</th><th>ধরন</th><th>আইপি</th><th>পোর্ট</th><th>অবস্থা</th><th>সর্বশেষ সিঙ্ক</th>
          <th></th>
        </tr></thead>
        <tbody>
          <tr v-for="d in devices" :key="d.id">
            <td>{{ d.device_name }}</td>
            <td><Badge :type="d.device_type === 'bio' ? 'primary' : d.device_type === 'gateway' ? 'warning' : 'info'">{{ d.device_type }}</Badge></td>
            <td class="mono">{{ d.ip_address || '—' }}</td>
            <td>{{ d.port ? d.port : '—' }}</td>
            <td><StatusBadge :active="d.is_active" /></td>
            <td class="dimmed">{{ d.last_synced_at || 'কখনো না' }}</td>
            <td>
              <div class="row-actions">
                <NavLink :to="`/digital-attendance/devices/${d.id}/edit`" class="btn-ghost btn-sm">
                  <Icon name="edit" />
                </NavLink>
                <button class="btn-ghost btn-sm text-danger" @click="destroy(d)" :disabled="destroying === d.id">
                  <Icon name="delete" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="meta" class="table-footer">
        <span v-if="meta.current_page > 1"><button class="btn btn-sm btn-outline" @click="goPage(meta.current_page - 1)">পূর্বে</button></span>
        <span>পৃষ্ঠা {{ meta.current_page }} / {{ meta.last_page }}</span>
        <span v-if="meta.current_page < meta.last_page"><button class="btn btn-sm btn-outline" @click="goPage(meta.current_page + 1)">পরে</button></span>
      </div>
    </div>
    <ApiAlert :message="alert" v-if="alert" />
    <ConfirmationModal v-if="confirmDelete" :title="confirmDelete.title" :message="confirmDelete.message" @confirm="doDestroy" @cancel="confirmDelete = null" />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import { useRouter, useRoute } from 'vue-router'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const router = useRouter()
const route = useRoute()

const devices = ref<any[]>([])
const meta = ref<any>(null)
const stats = ref<any>({})
const loading = ref(false)
const alert = ref('')
const filters = reactive({
  search: '',
  type: '',
  is_active: '',
  page: 1,
  per_page: 15,
})
const destroying = ref<number | null>(null)
const confirmDelete = ref<{ id: number; title: string; message: string } | null>(null)

function goPage(page: number) { filters.page = page; load() }

async function load() {
  loading.value = true; alert.value = ''
  try {
    const params = new URLSearchParams({
      page: String(filters.page),
      per_page: String(filters.per_page),
      ...(filters.search && { search: filters.search }),
      ...(filters.type && { type: filters.type }),
      ...(filters.is_active !== '' && { is_active: String(filters.is_active) }),
    })
    const res = await api.get(`/digital-attendance/devices?${params}`)
    devices.value = res.data?.data || []
    meta.value = res.data?.meta || null
    try {
      const overview = await api.get('/digital-attendance/stats')
      stats.value = overview.data || {}
    } catch {}
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'ডিভাইস লোড করতে ত্রুটি হয়েছে।'
  } finally { loading.value = false }
}

async function destroy(id: number) {
  destroying.value = id
  confirmDelete.value = {
    id,
    title: 'ডিভাইস মুছে ফেলবেন?',
    message: 'এই ডিভাইসটি মুছে ফেলা হলে পুনরুদ্ধার করা যাবে না। আপনি কি ঠিক আছেন?',
  }
}

async function doDestroy() {
  if (!confirmDelete.value) return
  try {
    await api.delete(`/digital-attendance/devices/${confirmDelete.value.id}`)
    alert.value = 'ডিভাইস মুছে ফেলা হয়েছে।'
    confirmDelete.value = null
    load()
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'মুছতে ত্রুটি হয়েছে।'
  } finally { destroying.value = null }
}

onMounted(() => {
  if (!isAuthenticated.value && !authLoading.value) router.push('/login')
  load()
})
</script>
