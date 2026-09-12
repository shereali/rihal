<template>
  <div class="page-wrapper slide-up-fade">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">শিক্ষার্থী প্রমোশন</span>
        <h1>প্রমোশন ও গ্র্যাজুয়েশন</h1>
        <p class="page-subtitle">শ্রেণি প্রমোশন ও গ্র্যাজুয়েশন পরিচালনা করুন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="openBulk">
          <Icon name="users" /> বাল্ক প্রমোশন
        </button>
        <button class="btn btn-primary" @click="showCreate = true">
          <Icon name="plus" /> নতুন প্রমোশন
        </button>
      </div>
    </div>

    <div class="table-card">
      <div class="toolbar">
        <div class="search-box">
          <Icon name="search" class="search-icon" />
          <input
            v-model="search"
            type="text"
            placeholder="শিক্ষার্থী নাম বা আইডি দিয়ে খুঁজুন..."
            @input="debounceSearch"
          />
          <button v-if="search" @click="search = ''; fetchPromotions(1)" class="clear-search-btn" title="মুছে ফেলুন">
            <Icon name="close" />
          </button>
        </div>
        <div class="select-wrapper">
          <select v-model="statusFilter" class="form-select" @change="fetchPromotions(1)">
            <option value="">সব অবস্থা</option>
            <option value="pending">মুলতুবি</option>
            <option value="approved">অনুমোদিত</option>
            <option value="rejected">প্রত্যাখ্যান</option>
          </select>
        </div>
        <div class="select-wrapper">
          <select v-model="classFilter" class="form-select" @change="fetchPromotions(1)">
            <option value="">সব শ্রেণি</option>
            <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="pagination-info text-muted">
          মোট <strong style="color: var(--color-primary);">{{ promotions.total || 0 }}</strong> টি রেকর্ড
        </div>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>প্রমোশন তালিকা লোড হচ্ছে...</p>
      </div>

      <div v-else-if="promotions.data?.length" class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>শিক্ষার্থী</th>
              <th>আইডি</th>
              <th>পূর্বের শ্রেণি</th>
              <th>পরবর্তী শ্রেণি</th>
              <th>শিক্ষাবর্ষ</th>
              <th>প্রমোশনের তারিখ</th>
              <th>অবস্থা</th>
              <th class="text-right">কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in promotions.data" :key="p.id">
              <td>
                <strong>{{ p.student?.name_bn || p.student?.name_en || p.student?.name?.trim() || 'অজানা' }}</strong>
                <div class="text-muted text-xs" v-if="p.student?.roll_number || p.student?.roll_no">রোল: {{ p.student?.roll_number || p.student?.roll_no }}</div>
              </td>
              <td><code class="mono id-badge">{{ p.student_id }}</code></td>
              <td>{{ p.fromClass?.name_bn || p.fromClass?.name_en || p.fromClass?.name || '—' }}</td>
              <td>
                <span class="badge-pill class-pill">{{ p.toClass?.name_bn || p.toClass?.name_en || p.toClass?.name || '—' }}</span>
              </td>
              <td>{{ p.academic_year }}</td>
              <td>{{ formatDate(p.promotion_date) }}</td>
              <td>
                <span class="status-pill" :class="getStatusBadgeClass(p.status)">
                  <span class="status-dot"></span> {{ formatStatus(p.status) }}
                </span>
              </td>
              <td class="text-right">
                <div class="flex gap-1" style="justify-content: flex-end;">
                  <button class="action-btn edit" @click="editPromotion(p)" title="সম্পাদনা">
                    <Icon name="pencil" />
                  </button>
                  <button class="action-btn delete" @click="deletePromotion(p)" title="মুছে ফেলুন">
                    <Icon name="delete" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="empty-state">
        <h3>কোনো প্রমোশন পাওয়া যায়নি</h3>
        <p class="text-muted">এখনও কোনো শিক্ষার্থী প্রমোশন করা হয়নি।</p>
        <button class="btn btn-primary mt-2" @click="showCreate = true">প্রথম প্রমোশন যোগ করুন</button>
      </div>

      <div v-if="promotions.last_page > 1" class="pagination-wrapper">
        <div class="pagination-info">{{ promotions.from }}–{{ promotions.to }} / মোট {{ promotions.total }} রেকর্ড</div>
        <div class="pagination-numbers">
          <button class="pagination-btn" :disabled="!promotions.prev_page_url" @click="goPage(promotions.current_page - 1)">
            <Icon name="chevron-left" /> পূর্ববর্তী
          </button>
          <span class="page-info">পৃষ্ঠা {{ promotions.current_page }} / {{ promotions.last_page }}</span>
          <button class="pagination-btn" :disabled="!promotions.next_page_url" @click="goPage(promotions.current_page + 1)">
            পরবর্তী <Icon name="chevron-right" />
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreate" class="modal-overlay" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ editingPromotion ? 'প্রমোশন সম্পাদনা' : 'নতুন প্রমোশন' }}</h3>
          <button class="action-btn" @click="closeModal">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="savePromotion">
            <div class="form-group">
              <label class="form-label">শিক্ষার্থী <span class="required" style="color: var(--color-error);">*</span></label>
              <select v-model="form.student_id" class="form-select" required>
                <option value="">শিক্ষার্থী নির্বাচন করুন</option>
                <option v-for="s in studentOptions" :key="s.id" :value="s.id">
                  {{ s.name }} ({{ s.roll_no }}) — {{ s.class?.name || '—' }}
                </option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">পূর্বের শ্রেণি <span class="required" style="color: var(--color-error);">*</span></label>
                <select v-model="form.from_class_id" class="form-select" required>
                  <option value="">শ্রেণি নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">পরবর্তী শ্রেণি <span class="required" style="color: var(--color-error);">*</span></label>
                <select v-model="form.to_class_id" class="form-select" required>
                  <option value="">শ্রেণি নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">শিক্ষাবর্ষ <span class="required" style="color: var(--color-error);">*</span></label>
                <input v-model="form.academic_year" type="text" class="form-control" placeholder="যেমন: ২০২৫-২০২৬" required />
              </div>
              <div class="form-group">
                <label class="form-label">প্রমোশনের তারিখ <span class="required" style="color: var(--color-error);">*</span></label>
                <input v-model="form.promotion_date" type="date" class="form-control" required />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">অবস্থা</label>
              <select v-model="form.status" class="form-select">
                <option value="pending">মুলতুবি</option>
                <option value="approved">অনুমোদিত</option>
                <option value="rejected">প্রত্যাখ্যান</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">মন্তব্য</label>
              <textarea v-model="form.comments" class="form-control" rows="2" placeholder="যদি থাকে"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="closeModal">বাতিল</button>
          <button class="btn btn-primary" @click="savePromotion" :disabled="saving">
            <Icon name="loader" v-if="saving" />
            {{ editingPromotion ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="showDelete" class="modal-overlay" @click.self="showDelete = false">
      <div class="modal-card" style="max-width: 440px;">
        <div class="modal-header">
          <h3>আপনি কি নিশ্চিত?</h3>
          <button class="action-btn" @click="showDelete = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <p>
            "<strong>{{ deleteTarget?.student?.name_bn || deleteTarget?.student?.name || 'শিক্ষার্থী' }}</strong>" এর প্রমোশন রেকর্ড মুছে ফেলতে চান?
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDelete = false">বাতিল</button>
          <button class="btn btn-danger" @click="confirmDelete" :disabled="deleting">
            <Icon name="loader" v-if="deleting" />
            মুছে ফেলুন
          </button>
        </div>
      </div>
    </div>

    <!-- Bulk Promote Modal -->
    <div v-if="showBulk" class="modal-overlay" @click.self="showBulk = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>বাল্ক প্রমোশন</h3>
          <button class="action-btn" @click="showBulk = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="doBulkPromote">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">পূর্বের শ্রেণি <span class="required" style="color: var(--color-error);">*</span></label>
                <select v-model="bulkForm.from_class_id" class="form-select" required>
                  <option value="">নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">পরবর্তী শ্রেণি <span class="required" style="color: var(--color-error);">*</span></label>
                <select v-model="bulkForm.to_class_id" class="form-select" required>
                  <option value="">নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">শিক্ষাবর্ষ <span class="required" style="color: var(--color-error);">*</span></label>
                <input v-model="bulkForm.academic_year" type="text" class="form-control" placeholder="২০২৫-২০২৬" required />
              </div>
              <div class="form-group">
                <label class="form-label">প্রমোশনের তারিখ</label>
                <input v-model="bulkForm.promotion_date" type="date" class="form-control" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">শিক্ষার্থীদের নির্বাচন</label>
              <div class="flex gap-1 mb-2">
                <select v-model="newStudentId" class="form-select" style="flex: 1;">
                  <option value="">শিক্ষার্থী যোগ করুন...</option>
                  <option v-for="s in studentOptions" :key="s.id" :value="s.id">
                    {{ s.name }} ({{ s.roll_no }})
                  </option>
                </select>
                <button type="button" class="btn btn-outline" @click="addStudent" :disabled="!newStudentId">
                  যোগ করুন
                </button>
              </div>
              <div class="bulk-list">
                <div v-for="s in selectedStudents" :key="s.id" class="bulk-chip">
                  <span>{{ s.name }} ({{ s.roll_no }})</span>
                  <button type="button" class="chip-remove" @click="removeStudent(s.id)" title="মুছুন">
                    <Icon name="close" size="14" />
                  </button>
                </div>
                <div v-if="selectedStudents.length === 0" class="bulk-empty">
                  কোনো শিক্ষার্থী নির্বাচিত হয়নি
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showBulk = false">বাতিল</button>
          <button class="btn btn-primary" @click="doBulkPromote" :disabled="bulkSaving || selectedStudents.length === 0">
            <Icon name="loader" v-if="bulkSaving" />
            বাল্ক প্রমোশন সম্পাদন করুন ({{ selectedStudents.length }})
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import Icon from '~/components/Icon.vue'

const api = useApiClient()

const loading = ref(true)
const promotions = ref<any>({ data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })
const search = ref('')
const statusFilter = ref('')
const classFilter = ref('')
const classOptions = ref<any[]>([])
const studentOptions = ref<any[]>([])

const showCreate = ref(false)
const editingPromotion = ref<any>(null)
const form = reactive({
  student_id: '',
  from_class_id: '',
  to_class_id: '',
  academic_year: '২০২৫-২০২৬',
  promotion_date: '',
  status: 'approved',
  comments: '',
})
const saving = ref(false)
const showDelete = ref(false)
const deleteTarget = ref<any>(null)
const deleting = ref(false)
const showBulk = ref(false)
const bulkForm = reactive({ from_class_id: '', to_class_id: '', academic_year: '২০২৫-২০২৬', promotion_date: '' })
const bulkSaving = ref(false)
const selectedStudents = ref<any[]>([])
const newStudentId = ref('')
let searchTimeout: any = null
const per_page = 15

async function fetchPromotions(page = 1) {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: String(page),
      per_page: String(per_page),
      ...(search.value ? { search: search.value } : {}),
      ...(statusFilter.value ? { status: statusFilter.value } : {}),
      ...(classFilter.value ? { class_id: classFilter.value } : {})
    })
    const res = await api.get(`/promotions?${params}`).catch(() => null)
    promotions.value = res?.data?.data || res?.data || { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }
  } catch (err) { console.error('Fetch promotions failed:', err) }
  finally { loading.value = false }
}

