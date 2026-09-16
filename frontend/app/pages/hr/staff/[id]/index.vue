<template>
  <div class="page-wrapper">
    <div class="breadcrumb">
      <NuxtLink to="/hr">স্টাফ ও কর্মী</NuxtLink>
      <span class="sep">/</span>
      <NuxtLink :to="`/hr/staff/${staffId}`" class="breadcrumb-current">
        {{ staff?.name_bn || staff?.name_en || 'অজানা' }}
      </NuxtLink>
      <span class="sep">/</span>
      <span class="breadcrumb-current">বিস্তারিত</span>
    </div>

    <div class="detail-header">
      <div>
        <h1>কর্মকর্তার বিবরণী</h1>
        <p class="text-muted" v-if="staff">পদবী: {{ staff.designation || 'নির্ধারিত নয়' }} • বিভাগ: {{ staff.department || 'নির্ধারিত নয়' }}</p>
      </div>
      <div class="header-actions-group">
        <NuxtLink to="/hr" class="btn btn-outline btn-sm">
          <icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <button class="btn btn-primary btn-sm" @click="openQuickEditModal" v-if="staff">
          <icon name="pencil" /> তথ্য সম্পাদনা
        </button>
        <NuxtLink :to="`/hr/staff/${staffId}/edit`" class="btn btn-outline btn-sm" v-if="staff">
          <icon name="edit" /> সম্পূর্ণ এডিট
        </NuxtLink>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner" />
      <p>তথ্য লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!staff" class="not-found">
      <icon name="alert-circle" class="not-found-icon" />
      <h3>কর্মকর্তা পাওয়া যায়নি</h3>
      <NuxtLink to="/hr" class="btn btn-primary">স্টাফ তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="detail-layout">
      <div class="profile-card card">
        <div class="profile-header">
          <div class="profile-avatar">
            <div class="avatar-inner">
              <icon name="user-circle" />
            </div>
          </div>
          <div class="profile-identity">
            <h2 class="profile-name">{{ staff.name_bn || staff.name_en }}</h2>
            <p v-if="staff.designation" class="profile-designation">{{ staff.designation }}</p>
            <p v-if="staff.department" class="profile-department">{{ staff.department }}</p>
            <span class="status-badge" :class="staff.is_active ? 'active' : 'inactive'">
              {{ staff.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
            </span>
          </div>
        </div>
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-label">ফোন</div>
            <div class="stat-value">{{ staff.phone || '-' }}</div>
          </div>
          <div class="stat-item">
            <div class="stat-label">ইমেইল</div>
            <div class="stat-value"><a :href="`mailto:${staff.email}`" v-if="staff.email">{{ staff.email }}</a><span v-else>-</span></div>
          </div>
          <div class="stat-item">
            <div class="stat-label">পিতা/স্বামীর নাম</div>
            <div class="stat-value">{{ staff.fathers_name_bn || staff.fathers_name_en || '-' }}</div>
          </div>
          <div class="stat-item">
            <div class="stat-label">পরিচয়পত্র (NID)</div>
            <div class="stat-value">{{ staff.nid_number || '-' }}</div>
          </div>
          <div class="stat-item wide">
            <div class="stat-label">ঠিকানা</div>
            <div class="stat-value">{{ staff.address_bn || staff.address_en || '-' }}</div>
          </div>
          <div class="stat-item">
            <div class="stat-label">যোগদানের তারিখ</div>
            <div class="stat-value">{{ formatDate(staff.join_date) }}</div>
          </div>
          <div class="stat-item salary-item" v-if="staff.salary">
            <div class="stat-label">মাসিক বেতন</div>
            <div class="stat-value salary">{{ formatSalary(staff.salary) }}</div>
          </div>
        </div>
      </div>

      <div class="tabs-row">
        <button class="tab-btn" :class="{ active: activeTab === 'bio' }" @click="activeTab = 'bio'">
          <icon name="file-text" /> জীবনী
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'salary' }" @click="activeTab = 'salary'">
          <icon name="money" /> বেতন
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'attendance' }" @click="activeTab = 'attendance'">
          <icon name="calendar" /> হাজিরা
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'responsibilities' }" @click="activeTab = 'responsibilities'">
          <icon name="assignment" /> দায়িত্ব
        </button>
      </div>

      <div class="tab-content-wrapper card">
        <!-- Bio tab -->
        <div v-if="activeTab === 'bio'" class="tab-content">
          <div class="bio-card">
            <h3>জীবনী / পরিচয় (বাংলা)</h3>
            <div class="bio-text">
              {{ staff.bio_bn || 'কোনো জীবনী যুক্ত করা নেই' }}
            </div>
          </div>
          <div class="bio-card" style="margin-top: 1rem;">
            <h3>জীবনী / পরিচয় (ইংরেজি)</h3>
            <div class="bio-text">
              {{ staff.bio_en || 'No bio added' }}
            </div>
          </div>
        </div>

        <!-- Salary tab -->
        <div v-if="activeTab === 'salary'" class="tab-content">
          <div class="empty-tab-state">
            <icon name="money" class="empty-icon-tab" />
            <p>বর্তমান মাসিক বেতন: <strong>{{ formatSalary(staff.salary) }}</strong></p>
            <p class="text-muted">বেতন প্রদান ও ভাউচারের ইতিহাস সংশ্লিষ্ট মডিউল থেকে দেখতে পারবেন</p>
          </div>
        </div>

        <!-- Attendance tab -->
        <div v-if="activeTab === 'attendance'" class="tab-content">
          <div class="empty-tab-state">
            <icon name="calendar" class="empty-icon-tab" />
            <p>হাজিরা রেজিস্টার রেকর্ড</p>
            <p class="text-muted">ডিজিটাল হাজিরা মডিউল থেকে দৈনিক উপস্থিতি রেকর্ড সিঙ্ক হবে</p>
          </div>
        </div>

        <!-- Responsibilities tab -->
        <div v-if="activeTab === 'responsibilities'" class="tab-content">
          <div class="empty-tab-state">
            <icon name="assignment" class="empty-icon-tab" />
            <p>দায়িত্ব ও কার্যক্রমের বিবরণ</p>
            <p class="text-muted">প্রশাসনিক দায়িত্ব ও ক্লাসের রুটিন নির্ধারিত হলে এখানে প্রদর্শিত হবে</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Edit Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
          <div class="modal-card modal-lg">
            <div class="modal-header">
              <div class="modal-title-group">
                <h3>কর্মী তথ্য সম্পাদনা</h3>
                <p>ব্যক্তিগত তথ্য, পদবী, বিভাগ ও যোগাযোগের বিবরণ পরিবর্তন করুন</p>
              </div>
              <button class="modal-close-btn" @click="showEditModal = false">×</button>
            </div>
            <form @submit.prevent="saveQuickEdit">
              <div class="modal-body">
                <div v-if="editError" class="alert-banner error">{{ editError }}</div>

                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">নাম (বাংলায়) *</label>
                    <input v-model="editForm.name_bn" type="text" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label">নাম (ইংরেজিতে)</label>
                    <input v-model="editForm.name_en" type="text" class="form-control" />
                  </div>
                </div>

                <div class="form-row-3">
                  <div class="form-group">
                    <label class="form-label">পদবী</label>
                    <input v-model="editForm.designation" type="text" class="form-control" placeholder="উস্তাদ, সহকারী শিক্ষক..." />
                  </div>
                  <div class="form-group">
                    <label class="form-label">বিভাগ</label>
                    <select v-model="editForm.department" class="form-select">
                      <option value="">নির্বাচন করুন</option>
                      <option value="Academic">একাডেমিক</option>
                      <option value="Administration">প্রশাসন</option>
                      <option value="Finance">হিসাব ও অর্থ</option>
                      <option value="IT">আইটি</option>
                      <option value="Support">সহায়ক কর্মী</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">মাসিক বেতন (৳)</label>
                    <input v-model.number="editForm.salary" type="number" class="form-control" min="0" />
                  </div>
                </div>

                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">ফোন নম্বর</label>
                    <input v-model="editForm.phone" type="tel" class="form-control" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">ইমেইল</label>
                    <input v-model="editForm.email" type="email" class="form-control" />
                  </div>
                </div>

                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">জাতীয় পরিচয়পত্র (NID)</label>
                    <input v-model="editForm.nid_number" type="text" class="form-control" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">যোগদানের তারিখ</label>
                    <input v-model="editForm.join_date" type="date" class="form-control" />
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">ঠিকানা</label>
                  <textarea v-model="editForm.address_bn" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                  <label class="form-check-label">
                    <input type="checkbox" v-model="editForm.is_active" /> সক্রিয় কর্মী হিসেবে সংরক্ষণ করুন
                  </label>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-outline" @click="showEditModal = false">বাতিল</button>
                <button type="submit" class="btn btn-primary" :disabled="editSaving || !editForm.name_bn">
                  <icon name="loader" v-if="editSaving" />
                  {{ editSaving ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ করুন' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const staffId = route.params.id as string
const loading = ref(true)
const staff = ref<any>(null)
const activeTab = ref('bio')

const showEditModal = ref(false)
const editSaving = ref(false)
const editError = ref('')

const editForm = reactive({
  name_bn: '',
  name_en: '',
  phone: '',
  email: '',
  designation: '',
  department: '',
  salary: 0,
  nid_number: '',
  join_date: '',
  address_bn: '',
  is_active: true,
})

function openQuickEditModal() {
  if (!staff.value) return
  const s = staff.value
  editForm.name_bn = s.name_bn || ''
  editForm.name_en = s.name_en || ''
  editForm.phone = s.phone || s.user?.phone || ''
  editForm.email = s.email || s.user?.email || ''
  editForm.designation = s.designation || ''
  editForm.department = s.department || ''
  editForm.salary = s.salary ? Number(s.salary) : 0
  editForm.nid_number = s.nid_number || ''
  editForm.join_date = s.join_date ? String(s.join_date).slice(0, 10) : ''
  editForm.address_bn = s.address_bn || ''
  editForm.is_active = s.is_active ?? true
  editError.value = ''
  showEditModal.value = true
}

async function saveQuickEdit() {
  if (!editForm.name_bn.trim() || !staff.value) return
  editSaving.value = true
  editError.value = ''
  try {
    const res = await api.put(`/hr/staff/${staff.value.id}`, editForm)
    staff.value = res.data?.data || { ...staff.value, ...editForm }
    showEditModal.value = false
    alert('কর্মকর্তার তথ্য সফলভাবে আপডেট করা হয়েছে!')
  } catch (err: any) {
    console.error('Update staff error:', err)
    editError.value = err?.response?.data?.message || 'সংরক্ষণে ত্রুটি হয়েছে।'
  } finally {
    editSaving.value = false
  }
}

async function loadStaff() {
  loading.value = true
  try {
    const r = await api.get(`/hr/staff/${staffId}`)
    staff.value = r.data?.data
  } catch (e) {
    console.error('Failed to load staff:', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadStaff()
})

function formatDate(date: string | null | undefined) {
  if (!date) return '-'
  try {
    return new Date(date).toLocaleDateString('bn-BD', {
      day: 'numeric', month: 'short', year: 'numeric'
    })
  } catch {
    return '-'
  }
}

function formatSalary(amount: number) {
  if (!amount) return '-'
  return 'টাকা ' + Number(amount).toLocaleString('bn-BD', { minimumFractionDigits: 0 })
}
</script>

<style scoped>
.page-wrapper {
  max-width: 960px;
  margin: 0 auto;
  padding: 1.5rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 1rem;
  font-size: 0.82rem;
  color: var(--color-text-muted);
}

.breadcrumb .sep {
  color: var(--color-text-muted);
}

.breadcrumb a {
  color: var(--color-primary);
  text-decoration: none;
}

.breadcrumb .breadcrumb-current {
  color: var(--color-text);
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.header-actions-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

h1 {
  font-size: 1.5rem;
  color: var(--color-primary);
  font-family: var(--font-bn);
  margin: 0 0 0.25rem 0;
}

.loading-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 4rem 0;
}

.not-found {
  text-align: center;
  padding: 4rem 1rem;
}

.profile-card {
  padding: 1.5rem;
  border-radius: var(--radius-md);
  margin-bottom: 1.5rem;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid var(--color-border);
}

.avatar-inner {
  width: 64px;
  height: 64px;
  background: var(--color-primary-50, #f0fdf4);
  color: var(--color-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}

.profile-name {
  margin: 0 0 0.25rem 0;
  font-size: 1.35rem;
}

.profile-designation {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

.profile-department {
  margin: 0.2rem 0;
  font-size: 0.85rem;
  color: var(--color-primary);
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-badge.active {
  background: #dcfce7;
  color: #15803d;
}

.status-badge.inactive {
  background: #fee2e2;
  color: #b91c1c;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

.stat-item {
  background: var(--color-bg-subtle, #f9fafb);
  padding: 0.75rem 1rem;
  border-radius: var(--radius-sm);
}

.stat-item.wide {
  grid-column: span 2;
}

.stat-label {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 0.95rem;
  font-weight: 600;
}

.stat-value.salary {
  color: #059669;
}

.tabs-row {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  border-bottom: 1px solid var(--color-border);
  padding-bottom: 0.5rem;
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1px solid transparent;
  border-radius: var(--radius-sm);
  font-family: var(--font-bn);
  font-size: 0.9rem;
  cursor: pointer;
  color: var(--color-text-muted);
  transition: all 0.2s;
}

.tab-btn.active {
  background: var(--color-primary-50, #f0fdf4);
  color: var(--color-primary);
  border-color: var(--color-primary-200, #bbf7d0);
  font-weight: 600;
}

.tab-content-wrapper {
  padding: 1.5rem;
  border-radius: var(--radius-md);
}

.empty-tab-state {
  text-align: center;
  padding: 2rem 1rem;
  color: var(--color-text-muted);
}

.empty-icon-tab {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  opacity: 0.4;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.modal-card {
  background: var(--color-bg-card, #fff);
  border-radius: var(--radius-lg, 12px);
  width: 100%;
  max-width: 650px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--color-text-muted);
}

.modal-body {
  padding: 1.5rem;
  max-height: 75vh;
  overflow-y: auto;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border);
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-row-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text);
}

.form-control, .form-select {
  padding: 0.55rem 0.85rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-bg);
  font-size: 0.9rem;
}

.alert-banner {
  padding: 0.75rem 1rem;
  border-radius: var(--radius-sm);
  margin-bottom: 1rem;
  font-size: 0.85rem;
}

.alert-banner.error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}
</style>
