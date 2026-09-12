<template>
  <div class="attendance-page">
    <div class="page-header">
      <div class="header-left">
        <h1>হাজিরা রেকর্ড</h1>
        <p class="text-muted">
          {{ totalRecords }}টি রেকর্ড
          <span v-if="summaryData">
            | গড় হাজিরা: {{ totalRecords > 0 ? Math.round(((summaryData.present || 0) / (((summaryData.present || 0) + (summaryData.absent || 0)) || 1)) * 100) : 0 }}%
          </span>
        </p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/attendance/bulk" class="btn btn-secondary btn-sm"><icon name="account-group" /> বাল্ক হাজিরা</NuxtLink>
        <NuxtLink to="/attendance/create" class="btn btn-primary btn-sm"><icon name="plus" /> নতুন হাজিরা</NuxtLink>
        <select v-model="filter.dateFilter" class="form-select form-select-sm mr-2" @change="applyFilter">
          <option value="today">আজ</option>
          <option value="this_week">এই সপ্তাহ</option>
          <option value="this_month">এই মাস</option>
          <option value="all">সব</option>
        </select>
        <select v-model="filter.classId" class="form-select form-select-sm" @change="applyFilter">
          <option value="">সব শ্রেণি</option>
          <option v-for="cls in classOptions" :key="cls.id" :value="cls.id">{{ cls.name_bn }}</option>
        </select>
      </div>
    </div>

    <div class="stats-row" v-if="summaryData">
      <div class="stat-card stat-present">
        <div class="stat-icon"><icon name="check-circle" /></div>
        <div class="stat-info"><p class="stat-value">{{ summaryData.present }}</p><p class="stat-label">উপস্থিত</p></div>
      </div>
      <div class="stat-card stat-absent">
        <div class="stat-icon"><icon name="close-circle" /></div>
        <div class="stat-info"><p class="stat-value">{{ summaryData.absent }}</p><p class="stat-label">অনুপস্থিত</p></div>
      </div>
      <div class="stat-card stat-late">
        <div class="stat-icon"><icon name="clock-outline" /></div>
        <div class="stat-info"><p class="stat-value">{{ summaryData.late }}</p><p class="stat-label">দেরি</p></div>
      </div>
      <div class="stat-card stat-total">
        <div class="stat-icon"><icon name="account-group" /></div>
        <div class="stat-info"><p class="stat-value">{{ summaryData.total }}</p><p class="stat-label">মোট</p></div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /><p>হাজিরা তথ্য লোড হচ্ছে...</p></div>
        <div v-else-if="paginatedRecords.length === 0" class="empty-state"><p>কোনো হাজিরা রেকর্ড নেই</p></div>
        <div v-else class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>ছাত্র</th><th>শ্রেণি</th><th>তারিখ</th><th>অবস্থা</th><th>পদ্ধতি</th>
                <th>চেক-ইন</th><th>চেক-আউট</th><th>অভিভাবক</th><th>ক্রিয়া</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="record in paginatedRecords" :key="record.id">
                <td>
                  <p class="font-weight-medium">{{ record.student?.name_bn || record.student?.name_en }}</p>
                  <p class="text-muted text-sm" v-if="record.student?.name_en">{{ record.student.name_en }}</p>
                </td>
                <td><span class="badge badge-outline">{{ record.student?.class?.name_bn || record.student?.class_name || '-' }}</span></td>
                <td>
                  <strong>{{ formatDate(record.date) }}</strong>
                  <span v-if="record.date === todayDate" class="badge badge-success badge-sm ml-1">আজ</span>
                </td>
                <td>
                  <span v-if="record.is_present && !record.is_late && !record.is_half_day" class="status-badge status-present"><icon name="check" /> উপস্থিত</span>
                  <span v-else-if="record.is_present && record.is_late" class="status-badge status-late"><icon name="clock" /> দেরি</span>
                  <span v-else-if="record.is_present && record.is_half_day" class="status-badge status-late"><icon name="clock" /> অর্ধদিবস</span>
                  <span v-else class="status-badge status-absent"><icon name="close" /> অনুপস্থিত</span>
                </td>
                <td><span class="badge badge-outline" :class="getMethodBadge(record.method)">{{ formatMethod(record.method) }}</span></td>
                <td>{{ record.check_in_time ? formatTime(record.check_in_time) : '-' }}</td>
                <td>{{ record.check_out_time ? formatTime(record.check_out_time) : '-' }}</td>
                <td>
                  <span class="badge badge-outline" :class="record.parent_notified ? 'badge-success' : 'badge-secondary'">
                    {{ record.parent_notified ? 'জানানো হয়েছে' : 'জানানো হয়নি' }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline btn-sm" @click="editRecord(record)"><icon name="pencil" /></button>
                    <button class="btn btn-outline-danger btn-sm" @click="confirmDelete(record)"><icon name="delete" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="totalRecords > (attendanceData?.data?.per_page || 20)" class="pagination-wrapper">
          <div class="pagination">
            <button v-for="page in totalPages" :key="page" :class="['page-btn', { active: page === attendanceData?.data?.current_page }]" @click="goToPage(page)">{{ page }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>হাজিরা রেকর্ড মুছে ফেলা</h3>
          <button class="action-btn" @click="showDeleteModal = false"><icon name="close" /></button>
        </div>
        <div class="modal-body">
          <p>আপনি কি নিশ্চিতভাবে এই শিক্ষার্থীর হাজিরা রেকর্ড মুছে ফেলতে চান?</p>
          <p v-if="deleteTarget" class="text-muted text-sm mt-1">
            শিক্ষার্থী: <strong>{{ deleteTarget.student?.name_bn || deleteTarget.student?.name_en }}</strong> (তারিখ: {{ formatDate(deleteTarget.date) }})
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDeleteModal = false">বাতিল</button>
          <button class="btn btn-danger" @click="executeDelete" :disabled="deleting">
            <span v-if="deleting" class="spinner-sm"></span>
            {{ deleting ? 'মুছে ফেলা হচ্ছে...' : 'মুছে ফেলুন' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const attendanceData = ref<any>(null)
const summaryData = ref<any>(null)
const totalPages = ref(1)
const filter = ref({ dateFilter: 'today', classId: '' })
const classOptions = ref<any[]>([])
const todayDate = new Date().toISOString().split('T')[0]

const showDeleteModal = ref(false)
const deleteTarget = ref<any>(null)
const deleting = ref(false)

const totalRecords = computed(() => attendanceData.value?.data?.meta?.total ?? attendanceData.value?.data?.total ?? 0)
const paginatedRecords = computed(() => attendanceData.value?.data?.data || [])

async function loadAttendance() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.set('per_page', '20'); params.set('page', '1')
    if (filter.value.dateFilter === 'today') params.set('date', todayDate)
    if (filter.value.classId) params.set('class_id', filter.value.classId)
    const res = await api.get(`/attendance?${params.toString()}`)
    attendanceData.value = res.data
    totalPages.value = res.data?.meta?.last_page || res.data?.data?.last_page || 1
  } catch (error) { console.error('Failed to load attendance:', error) }
  finally { loading.value = false }
}

async function loadSummary() {
  try {
    const res = await api.get('/attendance/summary')
    summaryData.value = res.data.data
  } catch (error) { console.error('Failed to load summary:', error) }
}

async function loadClassOptions() {
  try {
    const res = await api.get('/academic/classes').catch(() => null)
    if (res?.data?.data && Array.isArray(res.data.data)) {
      classOptions.value = res.data.data.map((c: any) => ({
        id: c.id,
        name_bn: c.name_bn || c.name_en || `শ্রেণি #${c.id}`
      }))
    } else {
      classOptions.value = []
    }
  } catch (error) {
    console.error('Failed to load class options:', error)
    classOptions.value = []
  }
}

const goToPage = (page: number) => loadAttendance()
const applyFilter = () => loadAttendance()
const formatDate = (d: string | null | undefined) => d ? new Date(d).toLocaleDateString('bn-BD', { day:'numeric', month:'short', year:'numeric' }) : '-'
const formatTime = (t: string | null | undefined) => t ? new Date(t).toLocaleTimeString('bn-BD', { hour:'2-digit', minute:'2-digit' }) : '-'
const formatMethod = (m: string) => ({ fingerprint:'ফিঙ্গারপ্রিন্ট', manual:'ম্যানুয়াল', biometric:'বায়োমেট্রিক', qr:'QR কোড', online:'অনলাইন' }[m] || m)
const getMethodBadge = (m: string) => ({ fingerprint:'badge-primary', manual:'badge-secondary', biometric:'badge-success', qr:'badge-info', online:'badge-warning' }[m] || 'badge-outline')
const editRecord = (record: any) => navigateTo(`/attendance/${record.id}/edit`)

function confirmDelete(record: any) {
  deleteTarget.value = record
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`/attendance/${deleteTarget.value.id}`)
    showDeleteModal.value = false
    deleteTarget.value = null
    await Promise.all([loadAttendance(), loadSummary()])
  } catch (e) {
    console.error('Failed to delete attendance record:', e)
  } finally {
    deleting.value = false
  }
}