async function fetchClasses() {
  try {
    const [classRes, studentRes] = await Promise.all([
      api.get('/academic/classes?per_page=100').catch(() => null),
      api.get('/students?per_page=100').catch(() => null)
    ])
    const classList = classRes?.data?.data?.data || classRes?.data?.data || []
    classOptions.value = classList.map((c: any) => ({ id: c.id, name: c.name_bn || c.name_en || c.name }))
    studentOptions.value = (studentRes?.data?.data?.data || studentRes?.data?.data || []).map((s: any) => ({
      id: s.id,
      name: s.name_bn || s.name_en,
      roll_no: s.roll_number || s.id,
      class: s.academic_class
    }))
  } catch (err) { console.error('Fetch classes failed:', err) }
}

function debounceSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchPromotions(1), 300)
}

function goPage(page: number) {
  if (page < 1 || page > promotions.value.last_page) return
  fetchPromotions(page)
}

function editPromotion(p: any) {
  editingPromotion.value = p
  form.student_id = String(p.student_id || '')
  form.from_class_id = String(p.from_class_id || '')
  form.to_class_id = String(p.to_class_id || '')
  form.academic_year = p.academic_year || ''
  form.promotion_date = p.promotion_date ? toDateInput(p.promotion_date) : ''
  form.status = p.status || 'approved'
  form.comments = p.comments || ''
  showCreate.value = true
}

