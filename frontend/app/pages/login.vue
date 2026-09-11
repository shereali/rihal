<template>
  <div class="login-wrapper">
    <!-- Main Split-Screen Container -->
    <div class="login-card">
      
      <!-- Left Column: Islamic Institutional Showcase -->
      <div class="showcase-panel">
        <div class="showcase-bg-pattern" aria-hidden="true"></div>
        <div class="showcase-ambient-glow" aria-hidden="true"></div>

        <div class="showcase-content">
          <!-- Bismillah Calligraphy -->
          <div class="bismillah-wrap" aria-label="বিসমিল্লাহির রাহমানির রাহিম">
            <svg class="bismillah-svg" viewBox="0 0 320 54" fill="currentColor">
              <path d="M160 8c-6 0-11 3-14 8-3-5-8-8-14-8-11 0-20 9-20 20 0 14 17 26 34 26 17 0 34-12 34-26 0-11-9-20-20-20zm0 40c-12 0-24-9-24-20 0-7 5-12 11-12 5 0 9 3 11 8h4c2-5 6-8 11-8 6 0 11 5 11 12 0 11-12 20-23 20z" opacity="0.15"/>
              <text x="50%" y="36" text-anchor="middle" font-family="'Amiri', 'Scheherazade New', 'Traditional Arabic', 'Noto Naskh Arabic', serif" font-size="25" font-weight="bold" fill="url(#goldGrad)" letter-spacing="1">
                بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
              </text>
              <defs>
                <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                  <stop offset="0%" stop-color="#e5c158" />
                  <stop offset="50%" stop-color="#d4af37" />
                  <stop offset="100%" stop-color="#f5e2a3" />
                </linearGradient>
              </defs>
            </svg>
          </div>

          <!-- Brand Emblem & Title -->
          <div class="brand-crest">
            <div class="emblem-container">
              <!-- Custom Islamic Mihrab + Rehal (Quran Stand) Emblem -->
              <svg class="emblem-svg" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- 8-pointed star base (Rub el Hizb) -->
                <rect x="24" y="24" width="48" height="48" rx="6" transform="rotate(45 48 48)" stroke="url(#goldGrad)" stroke-width="1.5" stroke-dasharray="2 2" opacity="0.6"/>
                <rect x="24" y="24" width="48" height="48" rx="8" stroke="url(#goldGrad)" stroke-width="1.8"/>
                
                <!-- Inner Arch (Mihrab) -->
                <path d="M32 68V44C32 35.1634 39.1634 28 48 28C56.8366 28 64 35.1634 64 44V68" stroke="#ffffff" stroke-width="2" stroke-linecap="round" opacity="0.85"/>
                <path d="M48 24V28" stroke="url(#goldGrad)" stroke-width="2.5" stroke-linecap="round"/>
                
                <!-- Rihal (Folding Book Stand) Silhouette -->
                <path d="M36 65L60 47M60 65L36 47" stroke="url(#goldGrad)" stroke-width="2.5" stroke-linecap="round"/>
                
                <!-- Open Quran Book on Top -->
                <path d="M48 44C44 41 38 41 34 43V55C38 53 44 53 48 56C52 53 58 53 62 55V43C58 41 52 41 48 44Z" fill="url(#goldGrad)" fill-opacity="0.9" stroke="#ffffff" stroke-width="1"/>
                <line x1="48" y1="44" x2="48" y2="56" stroke="#0d3b24" stroke-width="1.2"/>
              </svg>
            </div>

            <div class="brand-titles">
              <div class="brand-name-wrap">
                <h1 class="brand-title">রিহাল</h1>
                <span class="brand-badge">PRO</span>
              </div>
              <p class="brand-tagline">মাদ্রাসা ও ইসলামিক শিক্ষা ব্যবস্থাপনা প্ল্যাটফর্ম</p>
            </div>
          </div>

          <!-- Quranic Inspiration Plaque -->
          <div class="verse-card">
            <div class="verse-arch-ornament">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="url(#goldGrad)" opacity="0.5"/>
                <path d="M12 6L13.8 9.6L17.8 10.2L14.9 13L15.6 17L12 15.1L8.4 17L9.1 13L6.2 10.2L10.2 9.6L12 6z" fill="url(#goldGrad)"/>
              </svg>
            </div>
            <p class="arabic-verse" lang="ar">« وَقُل رَّبِّ زِدْنِي عِلْمًا »</p>
            <p class="bengali-meaning">"বলুন: হে আমার পালনকর্তা! আমার জ্ঞান বৃদ্ধি করে দিন।"</p>
            <span class="verse-reference">— সূরা ত্বা-হা: ১১৪</span>
          </div>

          <!-- Institutional Core Modules -->
          <div class="showcase-features">
            <div class="feature-item">
              <div class="feature-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                  <path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20V6a2 2 0 0 0-2-2H6.5A2.5 2.5 0 0 0 4 6.5z"/>
                </svg>
              </div>
              <div class="feature-text">
                <strong>হিফজুল কুরআন মনিটরিং</strong>
                <span>সবক, সাতপারা ও আমোখতা ট্র্যাকিং</span>
              </div>
            </div>

            <div class="feature-item">
              <div class="feature-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 21h18M5 21V7l8-4v18M13 11h4M13 15h4M13 19h4M9 9H7M9 13H7M9 17H7"/>
                </svg>
              </div>
              <div class="feature-text">
                <strong>জামাত ও কিতাব বিভাগ</strong>
                <span>দরসে নেজামিয়া ও আধুনিক কারিকুলাম</span>
              </div>
            </div>

            <div class="feature-item">
              <div class="feature-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="2" y="6" width="20" height="12" rx="2"/>
                  <circle cx="12" cy="12" r="2"/>
                  <path d="M6 12h.01M18 12h.01"/>
                </svg>
              </div>
              <div class="feature-text">
                <strong>লিল্লাহ বোর্ডিং ও স্বচ্ছ তহবিল</strong>
                <span>যাকাত, অনুদান ও ডাবল-এন্ট্রি অডিট</span>
              </div>
            </div>
          </div>

          <!-- Trust & Protection Badge -->
          <div class="security-footer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
            <span>২৫৬-বিট এনক্রিপশন • শরীয়াহ ও পূর্ণ তথ্য নিরাপত্তা নিশ্চিত</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Premium Authentication Card -->
      <div class="form-panel">
        <!-- Golden Top Ribbon -->
        <div class="form-ribbon" aria-hidden="true"></div>

        <div class="form-inner">
          <!-- Form Header -->
          <div class="form-header">
            <div class="greeting-badge">
              <span class="pulse-dot"></span>
              <span>আসসালামু আলাইকুম</span>
            </div>
            <h2 class="form-title">অ্যাকাউন্টে প্রবেশ করুন</h2>
            <p class="form-subtitle">আপনার নির্ধারিত ইমেইল ও গোপন পাসওয়ার্ড দিয়ে লগইন করুন</p>
          </div>

          <!-- Quick Demo Role Switcher (For testing/presentations) -->
          <div class="demo-roles-box">
            <div class="demo-label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
              </svg>
              <span>দ্রুত ডেমো লগইন:</span>
            </div>
            <div class="demo-buttons">
              <button 
                type="button" 
                class="demo-chip" 
                :class="{ active: form.email === 'admin@demo.bd' }"
                @click="fillDemo('admin@demo.bd', 'admin123')"
                title="মুহতামিম / মাদ্রাসা অ্যাডমিন"
              >
                👑 মুহতামিম
              </button>
              <button 
                type="button" 
                class="demo-chip" 
                :class="{ active: form.email === 'ali.ahmed@demo.bd' }"
                @click="fillDemo('ali.ahmed@demo.bd', 'teacher123')"
                title="প্রধান শিক্ষক / উস্তাদ"
              >
                📚 প্রধান শিক্ষক
              </button>
              <button 
                type="button" 
                class="demo-chip" 
                :class="{ active: form.email === 'admin@rihal.app' }"
                @click="fillDemo('admin@rihal.app', 'admin123')"
                title="প্ল্যাটফর্ম অ্যাডমিন"
              >
                🛡️ প্ল্যাটফর্ম অ্যাডমিন
              </button>
            </div>
          </div>

          <!-- Alert Notifications -->
          <transition name="fade">
            <div v-if="error" class="custom-alert alert-error" role="alert">
              <div class="alert-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <line x1="12" y1="8" x2="12" y2="12"/>
                  <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
              </div>
              <div class="alert-body">
                <strong>লগইন ব্যর্থ হয়েছে</strong>
                <span>{{ error }}</span>
              </div>
            </div>
          </transition>

          <transition name="fade">
            <div v-if="success" class="custom-alert alert-success" role="status">
              <div class="alert-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                  <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
              </div>
              <div class="alert-body">
                <strong>সফলভাবে যাচাই করা হয়েছে</strong>
                <span>{{ success }}</span>
              </div>
            </div>
          </transition>

          <!-- Authentication Form -->
          <form @submit.prevent="handleLogin" class="auth-form" novalidate>
            <!-- Email Input -->
            <div class="form-group">
              <label for="login-email" class="input-label">
                <span>ইমেইল ঠিকানা</span>
                <span class="required-star">*</span>
              </label>
              <div class="input-container">
                <span class="input-leading-icon" aria-hidden="true">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                  </svg>
                </span>
                <input
                  id="login-email"
                  v-model="form.email"
                  type="email"
                  inputmode="email"
                  autocomplete="username"
                  placeholder="যেমন: admin@rihal.edu.bd"
                  :disabled="loading"
                  required
                  autofocus
                  class="custom-input"
                />
              </div>
            </div>

            <!-- Password Input -->
            <div class="form-group">
              <div class="label-row">
                <label for="login-password" class="input-label">
                  <span>পাসওয়ার্ড</span>
                  <span class="required-star">*</span>
                </label>
                <a href="#forgot" class="forgot-link" @click.prevent="showForgotHint">পাসওয়ার্ড ভুলে গেছেন?</a>
              </div>
              <div class="input-container">
                <span class="input-leading-icon" aria-hidden="true">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                </span>
                <input
                  id="login-password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  placeholder="আপনার গোপন পাসওয়ার্ড লিখুন"
                  :disabled="loading"
                  required
                  class="custom-input with-trailing-icon"
                />
                <button
                  type="button"
                  class="password-toggle-btn"
                  @click="showPassword = !showPassword"
                  :title="showPassword ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'"
                  :aria-label="showPassword ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'"
                >
                  <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24M1 1l22 22"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Options Row (Remember Me) -->
            <div class="form-options">
              <label class="custom-checkbox">
                <input type="checkbox" v-model="form.remember" :disabled="loading" />
                <span class="checkmark">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </span>
                <span class="checkbox-text">এই ডিভাইসে মনে রাখুন</span>
              </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn" :disabled="loading">
              <span v-if="loading" class="spinner" aria-hidden="true"></span>
              <span v-else class="btn-text">
                <span>লগইন করুন</span>
                <svg class="btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="5" y1="12" x2="19" y2="12"/>
                  <polyline points="12 5 19 12 12 19"/>
                </svg>
              </span>
            </button>
          </form>

          <!-- Register & Support Links -->
          <div class="form-footer">
            <p class="register-prompt">
              নতুন মাদ্রাসার জন্য যুক্ত হতে চান?
              <NuxtLink to="/register" class="register-link">নতুন অ্যাকাউন্ট খুলুন</NuxtLink>
            </p>
            
            <div class="system-status">
              <span class="status-indicator"></span>
              <span>রিহাল ক্লাউড সিস্টেম সক্রিয় • v2.4</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  layout: 'auth',
})

