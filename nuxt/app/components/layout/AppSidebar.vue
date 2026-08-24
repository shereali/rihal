<template>
  <div class="sidebar" :style="{ backgroundColor: tenantColor }">
    <div class="sidebar-header">
      <div class="sidebar-brand">
        <div class="brand-logo">
          <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="3"/>
            <path d="M30 70L50 30L70 70" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M40 55H60" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            <path d="M45 65H55" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="brand-text">
          <span class="brand-name">Rihal</span>
          <span class="brand-subtitle">মাদ্রাসা ব্যবস্থাপনা</span>
        </div>
      </div>
      <button class="sidebar-close" @click="showSidebar = false" title="Close sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <div class="sidebar-tenant" v-if="tenant?.name">
      <p class="tenant-label">বর্তমান শাখা</p>
      <p class="tenant-name">{{ tenant.name }}</p>
    </div>

    <nav class="sidebar-nav">
      <p class="nav-section-label">প্রধান মেনু</p>
      <ul class="nav-list">
        <li v-for="item in mainNavItems" :key="item.path">
          <NuxtLink
            :to="item.path"
            class="nav-item"
            :class="{ active: isActive(item.path) }"
            :title="item.tooltip"
          >
            <span class="nav-icon">
              <icon :name="item.icon" />
            </span>
            <span class="nav-label">{{ item.label }}</span>
          </NuxtLink>
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-bottom-item language-selector">
        <select v-model="currentLanguage" class="language-select">
          <option value="bn">বাংলা</option>
          <option value="en">ইংরেজি</option>
          <option value="ar">আরবি</option>
        </select>
      </div>

      <button class="sidebar-bottom-item logout-btn" @click="handleLogout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
          <polyline points="16,17 21,12 16,7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>লগ আউট</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

const { currentUser, logout } = useAuth()
const showSidebar = ref(true)
const currentLanguage = ref('bn')

const tenantColor = computed(() => '#145032')

const tenant = ref({
  name: 'দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট',
})

const role = computed(() => (currentUser.value as any)?.role)

