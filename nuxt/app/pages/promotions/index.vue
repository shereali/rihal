<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <h1>প্রমোশন এবং গ্র্যাজুয়েশন</h1>
        <p class="page-subtitle">শিক্ষার্থীদের শ্রেণি পরিবর্তন ও প্রমোশন অনুমোদন — তালিকা, অনুমোদন, তথ্য দেখা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary btn-sm" @click="openCreate"><Icon name="plus" /> নতুন প্রমোশন</button>
        <button class="btn btn-outline btn-sm" @click="load"><Icon name="refresh" /> রিফ্রেশ</button>
      </div>
    </div>
    <div class="module-stats card-grid" v-if="stats">
      <div class="stat-card"><div class="stat-icon"><Icon name="users" /></div><div><span class="stat-value">{{ stats?.total || 0 }}</span><span class="stat-label">মোট প্রমোশন</span></div></div>
      <div class="stat-card"><div class="stat-icon stat-icon-warning"><Icon name="clock" /></div><div><span class="stat-value">{{ stats?.pending || 0 }}</span><span class="stat-label">মুলতুবি</span></div></div>
      <div class="stat-card"><div class="stat-icon stat-icon-success"><Icon name="check" /></div><div><span class="stat-value">{{ stats?.approved || 0 }}</span><span class="stat-label">অনুমোদিত</span></div></div>
      <div class="stat-card"><div class="stat-icon stat-icon-danger"><Icon name="close" /></div><div><span class="stat-value">{{ stats?.rejected || 0 }}</span><span class="stat-label">প্রত্যাখ্যান</span></div></div>
    </div>
    <div class="module-empty-state" v-if="!promotions?.length">
      <Icon name="arrow-up-on-square" class="empty-icon" />
      <h3>কোনো প্রমোশন রেকর্ড নেই</h3>
      <p>প্রমোশন রেকর্ড তৈরি করতে নিচের বাটনে ক্লিক করুন।</p>
      <button class="btn btn-primary" @click="openCreate">শিক্ষার্থী প্রমোশন তৈরি করুন</button>
    </div>
    <div v-else class="module-table-wrap card">
      <div class="module-table-toolbar">
        <div class="search-box"><Icon name="search" /><input v-model="filters.search" placeholder="শিক্ষার্থী নাম বা ক্রম খুঁজুন..." /></div>
        <select v-model="filters.status" class="form-control">
          <option value="">সব অবস্থা</option>
          <option value="pending">মুলতুবি</option>
          <option value="approved">অনুমোদিত</option>
          <option value="rejected">প্রত্যাখ্যান</option>
        </select>
        <select v-model="filters.session_id" class="form-control" :disabled="sessions?.length === 0">
          <option value="">সব সেশন</option>
          <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.name_bn || s.name_en || 'সেশন ' + s.id }}</option>
        </select>
      </div>
      <table class="data-table">
        <thead><tr>
          <th>শিক্ষার্থী</th><th>ক্রম</th><th>গ্র্যাজুয়েশন শ্রেণি</th><th>সেশন</th><th>শ্রেণি</th><th>অবস্থা</th>
          <th>অনুমোদন</th><th></th>
        </tr></thead>
        <tbody>
          <tr v-for="p in promotions" :key="p.id">
            <td>{{ p.student_name_bn || p.student_name }}</td>
            <td class="mono">{{ p.student_number }}</td>
            <td><Badge :type="p.to_class_name ? 'primary' : 'info'">{{ p.to_class_name || p.to_class_name_en || '—' }}</Badge></td>
            <td class="dimmed">{{ p.session_name || p.session_name_en }}</td>
            <td class="dimmed">{{ p.academic_year }}</td>
            <td><StatusBadge :status="p.status" /></td>
            <td class="dimmed">{{ p.approved_at ? p.approved_by + ' (' + p.approved_at.substring(0, 10) + ')' : '—' }}</td>
            <td>
              <div class="row-actions">
                <NavLink :to="`/promotions/${p.id}`" class="btn-ghost btn-sm"><Icon name="eye" /></NavLink>
                <button v-if="p.status === 'pending'" class="btn-ghost btn-sm" @click="approve(p)" :disabled="approving?.id === p.id">
                  <Icon name="check" /> অনুমোদন
                </button>
                <button class="btn-ghost btn-sm text-danger" @click="destroy(p)" :disabled="destroying?.id === p.id">
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
    <div v-if="approveLoading" class="module-alert alert-info">অনুমোদন হচ্ছে...</div>
    <ConfirmationModal v-if="confirmDelete" :title="confirmDelete.title" :message="confirmDelete.message" @confirm="doDestroy" @cancel="confirmDelete = null" />
    <div v-if="createOpen" class="module-dialog-overlay" @click.self="createOpen = false">
      <div class="module-dialog">
        <div class="module-dialog-header"><h1>শিক্ষার্থী প্রমোশন তৈরি করুন</h1><button class="close-btn" @click="createOpen = false"><Icon name="close" /></button></div>
        <div class="module-dialog-body">
          <form @submit.prevent="createPromotion">
            <div class="field"><label>শিক্ষার্থী *</label>
              <select v-model="createForm.student_id" class="form-control" required>
                <option value="">নির্বাচন করুন</option>
                <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name_bn || s.name }} (ক্রম: {{ s.student_number || '—' }})</option>
              </select>
            </div>
            <div class="field"><label>গ্র্যাজুয়েশন শ্রেণি *</label>
              <select v-model="createForm.to_class_id" class="form-control" required>
                <option value="">নির্বাচন করুন</option>
                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn || c.name_en || 'শ্রেণি ' + c.id }}</option>
              </select>
            </div>
            <div class="field"><label>সেশন *</label>
              <select v-model="createForm.session_id" class="form-control" required>
                <option value="">নির্বাচন করুন</option>
                <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.name_bn || s.name_en || 'সেশন ' + s.id }}</option>
              </select>
            </div>
            <div class="field"><label>শ্রেণি (একাডেমিক) *</label><input v-model="createForm.academic_year" type="text" class="form-control" required placeholder="যেমন: ২০২৬" /></div>
            <div class="field checkbox-field"><label class="checkbox-label"><input type="checkbox" v-model="createForm.notes_check" /> নোট যোগ করুন</label></div>
            <div class="field" v-if="createForm.notes_check"><label>নোট</label><textarea v-model="createForm.notes" class="form-control" rows="2" placeholder="প্রমোশন সম্পর্কে নোট..."></textarea></div>
            <div class="form-actions">
              <button type="button" class="btn btn-outline" @click="createOpen = false">বাতিল করুন</button>
              <button type="submit" class="btn btn-primary" :disabled="creating">
                <span v-if="creating">তৈরি হচ্ছে...</span><span v-else>প্রমোশন তৈরি করুন</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import { useRouter } from 'vue-router'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const router = useRouter()

