<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">শিক্ষার্থী প্রমোশন</span>
        <h1>প্রমোশন ও গ্র্যাজুয়েশন</h1>
        <p>শ্রেণি প্রমোশন ও গ্র্যাজুয়েশন পরিচালনা করুন</p>
      </div>
      <div class="page-actions">
        <button class="btn btn-primary" @click="showCreate = true">
          <Icon name="plus" /> নতুন প্রমোশন
        </button>
      </div>
    </div>

    <div class="card">
      <div class="card-inner">
        <div slot="header" class="card-header-inner">
          <div class="search-bar">
            <Icon name="search" />
            <input
              v-model="search"
              type="text"
              placeholder="শিক্ষার্থী নাম বা আইডি দিয়ে খুঁজুন..."
              @input="debounceSearch"
              class="search-input"
            />
          </div>
          <div class="filter-row">
            <select v-model="statusFilter" class="form-select sm">
              <option value="">সব অবস্থা</option>
              <option value="pending">মুলতুবি</option>
              <option value="approved">অনুমোদিত</option>
              <option value="rejected">প্রত্যাখ্যান</option>
            </select>
            <select v-model="classFilter" class="form-select sm ml-2">
              <option value="">সব শ্রেণি</option>
              <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>প্রমোশন তালিকা লোড হচ্ছে...</p>
        </div>

        <table v-else-if="promotions.data?.length" class="data-table">
          <thead>
            <tr>
              <th>শিক্ষার্থী</th>
              <th>আইডি</th>
              <th>পূর্বের শ্রেণি</th>
              <th>পরবর্তী শ্রেণি</th>
              <th>শিক্ষাবর্ষ</th>
              <th>প্রমোশনের তারিখ</th>
              <th>অবস্থা</th>
              <th>কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in promotions.data" :key="p.id">
              <td>
                <strong>{{ p.student?.name?.trim() || 'অজানা' }}</strong>
                <br /><span class="text-muted text-sm">{{ p.student?.roll_no || '—' }}</span>
              </td>
              <td><code class="mono">{{ p.student_id }}</code></td>
              <td>{{ p.fromClass?.name || '—' }}</td>
              <td>
                <span class="badge badge-green">{{ p.toClass?.name || '—' }}</span>
              </td>
              <td>{{ p.academic_year }}</td>
              <td>{{ formatDate(p.promotion_date) }}</td>
              <td>
                <span :class="`badge badge-${statusClass(p.status)}`">
                  {{ formatStatus(p.status) }}
                </span>
              </td>
              <td class="actions-cell">
                <button class="btn btn-icon btn-sm" @click="editPromotion(p)" title="সম্পাদনা">
                  <Icon name="pencil" />
                </button>
                <button class="btn btn-icon btn-sm text-danger" @click="deletePromotion(p)" title="মুছে ফেলুন">
                  <Icon name="trash" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="empty-state">
          <Icon name="table" large />
          <h3>কোনো প্রমোশন নেই</h3>
          <p>এখনও কোনো শিক্ষার্থী প্রমোশন করা হয়নি।</p>
          <button class="btn btn-primary" @click="showCreate = true">প্রথম প্রমোশন যোগ করুন</button>
        </div>

        <div slot="footer" class="card-footer-inner">
          <div class="pagination-info">
            {{ promotions.from }}–{{ promotions.to }} / {{ promotions.total }} রেকর্ড
          </div>
          <div class="pagination" v-if="promotions.last_page > 1">
            <button class="btn btn-outline btn-sm" :disabled="!promotions.prev_page_url" @click="goPage(promotions.current_page - 1)">
              <Icon name="chevron-left" />
            </button>
            <span class="page-info">পৃষ্ঠা {{ promotions.current_page }} / {{ promotions.last_page }}</span>
            <button class="btn btn-outline btn-sm" :disabled="!promotions.next_page_url" @click="goPage(promotions.current_page + 1)">
              <Icon name="chevron-right" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreate" class="modal-overlay" @click.self="showCreate = false">
      <div class="modal" :class="{ 'modal-lg': editingPromotion }">
        <div class="modal-header">
          <h3>{{ editingPromotion ? 'প্রমোশন সম্পাদনা' : 'নতুন প্রমোশন' }}</h3>
          <button class="btn btn-icon" @click="closeModal">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="savePromotion">
            <div class="form-group">
              <label class="form-label">শিক্ষার্থী <span class="required">*</span></label>
              <select v-model="form.student_id" class="form-select">
                <option value="">শিক্ষার্থী নির্বাচন করুন</option>
                <option v-for="s in studentOptions" :key="s.id" :value="s.id">
                  {{ s.name }} ({{ s.roll_no }}) — {{ s.class?.name || '—' }}
                </option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">পূর্বের শ্রেণি <span class="required">*</span></label>
                <select v-model="form.from_class_id" class="form-select">
                  <option value="">শ্রেণি নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">পরবর্তী শ্রেণি <span class="required">*</span></label>
                <select v-model="form.to_class_id" class="form-select">
                  <option value="">শ্রেণি নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">শিক্ষাবর্ষ <span class="required">*</span></label>
                <input v-model="form.academic_year" type="text" class="form-control" placeholder="যেমন: ২০২৫-২০২৬" />
              </div>
              <div class="form-group">
                <label class="form-label">প্রমোশনের তারিখ <span class="required">*</span></label>
                <input v-model="form.promotion_date" type="date" class="form-control" />
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
            <Icon name="spinner" v-if="saving" />
            {{ editingPromotion ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirm -->
    <div v-if="showDelete" class="modal-overlay" @click.self="showDelete = false">
      <div class="modal">
        <div class="modal-header">
          <h3>আপনি কি নিশ্চিত?</h3>
          <button class="btn btn-icon" @click="showDelete = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <p>
            "{{ deleteTarget?.student?.name || 'শিক্ষার্থী' }}" এর প্রমোশন মুছে ফেলতে চান।
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDelete = false">বাতিল</button>
          <button class="btn btn-danger" @click="confirmDelete" :disabled="deleting">
            <Icon name="spinner" v-if="deleting" />
            মুছে ফেলুন
          </button>
        </div>
      </div>
    </div>

    <!-- Bulk Promote Modal -->
    <div v-if="showBulk" class="modal-overlay" @click.self="showBulk = false">
      <div class="modal modal-lg">
        <div class="modal-header">
          <h3>বাল্ক প্রমোশন</h3>
          <button class="btn btn-icon" @click="showBulk = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="doBulkPromote">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">পূর্বের শ্রেণি <span class="required">*</span></label>
                <select v-model="bulkForm.from_class_id" class="form-select">
                  <option value="">নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">পরবর্তী শ্রেণি <span class="required">*</span></label>
                <select v-model="bulkForm.to_class_id" class="form-select">
                  <option value="">নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">শিক্ষাবর্ষ <span class="required">*</span></label>
                <input v-model="bulkForm.academic_year" type="text" class="form-control" placeholder="২০২৫-২০২৬" />
              </div>
              <div class="form-group">
                <label class="form-label">প্রমোশনের তারিখ</label>
                <input v-model="bulkForm.promotion_date" type="date" class="form-control" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">শিক্ষার্থীদের তালিকা</label>
              <div class="bulk-list">
                <div v-for="s in selectedStudents" :key="s.id" class="bulk-chip">
                  <span>{{ s.name }} ({{ s.roll_no }})</span>
                  <button type="button" class="chip-remove" @click="removeStudent(s.id)">
                    <Icon name="close" />
                  </button>
                </div>
                <div v-if="selectedStudents.length === 0" class="bulk-empty">
                  কোনো শিক্ষার্থী নির্বাচন করা হয়নি
                </div>
              </div>
              <select v-model="newStudentId" class="form-select mt-2">
                <option value="">আরও শিক্ষার্থী যোগ করুন</option>
                <option v-for="s in availableStudents" :key="s.id" :value="s.id">
                  {{ s.name }} ({{ s.roll_no }}) — {{ s.class?.name }}
                </option>
              </select>
              <button v-if="newStudentId" class="btn btn-outline btn-sm mt-2" @click="addStudent">
                <Icon name="plus" /> যোগ করুন
              </button>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showBulk = false">বাতিল</button>
          <button class="btn btn-primary" @click="doBulkPromote" :disabled="bulkSaving || selectedStudents.length === 0">
            <Icon name="spinner" v-if="bulkSaving" />
            বাল্ক প্রমোশন করুন ({{ selectedStudents.length }} জন)
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
const classOptions = ref<any[]>([])
const studentOptions = ref<any[]>([])
const search = ref('')
const statusFilter = ref('')
const classFilter = ref('')
const showCreate = ref(false)
const editingPromotion = ref<any>(null)
const form = reactive({ student_id: '', from_class_id: '', to_class_id: '', academic_year: '২০২৫-২০২৬', promotion_date: '', status: 'approved', comments: '' })
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

const availableStudents = computed(() => {
  if (!newStudentId.value) return []
  return studentOptions.value.filter(s => !selectedStudents.value.find(x => x.id === Number(newStudentId.value)))
})

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
    promotions.value = res?.data || { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }
  } catch (err) { console.error('Fetch promotions failed:', err) }
  finally { loading.value = false }
}

async function fetchClasses() {
  try {
    const [classRes, studentRes] = await Promise.all([
      api.get('/academic/classes?per_page=100').catch(() => null),
      api.get('/students?per_page=100').catch(() => null)
    ])
    classOptions.value = (classRes?.data?.data || []).map((c: any) => ({ id: c.id, name: c.name }))
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

function statusClass(s: string) {
  const map: Record<string, string> = { pending: 'yellow', approved: 'green', rejected: 'red' }
  return map[s] || 'gray'
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
.mono { font-family: 'Courier New', monospace; font-size: 0.85rem; }
.text-muted { color: #6c757d; }
.actions-cell { white-space: nowrap; }
.bulk-list { max-height: 200px; overflow-y: auto; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; padding: 0.5rem; }
.bulk-chip { display: flex; align-items: center; gap: 0.5rem; padding: 0.3rem 0.5rem; background: #f5f5f5; border-radius: 6px; margin-bottom: 0.3rem; font-size: 0.9rem; }
.chip-remove { color: #c62828; background: none; border: none; cursor: pointer; padding: 0; font-size: 0.9rem; }
.bulk-empty { color: #6c757d; font-size: 0.85rem; padding: 0.5rem; text-align: center; }
</style>
