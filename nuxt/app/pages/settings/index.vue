<template>
  <div class="settings-page">
    <div class="page-header">
      <h1>সিস্টেম সেটিংস</h1>
      <p class="text-muted">প্রোফাইল ও পছন্দসমূহ</p>
    </div>

    <div class="settings-grid">
      <div class="card">
        <div class="card-header"><h3>ব্যবহারকারীর প্রোফাইল</h3></div>
        <div class="card-body">
          <div v-if="!user" class="empty-state"><p>লগ ইন করা নেই</p></div>
          <dl v-else class="profile-list">
            <dt>নাম</dt><dd>{{ user.name_bn || user.name_en || '-' }}</dd>
            <dt>ইমেইল</dt><dd>{{ user.email || '-' }}</dd>
            <dt>ভূমিকা</dt><dd>{{ roleLabel }}</dd>
            <dt>টেন্যান্ট আইডি</dt><dd>{{ user.tenant_id || '-' }}</dd>
          </dl>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>ভাষা</h3></div>
        <div class="card-body">
          <div class="form-group">
            <label>ইন্টারফেস ভাষা</label>
            <select v-model="language" @change="onLanguageChange">
              <option value="bn">বাংলা</option>
              <option value="en">ইংরেজি</option>
              <option value="ar">আরবি</option>
            </select>
            <small class="text-muted">পছন্দটি আপনার ব্রাউজারে সংরক্ষিত হবে।</small>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>অ্যাকাউন্ট</h3></div>
        <div class="card-body">
          <button class="btn btn-danger" @click="handleLogout"><icon name="logout" /> লগ আউট</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '~/stores/auth'

const auth = useAuthStore()
const user = computed(() => auth.user)
const language = ref('bn')

const roleLabel = computed(() => {
  const r = user.value?.role || user.value?.roles?.[0]
  if (!r) return '-'
  const map: Record<string, string> = {
    super_admin: 'প্ল্যাটফর্ম প্রশাসক',
    admin: 'মাদ্রাসা প্রশাসক',
    teacher: 'শিক্ষক',
    student: 'ছাত্র',
    guardian: 'অভিভাবক',
  }
  return map[r] || r
})

function onLanguageChange() {
  if (import.meta.client) localStorage.setItem('rihal_lang', language.value)
}

function handleLogout() {
  auth.logout()
  navigateTo('/login')
}

onMounted(() => {
  if (import.meta.client) {
    const saved = localStorage.getItem('rihal_lang')
    if (saved) language.value = saved
  }
})
</script>

<style scoped>
.settings-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border-light); }
.card-header h3 { margin: 0; font-size: 1.05rem; font-family: 'Noto Sans Bengali', sans-serif; }
.card-body { padding: 1.25rem; }
.profile-list { display: grid; grid-template-columns: 130px 1fr; gap: 0.5rem 1rem; margin: 0; }
.profile-list dt { font-weight: 600; color: var(--color-text-muted); font-family: 'Noto Sans Bengali', sans-serif; }
.profile-list dd { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-group label { font-size: 0.9rem; font-weight: 500; font-family: 'Noto Sans Bengali', sans-serif; }
.form-group select { padding: 0.65rem 0.9rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: 1rem; font-family: 'Noto Sans Bengali', sans-serif; background: var(--color-bg); }
.text-muted { color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
small { font-size: 0.8rem; }
.empty-state { padding: 1rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.btn { padding: 0.7rem 1.4rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-family: 'Noto Sans Bengali', sans-serif; display: inline-flex; align-items: center; gap: 0.4rem; }
.btn-danger { background: var(--color-error); color: #fff; }
</style>