onMounted(async () => { await Promise.all([loadAttendance(), loadSummary(), loadClassOptions()]) })
</script>

<style scoped>
.attendance-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
.header-actions { display: flex; gap: 0.5rem; align-items: center; }
.stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: var(--color-bg-card); border-radius: var(--radius-md); border: 1px solid var(--color-border-light); }
.stat-card.stat-present { border-left: 4px solid var(--color-success); }
.stat-card.stat-absent { border-left: 4px solid var(--color-error); }
.stat-card.stat-late { border-left: 4px solid var(--color-warning); }
.stat-card.stat-total { border-left: 4px solid var(--color-primary); }
.stat-icon { width: 40px; height: 40px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
.stat-card.stat-present .stat-icon { background: rgba(40,167,69,0.1); color: var(--color-success); }
.stat-card.stat-absent .stat-icon { background: rgba(220,53,69,0.1); color: var(--color-error); }
.stat-card.stat-late .stat-icon { background: rgba(255,193,7,0.1); color: var(--color-warning); }
.stat-card.stat-total .stat-icon { background: rgba(99,102,241,0.1); color: var(--color-primary); }
.stat-value { font-size: 1.5rem; font-weight: 700; margin: 0; }
.stat-label { font-size: 0.875rem; color: var(--color-text-muted); margin: 0; }
.table-responsive { overflow-x: auto; }
.pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 1rem; }
.page-btn { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text); border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s; }
.page-btn:hover:not(.active) { background: var(--color-bg-hover); border-color: var(--color-border-dark); }
.page-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
.status-badge { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); font-size: 0.875rem; font-weight: 500; }
.status-present { background: rgba(40,167,69,0.12); color: var(--color-success); }
.status-absent { background: rgba(220,53,69,0.12); color: var(--color-error); }
.status-late { background: rgba(255,193,7,0.12); color: var(--color-warning); }

.modal-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); display: grid; place-items: center; z-index: 999; padding: 1rem; }
.modal-card { background: var(--color-bg-card, #fff); border-radius: var(--radius-md, 12px); border: 1px solid var(--color-border); width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); overflow: hidden; animation: modalPop 0.2s ease-out; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--color-border-light); }
.modal-header h3 { font-size: 1.15rem; font-weight: 700; margin: 0; }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; background: var(--color-bg-subtle, #f8fafc); border-top: 1px solid var(--color-border-light); }
.action-btn { background: transparent; border: none; cursor: pointer; color: var(--color-text-muted); font-size: 1.1rem; }
.action-btn:hover { color: var(--color-text); }
.btn-danger { background: var(--color-error, #dc2626); color: white; border: none; }
.btn-danger:hover { background: #b91c1c; }
@keyframes modalPop { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>