const form = reactive({
  email: '',
  password: '',
  remember: true,
})

const loading = ref(false)
const error = ref('')
const success = ref('')
const showPassword = ref(false)

const { login } = useAuth()

// Demo credential helper
function fillDemo(email: string, pass: string) {
  form.email = email
  form.password = pass
  error.value = ''
}

function showForgotHint() {
  error.value = 'পাসওয়ার্ড পুনরুদ্ধারের জন্য অনুগ্রহ করে আপনার মাদ্রাসার প্রধান অ্যাডমিন বা আইটি টিমের সাথে যোগাযোগ করুন।'
}

async function handleLogin() {
  if (!form.email || !form.password) {
    error.value = 'অনুগ্রহ করে ইমেইল এবং পাসওয়ার্ড দুটিই পূরণ করুন।'
    return
  }

  error.value = ''
  success.value = ''
  loading.value = true

  try {
    await login(form)
    success.value = 'সফলভাবে যাচাই করা হয়েছে। ড্যাশবোর্ডে প্রবেশ করা হচ্ছে...'
    setTimeout(() => {
      success.value = ''
      navigateTo('/dashboard')
    }, 1200)
  } catch (err: any) {
    error.value = err?.response?.data?.message ?? 'লগইন ব্যর্থ হয়েছে। অনুগ্রহ করে সঠিক ইমেইল ও পাসওয়ার্ড প্রদান করুন।'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ============================================================
   Rihal — Premium Islamic Auth Page Styles
   ============================================================ */

.login-wrapper {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem 0;
}

/* Panoramic Split Card */
.login-card {
  width: 100%;
  max-width: 1060px;
  background: var(--color-bg-card, #ffffff);
  border-radius: 24px;
  box-shadow: 
    0 24px 60px -12px rgba(9, 38, 23, 0.45),
    0 0 0 1px rgba(212, 175, 55, 0.25),
    0 8px 24px -4px rgba(0, 0, 0, 0.2);
  display: grid;
  grid-template-columns: 1fr 1.08fr;
  overflow: hidden;
  position: relative;
  transition: all 0.3s ease;
}

/* ------------------------------------------------------------
   LEFT COLUMN: Islamic Institutional Showcase
   ------------------------------------------------------------ */
.showcase-panel {
  background: linear-gradient(155deg, #092617 0%, #0e3b23 45%, #145032 100%);
  padding: 3rem 2.5rem;
  color: #ffffff;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
  border-right: 1px solid rgba(212, 175, 55, 0.2);
}

.showcase-bg-pattern {
  position: absolute;
  inset: 0;
  opacity: 0.08;
  background-image: 
    radial-gradient(circle at center, #d4af37 1px, transparent 1px),
    linear-gradient(45deg, rgba(212, 175, 55, 0.3) 25%, transparent 25%),
    linear-gradient(-45deg, rgba(212, 175, 55, 0.3) 25%, transparent 25%);
  background-size: 32px 32px;
  pointer-events: none;
}

.showcase-ambient-glow {
  position: absolute;
  top: -15%;
  left: 20%;
  width: 320px;
  height: 320px;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.18) 0%, rgba(20, 80, 50, 0) 70%);
  filter: blur(40px);
  pointer-events: none;
}

.showcase-content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
  height: 100%;
}

/* Bismillah Vector */
.bismillah-wrap {
  display: flex;
  justify-content: center;
  padding-bottom: 0.25rem;
}

.bismillah-svg {
  width: 260px;
  height: auto;
  filter: drop-shadow(0 2px 8px rgba(212, 175, 55, 0.3));
}

/* Brand Crest */
.brand-crest {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.emblem-container {
  width: 76px;
  height: 76px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.18) 0%, rgba(13, 59, 36, 0.6) 80%);
  border-radius: 18px;
  border: 1px solid rgba(212, 175, 55, 0.35);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3), inset 0 0 15px rgba(212, 175, 55, 0.15);
}