const mainNavItems = computed(() => {
  const items = [
    { path: '/dashboard', label: 'ড্যাশবোর্ড', icon: 'dashboard', tooltip: 'মূল ড্যাশবোর্ড ও তথ্যসারণি' },
    { path: '/notice', label: 'বিজ্ঞপ্তি ও ঘোষণা', icon: 'notice', tooltip: 'সব বিজ্ঞপ্তি তৈরি, দেখা ও সম্পাদনা' },
  ]

  if (role.value === 'student') {
    items.push({ path: '/student/me', label: 'আমার তথ্য', icon: 'students', tooltip: 'আমার প্রোফাইল ও ফলাফল' })
  }

  if (role.value === 'teacher') {
    items.push({ path: '/teacher/my-assignments', label: 'আমার বরাদ্দ', icon: 'assignment', tooltip: 'আমার দেওয়া ক্লাস ও পাঠ' })
    items.push(
      { path: '/attendance', label: 'হাজিরা', icon: 'attendance', tooltip: 'শ্রেণি হাজিরা নেওয়া' },
      { path: '/exams', label: 'পরীক্ষা', icon: 'exam', tooltip: 'পরীক্ষা তৈরি, ফলাফল, সিট ও জাতা' },
      { path: '/academic', label: 'একাডেমিক', icon: 'academic', tooltip: 'শ্রেণি, সেশন ও পাঠ্যক্রম' },
    )
  }

  if (role.value === 'parent' || role.value === 'guardian') {
    items.push({ path: '/portal', label: 'অভিভাবক পোর্টাল', icon: 'users', tooltip: 'ছাত্রের প্রগতি ও আয়-ব্যয়' })
  }

  if (role.value === 'admin' || role.value === 'super_admin') {
    items.push(
      // শিক্ষার্থী
      { path: '/students', label: 'ছাত্র ব্যবস্থাপনা', icon: 'students', tooltip: 'ছাত্র তালিকা, ভর্তি, বরাদ্দ' },
      { path: '/attendance', label: 'হাজিরা', icon: 'attendance', tooltip: 'হাজিরা রেকর্ড ও বার্ষিক হাজিরার হার' },
      { path: '/exams', label: 'পরীক্ষা', icon: 'exam', tooltip: 'পরীক্ষা, ফলাফল, সিট, জাতা ও বিন্যাস' },

      // একাডেমিক
      { path: '/academic', label: 'একাডেমিক', icon: 'academic', tooltip: 'একাডেমিক ড্যাশবোর্ড' },
      { path: '/academic/manage', label: 'একাডেমিক সেটআপ', icon: 'settings', tooltip: 'শ্রেণি, সেশন, বিভাগ, শিক্ষক নিয়োগ' },
      { path: '/academic/timetable', label: 'ক্লাস রুটিন', icon: 'calendar', tooltip: 'সপ্তাহান্তিক ও দৈনিক রুটিন' },

      // ফলাফল
      { path: '/results', label: 'ফলাফল', icon: 'document', tooltip: 'পরীক্ষার ফলাফল, জিপিএ ও মার্কস শীট' },
      { path: '/results/gpa', label: 'জিপিএ হিসাব', icon: 'chart', tooltip: 'গ্রেড পয়েন্ট গড় ও শ্রেণি অনুযায়ী অগ্রগতি' },

      // গৃহকাজ ও পাঠ পরিকল্পনা
      { path: '/homework', label: 'বাড়ির কাজ', icon: 'book', tooltip: 'দৈনিক গৃহকাজ, জমা ও মূল্যায়ন' },
      { path: '/lesson-plans', label: 'পাঠ পরিকল্পনা', icon: 'academic', tooltip: 'শিক্ষকদের দৈনিক পাঠ পরিকল্পনা' },

      // অ্যাকাউন্টিং
      { path: '/fees', label: 'ফি ও আয়-ব্যয়', icon: 'fees', tooltip: 'ফি কালেকশন, আয় ও ব্যয়' },
      { path: '/finance', label: 'অ্যাকাউন্টিং', icon: 'money', tooltip: 'ফান্ড, ডোনর, ডোনেশন, ব্যয় ও রিসেপ্ট' },
      { path: '/finance/funds', label: 'ফান্ড', icon: 'donor', tooltip: 'ফান্ড তৈরি, ব্যালেন্স ও ব্যয়' },
      { path: '/finance/donors', label: 'দাতা ও অনুদান', icon: 'donor', tooltip: 'দাতা তালিকা ও ডোনেশন হিসাব' },
      { path: '/finance/expenses', label: 'ব্যয়', icon: 'money', tooltip: 'ব্যয়, ভেন্ডর ও বার্ষিক হিসাব' },
      { path: '/finance/stocks', label: 'রিসেপ্ট স্টক', icon: 'document', tooltip: 'অর্থ রশিদ, স্টক ও বিতরণ' },
      { path: '/loan-due', label: 'লোন ও ডিউ', icon: 'cash', tooltip: 'ঋণ, কিস্তি ও বাকিয়া ব্যবস্থাপনা' },
      { path: '/orphan-sponsorship', label: 'অর্ফান স্পন্সরশিপ', icon: 'child', tooltip: 'অর্ফান শিশু এবং স্পন্সরশিপ ব্যবস্থাপনা' },

      // প্রশাসনিক
      { path: '/administration', label: 'প্রশাসনিক ড্যাশবোর্ড', icon: 'dashboard', tooltip: 'কর্মকর্তা, আয়োজন, ছুটি ও নিয়োগ এক নজরে' },
      { path: '/hr', label: 'স্টাফ ও কর্মী', icon: 'users', tooltip: 'কর্মকর্তা, বেতন, দায়ি�্ব, আয়োজন, ছুটি, ভিজিটর ও নিয়োগ' },
      { path: '/hr/events', label: 'আয়োজন', icon: 'calendar', tooltip: 'আয়োজন তৈরি, নাম নিবন্ধন ও নিবন্ধন ব্যবস্থাপনা' },
      { path: '/hr/holidays', label: 'ছুটির দিন', icon: 'calendar', tooltip: 'ছুটি, ছুটির প্রকার ও বার্ষিক ছুটি হিসাব' },
      { path: '/hr/recruitments', label: 'নিয়োগ', icon: 'users', tooltip: 'নিয়োগ বিজ্ঞপ্তি, আবেদন ও নিয়োগ প্রক্রিয়া' },

      // অপারেশনস
      { path: '/hostel', label: 'হোস্টেল', icon: 'building', tooltip: 'হোস্টেল কক্ষ, ওয়ার্ডেন ও ভিজিটর' },
      { path: '/hostel/rooms', label: 'কক্ষ ব্যবস্থাপনা', icon: 'building', tooltip: 'কক্ষ তৈরি, ধারণক্ষমতা, ভাড়া ও ছাত্র বরাদ্দ' },
      { path: '/transport', label: 'পরিবহন', icon: 'bus', tooltip: 'রুট, বাস, চালক ও শিক্ষার্থী বরাদ্দ' },
      { path: '/transport/routes', label: 'রুট ব্যবস্থাপনা', icon: 'bus', tooltip: 'রুট তৈরি, দূরত্ব, ভাড়া ও সময়সূচি' },
      { path: '/transport/buses', label: 'বাস ব্যবস্থাপনা', icon: 'bus', tooltip: 'বাস তথ্য, চালক, ক্ষমতা ও ডকুমেন্ট' },
      { path: '/transport/assignments', label: 'যাতায়াত বরাদ্দ', icon: 'assignment', tooltip: 'শিক্ষার্থী-বাস-রুট বরাদ্দ' },

      // সম্পত্তি
      { path: '/properties', label: 'সম্পত্তি ও সম্পদ', icon: 'building', tooltip: 'জমি, ভবন, সম্পত্তি, মূল্য ও রক্ষণাবেক্ষণ' },
      { path: '/properties/:id', label: 'সম্পত্তি বিবরণী', icon: 'building', tooltip: 'সম্পত্তির ডকুমেন্ট, রক্ষণাবেক্ষণ ও ভিজিটর' },

      // শিক্ষক বরাদ্দ
      { path: '/teacher-assignments', label: 'শিক্ষক বরাদ্দ', icon: 'assignment', tooltip: 'শিক্ষক-শ্রেণি-বিষয় বরাদ্দ ও সময়সূচি' },
      { path: '/teacher/my-assignments', label: 'অফিসিয়াল বরাদ্দ', icon: 'assignment', tooltip: 'শিক্ষকের দেওয়া ক্লাস ও পাঠের তালিকা' },

      // ফি ও আয়-ব্যয়
      { path: '/fees', label: 'ফি ও আয়-ব্যয়', icon: 'fees', tooltip: 'ফি কালেকশন, আয় ও ব্যয়' },
      { path: '/fees/collect', label: 'ফি সংগ্রহ', icon: 'money', tooltip: 'ছাত্র ফি প্রদান রেকর্ড ও পেমেন্ট' },

      // রিপোর্ট ও নোটিফিকেশন
      { path: '/reports', label: 'রিপোর্ট ও এক্সপোর্ট', icon: 'chart', tooltip: 'উপস্থাপন, প্রিন্ট ও CSV/엑সপোর্ট রিপোর্ট' },
      { path: '/activity-log', label: 'গতিবিধি লগ', icon: 'clock', tooltip: 'সিস্টেমে সব এন্ট্রি, ব্যবহারকারী ও সময়ের রেকর্ড' },
      { path: '/notifications', label: 'নোটিফিকেশন', icon: 'bell', tooltip: 'বিজ্ঞপ্তি, অনুপস্থিতি ও পেমেন্ট রিমাইন্ডার' },
      { path: '/portal', label: 'অভিভাবক পোর্টাল', icon: 'users', tooltip: 'অভিভাবকদের জন্য মোবাইল-ফ্রেন্ডলি পোর্টাল' },
      { path: '/reminder-tasks', label: 'রিমাইন্ডার টাস্ক', icon: 'clock', tooltip: 'স্মরণী বার্তা ও অটোমেটেড টাস্ক তৈরি ও ব্যবস্থাপনা' },
      { path: '/leave-applications', label: 'ছুটি ব্যবস্থাপনা', icon: 'calendar', tooltip: 'কর্মকর্তা ও শিক্ষকদের ছুটির আবেদন, অনুমোদন ও প্রত্যাখ্যান' },
    )
  }

  // settings always last; separate sub-items for depth
  items.push(
    { path: '/settings', label: 'সিস্টেম সেটিংস', icon: 'settings', tooltip: 'সিস্টেম কনফিগারেশন, ব্যবহারকারী, ভাষা ও বিজ্ঞপ্তি' },
    { path: '/settings/admin-users', label: 'ব্যবহারকারী ও ভূমিকা', icon: 'users', tooltip: 'ব্যবহারকারী তৈরি, ভূমিকা ও অনুমতি' },
    { path: '/settings/sessions', label: 'সেশন সেটআপ', icon: 'calendar', tooltip: 'শিক্ষাবর্ষ, সেশন ও পরীক্ষা সেশন' },
    { path: '/settings/classes', label: 'শ্রেণি ব্যবস্থাপনা', icon: 'academic', tooltip: 'শ্রেণি, বিভাগ, শিক্ষক নিয়োগ ও সিট' },
    { path: '/settings/sections', label: 'বিভাগ ব্যবস্থাপনা', icon: 'academic', tooltip: 'বিভাগ, শিক্ষক-বিষয় বরাদ্দ' },
    { path: '/settings/subjects', label: 'বিষয় ব্যবস্থাপনা', icon: 'academic', tooltip: 'বিষয় তৈরি, পাঠ্যক্রম, বই ও শিক্ষক নিয়োগ' },
    { path: '/settings/subject-assignment', label: 'বিষয় বরাদ্দ', icon: 'assignment', tooltip: 'শিক্ষক-বিষয়-শ্রেণি বরাদ্দ ও ক্লাস রুটিন' },
  )

  return items
})

