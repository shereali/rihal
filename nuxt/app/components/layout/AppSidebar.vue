<template>
  <div>
    <!-- Mobile Backdrop -->
    <div v-if="open" class="sidebar-backdrop" @click="emit('close')" />

    <aside class="sidebar" :class="{ open }">
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
        <button class="sidebar-close" @click="emit('close')" title="Close sidebar">
          <Icon name="close" size="20" />
        </button>
      </div>

      <div class="sidebar-tenant" v-if="tenant?.name">
        <p class="tenant-label">বর্তমান শাখা</p>
        <p class="tenant-name">{{ tenant.name }}</p>
      </div>

      <nav class="sidebar-nav">
        <div v-for="section in navSections" :key="section.title" class="nav-section">
          <p class="nav-section-label">{{ section.title }}</p>
          <ul class="nav-list">
            <li v-for="item in section.items" :key="item.label">
              <NuxtLink
                v-if="!item.subItems"
                :to="item.path"
                class="nav-item"
                :class="{ active: isActive(item.path) }"
                :title="item.tooltip"
                @click="handleNavClick"
              >
                <span class="nav-icon">
                  <Icon :name="item.icon" :size="18" />
                </span>
                <span class="nav-label">{{ item.label }}</span>
              </NuxtLink>

              <div v-else class="nav-item-group">
                <div 
                  class="nav-item nav-item-parent" 
                  :class="{ active: isAnySubItemActive(item.subItems), open: isExpanded(item.label) }"
                  @click="toggleExpand(item.label)"
                >
                  <span class="nav-icon">
                    <Icon :name="item.icon" :size="18" />
                  </span>
                  <span class="nav-label">{{ item.label }}</span>
                  <span class="nav-arrow" :class="{ rotated: isExpanded(item.label) }">
                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                  </span>
                </div>
                
                <ul v-show="isExpanded(item.label)" class="sub-nav-list">
                  <li v-for="subItem in item.subItems" :key="subItem.path">
                    <NuxtLink
                      :to="subItem.path"
                      class="sub-nav-item"
                      :class="{ active: isActive(subItem.path) }"
                      :title="subItem.tooltip"
                      @click="handleNavClick"
                    >
                      <span class="sub-nav-label">{{ subItem.label }}</span>
                    </NuxtLink>
                  </li>
                </ul>
              </div>
            </li>
          </ul>
        </div>
      </nav>

      <div class="sidebar-footer">
        <div class="sidebar-bottom-item language-selector">
          <select v-model="currentLanguage" class="language-select">
            <option value="bn">বাংলা</option>
            <option value="en">English</option>
            <option value="ar">العربية</option>
          </select>
        </div>

        <button class="sidebar-bottom-item logout-btn" @click="handleLogout">
          <Icon name="logout" size="14" />
          <span>লগ আউট</span>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, toRefs, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

const props = withDefaults(defineProps<{ open?: boolean }>(), { open: false })
const emit = defineEmits<{ (event: 'close'): void }>()
const { open } = toRefs(props)
const { currentUser, logout } = useAuth()
const currentLanguage = ref('bn')

const tenantColor = computed(() => 'transparent')

const tenant = ref({
  name: 'দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট',
})

const role = computed(() => (currentUser.value as any)?.role)

interface NavSubItem {
  path: string
  label: string
  tooltip?: string
}

interface NavItem {
  path?: string
  label: string
  icon: string
  tooltip?: string
  subItems?: NavSubItem[]
}

interface NavSection {
  title: string
  items: NavItem[]
}

const expandedItems = ref<Set<string>>(new Set())

function toggleExpand(label: string) {
  if (expandedItems.value.has(label)) {
    expandedItems.value.delete(label)
  } else {
    expandedItems.value.add(label)
  }
}

function isExpanded(label: string): boolean {
  return expandedItems.value.has(label)
}

function isAnySubItemActive(subItems?: NavSubItem[]): boolean {
  if (!subItems) return false
  return subItems.some(sub => isActive(sub.path))
}

