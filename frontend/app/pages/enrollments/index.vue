<template>
  <div class="page-wrapper slide-up-fade">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">ভর্তি ব্যবস্থাপনা</span>
        <h1>নতুন ভর্তি তালিকা</h1>
        <p class="page-subtitle">মাদ্রাসায় নতুন ভর্তির আবেদন এবং তাদের বর্তমান অবস্থা</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/enrollments/create" class="btn btn-primary">
          <Icon name="mdi:account-plus" /> নতুন ভর্তি যোগ করুন
        </NuxtLink>
      </div>
    </div>

    <!-- Toast Notification -->
    <Transition name="toast-fade">
      <div v-if="toastMessage" class="toast-banner" :class="toastMessage.type">
        <Icon :name="toastMessage.type === 'success' ? 'mdi:check-circle' : 'mdi:alert-circle'" class="toast-icon" />
        <span>{{ toastMessage.text }}</span>
      </div>
    </Transition>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><Icon name="mdi:account-multiple" /></div>
        <div class="stat-content">
          <div class="stat-value">{{ totalEnrollments }}</div>
          <div class="stat-label">মোট আবেদন</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><Icon name="mdi:clock-outline" /></div>
        <div class="stat-content">
          <div class="stat-value">{{ pendingEnrollments }}</div>
          <div class="stat-label">অপেক্ষমান</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><Icon name="mdi:check-circle-outline" /></div>
        <div class="stat-content">
          <div class="stat-value">{{ approvedEnrollments }}</div>
          <div class="stat-label">অনুমোদিত</div>
        </div>
      </div>
    </div>

    <div class="table-card">
      <div class="toolbar">
        <div class="search-box">
          <Icon name="mdi:magnify" class="search-icon" />
          <input type="text" v-model="searchQuery" placeholder="নাম, আইডি বা ফোন নম্বর দিয়ে খুঁজুন..." />
          <button v-if="searchQuery" @click="searchQuery = ''" class="clear-search-btn" title="Clear search">
            <Icon name="mdi:close" />
          </button>
        </div>
        <div class="select-wrapper">
          <select v-model="statusFilter" class="form-select">
            <option value="all">সকল অবস্থা</option>
            <option value="pending">অপেক্ষমান</option>
            <option value="approved">অনুমোদিত</option>
            <option value="rejected">বাতিল</option>
          </select>
        </div>
        <div class="pagination-info">
          মোট <span class="highlight">{{ totalEnrollments }}</span> টি আবেদন পাওয়া গেছে
        </div>
      </div>

      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>আইডি</th>
              <th>আবেদনকারীর নাম</th>
              <th>ভর্তির শ্রেণি</th>
              <th>ফোন নম্বর</th>
              <th>আবেদনের তারিখ</th>
              <th>অবস্থা</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="enrollment in enrollments" :key="enrollment.id">
              <td><strong>#{{ enrollment.enrollment_number || enrollment.id }}</strong></td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(enrollment.student?.name_bn || enrollment.name || 'S') }">
                    {{ (enrollment.student?.name_bn || enrollment.name || 'S').charAt(0) }}
                  </div>
                  <div class="user-info">
                    <span class="user-name">{{ enrollment.student?.name_bn || enrollment.student?.name_en || enrollment.name || 'অজ্ঞাত' }}</span>
                    <small class="text-muted">{{ enrollment.student?.father_name || enrollment.fatherName || 'পিতার নাম নেই' }}</small>
                  </div>
                </div>
              </td>
              <td>{{ enrollment.class?.name_bn || enrollment.class?.name_en || enrollment.className || '-' }}</td>
              <td>{{ enrollment.student?.user?.phone || enrollment.student?.father_phone || enrollment.student?.guardian_phone || enrollment.phone || '-' }}</td>
              <td>{{ formatDate(enrollment.enrollment_date || enrollment.created_at || enrollment.date) }}</td>
              <td>
                <span class="status-pill" :class="getStatusBadgeClass(enrollment.status)">
                  <span class="status-dot"></span> {{ getStatusLabel(enrollment.status) }}
                </span>
              </td>
              <td class="text-right">
                <div class="flex flex-end gap-1" style="justify-content: flex-end;">
                  <button class="action-btn" title="বিস্তারিত দেখুন" @click="viewDetails(enrollment)">
                    <Icon name="mdi:eye-outline" />
                  </button>
                  <button v-if="enrollment.status === 'pending'" class="action-btn approve" title="অনুমোদন করুন" @click="promptAction(enrollment, 'approve')" :disabled="loadingAction === enrollment.id">
                    <Icon name="mdi:check-circle-outline" />
                  </button>
                  <button v-if="enrollment.status === 'pending'" class="action-btn delete" title="বাতিল করুন" @click="promptAction(enrollment, 'reject')" :disabled="loadingAction === enrollment.id">
                    <Icon name="mdi:close-circle-outline" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="enrollments.length === 0 && !loading">
              <td colspan="7">
                <div class="empty-state">
                  <Icon name="mdi:account-question-outline" size="48" style="color: var(--color-border);" />
                  <p class="mt-2">কোনো ভর্তির আবেদন পাওয়া যায়নি।</p>
                </div>
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="7" class="text-center py-4">
                <Icon name="mdi:loading" class="animate-spin mr-2" /> লোডিং হচ্ছে...
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination-wrapper">
        <button class="pagination-btn" :disabled="currentPage === 1" @click="currentPage--">
          <Icon name="mdi:chevron-left" /> পূর্ববর্তী
        </button>
        <div class="pagination-numbers">
          <template v-for="(p, idx) in visiblePages" :key="idx">
            <span v-if="p === '...'" class="pagination-ellipsis">...</span>
            <button 
              v-else 
              class="pagination-num" 
              :class="{ active: currentPage === p }"
              @click="currentPage = Number(p)"
            >
              {{ p }}
            </button>
          </template>
        </div>
        <button class="pagination-btn" :disabled="currentPage === totalPages" @click="currentPage++">
          পরবর্তী <Icon name="mdi:chevron-right" />
        </button>
      </div>
    </div>

    <!-- Enrollment Detail Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showDetailModal && selectedEnrollment" class="modal-overlay" @click.self="showDetailModal = false">
          <div class="modal-card">
            <div class="modal-header">
              <div class="modal-title-wrap">
                <Icon name="mdi:card-account-details-outline" class="modal-icon text-primary" />
                <div>
                  <h3>ভর্তির আবেদন বিস্তারিত</h3>
                  <p class="modal-subtitle">আবেদন নম্বর #{{ selectedEnrollment.enrollment_number || selectedEnrollment.id }}</p>
                </div>
              </div>
              <button class="modal-close-btn" @click="showDetailModal = false">
                <Icon name="mdi:close" />
              </button>
            </div>

            <div class="modal-body">
              <div class="applicant-profile-card">
                <div class="user-avatar-initials large" :style="{ backgroundColor: getAvatarColor(selectedEnrollment.student?.name_bn || selectedEnrollment.name || 'S') }">
                  {{ (selectedEnrollment.student?.name_bn || selectedEnrollment.name || 'S').charAt(0) }}
                </div>
                <div class="applicant-info">
                  <h4 class="applicant-name">{{ selectedEnrollment.student?.name_bn || selectedEnrollment.student?.name_en || selectedEnrollment.name || 'অজ্ঞাত আবেদনকারী' }}</h4>
                  <p class="applicant-meta">ইংরেজি নাম: {{ selectedEnrollment.student?.name_en || 'দেওয়া হয়নি' }}</p>
                  <span class="status-pill mt-1" :class="getStatusBadgeClass(selectedEnrollment.status)">
                    <span class="status-dot"></span> {{ getStatusLabel(selectedEnrollment.status) }}
                  </span>
                </div>
              </div>

              <div class="details-grid">
                <div class="detail-item">
                  <span class="detail-label">ভর্তির শ্রেণি:</span>
                  <span class="detail-value font-semibold">{{ selectedEnrollment.class?.name_bn || selectedEnrollment.class?.name_en || selectedEnrollment.className || 'নির্দিষ্ট নয়' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">পিতার নাম:</span>
                  <span class="detail-value">{{ selectedEnrollment.student?.father_name || selectedEnrollment.fatherName || '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">মাতার নাম:</span>
                  <span class="detail-value">{{ selectedEnrollment.student?.mother_name || '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">অভিভাবকের ফোন:</span>
                  <span class="detail-value">{{ selectedEnrollment.student?.user?.phone || selectedEnrollment.student?.father_phone || selectedEnrollment.student?.guardian_phone || selectedEnrollment.phone || '-' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">আবেদনের তারিখ:</span>
                  <span class="detail-value">{{ formatDate(selectedEnrollment.enrollment_date || selectedEnrollment.created_at || selectedEnrollment.date) }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">ঠিকানা:</span>
                  <span class="detail-value">{{ selectedEnrollment.student?.permanent_address || selectedEnrollment.student?.present_address || '-' }}</span>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn btn-secondary" @click="showDetailModal = false">
                বন্ধ করুন
              </button>
              <template v-if="selectedEnrollment.status === 'pending'">
                <button class="btn btn-danger-soft" @click="promptAction(selectedEnrollment, 'reject')">
                  <Icon name="mdi:close-circle-outline" /> বাতিল করুন
                </button>
                <button class="btn btn-success" @click="promptAction(selectedEnrollment, 'approve')">
                  <Icon name="mdi:check-circle-outline" /> অনুমোদন করুন
                </button>
              </template>
            </div>
          </div>
        </div>
      </Teleport>
    </ClientOnly>

    <!-- Confirm Action Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showConfirmModal && actionTarget" class="modal-overlay" @click.self="showConfirmModal = false">
          <div class="modal-card small-modal">
            <div class="modal-header">
              <h3>{{ pendingActionType === 'approve' ? 'আবেদন অনুমোদন নিশ্চিতকরণ' : 'আবেদন বাতিল নিশ্চিতকরণ' }}</h3>
              <button class="modal-close-btn" @click="showConfirmModal = false">
                <Icon name="mdi:close" />
              </button>
            </div>
            <div class="modal-body">
              <p>
                আপনি কি নিশ্চিত যে <strong>{{ actionTarget.student?.name_bn || actionTarget.name || 'এই আবেদনকারী' }}</strong>-এর ভর্তির আবেদন 
                <span :class="pendingActionType === 'approve' ? 'text-success font-semibold' : 'text-danger font-semibold'">
                  {{ pendingActionType === 'approve' ? 'অনুমোদন' : 'বাতিল' }}
                </span> 
                করতে চান?
              </p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="showConfirmModal = false" :disabled="loadingAction !== null">
                বাতিল করুন
              </button>
              <button 
                :class="pendingActionType === 'approve' ? 'btn btn-primary' : 'btn btn-danger'"
                :disabled="loadingAction !== null"
                @click="executeConfirmedAction"
              >
                <Icon v-if="loadingAction !== null" name="mdi:loading" class="animate-spin mr-1" />
                {{ pendingActionType === 'approve' ? 'হ্যাঁ, অনুমোদন করুন' : 'হ্যাঁ, বাতিল করুন' }}
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const searchQuery = ref('')
const statusFilter = ref('all')
const currentPage = ref(1)
const itemsPerPage = 10
const totalPages = ref(1)

const enrollments = ref<any[]>([])
const totalEnrollments = ref(0)
const pendingEnrollments = ref(0)
const approvedEnrollments = ref(0)

const loading = ref(false)
const loadingAction = ref<string | number | null>(null)

// Detail modal state
const showDetailModal = ref(false)
const selectedEnrollment = ref<any>(null)

// Confirm modal state
const showConfirmModal = ref(false)
const actionTarget = ref<any>(null)
const pendingActionType = ref<'approve' | 'reject'>('approve')

// Toast state
const toastMessage = ref<{ text: string, type: 'success' | 'error' } | null>(null)
let toastTimer: any = null

function showToast(text: string, type: 'success' | 'error' = 'success') {
  if (toastTimer) clearTimeout(toastTimer)
  toastMessage.value = { text, type }
  toastTimer = setTimeout(() => {
    toastMessage.value = null
  }, 3500)
}

const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages: (number | string)[] = []
  if (current <= 4) {
    pages.push(1, 2, 3, 4, 5, '...', total)
  } else if (current >= total - 3) {
    pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total)
  } else {
    pages.push(1, '...', current - 1, current, current + 1, '...', total)
  }
  return pages
})

async function fetchEnrollments() {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: currentPage.value.toString(),
      per_page: itemsPerPage.toString(),
    })
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value !== 'all') params.append('status', statusFilter.value)

    const res = await api.get(`/enrollments?${params.toString()}`).catch((err) => {
      console.error('Failed to fetch paginated enrollments:', err)
      return { data: { data: { data: [] } } }
    })
    
    enrollments.value = res.data?.data?.data || res.data?.data || []
    const meta = res.data?.data?.meta || res.data?.meta || {}
    const totalCount = meta.total || res.data?.data?.total || res.data?.total || enrollments.value.length
    totalPages.value = meta.last_page || Math.ceil(totalCount / itemsPerPage) || 1
    totalEnrollments.value = totalCount

    // Update approximate counts from the current dataset or overall
    if (statusFilter.value === 'all' && !searchQuery.value) {
      pendingEnrollments.value = enrollments.value.filter((e: any) => e.status === 'pending').length
      approvedEnrollments.value = enrollments.value.filter((e: any) => ['active', 'approved', 'enrolled'].includes(e.status)).length
    }
  } catch (error) {
    console.error('Failed to fetch enrollments:', error)
  } finally {
    loading.value = false
  }
}

watch([statusFilter, searchQuery], () => {
  currentPage.value = 1
  fetchEnrollments()
})

watch(currentPage, () => {
  fetchEnrollments()
})

onMounted(() => {
  fetchEnrollments()
})

function getStatusLabel(status: string) {
  if (status === 'pending') return 'অপেক্ষমান'
  if (status === 'active' || status === 'approved' || status === 'enrolled') return 'অনুমোদিত'
  if (status === 'rejected') return 'বাতিল'
  if (status === 'transferred') return 'স্থানান্তরিত'
  if (status === 'dropped') return 'ছাড়া'
  return status || 'অজ্ঞাত'
}

function viewDetails(enrollment: any) {
  selectedEnrollment.value = enrollment
  showDetailModal.value = true
}

function promptAction(enrollment: any, action: 'approve' | 'reject') {
  actionTarget.value = enrollment
  pendingActionType.value = action
  showConfirmModal.value = true
}

async function executeConfirmedAction() {
  if (!actionTarget.value) return
  const id = actionTarget.value.id
  loadingAction.value = id
  const newStatus = pendingActionType.value === 'approve' ? 'active' : 'rejected'
  
  try {
    await api.put(`/enrollments/${id}`, { status: newStatus })
    showConfirmModal.value = false
    if (showDetailModal.value && selectedEnrollment.value?.id === id) {
      selectedEnrollment.value.status = newStatus
    }
    showToast(
      pendingActionType.value === 'approve' ? 'আবেদনটি সফলভাবে অনুমোদন করা হয়েছে!' : 'আবেদনটি বাতিল করা হয়েছে।',
      'success'
    )
    await fetchEnrollments()
  } catch (e: any) {
    console.error('Enrollment status update failed:', e)
    showToast(e?.response?.data?.message || 'অ্যাকশন সম্পন্ন করতে ব্যর্থ হয়েছে।', 'error')
  } finally {
    loadingAction.value = null
  }
}

function getStatusBadgeClass(status: string) {
  if (status === 'pending') return 'badge-pending'
  if (status === 'active' || status === 'approved' || status === 'enrolled') return 'badge-approved'
  if (status === 'rejected') return 'badge-rejected'
  return 'badge-secondary'
}

function formatDate(dateString: string) {
  if (!dateString) return '-'
  const d = new Date(dateString)
  if (isNaN(d.getTime())) return '-'
  return d.toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
}

function getAvatarColor(name: string) {
  const colors = ['#2b719e', '#167344', '#9b7415', '#7255a5', '#c56bc4']
  const charCode = name?.charCodeAt(0) || 0
  return colors[charCode % colors.length]
}
</script>

<style scoped>
.page-wrapper {
  padding: 1.5rem;
}
.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}
.header-title-block h1 {
  font-size: 1.6rem;
  font-weight: 700;
  margin: 0.2rem 0;
}
.eyebrow {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-primary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.page-subtitle {
  font-size: 0.9rem;
  color: var(--color-text-muted);
  margin: 0;
}
.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: var(--color-bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
  box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}
.stat-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  font-size: 1.4rem;
}
.stat-icon-wrap.blue { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.stat-icon-wrap.amber { background: rgba(245, 158, 11, 0.12); color: #d97706; }
.stat-icon-wrap.green { background: rgba(16, 185, 129, 0.12); color: #059669; }

.table-card {
  background: var(--color-bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
  overflow: hidden;
}
.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
  flex-wrap: wrap;
  gap: 0.75rem;
}
.search-box {
  position: relative;
  min-width: 260px;
  flex: 1;
  max-width: 400px;
}
.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-muted);
}
.search-box input {
  width: 100%;
  padding: 0.5rem 2rem 0.5rem 2.25rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
  background: var(--color-bg);
  color: var(--color-text);
  font-size: 0.9rem;
}
.clear-search-btn {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  cursor: pointer;
  color: var(--color-text-muted);
}
.select-wrapper select {
  padding: 0.5rem 1rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
  background: var(--color-bg);
  color: var(--color-text);
  font-size: 0.9rem;
}
.pagination-info {
  font-size: 0.85rem;
  color: var(--color-text-muted);
}
.table-responsive {
  overflow-x: auto;
}
.premium-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.premium-table th {
  padding: 0.85rem 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-muted);
  background: var(--color-bg-subtle, #f8fafc);
  border-bottom: 1px solid var(--color-border-light);
}
.premium-table td {
  padding: 0.85rem 1rem;
  font-size: 0.9rem;
  border-bottom: 1px solid var(--color-border-light);
}
.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.user-avatar-initials {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 700;
  font-size: 0.9rem;
}
.user-avatar-initials.large {
  width: 48px;
  height: 48px;
  font-size: 1.2rem;
}
.user-info {
  display: flex;
  flex-direction: column;
}
.user-name {
  font-weight: 600;
  color: var(--color-text);
}
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.2rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
}
.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}
.badge-pending {
  background: rgba(245, 158, 11, 0.15);
  color: #b45309;
}
.badge-pending .status-dot { background: #f59e0b; }
.badge-approved {
  background: rgba(16, 185, 129, 0.15);
  color: #047857;
}
.badge-approved .status-dot { background: #10b981; }
.badge-rejected {
  background: rgba(239, 68, 68, 0.15);
  color: #b91c1c;
}
.badge-rejected .status-dot { background: #ef4444; }
.badge-secondary {
  background: rgba(156, 163, 175, 0.15);
  color: #4b5563;
}
.action-btn {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
  background: var(--color-bg);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  color: var(--color-text-muted);
}
.action-btn:hover {
  background: var(--color-bg-hover);
  color: var(--color-text);
}
.action-btn.approve:hover {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border-color: #059669;
}
.action-btn.delete:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border-color: #dc2626;
}
.empty-state {
  padding: 3rem 1rem;
  text-align: center;
  color: var(--color-text-muted);
}

/* Toast */
.toast-banner {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 1100;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.75rem 1.25rem;
  border-radius: var(--radius-md);
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  font-size: 0.9rem;
  font-weight: 500;
}
.toast-banner.success {
  background: #064e3b;
  color: #ecfdf5;
  border: 1px solid #059669;
}
.toast-banner.error {
  background: #7f1d1d;
  color: #fef2f2;
  border: 1px solid #dc2626;
}
.toast-fade-enter-active, .toast-fade-leave-active {
  transition: all 0.3s ease;
}
.toast-fade-enter-from, .toast-fade-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
  display: grid;
  place-items: center;
  z-index: 1000;
  padding: 1rem;
}
.modal-card {
  background: var(--color-bg-card, #fff);
  border-radius: var(--radius-md, 12px);
  border: 1px solid var(--color-border);
  width: 100%;
  max-width: 540px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.25);
  overflow: hidden;
  animation: modalPop 0.2s ease-out;
}
.modal-card.small-modal {
  max-width: 440px;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.1rem 1.4rem;
  border-bottom: 1px solid var(--color-border-light);
}
.modal-title-wrap {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.modal-icon {
  font-size: 1.7rem;
}
.modal-subtitle {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  margin: 0;
}
.modal-close-btn {
  background: transparent;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: var(--color-text-muted);
}
.modal-body {
  padding: 1.4rem;
}
.applicant-profile-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--color-bg-subtle, #f8fafc);
  border-radius: var(--radius-sm);
  margin-bottom: 1.25rem;
}
.applicant-name {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}
.applicant-meta {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  margin: 0.15rem 0 0.35rem 0;
}
.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem;
}
.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}
.detail-label {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}
.detail-value {
  font-size: 0.9rem;
  color: var(--color-text);
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.4rem;
  background: var(--color-bg-subtle, #f8fafc);
  border-top: 1px solid var(--color-border-light);
}
.btn-danger-soft {
  background: rgba(239, 68, 68, 0.12);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.2);
}
.btn-danger-soft:hover {
  background: rgba(239, 68, 68, 0.2);
}

/* Pagination */
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
  padding: 0.5rem 0.85rem;
  border-radius: var(--radius-sm);
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  color: var(--color-text);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.pagination-btn:hover:not(:disabled) {
  border-color: var(--color-primary);
  color: var(--color-primary);
}
.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.pagination-numbers {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}
.pagination-num {
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  border-radius: var(--radius-sm);
  border: 1px solid transparent;
  background: transparent;
  color: var(--color-text-muted);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.pagination-num:hover {
  background: var(--color-bg-subtle);
  color: var(--color-text);
}
.pagination-num.active {
  background: var(--color-primary);
  color: #fff;
  border-color: var(--color-primary);
}
.pagination-ellipsis {
  padding: 0 0.25rem;
  color: var(--color-text-muted);
}
</style>
