<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-header">
        <div class="brand-logo">
          <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="3"/>
            <path d="M30 70L50 30L70 70" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M40 55H60" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            <path d="M45 65H55" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
          </svg>
        </div>
        <h1 class="brand-name">Rihal</h1>
        <p class="brand-subtitle">মাদ্রাসা ব্যবস্থাপনা প্ল্যাটফর্ম</p>
      </div>

      <form @submit.prevent="handleRegister" class="auth-form">
        <div v-if="error" class="alert alert-error" role="alert">{{ error }}</div>
        <div v-if="success" class="alert alert-success" role="status">{{ success }}</div>

        <div class="form-group">
          <label for="name_bn" class="form-label">পূর্ণ নাম (বাংলা) *</label>
          <input id="name_bn" v-model="form.name_bn" type="text" class="form-input" placeholder="আপনার পূর্ণ নাম বাংলায় লিখুন" :disabled="loading" required autofocus autocomplete="name" />
          <p v-if="form.name_bn && form.name_bn.length < 2" class="hint-error">নাম কমপক্ষে ২ অক্ষর হতে হবে</p>
        </div>

        <div class="form-group">
          <label for="name_en" class="form-label">নাম (ইংরেজি)</label>
          <input id="name_en" v-model="form.name_en" type="text" class="form-input" placeholder="Your full name in English (optional)" :disabled="loading" autocomplete="name" />
        </div>

        <div class="form-group">
          <label for="reg-email" class="form-label">ইমেইল ঠিকানা *</label>
          <input id="reg-email" v-model="form.email" type="email" inputmode="email" autocomplete="email" class="form-input" placeholder="ইমেইল ঠিকানা দিন" :disabled="loading" required />
        </div>

        <div class="form-group">
          <label for="reg-password" class="form-label">পাসওয়ার্ড *</label>
          <input id="reg-password" v-model="form.password" type="password" autocomplete="new-password" class="form-input" placeholder="কমপক্ষে ৮ অক্ষর, অক্ষর ও সংখ্যা সমেত" :disabled="loading" required />
        </div>

        <div class="form-group">
          <label for="reg-password-confirm" class="form-label">পাসওয়ার্ড নিশ্চিত করুন *</label>
          <input id="reg-password-confirm" v-model="form.password_confirmation" type="password" autocomplete="new-password" class="form-input" placeholder="পাসওয়ার্ড আবার লিখুন" :disabled="loading" required />
        </div>

        <div class="form-group">
          <label for="reg-phone" class="form-label">ফোন নম্বর (ঐচ্ছিক)</label>
          <input id="reg-phone" v-model="form.phone" type="tel" inputmode="tel" autocomplete="tel" class="form-input" placeholder="+৮৮০১৭০০০০০০০০" :disabled="loading" />
        </div>

        <button type="submit" class="btn-submit" :disabled="loading">
          <span v-if="loading" class="spinner" aria-hidden="true"></span>
          <span>একাউন্ট তৈরি করুন</span>
        </button>
      </form>

      <div class="auth-switch">
        <p>ইতিমধ্যে একাউন্ট আছে? <NuxtLink to="/login" class="link-primary">লগইন করুন</NuxtLink></p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  layout: 'auth',
})

const { register, isAuthenticated } = useAuth()
const form = ref({
  name_bn: '', name_en: '', email: '',
  password: '', password_confirmation: '',
  phone: '', tenant_id: null as string | null,
})
const error = ref('')
const success = ref('')
const loading = ref(false)

const isFormValid = computed(() => {
  return form.value.name_bn.trim().length >= 2 &&
    form.value.email.includes('@') &&
    form.value.password.length >= 8 &&
    form.value.password === form.value.password_confirmation
})

onMounted(() => {
  if (isAuthenticated.value) navigateTo('/dashboard')
})

function focusFirstInvalid() {
  const f = form.value
  if (f.name_bn.trim().length < 2) {
    document.getElementById('name_bn')?.focus()
  } else if (!f.email.includes('@')) {
    document.getElementById('reg-email')?.focus()
  } else if (f.password.length < 8) {
    document.getElementById('reg-password')?.focus()
  } else if (f.password !== f.password_confirmation) {
    document.getElementById('reg-password-confirm')?.focus()
  }
}

