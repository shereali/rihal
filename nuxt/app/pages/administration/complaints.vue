<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/administration" class="back-link"><icon name="arrow-left" /> প্রশাসনিক ড্যাশবোর্ড</NuxtLink>
        <h1>পরামর্শ ও অভিযোগ বাক্স (Complaints & Feedback)</h1>
        <p class="page-subtitle">অভিভাবক, শিক্ষার্থী ও শিক্ষকদের পরামর্শ, অভিযোগ গ্রহণ ও সমাধান ট্র্যাকিং</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openAddComplaintModal">
          <icon name="plus" /> নতুন অভিযোগ / পরামর্শ লিপিবদ্ধ করুন
        </button>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="বিষয়, প্রেরক বা ট্র্যাকিং নম্বর খুঁজুন..." />
        <button v-if="search" class="clear-search-btn" @click="search = ''">×</button>
      </div>
      <select v-model="categoryFilter" class="form-select">
        <option value="">সকল ক্যাটাগরি</option>
        <option value="academic">একাডেমিক ও পাঠদান</option>
        <option value="boarding">হোস্টেল ও খাবার</option>
        <option value="discipline">শৃঙ্খলা ও আচরণ</option>
        <option value="general">সাধারণ পরামর্শ</option>
      </select>
      <select v-model="statusFilter" class="form-select">
        <option value="">সকল অবস্থা</option>
        <option value="resolved">সমাধানকৃত (Resolved)</option>
        <option value="pending">বিচারাধীন (Pending)</option>
      </select>
    </div>

    <!-- Complaints Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>ট্র্যাকিং আইডি</th>
              <th>তারিখ</th>
              <th>প্রেরক (পরিচয়)</th>
              <th>ক্যাটাগরি</th>
              <th>অভিযোগ / পরামর্শের বিষয়</th>
              <th>অগ্রাধিকার</th>
              <th class="text-center">অবস্থা</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in filteredComplaints" :key="c.id">
              <td><strong class="mono-font">{{ c.tracking_id }}</strong></td>
              <td>{{ c.date }}</td>
              <td>
                <div><strong>{{ c.sender_name }}</strong></div>
                <div class="sub-text">{{ c.sender_type }}</div>
              </td>
              <td><span class="fund-tag">{{ c.category_label }}</span></td>
              <td><strong>{{ c.subject }}</strong></td>
              <td>
                <span class="type-tag" :class="c.priority">{{ c.priority_label }}</span>
              </td>
              <td class="text-center">
                <span class="status-pill" :class="c.status === 'resolved' ? 'badge-approved' : 'badge-pending'">
                  <span class="status-dot" />
                  {{ c.status === 'resolved' ? 'সমাধানকৃত' : 'বিচারাধীন' }}
                </span>
              </td>
              <td class="text-right">
                <button class="action-btn" @click="toggleResolve(c)" :title="c.status === 'resolved' ? 'পুনরায় খুলুন' : 'সমাধান চিহ্নিত করুন'">
                  <icon :name="c.status === 'resolved' ? 'refresh' : 'check'" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন অভিযোগ বা পরামর্শ এন্ট্রি</h3>
            <p>অভিযোগকারীর বিবরণ ও বিষয়বস্তু লিপিবদ্ধ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showModal = false">×</button>
        </div>
        <form @submit.prevent="saveComplaint" class="modal-form">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">প্রেরকের নাম *</label>
              <input v-model="form.sender_name" class="form-input" placeholder="মুহাম্মদ আনোয়ার (অভিভাবক)" required />
            </div>
            <div class="form-group">
              <label class="form-label">প্রেরকের ধরন *</label>
              <select v-model="form.sender_type" class="form-select" required>
                <option value="অভিভাবক">অভিভাবক</option>
                <option value="শিক্ষার্থী">শিক্ষার্থী</option>
                <option value="শিক্ষক / স্টাফ">শিক্ষক / স্টাফ</option>
                <option value="শুভাকাঙ্ক্ষী">শুভাকাঙ্ক্ষী</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">ক্যাটাগরি *</label>
              <select v-model="form.category" class="form-select" required>
                <option value="academic">একাডেমিক ও পাঠদান</option>
                <option value="boarding">হোস্টেল ও খাবার</option>
                <option value="discipline">শৃঙ্খলা ও আচরণ</option>
                <option value="general">সাধারণ পরামর্শ</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">অগ্রাধিকার *</label>
              <select v-model="form.priority" class="form-select" required>
                <option value="urgent">জরুরি (Urgent)</option>
                <option value="medium">সাধারণ (Medium)</option>
                <option value="low">কম (Low)</option>
              </select>
            </div>
            <div class="form-group wide">
              <label class="form-label">বিষয় *</label>
              <input v-model="form.subject" class="form-input" placeholder="যেমন: হোস্টেলের রাতের খাবারের মান উন্নয়ন প্রসঙ্গে" required />
            </div>
            <div class="form-group wide">
              <label class="form-label">বিস্তারিত বিবরণ</label>
              <textarea v-model="form.description" class="form-textarea" rows="3" placeholder="বিস্তারিত লিখুন..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary">সংরক্ষণ করুন</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const search = ref('')