const navSections = computed<NavSection[]>(() => {
  const sections: NavSection[] = []

  // Main
  const mainItems: NavItem[] = [
    { 
      label: 'ড্যাশবোর্ড', 
      icon: 'mdi:view-dashboard-outline', 
      subItems: [
        { path: '/dashboard', label: 'মূল ড্যাশবোর্ড', tooltip: 'মূল ড্যাশবোর্ড ও তথ্যসারণি' },
        { path: '/module-dashboard', label: 'মডিউল ড্যাশবোর্ড', tooltip: 'সব মডিউলের কার্যক্রম এক নজরে' }
      ]
    }
  ]

  if (role.value === 'student') {
    mainItems.push({ path: '/student/me', label: 'আমার তথ্য', icon: 'mdi:account-school-outline', tooltip: 'আমার প্রোফাইল ও ফলাফল' })
  }

  if (role.value === 'teacher') {
    mainItems.push({ path: '/teacher/my-assignments', label: 'আমার বরাদ্দ', icon: 'mdi:clipboard-text-outline', tooltip: 'আমার দেওয়া ক্লাস ও পাঠ' })
    mainItems.push(
      { path: '/attendance', label: 'হাজিরা', icon: 'mdi:calendar-check-outline', tooltip: 'শ্রেণি হাজিরা নেওয়া' },
      { path: '/exams', label: 'পরীক্ষা', icon: 'mdi:file-document-edit-outline', tooltip: 'পরীক্ষা তৈরি, ফলাফল, সিট ও জাতা' },
      { path: '/academic', label: 'একাডেমিক', icon: 'mdi:school-outline', tooltip: 'শ্রেণি, সেশন ও পাঠ্যক্রম' },
    )
  }

  if (role.value === 'parent' || role.value === 'guardian') {
    mainItems.push({ path: '/portal', label: 'অভিভাবক পোর্টাল', icon: 'mdi:account-child-outline', tooltip: 'ছাত্রের প্রগতি ও আয়-ব্যয়' })
  }

  sections.push({ title: 'প্রধান মেনু', items: mainItems })

  if (role.value === 'admin' || role.value === 'super_admin' || !role.value) {
    sections.push({
      title: 'একাডেমিক ও শিক্ষার্থী',
      items: [
        { 
          label: 'ছাত্র ও ভর্তি', 
          icon: 'mdi:account-group-outline', 
          subItems: [
            { path: '/students', label: 'ছাত্র তালিকা', tooltip: 'ছাত্র তালিকা ও প্রোফাইল' },
            { path: '/enrollments', label: 'ভর্তি', tooltip: 'নতুন ভর্তি' },
            { path: '/students/admission-register', label: 'ভর্তি খাতা', tooltip: 'ভর্তি খাতা ও ফর্ম' },
            { path: '/promotions', label: 'প্রমোশন', tooltip: 'পরবর্তী শ্রেণিতে প্রমোশন' },
            { path: '/certificates', label: 'সনদ ও প্রশংসাপত্র', tooltip: 'প্রশংসাপত্র প্রদান' },
          ]
        },
        { 
          label: 'একাডেমিক বিবরণ', 
          icon: 'mdi:school-outline', 
          subItems: [
            { path: '/academic', label: 'রুটিন ও সেশন', tooltip: 'রুটিন, সেশন ও শ্রেণি' },
            { path: '/attendance', label: 'শ্রেণি হাজিরা', tooltip: 'শ্রেণি হাজিরা রেকর্ড' },
            { path: '/digital-attendance', label: 'ডিজিটাল হাজিরা', tooltip: 'ডিজিটাল হাজিরা ব্যবস্থা' },
            { path: '/lesson-plans', label: 'পাঠ পরিকল্পনা', tooltip: 'পাঠ পরিকল্পনা ও সিলেবাস' },
            { path: '/homework', label: 'বাড়ির কাজ', tooltip: 'বাড়ির কাজ প্রদান' }
          ]
        },
      ],
    })

    sections.push({
      title: 'পরীক্ষা ও ফলাফল',
      items: [
        { 
          label: 'পরীক্ষা ব্যবস্থাপনা', 
          icon: 'mdi:file-document-edit-outline', 
          subItems: [
            { path: '/exams', label: 'পরীক্ষা ও রুটিন', tooltip: 'পরীক্ষা তৈরি ও রুটিন' },
            { path: '/marks', label: 'মার্কস এন্ট্রি', tooltip: 'পরীক্ষার মার্কস এন্ট্রি' },
            { path: '/results', label: 'ফলাফল প্রকাশ', tooltip: 'ফলাফল তৈরি ও প্রকাশ' },
            { path: '/lesson-evaluation', label: 'দৈনিক মূল্যায়ন', tooltip: 'সবক মূল্যায়ন শীট' }
          ]
        },
      ],
    })

    sections.push({
      title: 'স্টাফ ও হিসাব',
      items: [
        { 
          label: 'শিক্ষক ও স্টাফ', 
          icon: 'mdi:account-tie', 
          subItems: [
            { path: '/hr', label: 'স্টাফ তালিকা (HR)', tooltip: 'কর্মকর্তা ও বেতন' },
            { path: '/hr/recruitments', label: 'নিয়োগ', tooltip: 'নতুন স্টাফ নিয়োগ' },
            { path: '/hr/holidays', label: 'ছুটির দিন', tooltip: 'সরকারি ও সাপ্তাহিক ছুটি' },
            { path: '/hr/events', label: 'ইভেন্ট', tooltip: 'স্টাফ ইভেন্ট' },
            { path: '/hr/hostel-visitors', label: 'হোস্টেল ভিজিটর', tooltip: 'হোস্টেল ভিজিটর ট্র্যাকিং' },
            { path: '/teacher-assignments', label: 'শিক্ষকের কাজ', tooltip: 'শিক্ষকের কাজ বরাদ্দ' },
            { path: '/leave-applications', label: 'ছুটির আবেদন', tooltip: 'ছুটির আবেদন ও অনুমোদন' }
          ]
        },
        { 
          label: 'অ্যাকাউন্টিং (হিসাব)', 
          icon: 'mdi:book-open-page-variant-outline', 
          subItems: [
            { path: '/accounting', label: 'ড্যাশবোর্ড', tooltip: 'অ্যাকাউন্টিং ড্যাশবোর্ড' },
            { path: '/accounting/vouchers', label: 'ভাউচার এন্ট্রি', tooltip: 'রশিদ ও পেমেন্ট ভাউচার' },
            { path: '/accounting/chart', label: 'চার্ট অফ একাউন্টস', tooltip: 'খাত পরিচালনা' },
            { path: '/accounting/statements', label: 'স্টেটমেন্ট', tooltip: 'আর্থিক প্রতিবেদন' },
            { path: '/accounting/fixed-assets', label: 'স্থায়ী সম্পদ', tooltip: 'স্থায়ী সম্পদের হিসাব' },
          ]
        },
        { 
          label: 'অর্থায়ন ও ফান্ড', 
          icon: 'mdi:cash-multiple', 
          subItems: [
            { path: '/fees', label: 'ফি ও আদায়', tooltip: 'ফি কালেকশন ও হিসাব' },
            { path: '/finance', label: 'ফান্ড ড্যাশবোর্ড', tooltip: 'লিল্লাহ, যাকাত ও ফান্ড' },
            { path: '/finance/donations', label: 'অনুদান (Donation)', tooltip: 'অনুদান এন্ট্রি' },
            { path: '/finance/expenses', label: 'খরচ (Expense)', tooltip: 'ফান্ড খরচ' },
            { path: '/orphan-sponsorship', label: 'এতিম স্পনসরশিপ', tooltip: 'এতিম স্পনসরশিপ পরিচালনা' },
            { path: '/loan-due', label: 'ঋণ ও বকেয়া', tooltip: 'ঋণ ও বকেয়া আদায়' }
          ]
        },
      ],
    })

    sections.push({
      title: 'প্রশাসন ও যোগাযোগ',
      items: [
        { 
          label: 'প্রশাসন ও সম্পদ', 
          icon: 'mdi:domain', 
          subItems: [
            { path: '/administration', label: 'প্রশাসন ড্যাশবোর্ড', tooltip: 'প্রশাসনিক কাজ' },
            { path: '/administration/complaints', label: 'অভিযোগ ও পরামর্শ', tooltip: 'অভিযোগ বাক্স' },
            { path: '/administration/responsibilities', label: 'দায়িত্ব বণ্টন', tooltip: 'দায়িত্ব ম্যাট্রিক্স' },
            { path: '/administration/discharge', label: 'অব্যাহতি রেজিস্টার', tooltip: 'স্টাফ অব্যাহতি' },
            { path: '/properties', label: 'সম্পদ ও সম্পত্তি', tooltip: 'সম্পদ ব্যবস্থাপনা' },
            { path: '/reports', label: 'রিপোর্টস', tooltip: 'প্রিন্ট ও ডেটা এক্সপোর্ট' }
          ]
        },
        {
          label: 'হোস্টেল ও বোর্ডিং',
          icon: 'mdi:bed',
          subItems: [
            { path: '/hostel', label: 'হোস্টেল কক্ষ', tooltip: 'কক্ষ ব্যবস্থাপনা' },
            { path: '/hostel/boarding-bazaar', label: 'বোর্ডিং বাজার', tooltip: 'বাজারের হিসাব' },
            { path: '/hostel/boarding-meals', label: 'বোর্ডিং মিলস', tooltip: 'মিল হিসাব' },
          ]
        },
        { 
          label: 'যাতায়াত ও পরিবহন', 
          icon: 'mdi:bus', 
          subItems: [
            { path: '/transport', label: 'রুট ও পরিবহন', tooltip: 'রুট পরিচালনা' },
            { path: '/transport/buses', label: 'বাস ও যানবাহন', tooltip: 'বাসের তালিকা' },
            { path: '/transport/assignments', label: 'অ্যাসাইনমেন্ট', tooltip: 'বাস, চালক ও স্টাফ' }
          ]
        },
        { 
          label: 'যোগাযোগ ও নোটিশ', 
          icon: 'mdi:bullhorn-outline', 
          subItems: [
            { path: '/notice', label: 'বিজ্ঞপ্তি ও ঘোষণা', tooltip: 'সব বিজ্ঞপ্তি তৈরি' },
            { path: '/notifications', label: 'নোটিফিকেশন', tooltip: 'সিস্টেম নোটিফিকেশন' },
            { path: '/reminder-tasks', label: 'রিমাইন্ডার ও টাস্ক', tooltip: 'রিমাইন্ডার এবং করণীয়' }
          ]
        },
      ],
    })
  }

  // System Settings
  sections.push({
    title: 'সিস্টেম',
    items: [
      { 
        label: 'সেটিংস ও লগ', 
        icon: 'mdi:cog-outline', 
        subItems: [
          { path: '/settings', label: 'সেটিংস', tooltip: 'সিস্টেম কনফিগারেশন' },
          { path: '/activity-log', label: 'কার্যকলাপ লগ', tooltip: 'ব্যবহারকারীর কার্যকলাপ' },
          { path: '/changelog', label: 'পরিবর্তন লগ', tooltip: 'সিস্টেম আপডেট লগ' }
        ]
      },
    ],
  })

  return sections
})

