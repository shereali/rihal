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
      <h1>কর্মকর্তার বিবরণী</h1>
      <NuxtLink to="/hr" class="back-link">
        <icon name="arrow-left" /> স্টাফ তালিকায় ফিরে যান
      </NuxtLink>
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
            <div class="stat-label">পরিচয়পত্র</div>
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
        <button class="tab-btn" :class="{ active: activeTab === 'salary' }" @click="activeTab = 'salary'">
          <icon name="money" /> বেতন
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'attendance' }" @click="activeTab = 'attendance'">
          <icon name="calendar" /> হাজিরা
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'responsibilities' }" @click="activeTab = 'responsibilities'">
          <icon name="assignment" /> দায়িত্ব
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'bio' }" @click="activeTab = 'bio'">
          <icon name="file-text" /> জীবনী
        </button>
      </div>

      <div class="tab-content-wrapper card">
        <!-- Salary tab -->
        <div v-if="activeTab === 'salary'" class="tab-content">
          <div class="empty-tab-state">
            <icon name="money" class="empty-icon-tab" />
            <p>এখনও কোনো বেতনের ইতিহাস নেই</p>
            <p class="text-muted">বেতন আপডেটের ইতিহাস এই অংশে দেখা যাবে</p>
          </div>
        </div>

        <!-- Attendance tab -->
        <div v-if="activeTab === 'attendance'" class="tab-content">
          <div class="empty-tab-state">
            <icon name="calendar" class="empty-icon-tab" />
            <p>হাজিরার তথ্য এখনও যুক্ত হয়নি</p>
            <p class="text-muted">হাজিরা ডেটা সংযোগের পর এই অংশে দেখা যাবে</p>
          </div>
        </div>

        <!-- Responsibilities tab -->
        <div v-if="activeTab === 'responsibilities'" class="tab-content">
          <div class="empty-tab-state">
            <icon name="assignment" class="empty-icon-tab" />
            <p>দায়িত্ব তথ্য এখনও যুক্ত হয়নি</p>
            <p class="text-muted">দায়িত্ব অথবা দক্ষতার তথ্য পরে যুক্ত হবে</p>
          </div>
        </div>

        <!-- Bio tab -->
        <div v-if="activeTab === 'bio'" class="tab-content">
          <div class="bio-card">
            <h3>জীবনী / পরিচয় (বাংলা)</h3>
            <div class="bio-text">
              {{ staff.bio_bn || 'কোনো জীবনী added নয়' }}
            </div>
            <p v-if="!staff.bio_bn" class="text-muted">এখনও কোনো জীবনী যুক্ত করা হয়নি</p>
          </div>
          <div class="bio-card">
            <h3>জীবনী / পরিচয় (ইংরেজি)</h3>
            <div class="bio-text">
              {{ staff.bio_en || 'No bio added' }}
            </div>
            <p v-if="!staff.bio_en" class="text-muted">No bio added yet</p>
          </div>
          <div v-if="!staff.bio_bn && !staff.bio_en" class="add-bio-btn">
            <button class="btn btn-outline">জীবনী যোগ করুন</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const router = useRouter()
const api = useApiClient()
const staffId = route.params.id as string
const loading = ref(true)
const staff = ref<any>(null)
const activeTab = ref('bio')