const categoryFilter = ref('')
const statusFilter = ref('')
const showModal = ref(false)

const complaintsList = ref<any[]>([
  {
    id: 1,
    tracking_id: 'CMP-2026-018',
    date: '২৫ আগস্ট, ২০২৬',
    sender_name: 'হাজী মোঃ রফিকুল ইসলাম',
    sender_type: 'অভিভাবক (মিজান জামাত)',
    category: 'boarding',
    category_label: 'হোস্টেল ও খাবার',
    subject: 'হোস্টেলের রাতের খাবারের মেনুতে ডালের মান উন্নয়ন প্রসঙ্গে',
    priority: 'medium',
    priority_label: 'সাধারণ',
    status: 'resolved'
  }
])

const form = reactive({
  sender_name: '',
  sender_type: 'অভিভাবক',
  category: 'academic',
  priority: 'medium',
  subject: '',
  description: ''
})

async function loadComplaints() {
  try {
    const res = await api.get('/administration/complaints').catch(() => ({ data: { data: [] } }))
    const fetched = res.data?.data || []
    if (fetched.length > 0) {
      const catLabels: Record<string, string> = { academic: 'একাডেমিক', boarding: 'হোস্টেল ও খাবার', discipline: 'শৃঙ্খলা', general: 'সাধারণ' }
      const priLabels: Record<string, string> = { urgent: 'জরুরি', medium: 'সাধারণ', low: 'কম' }
      complaintsList.value = fetched.map((c: any) => ({
        id: c.id,
        tracking_id: c.tracking_id,
        date: c.date,
        sender_name: c.sender_name,
        sender_type: c.sender_type,
        category: c.category,
        category_label: catLabels[c.category] || c.category,
        subject: c.subject,
        priority: c.priority,
        priority_label: priLabels[c.priority] || c.priority,
        status: c.status
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

const filteredComplaints = computed(() => {
  return complaintsList.value.filter(c => {
    const term = (c.tracking_id + ' ' + c.sender_name + ' ' + c.subject).toLowerCase()
    const matchesSearch = !search.value || term.includes(search.value.toLowerCase())
    const matchesCategory = !categoryFilter.value || c.category === categoryFilter.value
    const matchesStatus = !statusFilter.value || c.status === statusFilter.value
    return matchesSearch && matchesCategory && matchesStatus
  })
})

function openAddComplaintModal() {
  form.sender_name = ''
  form.subject = ''
  form.description = ''
  showModal.value = true
}

async function saveComplaint() {
  const catLabels: Record<string, string> = { academic: 'একাডেমিক', boarding: 'হোস্টেল ও খাবার', discipline: 'শৃঙ্খলা', general: 'সাধারণ' }
  const priLabels: Record<string, string> = { urgent: 'জরুরি', medium: 'সাধারণ', low: 'কম' }

  try {
    const res = await api.post('/administration/complaints', { ...form }).catch(() => null)
    const saved = res?.data?.data
    complaintsList.value.unshift(saved ? {
      id: saved.id,
      tracking_id: saved.tracking_id,
      date: saved.date,
      sender_name: saved.sender_name,
      sender_type: saved.sender_type,
      category: saved.category,
      category_label: catLabels[saved.category] || 'সাধারণ',
      subject: saved.subject,
      priority: saved.priority,
      priority_label: priLabels[saved.priority] || 'সাধারণ',
      status: 'pending'
    } : {
      id: Date.now(),
      tracking_id: 'CMP-2026-' + Math.floor(100 + Math.random() * 900),
      date: '২৬ আগস্ট, ২০২৬',
      sender_name: form.sender_name,
      sender_type: form.sender_type,
      category: form.category,
      category_label: catLabels[form.category] || 'সাধারণ',
      subject: form.subject,
      priority: form.priority,
      priority_label: priLabels[form.priority] || 'সাধারণ',
      status: 'pending'
    })
  } catch (e) {
    console.error(e)
  }
  showModal.value = false
}

async function toggleResolve(c: any) {
  const newStatus = c.status === 'resolved' ? 'pending' : 'resolved'
  c.status = newStatus
  await api.patch(`/administration/complaints/${c.id}/toggle-resolve`).catch(() => {})
}

onMounted(loadComplaints)
</script>

<style scoped>
.page-wrapper { max-width: 1320px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.6rem; align-items: center; }

.clear-search-btn { background: none; border: none; font-size: 1.1rem; color: var(--color-text-light); cursor: pointer; padding: 0 0.2rem; }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.mono-font { font-family: monospace; font-size: 0.84rem; }
.sub-text { font-size: 0.74rem; color: var(--color-text-light); }
.fund-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); border-radius: 6px; font-size: 0.78rem; font-weight: 600; }

.type-tag { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
.type-tag.urgent { background: #fee2e2; color: #dc2626; }
.type-tag.medium { background: #fffbeb; color: #b45309; }
.type-tag.low { background: #f3f4f6; color: #4b5563; }

.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--color-border-light); background: var(--color-bg); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-light); transition: all 0.15s ease; }
.action-btn:hover { background: rgba(0, 0, 0, 0.05); color: var(--color-text); }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-ghost { background: transparent; color: var(--color-text); }

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }
</style>