const promotions = ref<any[]>([])
const meta = ref<any>(null)
const stats = ref<any>({})
const sessions = ref<any[]>([])
const classes = ref<any[]>([])
const students = ref<any[]>([])
const loading = ref(false)
const alert = ref('')
const filters = reactive({
  search: '',
  status: '',
  session_id: '',
  page: 1,
  per_page: 15,
})
const createOpen = ref(false)
const creating = ref(false)
const approving = ref<{ id: number } | null>(null)
const destroyConfirm = ref<{ id: number; title: string; message: string } | null>(null)
const createForm = reactive({
  student_id: 0,
  to_class_id: 0,
  session_id: 0,
  academic_year: '২০২৬',
  notes: '',
  notes_check: false,
})

function goPage(page: number) { filters.page = page; load() }

async function load() {
  loading.value = true; alert.value = ''
  try {
    const params = new URLSearchParams({
      page: String(filters.page),
      per_page: String(filters.per_page),
      ...(filters.search && { search: filters.search }),
      ...(filters.status && { status: filters.status }),
      ...(filters.session_id && { session_id: String(filters.session_id) }),
    })
    const [promRes, sessRes, clsRes, stuRes] = await Promise.all([
      api.get(`/promotions?${params}`),
      api.get('/academic/sessions'),
      api.get('/academic/classes'),
      api.get('/students'),
    ])
    promotions.value = promRes.data?.data || []
    meta.value = promRes.data?.meta || null
    sessions.value = sessRes.data?.data || []
    classes.value = clsRes.data?.data || []
    students.value = stuRes.data?.data || []
    try {
      const overview = await api.get('/promotions/stats')
      stats.value = overview.data || {}
    } catch {}
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'প্রমোশন লোড করতে ত্রুটি হয়েছে।'
  } finally { loading.value = false }
}

function openCreate() { createOpen.value = true }

async function createPromotion() {
  creating.value = true; alert.value = ''
  try {
    await api.post('/promotions', createForm)
    alert.value = 'প্রমোশন সফলভাবে তৈরি করা হয়েছে।'
    createOpen.value = false
    createForm.student_id = 0
    createForm.to_class_id = 0
    createForm.session_id = 0
    createForm.academic_year = '২০২৬'
    createForm.notes = ''
    createForm.notes_check = false
    load()
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'প্রমোশন তৈরি করতে ত্রুটি হয়েছে।'
  } finally { creating.value = false }
}

async function approve(p: any) {
  approving.value = { id: p.id }
  try {
    await api.post(`/promotions/${p.id}/approve`, {})
    await load()
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'অনুমোদন করতে ত্রুটি হয়েছে।'
  } finally { approving.value = null }
}

function destroy(p: any) {
  destroyingConfirm.value = {
    id: p.id,
    title: 'প্রমোশন রেকর্ড মুছে ফেলবেন?',
    message: 'এই রেকর্ডটি মুছে ফেলা হলে পুনরুদ্ধার করা যাবে না। আপনি কি ঠিক আছেন?',
  }
}

async function doDestroy() {
  if (!destroyConfirm.value) return
  try {
    await api.delete(`/promotions/${destroyConfirm.value.id}`)
    alert.value = 'প্রমোশন রেকর্ড মুছে ফেলা হয়েছে।'
    destroyConfirm.value = null
    load()
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'মুছতে ত্রুটি হয়েছে।'
  }
}

const destroyingConfirm = ref<{ id: number; title: string; message: string } | null>(null)

onMounted(() => {
  if (!isAuthenticated.value && !authLoading.value) router.push('/login')
  load()
})
</script>