function closeModal() {
  showCreate.value = false
  editingPromotion.value = null
  form.student_id = ''
  form.from_class_id = ''
  form.to_class_id = ''
  form.academic_year = '২০২৫-২০২৬'
  form.promotion_date = ''
  form.status = 'approved'
  form.comments = ''
}

function toDateInput(date: string) {
  if (!date) return ''
  try { return new Date(date).toISOString().split('T')[0] } catch { return date }
}

function formatDate(date: string) {
  if (!date) return '—'
  try { return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) } catch { return date }
}

function getStatusBadgeClass(status: string) {
  if (status === 'approved') return 'badge-approved'
  if (status === 'pending') return 'badge-pending'
  if (status === 'rejected') return 'badge-rejected'
  return 'badge-pending'
}

function formatStatus(s: string) {
  const map: Record<string, string> = { pending: 'মুলতুবি', approved: 'অনুমোদিত', rejected: 'প্রত্যাখ্যান' }
  return map[s] || s
}

async function savePromotion() {
  saving.value = true
  try {
    const url = editingPromotion.value ? `/promotions/${editingPromotion.value.id}` : '/promotions'
    const body = {
      ...form,
      student_id: Number(form.student_id),
      from_class_id: Number(form.from_class_id),
      to_class_id: Number(form.to_class_id)
    }
    if (editingPromotion.value) {
      await api.put(url, body).catch(() => null)
    } else {
      await api.post(url, body).catch(() => null)
    }
    closeModal()
    fetchPromotions(promotions.value.current_page)
  } catch (err) { console.error('Save failed:', err) }
  finally { saving.value = false }
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`/promotions/${deleteTarget.value.id}`).catch(() => null)
    showDelete.value = false
    deleteTarget.value = null
    fetchPromotions(promotions.value.current_page)
  } catch (err) { console.error('Delete failed:', err) }
  finally { deleting.value = false }
}

