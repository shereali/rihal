<template>
  <header class="topbar">
    <div class="topbar-left">
      <button class="mobile-menu-btn" @click="showSidebar = true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
    </div>

    <div class="topbar-right">
      <!-- Search (placeholder) -->
      <div class="topbar-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          type="text"
          placeholder="একটি ছাত্র খুঁজুন..."
          class="search-input"
        />
      </div>

      <!-- Quick actions -->
      <button class="topbar-action" v-if="isPlatformAdmin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14"/>
        </svg>
        <span>নতুন</span>
      </button>

      <!-- SMS Balance -->
      <div class="topbar-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
        </svg>
        <span>SMS: ৫৮৯ টাকা</span>
      </div>

      <!-- User menu -->
      <div class="topbar-user">
        <button class="user-menu-trigger">
          <div class="user-avatar">
            <img v-if="user?.avatar_url" :src="user.avatar_url" :alt="user.name" />
            <span v-else>{{ user?.name?.charAt(0) || 'U' }}</span>
          </div>
          <div class="user-info">
            <span class="user-name">{{ user?.name }}</span>
            <span class="user-role">{{ user?.role }}</span>
          </div>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6,9 12,15 18,9"/>
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'

const { user, isPlatformAdmin, logout } = useAuth()
const showSidebar = ref(false)
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
  padding: 0 1.5rem;
  z-index: 90;
  box-shadow: var(--shadow-sm);
}

.topbar-left {
  display: flex;
  align-items: center;
}

.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  padding: 0.5rem;
  color: var(--color-text);
  cursor: pointer;
}

.mobile-menu-btn svg {
  width: 24px;
  height: 24px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.topbar-search {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 0.375rem 0.75rem;
  width: 280px;
  transition: border-color var(--transition-fast);
}

.topbar-search:focus-within {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
}

.topbar-search svg {
  width: 16px;
  height: 16px;
  color: var(--color-text-muted);
  flex-shrink: 0;
}

.search-input {
  flex: 1;
  border: none;
  background: none;
  font-size: 0.875rem;
  color: var(--color-text);
  outline: none;
}

.search-input::placeholder {
  color: var(--color-text-muted);
}

.topbar-action {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.75rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-fast);
  white-space: nowrap;
}

.topbar-action:hover {
  background: var(--color-primary-dark);
}

.topbar-action svg {
  width: 16px;
  height: 16px;
}

.topbar-badge {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: 0.8125rem;
  color: var(--color-text);
}

.topbar-badge svg {
  width: 14px;
  height: 14px;
  color: var(--color-primary);
}

.topbar-user {
  margin-left: auto;
}

.user-menu-trigger {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  background: none;
  border: 1px solid transparent;
  border-radius: var(--radius-md);
  padding: 0.25rem 0.5rem;
  cursor: pointer;
  transition: border-color var(--transition-fast);
}

.user-menu-trigger:hover {
  border-color: var(--color-border);
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--color-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  overflow: hidden;
  flex-shrink: 0;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  text-align: left;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text);
  line-height: 1.2;
}

.user-role {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.user-menu-trigger svg {
  width: 14px;
  height: 14px;
  color: var(--color-text-muted);
  margin-left: 0.25rem;
}

@media (max-width: 768px) {
  .mobile-menu-btn {
    display: flex;
  }

  .topbar-search {
    display: none;
  }

  .topbar-badge span {
    display: none;
  }

  .user-info {
    display: none;
  }

  .topbar {
    padding: 0 1rem;
  }
}
</style>
