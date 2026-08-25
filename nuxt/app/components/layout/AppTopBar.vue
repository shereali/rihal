<template>
  <header class="topbar">
    <div class="topbar-left">
      <button class="mobile-menu-btn" type="button" aria-label="মেনু" @click="emit('toggle-sidebar')">
        <span></span><span></span><span></span>
      </button>
      <!-- Support banner: compact inline strip -->
      <div class="topbar-support">
        <span class="support-label">সাপোর্ট: প্রতিদিন ১০ AM – ৭ PM</span>
        <NuxtLink to="https://wa.me/8801749240901" target="_blank" class="support-wa">
          <Icon name="whatsapp" size="14" />
        </NuxtLink>
      </div>
    </div>
    <div class="topbar-right">
      <!-- Dual-mode search: student or invoice -->
      <div class="topbar-search-wrap">
        <div class="search-mode-toggle">
          <button type="button" :class="{ active: searchMode === 'student' }" @click="searchMode = 'student'">
            ছাত্র
          </button>
          <button type="button" :class="{ active: searchMode === 'invoice' }" @click="searchMode = 'invoice'">
            রশিদ
          </button>
        </div>
        <div class="topbar-search">
          <Icon :name="searchMode === 'invoice' ? 'invoice' : 'search'" />
          <input
            v-model="search"
            type="text"
            :placeholder="searchMode === 'student' ? 'একটি ছাত্র খুঁজুন...' : 'রশিদ নাম্বার বা নাম লিখুন...'"
            @keyup.enter="handleSearch"
          />
        </div>
      </div>

      <NuxtLink to="/students/create" class="new-action">
        <Icon name="plus" />
        <span>নতুন</span>
      </NuxtLink>

      <button class="topbar-badge" type="button" title="SMS ব্যালেন্স">
        <Icon name="message" />
        <span>SMS: ৫৮৯ টাকা</span>
      </button>

      <!-- Notifications popover -->
      <div class="topbar-menu">
        <button class="round-action" type="button" aria-label="নোটিফিকেশন" @click="notificationOpen = !notificationOpen">
          <Icon name="bell" />
          <i v-if="unreadCount" />
        </button>
        <div v-if="notificationOpen" class="popover notification-popover">
          <div class="popover-head">
            <strong>নোটিফিকেশন</strong>
            <NuxtLink to="/notifications">সব দেখুন</NuxtLink>
          </div>
          <div v-if="!notifications.length" class="popover-empty">কোনো নতুন নোটিফিকেশন নেই</div>
          <NuxtLink v-for="n in notifications.slice(0, 4)" :key="n.id" to="/notifications" class="notification-item">
            <span class="notification-dot" />
            <span><b>{{ n.title_bn }}</b><small>{{ n.body_bn }}</small></span>
          </NuxtLink>
        </div>
      </div>

      <!-- 4-theme switcher: light, dark, islamic, professional -->
      <div class="topbar-menu">
        <button class="round-action" type="button" aria-label="থিম" @click="themeOpen = !themeOpen">
          <Icon :name="currentTheme === 'dark' || currentTheme === 'professional' ? 'moon' : 'sun'" />
        </button>
        <div v-if="themeOpen" class="popover theme-popover">
          <strong>থিম বেছে নিন</strong>
          <button
            v-for="t in themes"
            :key="t.value"
            class="theme-option"
            :class="{ active: currentTheme === t.value }"
            @click="setTheme(t.value)"
          >
            <span class="theme-swatch" :style="{ background: t.swatch }" />
            {{ t.label }}
          </button>
        </div>
      </div>

      <!-- Language selector (বাংলা/ইংরেজি/আরবি) -->
      <div class="topbar-menu">
        <button class="language-button" type="button" @click="languageOpen = !languageOpen">
          বাংলা <Icon name="chevron-down" />
        </button>
        <div v-if="languageOpen" class="popover language-popover">
          <strong>ভাষা</strong>
          <button class="active">বাংলা <span>✓</span></button>
          <button disabled>English <small>শীঘ্রই</small></button>
          <button disabled>العربية <small>শীঘ্রই</small></button>
        </div>
      </div>

      <!-- User menu -->
      <div class="topbar-menu">
        <button class="user-menu-trigger" type="button" @click="userOpen = !userOpen">
          <div class="user-avatar">
            <img v-if="currentUser?.avatar_url" :src="currentUser.avatar_url" :alt="currentUser.name_bn" />
            <span v-else>{{ (currentUser?.name_bn || currentUser?.name_en || 'U').charAt(0) }}</span>
          </div>
          <div class="user-info">
            <span class="user-name">{{ currentUser?.name_bn || currentUser?.name_en || 'ব্যবহারকারী' }}</span>
            <span class="user-role">{{ roleLabel }}</span>
          </div>
          <Icon name="chevron-down" />
        </button>
        <div v-if="userOpen" class="popover user-popover">
          <div class="profile-head">
            <div class="large-avatar">{{ (currentUser?.name_bn || 'U').charAt(0) }}</div>
            <div>
              <b>{{ currentUser?.name_bn || 'ব্যবহারকারী' }}</b>
              <small>{{ currentUser?.email || '' }}</small>
            </div>
          </div>
          <NuxtLink to="/settings">⚙️ প্রোফাইল সেটিংস</NuxtLink>
          <button class="logout-link" @click="logout">↪ লগ আউট</button>
        </div>
      </div>

      <!-- Floating AI chat control (Teleport to body via child component) -->
      <AiChatControl />
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useApiClient } from '~/utils/api'
import AiChatControl from './AiChatControl.vue'