function isActive(path?: string): boolean {
  if (!path) return false
  const current = useRoute().path
  if (path === '/dashboard') return current === '/dashboard'
  if (path === '/settings') return current === '/settings'
  if (path === '/finance') return current === '/finance'
  if (path === '/academic') return current === '/academic'
  if (path === '/hr') return current === '/hr'
  if (path === '/hostel') return current === '/hostel'
  if (path === '/transport') return current === '/transport'
  if (path === '/results') return current === '/results'
  if (path === '/fees') return current === '/fees'
  return current === path || current.startsWith(`${path}/`)
}

function handleNavClick() {
  if (import.meta.client && window.innerWidth <= 768) {
    emit('close')
  }
}

function handleLogout() {
  logout()
  navigateTo('/login')
}

// Auto-expand menus containing active route on mount
onMounted(() => {
  navSections.value.forEach(section => {
    section.items.forEach(item => {
      if (item.subItems && isAnySubItemActive(item.subItems)) {
        expandedItems.value.add(item.label)
      }
    })
  })
})
</script>

<style scoped>
.sidebar-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(2px);
  z-index: 95;
  display: none;
}

.sidebar {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  width: var(--sidebar-width, 260px);
  background: var(--glass-bg) !important;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-right: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  color: var(--color-text);
  z-index: 40;
  display: flex;
  flex-direction: column;
  transition: width var(--transition-normal, 0.3s), transform var(--transition-normal, 0.3s);
}

