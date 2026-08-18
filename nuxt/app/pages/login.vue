<template>
  <div class="page-login">
  <div class="auth-container">
    <div class="auth-header">
      <div class="brand-logo">
        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="3" />
          <path d="M30 70L50 30L70 70" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M40 55H60" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
          <path d="M45 65H55" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
        </svg>
      </div>
      <h1 class="brand-name">Rihal</h1>
      <p class="brand-subtitle">মাদ্রাসা ব্যবস্থাপনা প্ল্যাটফর্ম</p>
    </div>

    <form @submit.prevent="handleLogin" class="login-form">
      <div v-if="error" class="alert alert-error">
        {{ error }}
      </div>

      <div v-if="success" class="alert alert-success">
        {{ success }}
      </div>

      <div class="form-group">
        <label for="email">ইমেইল</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          placeholder="example@domain.com"
          :disabled="loading"
          autofocus
        />
      </div>

      <div class="form-group">
        <label for="password">পাসওয়ার্ড</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          placeholder="আপনার পাসওয়ার্ড"
          :disabled="loading"
        />
      </div>

      <div class="form-options">
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.remember" />
          মনে রাখুন
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
        <span v-if="loading" class="spinner"></span>
        <span v-else>লগইন করুন</span>
      </button>
    </form>

    <div class="auth-footer">
      <NuxtLink to="/register">অ্যাকাউন্ট খুলুন</NuxtLink>
    </div>
  </div>
  </div>
</template>

<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const loading = ref(false)
const error = ref('')
const success = ref('')

const { login } = useAuth()

async function handleLogin() {
  error.value = ''
  success.value = ''
  loading.value = true

  try {
    await login(form)
    success.value = 'সফলভাবে লগইন হয়েছে...'
    setTimeout(() => { success.value = '' }, 2000)
  } catch (err: any) {
    error.value = err?.response?.data?.message ?? 'লগইন ব্যর্থ হয়েছে। ইমেইল ও পাসওয়ার্ড যাচাই করুন।'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page-login {
  min-height: 100vh;
  background: var(--color-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.auth-container {
  width: 100%;
  max-width: 420px;
  background: var(--color-bg-card);
  border-radius: 16px;
  box-shadow: var(--shadow-lg);
  padding: 2.5rem 2rem;
  border: 1px solid var(--color-border-light);
}

.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.brand-logo {
  width: 72px;
  height: 72px;
  margin: 0 auto 1rem;
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-logo svg {
  width: 100%;
  height: 100%;
}

.brand-name {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--color-primary);
  margin: 0 0 0.25rem;
  font-family: 'Noto Sans Bengali', sans-serif;
}

.brand-subtitle {
  font-size: 0.95rem;
  color: var(--color-text-light);
  margin: 0;
  font-family: 'Noto Sans Bengali', sans-serif;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group label {
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--color-text);
  font-family: 'Noto Sans Bengali', sans-serif;
}

.form-group input {
  padding: 0.75rem 1rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 1rem;
  font-family: 'Noto Sans Bengali', sans-serif;
  transition: border-color 0.2s;
  background: var(--color-bg);
}

.form-group input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(20, 80, 50, 0.12);
}

.form-group input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: var(--color-text);
  cursor: pointer;
  font-family: 'Noto Sans Bengali', sans-serif;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: var(--color-primary);
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  font-family: 'Noto Sans Bengali', sans-serif;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background: var(--color-primary);
  color: var(--color-text-on-primary);
}

.btn-primary:hover:not(:disabled) {
  background: var(--color-primary-dark);
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-block {
  width: 100%;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid var(--color-text-on-primary);
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: 'Noto Sans Bengali', sans-serif;
}

.alert-error {
  background: #fce4e4;
  color: var(--color-error);
  border: 1px solid #f5c6c6;
}

.alert-success {
  background: #e8f5e9;
  color: var(--color-success);
  border: 1px solid #c8e6c9;
}

.auth-footer {
  margin-top: 1.5rem;
  text-align: center;
}

.auth-footer a {
  color: var(--color-primary);
  font-weight: 500;
  font-size: 0.95rem;
  text-decoration: none;
  font-family: 'Noto Sans Bengali', sans-serif;
}

.auth-footer a:hover {
  text-decoration: underline;
}
</style>
