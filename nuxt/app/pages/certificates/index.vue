<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">সার্টিফিকেশন</span>
        <h1>সার্টিফিকেট ও পাঠ্যক্রম</h1>
        <p>সার্টিফিকেট টেমপলেট, প্রকাশনা, পাঠ্যক্রম ও বই পরিচালনা করুন</p>
      </div>
      <div class="page-actions">
        <button class="btn btn-primary" @click="activeTab = 'templates'; showCreate = true">
          <Icon name="plus" /> নতুন টেমপলেট
        </button>
        <button class="btn btn-outline" @click="activeTab = 'issue'; showIssue = true">
          <Icon name="certificate" /> সার্টিফিকেট প্রকাশ করুন
        </button>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="['tab-btn', { active: activeTab === tab.key }]"
        @click="activeTab = tab.key"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Templates Tab -->
    <div v-if="activeTab === 'templates'" class="card mt-3">
      <div class="card-inner">
        <div slot="header" class="card-header-inner">
          <div class="search-bar">
            <Icon name="search" />
            <input v-model="templateSearch" type="text" placeholder="টেমপলেট খুঁজুন..." @input="debounceTemplateSearch" class="search-input" />
          </div>
          <div class="filter-row">
            <select v-model="templateTypeFilter" class="form-select sm">
              <option value="">সব ধরন</option>
              <option value="annual">বার্ষিক</option>
              <option value="transfer">হস্তান্তর</option>
              <option value="sanction">অনুমোদন</option>
              <option value="conduct">আচরণ</option>
              <option value="others">অন্যান্য</option>
            </select>
            <select v-model="templateClassFilter" class="form-select sm ml-2">
              <option value="">সব শ্রেণি</option>
              <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
        </div>

        <div v-if="templateLoading" class="loading-state">
          <div class="spinner"></div>
          <p>টেমপলেট লোড হচ্ছে...</p>
        </div>

        <table v-else-if="templates.data?.length" class="data-table">
          <thead>
            <tr>
              <th>শিরোনাম</th>
              <th>ধরন</th>
              <th>শ্রেণি</th>
              <th>বিষয়</th>
              <th>সক্রিয়</th>
              <th>কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in templates.data" :key="t.id">
              <td><strong>{{ t.title }}</strong></td>
              <td><span class="badge badge-blue">{{ formatTemplateType(t.template_type) }}</span></td>
              <td>{{ t.classRelation?.name || '—' }}</td>
              <td>{{ t.subjectRelation?.name || '—' }}</td>
              <td>
                <span :class="`status-switch ${t.is_active ? 'on' : 'off'}`">
                  {{ t.is_active ? 'হ্যাঁ' : 'না' }}
                </span>
              </td>
              <td class="actions-cell">
                <button class="btn btn-icon btn-sm" @click="editTemplate(t)">
                  <Icon name="pencil" />
                </button>
                <button class="btn btn-icon btn-sm text-danger" @click="deleteTemplate(t)">
                  <Icon name="trash" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="empty-state">
          <Icon name="table" large />
          <h3>কোনো টেমপলেট নেই</h3>
          <button class="btn btn-primary" @click="showCreate = true">প্রথম টেমপলেট তৈরি করুন</button>
        </div>

        <div slot="footer" class="card-footer-inner">
          <div class="pagination-info">{{ templates.from }}–{{ templates.to }} / {{ templates.total }} টেমপলেট</div>
          <div class="pagination" v-if="templates.last_page > 1">
            <button class="btn btn-outline btn-sm" :disabled="!templates.prev_page_url" @click="goTemplatePage(templates.current_page - 1)">
              <Icon name="chevron-left" />
            </button>
            <span class="page-info">পৃষ্ঠা {{ templates.current_page }} / {{ templates.last_page }}</span>
            <button class="btn btn-outline btn-sm" :disabled="!templates.next_page_url" @click="goTemplatePage(templates.current_page + 1)">
              <Icon name="chevron-right" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Issue Certificates Tab -->
    <div v-if="activeTab === 'issue'" class="card mt-3">
      <div class="card-inner">
        <div slot="header" class="card-header-inner">
          <div class="search-bar">
            <Icon name="search" />
            <input v-model="issueSearch" type="text" placeholder="শিক্ষার্থী নাম দিয়ে খুঁজুন..." @input="debounceIssueSearch" class="search-input" />
          </div>
          <div class="filter-row">
            <select v-model="issueClassFilter" class="form-select sm">
              <option value="">সব শ্রেণি</option>
              <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
        </div>

        <div v-if="issueLoading" class="loading-state">
          <div class="spinner"></div>
          <p>সার্টিফিকেট প্রকাশনা লোড হচ্ছে...</p>
        </div>

        <table v-else-if="issuedCerts.data?.length" class="data-table">
          <thead>
            <tr>
              <th>সার্টিফিকেট নং</th>
              <th>শিক্ষার্থী</th>
              <th>আইডি</th>
              <th>শ্রেণি</th>
              <th>টেমপলেট</th>
              <th>প্রকাশের তারিখ</th>
              <th>কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in issuedCerts.data" :key="c.id">
              <td><code class="mono">{{ c.certificate_number }}</code></td>
              <td><strong>{{ c.studentRelation?.name?.trim() || 'অজানা' }}</strong></td>
              <td><code class="mono">{{ c.student_id }}</code></td>
              <td>{{ c.classRelation?.name || '—' }}</td>
              <td>{{ c.templateRelation?.title || '—' }}</td>
              <td>{{ formatDate(c.issue_date) }}</td>
              <td class="actions-cell">
                <button class="btn btn-icon btn-sm" @click="viewIssue(c)">
                  <Icon name="eye" />
                </button>
                <button class="btn btn-icon btn-sm text-danger" @click="deleteIssue(c)">
                  <Icon name="trash" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="empty-state">
          <Icon name="table" large />
          <h3>কোনো প্রকাশনা নেই</h3>
          <button class="btn btn-outline" @click="showIssue = true">প্রথম সার্টিফিকেট প্রকাশ করুন</button>
        </div>

        <div slot="footer" class="card-footer-inner">
          <div class="pagination-info">{{ issuedCerts.from }}–{{ issuedCerts.to }} / {{ issuedCerts.total }} রেকর্ড</div>
        </div>
      </div>
    </div>

    <!-- Syllabus & Books Tab -->
    <div v-if="activeTab === 'syllabus'" class="card mt-3">
      <div class="card-inner">
        <div slot="header" class="card-header-inner">
          <h3>পাঠ্যক্রম ও বই</h3>
        </div>
        <div class="syllabus-list">
          <div v-for="sub in subjects" :key="sub.id" class="syllabus-item">
            <div class="syllabus-header">
              <strong>{{ sub.name }}</strong>
              <span class="code-badge">{{ sub.code || '—' }}</span>
            </div>
            <div class="syllabus-body">
              <div class="syllabus-row">
                <span class="label">শ্রেণি</span>
                <span class="value">{{ sub.classes?.map(c => c.name).join(', ') || '—' }}</span>
              </div>
            </div>
          </div>
          <div v-if="subjects.length === 0" class="empty-state">
            <Icon name="book" large />
            <h3>কোনো পাঠ্যক্রম নেই</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Template Modal -->
    <div v-if="showCreate" class="modal-overlay" @click.self="showCreate = false">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ editingTemplate ? 'টেমপলেট সম্পাদনা' : 'নতুন টেমপলেট' }}</h3>
          <button class="btn btn-icon" @click="showCreate = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveTemplate">
            <div class="form-group">
              <label class="form-label">শিরোনাম <span class="required">*</span></label>
              <input v-model="templateForm.title" type="text" class="form-control" placeholder="যেমন: বার্ষিক পরীক্ষা ফলাফল সার্টিফিকেট" />
            </div>
            <div class="form-group">
              <label class="form-label">ধরন <span class="required">*</span></label>
              <select v-model="templateForm.template_type" class="form-select">
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
              <textarea v-model="templateForm.template_data_json" class="form-control font-mono" rows="4" placeholder='{"header": "...", "footer": "..."}'></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">সক্রিয়</label>
              <div class="toggle-row">
                <label class="toggle">
                  <input type="checkbox" v-model="templateForm.is_active" />
                  <span class="toggle-slider"></span>
                </label>
                <span class="ml-2 text-muted">{{ templateForm.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showCreate = false">বাতিল</button>
          <button class="btn btn-primary" @click="saveTemplate" :disabled="templateSaving">
            <Icon name="spinner" v-if="templateSaving" />
            {{ editingTemplate ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Issue Certificate Modal -->
    <div v-if="showIssue" class="modal-overlay" @click.self="showIssue = false">
      <div class="modal">
        <div class="modal-header">
          <h3>সার্টিফিকেট প্রকাশ করুন</h3>
          <button class="btn btn-icon" @click="showIssue = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="issueCertificate">
            <div class="form-group">
              <label class="form-label">শিক্ষার্থী <span class="required">*</span></label>
              <select v-model="issueForm.student_id" class="form-select">
                <option value="">শিক্ষার্থী নির্বাচন করুন</option>
                <option v-for="s in studentOptions" :key="s.id" :value="s.id">
                  {{ s.name }} ({{ s.roll_no }}) — {{ s.class?.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">টেমপলেট <span class="required">*</span></label>
              <select v-model="issueForm.template_id" class="form-select">
                <option value="">টেমপলেট নির্বাচন করুন</option>
                <option v-for="t in templateOptions" :key="t.id" :value="t.id">{{ t.title }} ({{ t.template_type }})</option>
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
                <label class="form-label">প্রকাশের তারিখ <span class="required">*</span></label>
                <input v-model="issueForm.issue_date" type="date" class="form-control" />
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
            <Icon name="spinner" v-if="issueSaving" />
            প্রকাশ করুন
          </button>
        </div>
      </div>
    </div>

    <!-- View Issue Modal -->
    <div v-if="showViewIssue" class="modal-overlay" @click.self="showViewIssue = false">
      <div class="modal">
        <div class="modal-header">
          <h3>সার্টিফিকেটের বিবরণ</h3>
          <button class="btn btn-icon" @click="showViewIssue = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <dl class="info-list">
            <div class="info-row"><dt>সার্টিফিকেট নং</dt><dd><code class="mono">{{ viewIssueData?.certificate_number }}</code></dd></div>
            <div class="info-row"><dt>শিক্ষার্থী</dt><dd>{{ viewIssueData?.studentRelation?.name }}</dd></div>
            <div class="info-row"><dt>শ্রেণি</dt><dd>{{ viewIssueData?.classRelation?.name }}</dd></div>
            <div class="info-row"><dt>টেমপলেট</dt><dd>{{ viewIssueData?.templateRelation?.title }}</dd></div>
            <div class="info-row"><dt>প্রকাশের তারিখ</dt><dd>{{ viewIssueData?.issue_date ? formatDate(viewIssueData.issue_date) : '—' }}</dd></div>
            <div class="info-row"><dt>অনুমোদনকারী</dt><dd>{{ viewIssueData?.authorized_by || '—' }}</dd></div>
          </dl>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showViewIssue = false">বাতিল</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '~/components/ui/Icon.vue'

export default {
  components: { Icon },
  data() {
    return {
      activeTab: 'templates',
      tabs: [
        { key: 'templates', label: 'টেমপলেট' },
        { key: 'issue', label: 'প্রকাশনা' },
        { key: 'syllabus', label: 'পাঠ্যক্রম ও বই' },
      ],
      templateLoading: true,
      templates: { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null },
      templateSearch: '',
      templateTypeFilter: '',
      templateClassFilter: '',
      classOptions: [],
      subjectOptions: [],
      templateOptions: [],
      studentOptions: [],
      showCreate: false,
      editingTemplate: null,
      templateForm: { title: '', template_type: 'annual', class_id: '', subject_id: '', template_data_json: '{}', is_active: true },
      templateSaving: false,
      issueLoading: true,
      issuedCerts: { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null },
      issueSearch: '',
      issueClassFilter: '',
      showIssue: false,
      issueForm: { student_id: '', template_id: '', class_id: '', subject_id: '', issue_date: '', authorized_by: '', remarks: '' },
      issueSaving: false,
      showViewIssue: false,
      viewIssueData: null,
      searchTimeouts: { template: null, issue: null },
    }
  },

  computed: {
    templatesApiUrl() { return `${process.env.apiBase}/certificate-templates` },
    issueApiUrl() { return `${process.env.apiBase}/certificates` },
  },

  async mounted() {
    await Promise.all([this.fetchClasses(), this.fetchSubjects(), this.fetchTemplates(), this.fetchIssueCerts()])
  },

  methods: {
    async fetchClasses() {
      try {
        const res = await fetch(`${process.env.apiBase}/academic/classes?per_page=100`)
        const json = await res.json()
        this.classOptions = (json.data?.data || []).map(c => ({ id: c.id, name: c.name }))
      } catch (err) { console.error('Fetch classes failed:', err) }
    },

    async fetchSubjects() {
      try {
        const res = await fetch(`${process.env.apiBase}/certificates/syllabus`)
        const json = await res.json()
        this.subjectOptions = (json.data || []).map(s => ({ id: s.id, name: s.name, code: s.code }))
      } catch (err) { console.error('Fetch subjects failed:', err) }
    },

    async fetchTemplates(page = 1) {
      this.templateLoading = true
      try {
        const params = new URLSearchParams({ page, per_page: 15, ...(this.templateSearch ? { search: this.templateSearch } : {}), ...(this.templateTypeFilter ? { type: this.templateTypeFilter } : {}), ...(this.templateClassFilter ? { class_id: this.templateClassFilter } : {}) })
        const res = await fetch(`${this.templatesApiUrl}?${params}`)
        const json = await res.json()
        this.templates = json.data || { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }
      } catch (err) { console.error('Fetch templates failed:', err) }
      finally { this.templateLoading = false }
    },

    async fetchIssueCerts(page = 1) {
      this.issueLoading = true
      try {
        const params = new URLSearchParams({ page, per_page: 15, ...(this.issueSearch ? { search: this.issueSearch } : {}), ...(this.issueClassFilter ? { class_id: this.issueClassFilter } : {}) })
        const res = await fetch(`${this.issueApiUrl}?${params}`)
        const json = await res.json()
        this.issuedCerts = json.data || { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }
      } catch (err) { console.error('Fetch issue certs failed:', err) }
      finally { this.issueLoading = false }
    },

    debounceTemplateSearch() {
      clearTimeout(this.searchTimeouts.template)
      this.searchTimeouts.template = setTimeout(() => this.fetchTemplates(1), 300)
    },

    debounceIssueSearch() {
      clearTimeout(this.searchTimeouts.issue)
      this.searchTimeouts.issue = setTimeout(() => this.fetchIssueCerts(1), 300)
    },

    goTemplatePage(page) {
      if (page < 1 || page > this.templates.last_page) return
      this.fetchTemplates(page)
    },

    editTemplate(t) {
      this.editingTemplate = t
      this.templateForm = {
        title: t.title || '',
        template_type: t.template_type || 'annual',
        class_id: String(t.class_id || ''),
        subject_id: String(t.subject_id || ''),
        template_data_json: JSON.stringify(t.template_data || {}, null, 2),
        is_active: t.is_active ?? true,
      }
      this.showCreate = true
    },

    async saveTemplate() {
      this.templateSaving = true
      try {
        const url = this.editingTemplate ? `${this.templatesApiUrl}/${this.editingTemplate.id}` : this.templatesApiUrl
        const method = this.editingTemplate ? 'put' : 'post'
        const body = {
          ...this.templateForm,
          class_id: Number(this.templateForm.class_id) || null,
          subject_id: Number(this.templateForm.subject_id) || null,
          template_data: this.templateForm.template_data_json ? JSON.parse(this.templateForm.template_data_json) : {},
        }
        const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
        const json = await res.json()
        if (json.status && json.status < 500) {
          this.showCreate = false
          this.editingTemplate = null
          this.fetchTemplates(this.templates.current_page)
        } else {
          alert(json.message || 'সংরক্ষণে সমস্যা')
        }
      } catch (err) { console.error('Template save failed:', err) }
      finally { this.templateSaving = false }
    },

    deleteTemplate(t) {
      if (!confirm(`"${t.title}" টেমপলেটটি মুছে ফেলতে চান?`)) return
      fetch(`${this.templatesApiUrl}/${t.id}`, { method: 'delete' })
        .then(async r => {
          const json = await r.json()
          if (json.status && json.status < 500) this.fetchTemplates(this.templates.current_page)
          else alert(json.message || 'মুছে ফেলতে সমস্যা')
        })
        .catch(err => console.error('Delete failed:', err))
    },

    viewIssue(c) {
      this.viewIssueData = c
      this.showViewIssue = true
    },

    deleteIssue(c) {
      if (!confirm(`${c.certificate_number} সার্টিফিকেটটি মুছে ফেলতে চান?`)) return
      fetch(`${this.issueApiUrl}/${c.id}`, { method: 'delete' })
        .then(async r => {
          const json = await r.json()
          if (json.status && json.status < 500) this.fetchIssueCerts(this.issuedCerts.current_page)
          else alert(json.message || 'মুছে ফেলতে সমস্যা')
        })
        .catch(err => console.error('Delete failed:', err))
    },

    async issueCertificate() {
      this.issueSaving = true
      try {
        const res = await fetch(this.issueApiUrl, {
          method: 'post',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            student_id: Number(this.issueForm.student_id),
            template_id: Number(this.issueForm.template_id),
            class_id: Number(this.issueForm.class_id) || null,
            subject_id: Number(this.issueForm.subject_id) || null,
            issue_date: this.issueForm.issue_date,
            authorized_by: this.issueForm.authorized_by || null,
            remarks: this.issueForm.remarks || null,
          }),
        })
        const json = await res.json()
        if (json.status && json.status < 500) {
          this.showIssue = false
          this.issueForm = { student_id: '', template_id: '', class_id: '', subject_id: '', issue_date: '', authorized_by: '', remarks: '' }
          this.fetchIssueCerts(this.issuedCerts.current_page)
        } else {
          alert(json.message || 'প্রকাশে সমস্যা')
        }
      } catch (err) { console.error('Issue failed:', err) }
      finally { this.issueSaving = false }
    },

    formatTemplateType(type) {
      const map = { annual: 'বার্ষিক', transfer: 'হস্তান্তর', sanction: 'অনুমোদন', conduct: 'আচরণ', others: 'অন্যান্য' }
      return map[type] || type
    },

    formatDate(date) {
      if (!date) return '—'
      try { return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) }
      catch { return date }
    },
  },
}
</script>

<style scoped lang="scss">
.mono { font-family: 'Courier New', monospace; font-size: 0.85rem; }
.text-muted { color: #6c757d; }
.actions-cell { white-space: nowrap; }
.status-switch { font-weight: 500; &.on { color: #2e7d32; } &.off { color: #c62828; } }
.syllabus-list { max-height: 400px; overflow-y: auto; }
.syllabus-item { border-bottom: 1px solid rgba(0,0,0,0.06); padding: 0.75rem 0; }
.syllabus-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem; }
.code-badge { font-size: 0.8rem; background: #e3f2fd; color: #1565c0; padding: 0.1rem 0.5rem; border-radius: 4px; }
.syllabus-row { display: flex; gap: 0.5rem; font-size: 0.9rem; }
.syllabus-row .label { color: #6c757d; min-width: 60px; }
.syllabus-row .value { flex: 1; }
.font-mono { font-family: 'Courier New', monospace; font-size: 0.85rem; }
.toggle-row { display: flex; align-items: center; }
.toggle { position: relative; display: inline-block; width: 40px; height: 22px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.3s; border-radius: 22px; }
.toggle-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; }
.toggle input:checked + .toggle-slider { background-color: #145032; }
.toggle input:checked + .toggle-slider:before { transform: translateX(18px); }
</style>