.emblem-svg {
  width: 60px;
  height: 60px;
  filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.4));
}

.brand-titles {
  display: flex;
  flex-direction: column;
}

.brand-name-wrap {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.brand-title {
  font-size: 2.25rem;
  font-weight: 800;
  margin: 0;
  line-height: 1.1;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  background: linear-gradient(135deg, #ffffff 40%, #e5c158 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: -0.02em;
}

.brand-badge {
  font-size: 0.65rem;
  font-weight: 800;
  background: linear-gradient(135deg, #d4af37, #aa8218);
  color: #0b291a;
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.brand-tagline {
  font-size: 0.88rem;
  color: #cfe6da;
  margin: 0.3rem 0 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  line-height: 1.4;
}

/* Quranic Verse Plaque */
.verse-card {
  background: rgba(8, 29, 18, 0.65);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(212, 175, 55, 0.3);
  border-radius: 14px;
  padding: 1.25rem 1.4rem;
  text-align: center;
  position: relative;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.verse-arch-ornament {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: #092617;
  padding: 0 8px;
}

.arabic-verse {
  font-family: 'Amiri', 'Scheherazade New', 'Traditional Arabic', serif;
  font-size: 1.35rem;
  color: #f3e5ab;
  margin: 0.25rem 0 0.4rem;
  direction: rtl;
  font-weight: bold;
  letter-spacing: 0.5px;
}

.bengali-meaning {
  font-size: 0.85rem;
  color: #e8f3ec;
  margin: 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  font-style: italic;
  line-height: 1.4;
}

.verse-reference {
  display: block;
  font-size: 0.72rem;
  color: #d4af37;
  margin-top: 0.4rem;
  font-weight: 600;
}

/* Features List */
.showcase-features {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  background: rgba(255, 255, 255, 0.04);
  padding: 0.65rem 0.9rem;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.07);
  transition: all 0.2s ease;
}

.feature-item:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(212, 175, 55, 0.25);
  transform: translateX(4px);
}

.feature-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: rgba(212, 175, 55, 0.15);
  color: #f3e5ab;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.feature-text {
  display: flex;
  flex-direction: column;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.feature-text strong {
  font-size: 0.86rem;
  color: #ffffff;
  font-weight: 600;
}

.feature-text span {
  font-size: 0.75rem;
  color: #b3d1c3;
}

/* Security Badge */
.security-footer {
  margin-top: auto;
  padding-top: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.74rem;
  color: #9cbdae;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.security-footer svg {
  color: #d4af37;
  flex-shrink: 0;
}

/* ------------------------------------------------------------
   RIGHT COLUMN: Premium Authentication Form
   ------------------------------------------------------------ */
.form-panel {
  background: #ffffff;
  position: relative;
  display: flex;
  flex-direction: column;
}

.form-ribbon {
  height: 5px;
  width: 100%;
  background: linear-gradient(90deg, #145032 0%, #d4af37 50%, #145032 100%);
}

.form-inner {
  padding: 3rem 2.8rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  flex: 1;
  justify-content: center;
}

.form-header {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.greeting-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: #eef7f2;
  color: #145032;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  width: fit-content;
  border: 1px solid #d2e7dc;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.pulse-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.form-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #111827;
  margin: 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  letter-spacing: -0.01em;
}

.form-subtitle {
  font-size: 0.88rem;
  color: #6b7280;
  margin: 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

/* Demo Role Pills */
.demo-roles-box {
  background: #f8faf9;
  border: 1px dashed #d1e3d9;
  border-radius: 12px;
  padding: 0.75rem 0.9rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.demo-label {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #186640;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.demo-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.demo-chip {
  padding: 0.35rem 0.7rem;
  background: #ffffff;
  border: 1px solid #d0dfd7;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #2c4436;
  cursor: pointer;
  transition: all 0.15s ease;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.demo-chip:hover {
  background: #eef7f2;
  border-color: #145032;
  color: #145032;
}

.demo-chip.active {
  background: #145032;
  border-color: #145032;
  color: #ffffff;
}

/* Custom Alerts */
.custom-alert {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  border-radius: 10px;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  font-size: 0.84rem;
  line-height: 1.4;
}

.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.alert-success {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
}

.alert-icon {
  flex-shrink: 0;
  margin-top: 1px;
}

.alert-body {
  display: flex;
  flex-direction: column;
}

.alert-body strong {
  font-size: 0.88rem;
  margin-bottom: 0.1rem;
}

/* Form Styles */
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.input-label {
  font-size: 0.84rem;
  font-weight: 600;
  color: #374151;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.required-star {
  color: #dc2626;
  margin-left: 0.2rem;
}

.forgot-link {
  font-size: 0.8rem;
  color: #186640;
  font-weight: 500;
  text-decoration: none;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  transition: color 0.15s ease;
}

.forgot-link:hover {
  color: #0d3b24;
  text-decoration: underline;
}

.input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.input-leading-icon {
  position: absolute;
  left: 0.95rem;
  color: #9ca3af;
  display: flex;
  align-items: center;
  pointer-events: none;
  transition: color 0.2s;
}

.custom-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.65rem;
  font-size: 0.95rem;
  color: #1f2937;
  background: #fcfdfd;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  transition: all 0.2s ease;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.custom-input.with-trailing-icon {
  padding-right: 2.75rem;
}

.custom-input:focus {
  outline: none;
  border-color: #145032;
  background: #ffffff;
  box-shadow: 0 0 0 3.5px rgba(20, 80, 50, 0.12);
}

.input-container:focus-within .input-leading-icon {
  color: #145032;
}

.password-toggle-btn {
  position: absolute;
  right: 0.75rem;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: color 0.15s ease;
}

.password-toggle-btn:hover {
  color: #374151;
}

/* Checkbox */
.form-options {
  display: flex;
  align-items: center;
}

.custom-checkbox {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  font-size: 0.86rem;
  color: #4b5563;
  user-select: none;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.custom-checkbox input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  width: 18px;
  height: 18px;
  border-radius: 5px;
  border: 1.5px solid #d1d5db;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
}

.checkmark svg {
  display: none;
  color: #ffffff;
}

.custom-checkbox input:checked ~ .checkmark {
  background: #145032;
  border-color: #145032;
}

.custom-checkbox input:checked ~ .checkmark svg {
  display: block;
}

/* Submit Button */
.submit-btn {
  width: 100%;
  padding: 0.85rem 1.5rem;
  background: linear-gradient(135deg, #145032 0%, #0d3b24 100%);
  color: #ffffff;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 4px 14px rgba(20, 80, 50, 0.25);
  transition: all 0.2s ease;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  margin-top: 0.25rem;
}

.submit-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #186640 0%, #10482c 100%);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(20, 80, 50, 0.35);
}

.submit-btn:active:not(:disabled) {
  transform: translateY(0);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-text {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-arrow {
  transition: transform 0.2s ease;
}

.submit-btn:hover .btn-arrow {
  transform: translateX(3px);
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Footer links */
.form-footer {
  margin-top: 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.85rem;
  text-align: center;
}

.register-prompt {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.register-link {
  color: #145032;
  font-weight: 700;
  text-decoration: none;
  margin-left: 0.3rem;
  transition: color 0.15s;
}

.register-link:hover {
  color: #d4af37;
  text-decoration: underline;
}

.system-status {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.75rem;
  color: #9ca3af;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.status-indicator {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10b981;
}

/* Transition Animations */
.fade-enter-active,
.fade-leave-active {
  transition: all 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* ------------------------------------------------------------
   RESPONSIVE DESIGN BREAKPOINTS
   ------------------------------------------------------------ */
@media (max-width: 960px) {
  .login-card {
    grid-template-columns: 1fr;
    max-width: 520px;
  }

  .showcase-panel {
    padding: 2.25rem 1.75rem;
    border-right: none;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
  }

  .showcase-features,
  .security-footer {
    display: none;
  }

  .bismillah-svg {
    width: 200px;
  }

  .brand-crest {
    justify-content: center;
    text-align: center;
    flex-direction: column;
    gap: 0.75rem;
  }

  .brand-name-wrap {
    justify-content: center;
  }

  .verse-card {
    padding: 1rem;
  }

  .form-inner {
    padding: 2.25rem 1.75rem;
  }
}

@media (max-width: 480px) {
  .login-card {
    border-radius: 18px;
  }

  .showcase-panel {
    padding: 1.75rem 1.25rem;
  }

  .brand-title {
    font-size: 1.85rem;
  }

  .form-inner {
    padding: 1.75rem 1.25rem;
  }

  .form-title {
    font-size: 1.45rem;
  }

  .demo-buttons {
    flex-direction: column;
  }
}
</style>
