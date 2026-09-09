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
          মোট <span class="highlight">{{ filteredEnrollments.length }}</span> টি আবেদন পাওয়া গেছে
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
                    <span class="user-name">{{ enrollment.student?.name_bn || enrollment.name || 'অজ্ঞাত' }}</span>
                    <small class="text-muted">{{ enrollment.student?.father_name || enrollment.fatherName || 'পিতার নাম নেই' }}</small>
                  </div>
                </div>
              </td>
              <td>{{ enrollment.class?.name_bn || enrollment.className || '-' }}</td>
              <td>{{ enrollment.student?.phone || enrollment.phone || '-' }}</td>
              <td>{{ formatDate(enrollment.enrollment_date || enrollment.created_at || enrollment.date) }}</td>
              <td>
                <span class="status-pill" :class="getStatusBadgeClass(enrollment.status)">
                  <span class="status-dot"></span> {{ getStatusLabel(enrollment.status) }}
                </span>
              </td>
              <td class="text-right">
                <div class="flex flex-end gap-1" style="justify-content: flex-end;">
                  <button class="action-btn" title="বিস্তারিত দেখুন" @click="viewDetails(enrollment.id)">
                    <Icon name="mdi:eye-outline" />
                  </button>
                  <button v-if="enrollment.status === 'pending'" class="action-btn approve" title="অনুমোদন করুন" @click="approveEnrollment(enrollment.id)" :disabled="loadingAction === enrollment.id">
                    <Icon name="mdi:check-circle-outline" />
                  </button>
                  <button v-if="enrollment.status === 'pending'" class="action-btn delete" title="বাতিল করুন" @click="rejectEnrollment(enrollment.id)" :disabled="loadingAction === enrollment.id">
                    <Icon name="mdi:close-circle-outline" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="enrollments.length === 0 && !loading">
              <td colspan="7">
                <div class="empty-state">
                  <Icon name="mdi:flask-empty-outline" size="48" style="color: var(--color-border);" />
                  <p class="mt-2">কোনো আবেদন পাওয়া যায়নি।</p>
                </div>
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="7" class="text-center py-4">
                লোডিং...
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
          <button 
            v-for="page in totalPages" 
            :key="page" 
            class="pagination-num" 
            :class="{ active: currentPage === page }"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
        </div>
        <button class="pagination-btn" :disabled="currentPage === totalPages" @click="currentPage++">
          পরবর্তী <Icon name="mdi:chevron-right" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const searchQuery = ref('')
const statusFilter = ref('all')
const currentPage = ref(1)
const itemsPerPage = 8
const totalPages = ref(1)

const enrollments = ref<any[]>([])
const totalEnrollments = ref(0)
const pendingEnrollments = ref(0)
const approvedEnrollments = ref(0)

const loading = ref(false)
const loadingAction = ref<string | number | null>(null)

async function fetchEnrollments() {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: currentPage.value.toString(),
      per_page: itemsPerPage.toString(),
    })
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (statusFilter.value !== 'all') params.append('status', statusFilter.value)

    const [res, allRes] = await Promise.all([
      api.get(`/enrollments?${params.toString()}`),
      api.get('/enrollments?per_page=1000').catch(() => ({ data: { data: { data: [] } } }))
    ])
    
    enrollments.value = res.data?.data?.data || res.data?.data || []
    const meta = res.data?.data?.meta || {}
    totalPages.value = meta.last_page || Math.ceil((res.data?.data?.total || enrollments.value.length) / itemsPerPage) || 1
    
    const all = allRes.data?.data?.data || allRes.data?.data || []
    totalEnrollments.value = all.length
    pendingEnrollments.value = all.filter((e: any) => e.status === 'pending').length
    approvedEnrollments.value = all.filter((e: any) => e.status === 'active' || e.status === 'approved').length

  } catch (error) {
    console.error('Failed to fetch enrollments:', error)
  } finally {
    loading.value = false
  }
}

watch([currentPage, statusFilter, searchQuery], () => {
  if (currentPage.value === 1 || arguments[0] === currentPage) {
    fetchEnrollments()
  } else {
    currentPage.value = 1
  }
})

onMounted(() => {
  fetchEnrollments()
})

function getStatusLabel(status: string) {
  if (status === 'pending') return 'অপেক্ষমান'
  if (status === 'active' || status === 'approved') return 'অনুমোদিত'
  if (status === 'rejected') return 'বাতিল'
  if (status === 'transferred') return 'স্থানান্তরিত'
  if (status === 'dropped') return 'ছাড়া'
  return status
}

async function approveEnrollment(id: string) {
  if (!confirm('আপনি কি এই আবেদনটি অনুমোদন করতে চান?')) return
  loadingAction.value = id
  try {
    await api.put(`/enrollments/${id}`, { status: 'active' })
    await fetchEnrollments()
  } catch (e) {
    console.error(e)
    alert('অনুমোদন ব্যর্থ হয়েছে')
  } finally {
    loadingAction.value = null
  }
}

async function rejectEnrollment(id: string) {
  if (!confirm('আপনি কি এই আবেদনটি বাতিল করতে চান?')) return
  loadingAction.value = id
  try {
    await api.put(`/enrollments/${id}`, { status: 'rejected' })
    await fetchEnrollments()
  } catch (e) {
    console.error(e)
    alert('বাতিল ব্যর্থ হয়েছে')
  } finally {
    loadingAction.value = null
  }
}

function viewDetails(id: string) {
  alert('ভর্তির বিস্তারিত (ID: ' + id + ') শীঘ্রই আসছে!')
}

function getStatusBadgeClass(status: string) {
  if (status === 'pending') return 'badge-pending'
  if (status === 'active' || status === 'approved') return 'badge-approved'
  if (status === 'rejected') return 'badge-rejected'
  return 'badge-secondary'
}

function formatDate(dateString: string) {
  if (!dateString) return '-'
  const d = new Date(dateString)
  return d.toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
}

function getAvatarColor(name: string) {
  const colors = ['#2b719e', '#167344', '#9b7415', '#7255a5', '#c56bc4']
  const charCode = name?.charCodeAt(0) || 0
  return colors[charCode % colors.length]
}
</script>

<style scoped>
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
  font-family: var(--font-bn);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
}
.pagination-btn:hover:not(:disabled) {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-primary-50);
}
.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: var(--color-bg-muted);
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
  color: var(--color-text-light);
  font-family: var(--font-sans);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
}
.pagination-num:hover {
  background: var(--color-bg-muted);
  color: var(--color-text);
}
.pagination-num.active {
  background: var(--color-primary);
  color: #fff;
  border-color: var(--color-primary);
  box-shadow: 0 4px 10px rgba(20, 80, 50, 0.2);
}
</style>