onMounted(async () => {
  try {
    const r = await api.get(`/hr/staff/${staffId}`)
    staff.value = r.data?.data
  } catch (e) {
    console.error('Failed to load staff:', e)
  } finally {
    loading.value = false
  }
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
  return 'টাকা ' + amount.toLocaleString('bn-BD', { minimumFractionDigits: 0 })
}
</script>

<style scoped>
.page-wrapper {
  max-width: 900px;
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
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.2rem;
}

h1 {
  font-size: 1.5rem;
  color: var(--color-primary);
  font-family: var(--font-bn);
  margin: 0;
}

.back-link {
  color: var(--color-primary);
  text-decoration: none;
  font-size: 0.82rem;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-family: var(--font-bn);
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
  padding: 4rem 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.7rem;
}

.not-found-icon {
  width: 56px;
  height: 56px;
  color: var(--color-text-muted);
}

.not-found h3 {
  font-family: var(--font-bn);
  font-size: 1.1rem;
  color: var(--color-text);
  margin: 0;
}

.not-found p {
  color: var(--color-text-muted);
  margin: 0;
}

.btn {
  padding: 0.6rem 1.2rem;
  border-radius: 10px;
  font-family: var(--font-bn);
  font-weight: 600;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  text-decoration: none;
  border: 1px solid transparent;
  transition: all 0.15s ease;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
}

.btn-primary:hover {
  opacity: 0.9;
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-outline:hover {
  background: var(--color-bg-muted);
}

.detail-layout {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.profile-card {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 15px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 1.2rem;
  padding: 1.2rem;
  background: rgba(0,0,0,0.02);
  border-bottom: 1px solid var(--color-border-light);
}

.profile-avatar {
  width: 72px;
  height: 72px;
  background: var(--color-primary-100);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-inner {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.profile-identity {
  flex: 1;
}

.profile-name {
  font-family: var(--font-bn);
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text);
  margin: 0 0 0.2rem;
}

.profile-designation {
  font-family: var(--font-bn);
  font-size: 0.85rem;
  color: var(--color-text-light);
  margin: 0 0 0.1rem;
}

.profile-department {
  font-family: var(--font-bn);
  font-size: 0.8rem;
  color: var(--color-text-muted);
  margin: 0 0 0.4rem;
}

.status-badge {
  padding: 0.2rem 0.6rem;
  border-radius: 99px;
  font-size: 0.65rem;
  font-weight: 600;
  white-space: nowrap;
}

.status-badge.active {
  background: #e6f4ec;
  color: #19724a;
}

.status-badge.inactive {
  background: #fde8e8;
  color: #a03030;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0;
}

.stat-item {
  padding: 0.8rem 1rem;
  border-bottom: 1px solid var(--color-border-light);
  border-right: 1px solid var(--color-border-light);
}

.stat-item.wide {
  grid-column: span 3;
  border-right: none;
}

.stat-item.salary-item {
  background: rgba(212, 175, 55, 0.08);
}

.stat-label {
  font-size: 0.65rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
  font-weight: 600;
  margin-bottom: 0.2rem;
  font-family: var(--font-bn);
}

.stat-value {
  font-size: 0.85rem;
  color: var(--color-text);
  font-family: var(--font-bn);
}

.stat-value a {
  color: var(--color-primary);
  text-decoration: none;
}

.stat-value.salary {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-primary);
}

.tabs-row {
  display: flex;
  gap: 0.4rem;
  margin-bottom: 0;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.5rem 0.8rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: white;
  color: var(--color-text-muted);
  font-family: var(--font-bn);
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.tab-btn:hover {
  background: var(--color-bg-muted);
  color: var(--color-text);
}

.tab-btn.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

.tab-btn.active icon {
  color: white;
}

.tab-content-wrapper {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 15px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.tab-content {
  padding: 1.2rem;
}

.empty-tab-state {
  text-align: center;
  padding: 2rem 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.empty-icon-tab {
  width: 40px;
  height: 40px;
  color: var(--color-text-muted);
}

.empty-tab-state p {
  font-family: var(--font-bn);
  font-size: 0.82rem;
  color: var(--color-text);
  margin: 0;
}

.text-muted {
  font-size: 0.72rem !important;
  color: var(--color-text-muted) !important;
}

.bio-card {
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--color-border-light);
}

.bio-card:last-of-type {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.bio-card h3 {
  font-family: var(--font-bn);
  font-size: 0.78rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
  margin: 0 0 0.5rem;
}

.bio-text {
  font-family: var(--font-bn);
  font-size: 0.85rem;
  color: var(--color-text);
  line-height: 1.7;
  white-space: pre-wrap;
}

.add-bio-btn {
  text-align: center;
  margin-top: 1rem;
}

@media (max-width: 600px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .stat-item,
  .stat-item.wide {
    grid-column: span 1;
    border-right: none;
  }
  .detail-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>