@media (min-width: 769px) {
  .sidebar:not(.open) {
    width: 80px;
  }
  .sidebar:not(.open) .brand-text,
  .sidebar:not(.open) .tenant-label,
  .sidebar:not(.open) .tenant-name,
  .sidebar:not(.open) .nav-section-label,
  .sidebar:not(.open) .nav-label,
  .sidebar:not(.open) .language-selector,
  .sidebar:not(.open) .logout-btn span,
  .sidebar:not(.open) .nav-arrow,
  .sidebar:not(.open) .sub-nav-list {
    display: none;
  }
  .sidebar:not(.open) .nav-item {
    justify-content: center;
    padding: 0.6rem;
  }
  .sidebar:not(.open) .sidebar-brand {
    justify-content: center;
  }
  .sidebar:not(.open) .sidebar-footer {
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .sidebar-backdrop {
    display: block;
  }

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

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--glass-border);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.brand-logo {
  width: 36px;
  height: 36px;
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
  font-size: 1.05rem;
  line-height: 1.2;
  letter-spacing: 0.02em;
}

.brand-subtitle {
  font-size: 0.75rem;
  opacity: 0.7;
}

.sidebar-close {
  display: none;
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  padding: 0.25rem;
}

.sidebar-close svg {
  width: 20px;
  height: 20px;
}

.sidebar-tenant {
  padding: 0.75rem 1.25rem 0.25rem;
}

.tenant-label {
  font-size: 0.7rem;
  opacity: 0.55;
  letter-spacing: 0.02em;
  margin-bottom: 0.15rem;
}

.tenant-name {
  font-size: 0.82rem;
  font-weight: 600;
  line-height: 1.35;
  opacity: 0.9;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 0.5rem 0.65rem;
}

.nav-section {
  margin-bottom: 0.75rem;
}

.nav-section-label {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-text-muted);
  padding: 0.4rem 0.5rem 0.25rem;
}