function deletePromotion(p: any) {
  deleteTarget.value = p
  showDelete.value = true
}

function openBulk() {
  showBulk.value = true
  selectedStudents.value = []
  newStudentId.value = ''
  bulkForm.from_class_id = ''
  bulkForm.to_class_id = ''
  bulkForm.academic_year = '২০২৫-২০২৬'
  bulkForm.promotion_date = ''
}

function addStudent() {
  if (!newStudentId.value) return
  const s = studentOptions.value.find(x => x.id === Number(newStudentId.value))
  if (s && !selectedStudents.value.find(x => x.id === s.id)) {
    selectedStudents.value.push(s)
  }
  newStudentId.value = ''
}

function removeStudent(id: number) {
  selectedStudents.value = selectedStudents.value.filter(s => s.id !== Number(id))
}

async function doBulkPromote() {
  if (selectedStudents.value.length === 0) return
  bulkSaving.value = true
  try {
    const ids = selectedStudents.value.map(s => s.id)
    await api.post('/promotions/bulk-promote', {
      ...bulkForm,
      student_ids: ids,
      from_class_id: Number(bulkForm.from_class_id),
      to_class_id: Number(bulkForm.to_class_id)
    }).catch(() => null)
    showBulk.value = false
    selectedStudents.value = []
    fetchPromotions(promotions.value.current_page)
    alert(`${ids.length} জন শিক্ষার্থীর বাল্ক প্রমোশন সফলভাবে সম্পন্ন হয়েছে!`)
  } catch (err) { console.error('Bulk failed:', err) }
  finally { bulkSaving.value = false }
}

onMounted(() => {
  fetchPromotions()
  fetchClasses()
})
</script>

<style scoped lang="scss">
.id-badge {
  background: var(--color-bg-muted);
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  border: 1px solid var(--color-border-light);
  font-family: monospace;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text);
}

.badge-pill {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 99px;
  font-size: var(--text-xs);
  font-weight: var(--weight-semibold);
  font-family: var(--font-bn);

  &.class-pill {
    background: rgba(20, 80, 50, 0.1);
    color: var(--color-primary);
  }
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.modal-body {
  padding: 1.25rem 1.5rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border-light);
  background: var(--color-bg-muted);
  border-bottom-left-radius: var(--radius-lg);
  border-bottom-right-radius: var(--radius-lg);
}

.bulk-list {
  max-height: 180px;
  overflow-y: auto;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 0.6rem;
  background: var(--color-bg);
}

.bulk-chip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.35rem 0.65rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-sm);
  margin-bottom: 0.35rem;
  font-size: var(--text-sm);
  font-family: var(--font-bn);
}

.chip-remove {
  color: var(--color-error);
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.1rem;
  display: flex;
  align-items: center;

  &:hover {
    opacity: 0.75;
  }
}

.bulk-empty {
  color: var(--color-text-muted);
  font-size: var(--text-sm);
  padding: 0.8rem;
  text-align: center;
  font-family: var(--font-bn);
}

.pagination-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border-light);
  background: var(--color-bg-card);
}

.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.45rem 0.85rem;
  border-radius: var(--radius-sm);
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  color: var(--color-text);
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  font-weight: var(--weight-medium);
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover:not(:disabled) {
    border-color: var(--color-primary);
    color: var(--color-primary);
    background: var(--color-primary-50);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: var(--color-bg-muted);
  }
}

.pagination-numbers {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.page-info {
  font-size: var(--text-sm);
  color: var(--color-text-light);
  font-family: var(--font-bn);
}
</style>
