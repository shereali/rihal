<template>
  <div class="page-wrapper slide-up-fade">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">সার্টিফিকেশন</span>
        <h1>সার্টিফিকেট ও পাঠ্যক্রম</h1>
        <p class="page-subtitle">সার্টিফিকেট টেমপলেট, প্রকাশনা, পাঠ্যক্রম ও বই পরিচালনা করুন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="activeTab = 'templates'; showCreate = true">
          <Icon name="plus" /> নতুন টেমপলেট
        </button>
        <button class="btn btn-outline" @click="activeTab = 'issue'; showIssue = true">
          <Icon name="tag" /> সার্টিফিকেট প্রকাশ করুন
        </button>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs-nav mb-3">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="['tab-btn', { active: activeTab === tab.key }]"
        @click="activeTab = tab.key"
      >
        <Icon :name="tab.icon" />
        {{ tab.label }}
      </button>
    </div>

    <!-- Templates Tab -->
    <div v-if="activeTab === 'templates'" class="table-card">
      <div class="toolbar">
        <div class="search-box">
          <Icon name="search" class="search-icon" />
          <input v-model="templateSearch" type="text" placeholder="টেমপলেট খুঁজুন..." @input="debounceTemplateSearch" />
          <button v-if="templateSearch" @click="templateSearch = ''; fetchTemplates(1)" class="clear-search-btn" title="মুছে ফেলুন">
            <Icon name="close" />
          </button>
        </div>
        <div class="select-wrapper">
          <select v-model="templateTypeFilter" class="form-select" @change="fetchTemplates(1)">
            <option value="">সব ধরন</option>
            <option value="annual">বার্ষিক</option>
            <option value="transfer">হস্তান্তর</option>
            <option value="sanction">অনুমোদন</option>
            <option value="conduct">আচরণ</option>
            <option value="others">অন্যান্য</option>
          </select>
        </div>
        <div class="select-wrapper">
          <select v-model="templateClassFilter" class="form-select" @change="fetchTemplates(1)">
            <option value="">সব শ্রেণি</option>
            <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="pagination-info text-muted">
          মোট <strong style="color: var(--color-primary);">{{ templates.total || 0 }}</strong> টি টেমপলেট
        </div>
      </div>

      <div v-if="templateLoading" class="loading-state">
        <div class="spinner"></div>
        <p>টেমপলেট লোড হচ্ছে...</p>
      </div>

      <div v-else-if="templates.data?.length" class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>শিরোনাম</th>
              <th>ধরন</th>
              <th>শ্রেণি</th>
              <th>বিষয়</th>
              <th>অবস্থা</th>
              <th class="text-right">কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in templates.data" :key="t.id">
              <td><strong>{{ t.title }}</strong></td>
              <td><span class="badge-pill type-pill">{{ formatTemplateType(t.template_type) }}</span></td>
              <td>{{ t.classRelation?.name_bn || t.classRelation?.name_en || t.classRelation?.name || '—' }}</td>
              <td>{{ t.subjectRelation?.name_bn || t.subjectRelation?.name_en || t.subjectRelation?.name || '—' }}</td>
              <td>
                <span class="status-pill" :class="t.is_active ? 'badge-approved' : 'badge-rejected'">
                  <span class="status-dot"></span> {{ t.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                </span>
              </td>
              <td class="text-right">
                <div class="flex gap-1" style="justify-content: flex-end;">
                  <button class="action-btn edit" title="সম্পাদনা" @click="editTemplate(t)">
                    <Icon name="pencil" />
                  </button>
                  <button class="action-btn delete" title="মুছে ফেলুন" @click="deleteTemplate(t)">
                    <Icon name="delete" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="empty-state">
        <h3>কোনো টেমপলেট পাওয়া যায়নি</h3>
        <p class="text-muted">এখনও কোনো সার্টিফিকেট টেমপলেট তৈরি করা হয়নি।</p>
        <button class="btn btn-primary mt-2" @click="showCreate = true">প্রথম টেমপলেট তৈরি করুন</button>
      </div>

      <div v-if="templates.last_page > 1" class="pagination-wrapper">
        <div class="pagination-info">{{ templates.from }}–{{ templates.to }} / মোট {{ templates.total }} টেমপলেট</div>
        <div class="pagination-numbers">
          <button class="pagination-btn" :disabled="!templates.prev_page_url" @click="goTemplatePage(templates.current_page - 1)">
            <Icon name="chevron-left" /> পূর্ববর্তী
          </button>
          <span class="page-info">পৃষ্ঠা {{ templates.current_page }} / {{ templates.last_page }}</span>
          <button class="pagination-btn" :disabled="!templates.next_page_url" @click="goTemplatePage(templates.current_page + 1)">
            পরবর্তী <Icon name="chevron-right" />
          </button>
        </div>
      </div>
    </div>

    <!-- Issue Certificates Tab -->
    <div v-if="activeTab === 'issue'" class="table-card">
      <div class="toolbar">
        <div class="search-box">
          <Icon name="search" class="search-icon" />
          <input v-model="issueSearch" type="text" placeholder="শিক্ষার্থী নাম দিয়ে খুঁজুন..." @input="debounceIssueSearch" />
          <button v-if="issueSearch" @click="issueSearch = ''; fetchIssueCerts(1)" class="clear-search-btn" title="মুছে ফেলুন">
            <Icon name="close" />
          </button>
        </div>
        <div class="select-wrapper">
          <select v-model="issueClassFilter" class="form-select" @change="fetchIssueCerts(1)">
            <option value="">সব শ্রেণি</option>
            <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="pagination-info text-muted">
          মোট <strong style="color: var(--color-primary);">{{ issuedCerts.total || 0 }}</strong> টি সনদ
        </div>
      </div>

      <div v-if="issueLoading" class="loading-state">
        <div class="spinner"></div>
        <p>সার্টিফিকেট প্রকাশনা লোড হচ্ছে...</p>
      </div>

      <div v-else-if="issuedCerts.data?.length" class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>সার্টিফিকেট নং</th>
              <th>শিক্ষার্থী</th>
              <th>আইডি</th>
              <th>শ্রেণি</th>
              <th>টেমপলেট</th>
              <th>প্রকাশের তারিখ</th>
              <th class="text-right">কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in issuedCerts.data" :key="c.id">
              <td><code class="mono cert-badge">{{ c.certificate_number }}</code></td>
              <td><strong>{{ c.studentRelation?.name_bn || c.studentRelation?.name_en || c.studentRelation?.name?.trim() || 'অজানা' }}</strong></td>
              <td><code class="mono">{{ c.student_id }}</code></td>
              <td>{{ c.classRelation?.name_bn || c.classRelation?.name_en || c.classRelation?.name || '—' }}</td>
              <td>{{ c.templateRelation?.title || '—' }}</td>
              <td>{{ formatDate(c.issue_date) }}</td>
              <td class="text-right">
                <div class="flex gap-1" style="justify-content: flex-end;">
                  <button class="action-btn edit" title="বিবরণ দেখুন" @click="viewIssue(c)">
                    <Icon name="eye" />
                  </button>
                  <button class="action-btn delete" title="মুছে ফেলুন" @click="deleteIssue(c)">
                    <Icon name="delete" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="empty-state">
        <h3>কোনো প্রকাশনা নেই</h3>
        <p class="text-muted">এখনও কোনো শিক্ষার্থীকে সার্টিফিকেট প্রদান করা হয়নি।</p>
        <button class="btn btn-outline mt-2" @click="showIssue = true">প্রথম সার্টিফিকেট প্রকাশ করুন</button>
      </div>

      <div v-if="issuedCerts.last_page > 1" class="pagination-wrapper">
        <div class="pagination-info">{{ issuedCerts.from }}–{{ issuedCerts.to }} / মোট {{ issuedCerts.total }} রেকর্ড</div>
        <div class="pagination-numbers">
          <button class="pagination-btn" :disabled="!issuedCerts.prev_page_url" @click="goIssuePage(issuedCerts.current_page - 1)">
            <Icon name="chevron-left" /> পূর্ববর্তী
          </button>
          <span class="page-info">পৃষ্ঠা {{ issuedCerts.current_page }} / {{ issuedCerts.last_page }}</span>
          <button class="pagination-btn" :disabled="!issuedCerts.next_page_url" @click="goIssuePage(issuedCerts.current_page + 1)">
            পরবর্তী <Icon name="chevron-right" />
          </button>
        </div>
      </div>
    </div>

    <!-- Syllabus & Books Tab -->
    <div v-if="activeTab === 'syllabus'" class="table-card p-4" style="padding: 1.5rem;">
      <div class="flex-between mb-3">
        <div>
          <h3 style="font-weight: 700; font-size: 1.15rem; color: var(--color-text);">পাঠ্যক্রম ও বই তালিকা</h3>
          <p class="text-muted text-sm">বিভিন্ন শ্রেণির জন্য অনুমোদিত বিষয় ও পাঠ্যসূচি</p>
        </div>
      </div>
      <div class="syllabus-grid">
        <div v-for="sub in subjects" :key="sub.id" class="syllabus-card">
          <div class="syllabus-card-header">
            <h4>{{ sub.name_bn || sub.name_en || sub.name }}</h4>
            <span class="code-badge">{{ sub.code || '—' }}</span>
          </div>
          <div class="syllabus-card-body">
            <div class="syllabus-field">
              <span class="label">শ্রেণি:</span>
              <span class="value">{{ Array.isArray(sub.classes) ? (sub.classes.map(c => c.name_bn || c.name_en || c.name).join(', ') || '—') : (sub.classes?.name_bn || sub.classes?.name_en || sub.classes?.name || '—') }}</span>
            </div>
          </div>
        </div>
        <div v-if="subjects.length === 0" class="empty-state">
          <h3>কোনো পাঠ্যক্রম পাওয়া যায়নি</h3>
        </div>
      </div>
    </div>

    <!-- Create/Edit Template Modal -->
    <div v-if="showCreate" class="modal-overlay" @click.self="showCreate = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ editingTemplate ? 'টেমপলেট সম্পাদনা' : 'নতুন টেমপলেট' }}</h3>
          <button class="action-btn" @click="showCreate = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveTemplate">
            <div class="form-group">
              <label class="form-label">শিরোনাম <span class="required" style="color: var(--color-error);">*</span></label>
              <input v-model="templateForm.title" type="text" class="form-control" placeholder="যেমন: বার্ষিক পরীক্ষা ফলাফল সার্টিফিকেট" required />
            </div>
            <div class="form-group">
              <label class="form-label">ধরন <span class="required" style="color: var(--color-error);">*</span></label>
              <select v-model="templateForm.template_type" class="form-select" required>
                <option value="annual">বার্ষিক</option>
                <option value="transfer">হস্তান্তর</option>
                <option value="sanction">অনুমোদন</option>
                <option value="conduct">আচরণ</option>
                <option value="others">অন্যান্য</option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">শ্রেণি</label>
                <select v-model="templateForm.class_id" class="form-select">
                  <option value="">নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">বিষয়</label>
                <select v-model="templateForm.subject_id" class="form-select">
                  <option value="">নির্বাচন করুন</option>
                  <option v-for="s in subjectOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">টেমপলেট ডেটা (JSON)</label>
              <textarea v-model="templateForm.template_data_json" class="form-control font-mono" rows="3" placeholder='{"header": "...", "footer": "..."}'></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">সক্রিয়</label>
              <div class="toggle-row">
                <label class="toggle">
                  <input type="checkbox" v-model="templateForm.is_active" />
                  <span class="toggle-slider"></span>
                </label>
                <span class="ml-2 text-muted" style="margin-left: 0.5rem;">{{ templateForm.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showCreate = false">বাতিল</button>
          <button class="btn btn-primary" @click="saveTemplate" :disabled="templateSaving">
            <Icon name="loader" v-if="templateSaving" />
            {{ editingTemplate ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Issue Certificate Modal -->
    <div v-if="showIssue" class="modal-overlay" @click.self="showIssue = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>সার্টিফিকেট প্রকাশ করুন</h3>
          <button class="action-btn" @click="showIssue = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="issueCertificate">
            <div class="form-group">
              <label class="form-label">শিক্ষার্থী <span class="required" style="color: var(--color-error);">*</span></label>
              <select v-model="issueForm.student_id" class="form-select" required>
                <option value="">শিক্ষার্থী নির্বাচন করুন</option>
                <option v-for="s in studentOptions" :key="s.id" :value="s.id">
                  {{ s.name }} ({{ s.roll_no }}) — {{ s.class?.name_bn || s.class?.name_en || s.class?.name || s.class || '' }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">টেমপলেট <span class="required" style="color: var(--color-error);">*</span></label>
              <select v-model="issueForm.template_id" class="form-select" required>
                <option value="">টেমপলেট নির্বাচন করুন</option>
                <option v-for="t in templateOptions" :key="t.id" :value="t.id">{{ t.title }}</option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">শ্রেণি</label>
                <select v-model="issueForm.class_id" class="form-select">
                  <option value="">নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">বিষয়</label>
                <select v-model="issueForm.subject_id" class="form-select">
                  <option value="">নির্বাচন করুন</option>
                  <option v-for="s in subjectOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">প্রকাশের তারিখ <span class="required" style="color: var(--color-error);">*</span></label>
                <input v-model="issueForm.issue_date" type="date" class="form-control" required />
              </div>
              <div class="form-group">
                <label class="form-label">অনুমোদনকারী</label>
                <input v-model="issueForm.authorized_by" type="text" class="form-control" placeholder="প্রধান শিক্ষক" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">মন্তব্য</label>
              <textarea v-model="issueForm.remarks" class="form-control" rows="2" placeholder="যদি থাকে"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showIssue = false">বাতিল</button>
          <button class="btn btn-primary" @click="issueCertificate" :disabled="issueSaving">
            <Icon name="loader" v-if="issueSaving" />
            প্রকাশ করুন
          </button>
        </div>
      </div>
    </div>

    <!-- View Issue Modal -->
    <div v-if="showViewIssue" class="modal-overlay" @click.self="showViewIssue = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>সার্টিফিকেটের বিবরণ</h3>
          <button class="action-btn" @click="showViewIssue = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <dl class="info-list">
            <div class="info-row"><dt>সার্টিফিকেট নং</dt><dd><code class="cert-badge">{{ viewIssueData?.certificate_number }}</code></dd></div>
            <div class="info-row"><dt>শিক্ষার্থী</dt><dd>{{ viewIssueData?.studentRelation?.name_bn || viewIssueData?.studentRelation?.name_en || viewIssueData?.studentRelation?.name || '—' }}</dd></div>
            <div class="info-row"><dt>শ্রেণি</dt><dd>{{ viewIssueData?.classRelation?.name_bn || viewIssueData?.classRelation?.name_en || viewIssueData?.classRelation?.name || '—' }}</dd></div>
            <div class="info-row"><dt>টেমপলেট</dt><dd>{{ viewIssueData?.templateRelation?.title || '—' }}</dd></div>
            <div class="info-row"><dt>প্রকাশের তারিখ</dt><dd>{{ viewIssueData?.issue_date ? formatDate(viewIssueData.issue_date) : '—' }}</dd></div>
            <div class="info-row"><dt>অনুমোদনকারী</dt><dd>{{ viewIssueData?.authorized_by || '—' }}</dd></div>
          </dl>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showViewIssue = false">বন্ধ করুন</button>
        </div>
      </div>
    </div>

    <!-- In-App Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm && deleteConfirmItem" class="modal-overlay" @click.self="showDeleteConfirm = false">
      <div class="modal-card" style="max-width: 440px;">
        <div class="modal-header">
          <h3>{{ deleteConfirmType === 'template' ? 'টেমপলেট মুছে ফেলা' : 'সার্টিফিকেট মুছে ফেলা' }}</h3>
          <button class="action-btn" @click="showDeleteConfirm = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body" style="padding: 1.25rem;">
          <p v-if="deleteConfirmType === 'template'">
            আপনি কি নিশ্চিত যে <strong>"{{ deleteConfirmItem.title }}"</strong> টেমপলেটটি মুছে ফেলতে চান?
          </p>
          <p v-else>
            আপনি কি নিশ্চিত যে <strong>{{ deleteConfirmItem.certificate_number }}</strong> সনদটি মুছে ফেলতে চান?
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDeleteConfirm = false" :disabled="deleteConfirming">বাতিল</button>
          <button class="btn btn-danger" @click="executeConfirmedDelete" :disabled="deleteConfirming">
            <Icon name="loader" v-if="deleteConfirming" />
            মুছে ফেলুন
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import Icon from '~/components/Icon.vue'

const api = useApiClient()

const activeTab = ref('templates')
const tabs = [
  { key: 'templates', label: 'টেমপলেট', icon: 'book' },
  { key: 'issue', label: 'প্রকাশনা', icon: 'tag' },
  { key: 'syllabus', label: 'পাঠ্যক্রম ও বই', icon: 'book' },
]

const templateLoading = ref(true)
const templates = ref<any>({ data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })
const templateSearch = ref('')
const templateTypeFilter = ref('')
const templateClassFilter = ref('')
const classOptions = ref<any[]>([])
const subjectOptions = ref<any[]>([])
const templateOptions = ref<any[]>([])
const studentOptions = ref<any[]>([])
const subjects = ref<any[]>([])

const showCreate = ref(false)
const editingTemplate = ref<any>(null)
const templateForm = reactive({ title: '', template_type: 'annual', class_id: '', subject_id: '', template_data_json: '{}', is_active: true })

const showDeleteConfirm = ref(false)
const deleteConfirmItem = ref<any>(null)
const deleteConfirmType = ref<'template' | 'issue'>('template')
const deleteConfirming = ref(false)
const templateSaving = ref(false)

const issueLoading = ref(true)
const issuedCerts = ref<any>({ data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })
const issueSearch = ref('')
const issueClassFilter = ref('')
const showIssue = ref(false)
const issueForm = reactive({ student_id: '', template_id: '', class_id: '', subject_id: '', issue_date: '', authorized_by: '', remarks: '' })
const issueSaving = ref(false)

const showViewIssue = ref(false)
const viewIssueData = ref<any>(null)

let templateTimeout: any = null
let issueTimeout: any = null

async function fetchClasses() {
  try {
    const res = await api.get('/academic/classes?per_page=100').catch(() => null)
    const list = res?.data?.data?.data || res?.data?.data || []
    classOptions.value = list.map((c: any) => ({ id: c.id, name: c.name_bn || c.name_en || c.name }))
  } catch (err) { console.error(err) }
}

async function fetchSubjects() {
  try {
    const res = await api.get('/certificates/syllabus').catch(() => null)
    const list = res?.data?.data || res?.data || []
    subjectOptions.value = list.map((s: any) => ({ id: s.id, name: s.name_bn || s.name_en || s.name, code: s.code }))
    subjects.value = list
  } catch (err) { console.error(err) }
}

async function fetchTemplates(page = 1) {
  templateLoading.value = true
  try {
    const params = new URLSearchParams({
      page: String(page),
      per_page: '15',
      ...(templateSearch.value ? { search: templateSearch.value } : {}),
      ...(templateTypeFilter.value ? { type: templateTypeFilter.value } : {}),
      ...(templateClassFilter.value ? { class_id: templateClassFilter.value } : {})
    })
    const res = await api.get(`/certificate-templates?${params}`).catch(() => null)
    templates.value = res?.data?.data || res?.data || { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }
    templateOptions.value = (templates.value.data || []).map((t: any) => ({ id: t.id, title: t.title }))
  } catch (err) { console.error(err) }
  finally { templateLoading.value = false }
}

async function fetchIssueCerts(page = 1) {
  issueLoading.value = true
  try {
    const params = new URLSearchParams({
      page: String(page),
      per_page: '15',
      ...(issueSearch.value ? { search: issueSearch.value } : {}),
      ...(issueClassFilter.value ? { class_id: issueClassFilter.value } : {})
    })
    const res = await api.get(`/certificates?${params}`).catch(() => null)
    issuedCerts.value = res?.data?.data || res?.data || { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }
  } catch (err) { console.error(err) }
  finally { issueLoading.value = false }
}

async function fetchStudents() {
  try {
    const res = await api.get('/students?per_page=100').catch(() => null)
    studentOptions.value = (res?.data?.data?.data || res?.data?.data || []).map((s: any) => ({
      id: s.id,
      name: s.name_bn || s.name_en,
      roll_no: s.roll_number || s.id,
      class: s.academic_class
    }))
  } catch (err) { console.error(err) }
}

function debounceTemplateSearch() {
  clearTimeout(templateTimeout)
  templateTimeout = setTimeout(() => fetchTemplates(1), 300)
}

function debounceIssueSearch() {
  clearTimeout(issueTimeout)
  issueTimeout = setTimeout(() => fetchIssueCerts(1), 300)
}

function goTemplatePage(page: number) {
  if (page < 1 || page > templates.value.last_page) return
  fetchTemplates(page)
}

function goIssuePage(page: number) {
  if (page < 1 || page > issuedCerts.value.last_page) return
  fetchIssueCerts(page)
}

function editTemplate(t: any) {
  editingTemplate.value = t
  templateForm.title = t.title || ''
  templateForm.template_type = t.template_type || 'annual'
  templateForm.class_id = String(t.class_id || '')
  templateForm.subject_id = String(t.subject_id || '')
  templateForm.template_data_json = JSON.stringify(t.template_data || {}, null, 2)
  templateForm.is_active = t.is_active ?? true
  showCreate.value = true
}

async function saveTemplate() {
  templateSaving.value = true
  try {
    const url = editingTemplate.value ? `/certificate-templates/${editingTemplate.value.id}` : '/certificate-templates'
    const body = {
      ...templateForm,
      class_id: Number(templateForm.class_id) || null,
      subject_id: Number(templateForm.subject_id) || null,
      template_data: templateForm.template_data_json ? JSON.parse(templateForm.template_data_json) : {},
    }
    await (editingTemplate.value ? api.put(url, body) : api.post(url, body)).catch(() => null)
    showCreate.value = false
    editingTemplate.value = null
    fetchTemplates(templates.value.current_page)
  } catch (err) { console.error('Template save failed:', err) }
  finally { templateSaving.value = false }
}

function deleteTemplate(t: any) {
  deleteConfirmItem.value = t
  deleteConfirmType.value = 'template'
  showDeleteConfirm.value = true
}

function viewIssue(c: any) {
  viewIssueData.value = c
  showViewIssue.value = true
}

function deleteIssue(c: any) {
  deleteConfirmItem.value = c
  deleteConfirmType.value = 'issue'
  showDeleteConfirm.value = true
}

async function executeConfirmedDelete() {
  if (!deleteConfirmItem.value) return
  deleteConfirming.value = true
  try {
    if (deleteConfirmType.value === 'template') {
      await api.delete(`/certificate-templates/${deleteConfirmItem.value.id}`).catch(() => null)
      showDeleteConfirm.value = false
      deleteConfirmItem.value = null
      fetchTemplates(templates.value.current_page)
    } else {
      await api.delete(`/certificates/${deleteConfirmItem.value.id}`).catch(() => null)
      showDeleteConfirm.value = false
      deleteConfirmItem.value = null
      fetchIssueCerts(issuedCerts.value.current_page)
    }
  } catch (err) {
    console.error('Delete failed:', err)
  } finally {
    deleteConfirming.value = false
  }
}

async function issueCertificate() {
  issueSaving.value = true
  try {
    await api.post('/certificates', {
      student_id: Number(issueForm.student_id),
      template_id: Number(issueForm.template_id),
      class_id: Number(issueForm.class_id) || null,
      subject_id: Number(issueForm.subject_id) || null,
      issue_date: issueForm.issue_date,
      authorized_by: issueForm.authorized_by || null,
      remarks: issueForm.remarks || null,
    }).catch(() => null)

    showIssue.value = false
    issueForm.student_id = ''
    issueForm.template_id = ''
    fetchIssueCerts(issuedCerts.value.current_page)
  } catch (err) { console.error('Issue failed:', err) }
  finally { issueSaving.value = false }
}

function formatTemplateType(type: string) {
  const map: Record<string, string> = { annual: 'বার্ষিক', transfer: 'হস্তান্তর', sanction: 'অনুমোদন', conduct: 'আচরণ', others: 'অন্যান্য' }
  return map[type] || type
}

function formatDate(date: string) {
  if (!date) return '—'
  try { return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) }
  catch { return date }
}

onMounted(() => {
  fetchClasses()
  fetchSubjects()
  fetchTemplates()
  fetchIssueCerts()
  fetchStudents()
})
</script>

<style scoped lang="scss">
.tabs-nav {
  display: inline-flex;
  gap: 0.35rem;
  background: var(--color-bg-card);
  padding: 0.35rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
  box-shadow: var(--elevation-1);
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1.1rem;
  border-radius: var(--radius-sm);
  border: 1px solid transparent;
  background: transparent;
  color: var(--color-text-light);
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  font-weight: var(--weight-medium);
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    color: var(--color-text);
    background: var(--color-bg-muted);
  }

  &.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: #ffffff;
    font-weight: var(--weight-bold);
    box-shadow: 0 2px 8px rgba(20, 80, 50, 0.25);
  }
}

.badge-pill {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 99px;
  font-size: var(--text-xs);
  font-weight: var(--weight-semibold);
  font-family: var(--font-bn);

  &.type-pill {
    background: rgba(59, 130, 246, 0.12);
    color: #2563eb;
  }
}

.cert-badge {
  background: var(--color-bg-muted);
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  border: 1px solid var(--color-border-light);
  font-family: monospace;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-primary);
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

.info-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;

  .info-row {
    display: flex;
    justify-content: space-between;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--color-border-light);

    dt {
      color: var(--color-text-light);
      font-weight: var(--weight-medium);
      font-size: var(--text-sm);
    }

    dd {
      font-weight: var(--weight-semibold);
      color: var(--color-text);
      font-size: var(--text-sm);
    }
  }
}

.syllabus-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.syllabus-card {
  background: var(--color-bg);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  padding: 1rem;
  transition: all var(--transition-fast);

  &:hover {
    border-color: var(--color-primary-100);
    box-shadow: var(--elevation-1);
    transform: translateY(-2px);
  }
}

.syllabus-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;

  h4 {
    margin: 0;
    font-size: var(--text-base);
    font-weight: var(--weight-bold);
    color: var(--color-text);
  }
}

.code-badge {
  font-size: var(--text-xs);
  background: rgba(20, 80, 50, 0.1);
  color: var(--color-primary);
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  font-weight: 600;
  font-family: monospace;
}

.syllabus-field {
  display: flex;
  gap: 0.5rem;
  font-size: var(--text-sm);

  .label {
    color: var(--color-text-light);
    min-width: 45px;
  }

  .value {
    color: var(--color-text);
    font-weight: var(--weight-medium);
  }
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

.toggle-row { display: flex; align-items: center; }
.toggle { position: relative; display: inline-block; width: 40px; height: 22px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.3s; border-radius: 22px; }
.toggle-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; }
.toggle input:checked + .toggle-slider { background-color: #145032; }
.toggle input:checked + .toggle-slider:before { transform: translateX(18px); }
</style>