function isActive(path: string): boolean {
  return useRoute().path.startsWith(path)
}

function handleLogout() {
  logout()
  navigateTo('/login')
}
</script>

<style scoped>
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: var(--sidebar-width);
  height: 100vh;
  background: var(--color-bg-sidebar);
  color: white;
  display: flex;
  flex-direction: column;
  z-index: 100;
  transition: transform var(--transition-normal);
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.brand-logo {
  width: 40px;
  height: 40px;
  color: var(--color-accent);
  flex-shrink: 0;
}

.brand-logo svg {
  width: 100%;
  height: 100%;
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-weight: 700;
  font-size: 1rem;
  line-height: 1.2;
}

.brand-subtitle {
  font-size: 0.75rem;
  opacity: 0.65;
}

.sidebar-close {
  display: none;
}

.sidebar-tenant {
  padding: 0.75rem 1.25rem 0;
}

.tenant-label {
  font-size: 0.7rem;
  opacity: 0.55;
  letter-spacing: 0.02em;
  margin-bottom: 0.15rem;
}

.tenant-name {
  font-size: 0.85rem;
  font-weight: 600;
  line-height: 1.4;
  opacity: 0.9;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 0.75rem 0.5rem;
}

.nav-section-label {
  font-size: 0.65rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.35);
  padding: 0.5rem 0.65rem 0.4rem;
  margin-top: 0.2rem;
}

.nav-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.08rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.55rem 0.85rem;
  border-radius: 0.5rem;
  color: rgba(255, 255, 255, 0.78);
  font-size: 0.85rem;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.15s ease;
  white-space: nowrap;
  position: relative;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.nav-item.active {
  background: rgba(255, 255, 255, 0.14);
  color: #fff;
}

.nav-item.active::before {
  content: '';
  position: absolute;
  left: -0.5rem;
  top: 0.45rem;
  bottom: 0.45rem;
  width: 3px;
  background: var(--color-accent);
  border-radius: 0 3px 3px 0;
}

.nav-item .nav-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.nav-item .nav-label {
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.6rem 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  gap: 0.6rem;
}

.sidebar-bottom-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.language-selector select {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 0.35rem;
  padding: 0.28rem 0.3rem;
  font-size: 0.7rem;
  font-weight: 500;
  cursor: pointer;
  outline: none;
}

.language-selector select option {
  background: #000;
  color: #fff;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0.35rem 0.55rem;
  border-radius: 0.35rem;
  transition: all 0.15s ease;
}

.logout-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.logout-btn svg {
  width: 14px;
  height: 14px;
}

@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .sidebar-close {
    display: flex;
  }
}
</style>