const handleRegister = async () => {
  error.value = ''
  if (!isFormValid.value) {
    error.value = 'সব প্রয়োজনীয় ঘর পূরণ করুন: নাম (২+ অক্ষর), বৈধ ইমেইল, এবং মিলে যাওয়া ৮+ অক্ষরের পাসওয়ার্ড।'
    focusFirstInvalid()
    return
  }
  success.value = ''
  loading.value = true

  try {
    await register({
      name_bn: form.value.name_bn,
      name_en: form.value.name_en || undefined,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
      phone: form.value.phone || undefined,
      tenant_id: form.value.tenant_id || undefined,
    })
    success.value = 'একাউন্ট তৈরি হয়েছে। লগইন করুন...'
    form.value = { name_bn: '', name_en: '', email: '', password: '', password_confirmation: '', phone: '', tenant_id: null }
    setTimeout(() => { navigateTo('/login') }, 1500)
  } catch (err: any) {
    error.value = err?.response?.data?.message ?? 'একাউন্ট তৈরি করতে ব্যর্থ। ইমেইল ইউনিক হতে হবে এবং পাসওয়ার্ড নিরাপদ হতে হবে।'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  position: relative; overflow: hidden; padding: 1.5rem 0;
}
.auth-container {
  width: 100%; max-width: 440px; background: var(--color-bg-card);
  border-radius: var(--radius-xl); padding: 2.5rem; box-shadow: var(--elevation-3); position: relative;
  border: 1px solid var(--color-border-light);
}
@media (max-width: 360px) {
  .auth-container {
    padding: 1.75rem 1.25rem;
  }
}
.auth-header { text-align: center; margin-bottom: 2rem; }
.brand-logo { width: 72px; height: 72px; margin: 0 auto 1rem; color: var(--color-primary); }
.brand-logo svg { width: 100%; height: 100%; }
.brand-name { font-size: var(--text-display); font-weight: var(--weight-bold); color: var(--color-primary); margin: 0 0 0.25rem; font-family: var(--font-bn); }
.brand-subtitle { font-size: var(--text-base); color: var(--color-text-light); margin: 0; font-family: var(--font-bn); }
.auth-form { text-align: left; }
.form-group { margin-bottom: 1rem; }
.form-label { display: block; font-size: var(--text-sm); font-weight: var(--weight-semibold); color: var(--color-text); margin-bottom: 0.375rem; font-family: var(--font-bn); }
.form-input {
  width: 100%; padding: 0.75rem 1rem; font-size: 1rem; color: var(--color-text);
  background: var(--color-bg); border: 1px solid var(--color-border); border-radius: var(--radius-md);
  transition: all var(--transition-fast); font-family: var(--font-bn);
}
.form-input:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(20,80,50,0.12); }
.form-input:disabled { opacity: 0.6; cursor: not-allowed; }
.hint-error { font-size: 0.75rem; color: var(--color-error); margin: 0; }
.btn-submit {
  width: 100%; padding: 0.875rem; font-size: 1rem; font-weight: 600;
  color: var(--color-text-on-primary);
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
  border: none; border-radius: var(--radius-md); cursor: pointer; transition: all var(--transition-normal);
  display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  touch-action: manipulation;
}
.btn-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(20,80,50,0.3); }
.btn-submit:active:not(:disabled) { transform: scale(0.97); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }
.alert { padding: 0.75rem 1rem; border-radius: var(--radius-md); font-size: 0.875rem; margin-bottom: 1rem; font-family: 'Noto Sans Bengali', sans-serif; }
.alert-error { background: rgba(198,40,40,0.1); color: var(--color-error); border: 1px solid rgba(198,40,40,0.2); }
.alert-success { background: rgba(46,125,50,0.1); color: var(--color-success); border: 1px solid rgba(46,125,50,0.2); }
.auth-switch { text-align: center; margin-top: 1.5rem; }
.auth-switch p { font-size: 0.875rem; color: var(--color-text-muted); margin: 0; }
.link-primary { color: var(--color-primary); font-weight: 500; }
.link-primary:hover { color: var(--color-primary); }
.spinner { width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