const emit = defineEmits<{ 'toggle-sidebar': [] }>()
const api = useApiClient()
const { currentUser, logout } = useAuth()

const search = ref('')
const searchMode = ref<'student' | 'invoice'>('student')
const notifications = ref<any[]>([])
const notificationOpen = ref(false)
const themeOpen = ref(false)
const languageOpen = ref(false)
const userOpen = ref(false)

const themes = [
  { value: 'light', label: '☀️ লাইট', swatch: '#ffffff' },
  { value: 'dark', label: '🌙 ডার্ক', swatch: '#1a1a2e' },
  { value: 'islamic', label: '🕌 ইসলামিক', swatch: '#145032' },
  { value: 'professional', label: '💼 প্রফেশনাল', swatch: '#1a365d' },
]

const currentTheme = ref('light')

const unreadCount = computed(() => notifications.value.filter(n => !n.is_read).length)
const roleLabel = computed(() =>
  ({ admin: 'অ্যাডমিন', super_admin: 'সুপার অ্যাডমিন', teacher: 'শিক্ষক', student: 'শিক্ষার্থী', guardian: 'অভিভাবক' } as any)[currentUser.value?.role] || 'ব্যবহারকারী'
)

onMounted(async () => {
  const saved = localStorage.getItem('rihal_theme')
  if (saved && themes.some(t => t.value === saved)) {
    currentTheme.value = saved
  }
  applyTheme()
  try {
    const r = await api.get('/notifications?per_page=8')
    notifications.value = r.data?.data?.data || r.data?.data || []
  } catch {}
})

function setTheme(value: string) {
  currentTheme.value = value
  localStorage.setItem('rihal_theme', value)
  applyTheme()
  themeOpen.value = false
}

function applyTheme() {
  if (import.meta.client) {
    document.documentElement.dataset.theme = currentTheme.value
  }
}

function handleSearch() {
  if (!search.value.trim()) return
  if (searchMode.value === 'student') {
    navigateTo(`/students?search=${encodeURIComponent(search.value.trim())}`)
  } else {
    navigateTo(`/finance?search=${encodeURIComponent(search.value.trim())}`)
  }
  search.value = ''
}
</script>