.nav-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.12rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.48rem 0.75rem;
  border-radius: 0.45rem;
  color: var(--color-text);
  font-size: 0.84rem;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.15s ease;
  white-space: nowrap;
  position: relative;
}

.nav-item:hover {
  background: var(--color-primary-50);
  color: var(--color-text);
}

.nav-item.active {
  background: var(--color-primary-100);
  color: var(--color-primary);
  font-weight: 600;
}

.nav-item.active::before {
  content: '';
  position: absolute;
  left: -0.65rem;
  top: 0.35rem;
  bottom: 0.35rem;
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
  opacity: 0.85;
}

.nav-item.active .nav-icon {
  opacity: 1;
  color: var(--color-accent-light);
}

.nav-item .nav-label {
  overflow: hidden;
  text-overflow: ellipsis;
}

.nav-item-parent {
  cursor: pointer;
  user-select: none;
}

.nav-arrow {
  margin-left: auto;
  display: flex;
  align-items: center;
  transition: transform 0.2s ease;
  opacity: 0.6;
}

.nav-arrow.rotated {
  transform: rotate(180deg);
}

.sub-nav-list {
  list-style: none;
  margin: 0;
  padding: 0.25rem 0 0.25rem 2.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.sub-nav-item {
  display: block;
  padding: 0.35rem 0.5rem;
  color: var(--color-text-muted);
  font-size: 0.8rem;
  text-decoration: none;
  border-radius: 0.35rem;
  transition: all 0.15s ease;
  position: relative;
}

.sub-nav-item:hover {
  color: var(--color-primary);
  background: var(--color-primary-50);
}

.sub-nav-item.active {
  color: var(--color-primary);
  background: var(--color-primary-50);
  font-weight: 600;
}

.sub-nav-item.active::before {
  content: '•';
  position: absolute;
  left: -0.75rem;
  color: var(--color-primary);
}

.sidebar-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.65rem 0.85rem;
  border-top: 1px solid var(--glass-border);
  gap: 0.5rem;
}

.sidebar-bottom-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.language-selector select {
  background: var(--glass-bg);
  color: var(--color-text);
  border: 1px solid var(--glass-border);
  border-radius: 0.35rem;
  padding: 0.25rem 0.4rem;
  font-size: 0.72rem;
  font-weight: 500;
  cursor: pointer;
  outline: none;
}

.language-selector select option {
  background: var(--color-surface);
  color: var(--color-text);
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0.35rem 0.55rem;
  border-radius: 0.35rem;
  transition: all 0.15s ease;
}

.logout-btn:hover {
  background: var(--color-primary-50);
  color: var(--color-text);
}

.logout-btn svg {
  width: 14px;
  height: 14px;
}
</style>