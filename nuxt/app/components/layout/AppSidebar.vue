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

    <!-- Madrasa name (tenant context) -->
    <div class="sidebar-tenant" v-if="tenant?.name">
      <p class="tenant-label">বর্তমান শাখা</p>
      <p class="tenant-name">{{ tenant.name }}</p>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <p class="nav-section-label">প্রধান মেনু</p>
      <ul class="nav-list">
        <li v-for="item in mainNavItems" :key="item.path">
          <NuxtLink
            :to="item.path"
            class="nav-item"
            :class="{ active: isActive(item.path) }"
          >
            <span class="nav-icon">
              <icon :name="item.icon" />
            </span>
            <span class="nav-label">{{ item.label }}</span>
          </NuxtLink>
        </li>
      </ul>
    </nav>

    <!-- Bottom section: language + logout -->
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

// Tenant color from user or default
const tenantColor = computed(() => {
  // In real app, fetch from tenant API
  return '#145032'
})

const tenant = ref({
  name: 'দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট',
})

// Navigation items (role-aware)
const role = computed(() => (currentUser.value as any)?.role)
const mainNavItems = computed(() => {
  const items = [
    { path: '/dashboard', label: 'ড্যাশবোর্ড', icon: 'dashboard' },
    { path: '/notice', label: 'বিজ্ঞপ্তি ও ঘোষণা', icon: 'notice' },
  ]
  if (role.value === 'student') {
    items.push({ path: '/student/me', label: 'আমার তথ্য', icon: 'students' })
  } else if (role.value === 'teacher') {
    items.push({ path: '/teacher/my-assignments', label: 'আমার বরাদ্দ', icon: 'assignment' })
    items.push(
      { path: '/attendance', label: 'হাজিরা', icon: 'attendance' },
      { path: '/exams', label: 'পরীক্ষা', icon: 'exam' },
      { path: '/academic', label: 'একাডেমিক', icon: 'academic' },
    )
  } else {
    // admin / super_admin
    items.push(
      { path: '/students', label: 'ছাত্র ব্যবস্থাপনা', icon: 'students' },
      { path: '/attendance', label: 'হাজিরা', icon: 'attendance' },
      { path: '/academic', label: 'একাডেমিক', icon: 'academic' },
      { path: '/exams', label: 'পরীক্ষা', icon: 'exam' },
      { path: '/homework', label: 'বাড়ির কাজ', icon: 'book' },
      { path: '/teacher-assignments', label: 'শিক্ষক বরাদ্দ', icon: 'assignment' },
      { path: '/fees', label: 'ফি ও আয়-ব্যয়', icon: 'fees' },
      { path: '/finance/donors', label: 'দাতা ও অনুদান', icon: 'donor' },
      { path: '/reports', label: 'রিপোর্ট ও এক্সপোর্ট', icon: 'chart' },
      { path: '/notifications', label: 'নোটিফিকেশন', icon: 'bell' },
      { path: '/portal', label: 'অভিভাবক পোর্টাল', icon: 'users' },
    )
  }
  items.push({ path: '/settings', label: 'সিস্টেম সেটিংস', icon: 'settings' })
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
  font-size: 0.6875rem;
  opacity: 0.7;
  line-height: 1;
}

.sidebar-close {
  display: none;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  padding: 0.25rem;
}

.sidebar-close svg {
  width: 20px;
  height: 20px;
}

.sidebar-tenant {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 0.5rem;
}

.tenant-label {
  font-size: 0.6875rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  opacity: 0.6;
  margin-bottom: 0.25rem;
}

.tenant-name {
  font-size: 0.875rem;
  font-weight: 500;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem 0;
}

.nav-section-label {
  font-size: 0.6875rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  opacity: 0.5;
  padding: 0 1.25rem 0.5rem;
  margin-bottom: 0.25rem;
}

.nav-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 1.25rem;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all var(--transition-fast);
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.08);
  color: white;
}

.nav-item.active {
  background: rgba(255, 255, 255, 0.12);
  color: white;
  border-left-color: var(--color-accent);
}

.nav-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.nav-icon svg {
  width: 18px;
  height: 18px;
}

.nav-label {
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
}

.sidebar-footer {
  padding: 0.75rem 0;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-bottom-item {
  display: flex;
  align-items: center;
  padding: 0.5rem 1.25rem;
}

.logout-btn {
  width: 100%;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  transition: color var(--transition-fast);
}

.logout-btn:hover {
  color: white;
}

.logout-btn svg {
  width: 18px;
  height: 18px;
}

.language-select {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: var(--radius-sm);
  padding: 0.375rem 0.5rem;
  font-size: 0.8125rem;
  cursor: pointer;
  outline: none;
}

.language-select:focus {
  border-color: var(--color-accent);
}

@media (max-width: 768px) {
  .sidebar-close {
    display: block;
  }

  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .sidebar-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .language-selector {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 0.5rem;
    margin-top: 0.25rem;
  }
}
</style>