<style scoped>
.topbar {
  position: fixed;
  top: 0;
  right: 0;
  left: 0;
  height: var(--header-height);
  background: var(--color-bg-header);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.35rem;
  z-index: 90;
  box-shadow: var(--shadow-sm);
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.mobile-menu-btn {
  display: none;
  flex-direction: column;
  gap: 4px;
  padding: .5rem;
  border: 0;
  background: transparent;
  cursor: pointer;
}
.mobile-menu-btn span {
  display: block;
  width: 22px;
  height: 2px;
  border-radius: 4px;
  background: var(--color-text);
}

/* Support banner — compact inline strip */
.topbar-support {
  display: flex;
  align-items: center;
  gap: .4rem;
  padding: .3rem .55rem;
  background: var(--color-bg-muted);
  border: 1px solid var(--color-border-light);
  border-radius: 999px;
  font: .7rem var(--font-bn);
  color: var(--color-text-light);
}
.support-wa {
  display: inline-flex;
  align-items: center;
  color: #25d366;
  text-decoration: none;
}
.support-wa:hover { color: #128c7e; }

.topbar-right {
  display: flex;
  align-items: center;
  gap: .55rem;
  margin-left: auto;
}

.topbar-search-wrap {
  display: flex;
  align-items: center;
  gap: .5rem;
}

.search-mode-toggle {
  display: flex;
  background: var(--color-bg-muted);
  border: 1px solid var(--color-border-light);
  border-radius: 999px;
  padding: 2px;
}
.search-mode-toggle button {
  border: 0;
  background: transparent;
  color: var(--color-text-light);
  padding: .3rem .6rem;
  border-radius: 999px;
  font: .68rem var(--font-bn);
  cursor: pointer;
  transition: all .12s;
}
.search-mode-toggle button.active {
  background: var(--color-primary);
  color: #fff;
}

.topbar-search {
  display: flex;
  align-items: center;
  gap: .5rem;
  width: 240px;
  padding: .48rem .75rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 12px;
}
.topbar-search input {
  flex: 1;
  min-width: 0;
  border: 0;
  outline: 0;
  background: transparent;
  color: var(--color-text);
  font: .82rem var(--font-bn);
}

.new-action,
.topbar-badge,
.language-button {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  border: 1px solid var(--color-border);
  border-radius: 11px;
  padding: .56rem .75rem;
  background: var(--color-bg-card);
  color: var(--color-text);
  font: .78rem var(--font-bn);
  cursor: pointer;
}
.new-action {
  color: #fff;
  background: var(--color-primary);
  border-color: var(--color-primary);
}
.round-action {
  position: relative;
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border: 1px solid var(--color-border);
  border-radius: 11px;
  background: var(--color-bg-card);
  color: var(--color-text);
  cursor: pointer;
}
.round-action i {
  position: absolute;
  right: 5px;
  top: 5px;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #d74b4b;
  border: 1px solid var(--color-bg-card);
}
.topbar-menu {
  position: relative;
}
.popover {
  position: absolute;
  right: 0;
  top: calc(100% + .65rem);
  min-width: 260px;
  padding: .75rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: 14px;
  box-shadow: var(--shadow-lg);
  z-index: 110;
}
.popover-head,
.profile-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .6rem;
  padding: .35rem .5rem;
  border-bottom: 1px solid var(--color-border-light);
  margin-bottom: .5rem;
}
.popover-head strong,
.profile-head strong {
  font: 600 .82rem var(--font-bn);
  color: var(--color-text);
}
.popover-head a,
.profile-head a {
  font: .74rem var(--font-bn);
  color: var(--color-primary);
}
.popover-empty {
  padding: .5rem 0;
  text-align: center;
  color: var(--color-text-muted);
  font: .78rem var(--font-bn);
}
.notification-item {
  display: flex;
  align-items: flex-start;
  gap: .5rem;
  padding: .5rem .3rem;
  border-radius: 8px;
  color: var(--color-text);
  font: .78rem var(--font-bn);
  text-decoration: none;
  transition: background .12s;
}
.notification-item:hover { background: var(--color-bg-muted); }
.notification-dot {
  flex: 0 0 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-primary);
  margin-top: 5px;
}
.notification-item b {
  display: block;
  font-weight: 600;
  font-size: .82rem;
}
.notification-item small {
  display: block;
  color: var(--color-text-muted);
  font-size: .7rem;
  margin-top: .15rem;
}

.theme-popover {
  min-width: 280px;
}
.theme-popover strong {
  display: block;
  font: 600 .82rem var(--font-bn);
  color: var(--color-text);
  margin-bottom: .5rem;
}
.theme-option {
  display: flex;
  align-items: center;
  gap: .6rem;
  width: 100%;
  padding: .5rem .6rem;
  border: 0;
  background: transparent;
  border-radius: 9px;
  color: var(--color-text);
  font: .78rem var(--font-bn);
  cursor: pointer;
  transition: background .12s;
}
.theme-option:hover { background: var(--color-bg-muted); }
.theme-option.active { background: var(--color-primary-50); color: var(--color-primary); font-weight: 600; }
.theme-swatch {
  flex: 0 0 20px;
  height: 20px;
  border-radius: 6px;
  border: 1px solid var(--color-border);
}

.language-popover {
  min-width: 200px;
}
.language-popover strong {
  display: block;
  font: 600 .82rem var(--font-bn);
  color: var(--color-text);
  margin-bottom: .5rem;
}
.language-popover button {
  display: block;
  width: 100%;
  padding: .5rem .6rem;
  border: 0;
  background: transparent;
  color: var(--color-text);
  font: .78rem var(--font-bn);
  text-align: left;
  cursor: pointer;
  border-radius: 8px;
  transition: background .12s;
}
.language-popover button:hover { background: var(--color-bg-muted); }
.language-popover button.active {
  background: var(--color-primary);
  color: #fff;
  font-weight: 600;
}
.language-popover button span {
  color: var(--color-primary-100);
  font-size: .65rem;
  margin-left: .3rem;
}
.language-popover button:disabled {
  opacity: .4;
  cursor: default;
}
.language-popover button:disabled small {
  color: var(--color-text-muted);
}

.user-menu-trigger {
  display: flex;
  align-items: center;
  gap: .5rem;
  padding: .3rem .5rem;
  background: transparent;
  border: 1px solid var(--color-border);
  border-radius: 999px;
  cursor: pointer;
}
.user-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--color-primary-50);
  color: var(--color-primary);
  display: grid;
  place-items: center;
  font: 600 .85rem var(--font-bn);
  overflow: hidden;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.user-info {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}
.user-name {
  font: 600 .75rem var(--font-bn);
  color: var(--color-text);
}
.user-role {
  font: .65rem var(--font-bn);
  color: var(--color-text-muted);
}
.user-popover {
  min-width: 210px;
}
.profile-head {
  display: flex;
  align-items: center;
  gap: .6rem;
  padding: .5rem;
  border-bottom: 1px solid var(--color-border-light);
  margin-bottom: .5rem;
}
.large-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--color-primary-50);
  color: var(--color-primary);
  display: grid;
  place-items: center;
  font: 700 1.1rem var(--font-bn);
}
.profile-head b {
  display: block;
  font: 600 .85rem var(--font-bn);
  color: var(--color-text);
}
.profile-head small {
  font: .7rem var(--font-bn);
  color: var(--color-text-muted);
}
.user-popover a {
  display: block;
  padding: .5rem .6rem;
  border-radius: 8px;
  color: var(--color-text);
  font: .78rem var(--font-bn);
  text-decoration: none;
  transition: background .12s;
}
.user-popover a:hover { background: var(--color-bg-muted); }
.logout-link {
  display: block;
  width: 100%;
  padding: .5rem .6rem;
  border: 0;
  background: transparent;
  color: var(--color-error);
  font: .78rem var(--font-bn);
  text-align: left;
  cursor: pointer;
  border-radius: 8px;
  transition: background .12s;
}
.logout-link:hover { background: var(--color-error-bg); }

@media (max-width: 768px) {
  .mobile-menu-btn { display: flex; }
  .topbar { padding: 0 .5rem; gap: .35rem; }
  .topbar-left { flex: 0 0 auto; }
  .topbar-right { min-width: 0; gap: .3rem; }
  .topbar-search-wrap, .topbar-support, .language-button, .user-info, .user-menu-trigger > .icon-wrapper { display: none; }
  .topbar-badge span { display: none; }
  .topbar-badge, .new-action, .round-action, .user-menu-trigger { width: 36px; height: 36px; padding: 0; justify-content: center; }
  .user-menu-trigger { border-radius: 50%; }
  .user-avatar { width: 30px; height: 30px; }
}
@media (max-width: 900px) {
  .topbar-support { display: none; }
  .search-mode-toggle { display: none; }
  .topbar-search { width: 180px; }
}
@media (max-width: 640px) {
  .topbar-search { width: 140px; }
  .new-action span { display: none; }
}
</style>
