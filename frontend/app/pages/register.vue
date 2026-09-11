<template>
  <div class="register-wrapper">
    <!-- Main Split-Screen Panoramic Card -->
    <div class="register-card">
      
      <!-- Left Column: Institutional Value & Islamic Showcase -->
      <div class="showcase-panel">
        <div class="showcase-bg-pattern" aria-hidden="true"></div>
        <div class="showcase-ambient-glow" aria-hidden="true"></div>

        <div class="showcase-content">
          <!-- Bismillah Calligraphy -->
          <div class="bismillah-wrap" aria-label="বিসমিল্লাহির রাহমানির রাহিম">
            <svg class="bismillah-svg" viewBox="0 0 320 54" fill="currentColor">
              <path d="M160 8c-6 0-11 3-14 8-3-5-8-8-14-8-11 0-20 9-20 20 0 14 17 26 34 26 17 0 34-12 34-26 0-11-9-20-20-20zm0 40c-12 0-24-9-24-20 0-7 5-12 11-12 5 0 9 3 11 8h4c2-5 6-8 11-8 6 0 11 5 11 12 0 11-12 20-23 20z" opacity="0.15"/>
              <text x="50%" y="36" text-anchor="middle" font-family="'Amiri', 'Scheherazade New', 'Traditional Arabic', 'Noto Naskh Arabic', serif" font-size="25" font-weight="bold" fill="url(#goldGradReg)" letter-spacing="1">
                بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
              </text>
              <defs>
                <linearGradient id="goldGradReg" x1="0%" y1="0%" x2="100%" y2="0%">
                  <stop offset="0%" stop-color="#e5c158" />
                  <stop offset="50%" stop-color="#d4af37" />
                  <stop offset="100%" stop-color="#f5e2a3" />
                </linearGradient>
              </defs>
            </svg>
          </div>

          <!-- Brand Crest -->
          <div class="brand-crest">
            <div class="emblem-container">
              <!-- Custom Islamic Mihrab + Rehal (Quran Stand) Emblem -->
              <svg class="emblem-svg" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- 8-pointed star base (Rub el Hizb) -->
                <rect x="24" y="24" width="48" height="48" rx="6" transform="rotate(45 48 48)" stroke="url(#goldGradReg)" stroke-width="1.5" stroke-dasharray="2 2" opacity="0.6"/>
                <rect x="24" y="24" width="48" height="48" rx="8" stroke="url(#goldGradReg)" stroke-width="1.8"/>
                
                <!-- Inner Arch (Mihrab) -->
                <path d="M32 68V44C32 35.1634 39.1634 28 48 28C56.8366 28 64 35.1634 64 44V68" stroke="#ffffff" stroke-width="2" stroke-linecap="round" opacity="0.85"/>
                <path d="M48 24V28" stroke="url(#goldGradReg)" stroke-width="2.5" stroke-linecap="round"/>
                
                <!-- Rihal (Folding Book Stand) Silhouette -->
                <path d="M36 65L60 47M60 65L36 47" stroke="url(#goldGradReg)" stroke-width="2.5" stroke-linecap="round"/>
                
                <!-- Open Quran Book on Top -->
                <path d="M48 44C44 41 38 41 34 43V55C38 53 44 53 48 56C52 53 58 53 62 55V43C58 41 52 41 48 44Z" fill="url(#goldGradReg)" fill-opacity="0.9" stroke="#ffffff" stroke-width="1"/>
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

          <!-- Sacred Hadith Plaque on Seeking Knowledge -->
          <div class="verse-card">
            <div class="verse-arch-ornament">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="url(#goldGradReg)" opacity="0.5"/>
                <path d="M12 6L13.8 9.6L17.8 10.2L14.9 13L15.6 17L12 15.1L8.4 17L9.1 13L6.2 10.2L10.2 9.6L12 6z" fill="url(#goldGradReg)"/>
              </svg>
            </div>
            <p class="arabic-verse" lang="ar">« مَنْ سَلَكَ طَرِيقًا يَلْتَمِسُ فِيهِ عِلْمًا سَهَّلَ اللَّهُ لَهُ طَرِيقًا إِلَى الْجَنَّةِ »</p>
            <p class="bengali-meaning">"যে ব্যক্তি জ্ঞান অন্বেষণের পথে চলে, আল্লাহ তার জন্য জান্নাতের পথ সুগম করে দেন।"</p>
            <span class="verse-reference">— সহীহ মুসলিম: ২৬৯৯</span>
          </div>

          <!-- Madrasa Digitalization Pillars -->
          <div class="showcase-features">
            <div class="feature-item">
              <div class="feature-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                  <path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20V6a2 2 0 0 0-2-2H6.5A2.5 2.5 0 0 0 4 6.5z"/>
                </svg>
              </div>
              <div class="feature-text">
                <strong>সম্পূর্ণ মাদ্রাসা অটোমেশন</strong>
                <span>হিফজ, কিতাব, শিক্ষক ও ছাত্র রেকর্ড</span>
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
                <strong>স্বচ্ছ হিসাব ও লিল্লাহ তহবিল</strong>
                <span>যাকাত, ডোনেশন ও ডাবল-এন্ট্রি অডিট</span>
              </div>
            </div>

            <div class="feature-item">
              <div class="feature-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
              </div>
              <div class="feature-text">
                <strong>দ্রুত সেটআপ ও ক্লাউড অ্যাক্সেস</strong>
                <span>মুহূর্তেই আপনার মাদ্রাসার ডিজিটাল কার্যক্রম শুরু</span>
              </div>
            </div>
          </div>

          <!-- Security & Privacy Seal -->
          <div class="security-footer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
            <span>২৫৬-বিট এনক্রিপশন • শরীয়াহ ও পূর্ণ তথ্য নিরাপত্তা নিশ্চিত</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Smart Registration Form -->
      <div class="form-panel">
        <!-- Gilded Ornamental Top Ribbon -->
        <div class="form-ribbon" aria-hidden="true"></div>

        <div class="form-inner">
          <!-- Form Header -->
          <div class="form-header">
            <div class="greeting-badge">
              <span class="pulse-dot"></span>
              <span>নতুন মাদ্রাসার নিবন্ধন</span>
            </div>
            <h2 class="form-title">অ্যাকাউন্ট তৈরি করুন</h2>
            <p class="form-subtitle">আপনার মাদ্রাসার ডিজিটাল ব্যবস্থাপনা শুরু করতে প্রয়োজনীয় তথ্য প্রদান করুন</p>
          </div>

          <!-- Notifications -->
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
                <strong>নিবন্ধন অসম্পূর্ণ</strong>
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
                <strong>নিবন্ধন সফল হয়েছে!</strong>
                <span>{{ success }}</span>
              </div>
            </div>
          </transition>

          <!-- The Registration Form -->
          <form @submit.prevent="handleRegister" class="register-form" novalidate>
            
            <!-- 2-Column Grid: Names -->
            <div class="form-row-2">
              <!-- Bengali Name (Required) -->
              <div class="form-group">
                <label for="name_bn" class="input-label">
                  <span>পূর্ণ নাম (বাংলা)</span>
                  <span class="required-star">*</span>
                </label>
                <div class="input-container">
                  <span class="input-leading-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                      <circle cx="12" cy="7" r="4"/>
                    </svg>
                  </span>
                  <input
                    id="name_bn"
                    v-model="form.name_bn"
                    type="text"
                    autocomplete="name"
                    placeholder="যেমন: মাওলানা আব্দুর রহমান"
                    :disabled="loading"
                    required
                    autofocus
                    class="custom-input"
                  />
                </div>
                <span v-if="form.name_bn && form.name_bn.trim().length < 2" class="input-hint-error">
                  নাম কমপক্ষে ২ অক্ষর হতে হবে
                </span>
              </div>

              <!-- English Name (Optional) -->
              <div class="form-group">
                <label for="name_en" class="input-label">
                  <span>নাম (ইংরেজি)</span>
                  <span class="optional-tag">(ঐচ্ছিক)</span>
                </label>
                <div class="input-container">
                  <span class="input-leading-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"/>
                      <line x1="2" y1="12" x2="22" y2="12"/>
                      <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                  </span>
                  <input
                    id="name_en"
                    v-model="form.name_en"
                    type="text"
                    autocomplete="name"
                    placeholder="e.g. Abdur Rahman"
                    :disabled="loading"
                    class="custom-input"
                  />
                </div>
              </div>
            </div>

            <!-- 2-Column Grid: Contact (Email & Phone) -->
            <div class="form-row-2">
              <!-- Email Address (Required) -->
              <div class="form-group">
                <label for="reg-email" class="input-label">
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
                    id="reg-email"
                    v-model="form.email"
                    type="email"
                    inputmode="email"
                    autocomplete="email"
                    placeholder="principal@madrasa.edu.bd"
                    :disabled="loading"
                    required
                    class="custom-input"
                  />
                </div>
              </div>

              <!-- Phone Number (Optional) -->
              <div class="form-group">
                <label for="reg-phone" class="input-label">
                  <span>মোবাইল নম্বর</span>
                  <span class="optional-tag">(ঐচ্ছিক)</span>
                </label>
                <div class="input-container">
                  <span class="input-leading-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                  </span>
                  <input
                    id="reg-phone"
                    v-model="form.phone"
                    type="tel"
                    inputmode="tel"
                    autocomplete="tel"
                    placeholder="০১৭১১-XXXXXX"
                    :disabled="loading"
                    class="custom-input"
                  />
                </div>
              </div>
            </div>

            <!-- 2-Column Grid: Passwords -->
            <div class="form-row-2">
              <!-- Password (Required) -->
              <div class="form-group">
                <label for="reg-password" class="input-label">
                  <span>পাসওয়ার্ড</span>
                  <span class="required-star">*</span>
                </label>
                <div class="input-container">
                  <span class="input-leading-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </span>
                  <input
                    id="reg-password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    autocomplete="new-password"
                    placeholder="কমপক্ষে ৮ অক্ষর"
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
                <div class="password-validation-indicator" v-if="form.password">
                  <span :class="form.password.length >= 8 ? 'text-valid' : 'text-invalid'">
                    {{ form.password.length >= 8 ? '✓ ৮+ অক্ষর হয়েছে' : '✗ কমপক্ষে ৮ অক্ষর প্রয়োজন' }}
                  </span>
                </div>
              </div>

              <!-- Confirm Password (Required) -->
              <div class="form-group">
                <label for="reg-password-confirm" class="input-label">
                  <span>পাসওয়ার্ড নিশ্চিত করুন</span>
                  <span class="required-star">*</span>
                </label>
                <div class="input-container">
                  <span class="input-leading-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </span>
                  <input
                    id="reg-password-confirm"
                    v-model="form.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    autocomplete="new-password"
                    placeholder="পাসওয়ার্ড আবার লিখুন"
                    :disabled="loading"
                    required
                    class="custom-input with-trailing-icon"
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    @click="showConfirmPassword = !showConfirmPassword"
                    :title="showConfirmPassword ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'"
                    :aria-label="showConfirmPassword ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'"
                  >
                    <svg v-if="!showConfirmPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24M1 1l22 22"/>
                    </svg>
                  </button>
                </div>
                <div class="password-validation-indicator" v-if="form.password_confirmation">
                  <span :class="form.password === form.password_confirmation ? 'text-valid' : 'text-invalid'">
                    {{ form.password === form.password_confirmation ? '✓ পাসওয়ার্ড মিলেছে' : '✗ পাসওয়ার্ড মিলছে না' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Terms & Agreement Checkbox -->
            <div class="terms-row">
              <label class="custom-checkbox">
                <input type="checkbox" v-model="agreeTerms" :disabled="loading" required />
                <span class="checkmark">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </span>
                <span class="checkbox-text">
                  আমি রিহাল প্ল্যাটফর্মের <a href="#terms" class="text-link" @click.prevent>ব্যবহারবিধি</a> ও <a href="#privacy" class="text-link" @click.prevent>শরীয়াহ নীতিমালা</a> মেনে নিচ্ছি।
                </span>
              </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn" :disabled="loading">
              <span v-if="loading" class="spinner" aria-hidden="true"></span>
              <span v-else class="btn-text">
                <span>অ্যাকাউন্ট তৈরি করুন</span>
                <svg class="btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="5" y1="12" x2="19" y2="12"/>
                  <polyline points="12 5 19 12 12 19"/>
                </svg>
              </span>
            </button>
          </form>

          <!-- Bottom Navigation -->
          <div class="form-footer">
            <p class="login-prompt">
              ইতিমধ্যে মাদ্রাসা অ্যাকাউন্ট রয়েছে?
              <NuxtLink to="/login" class="login-link">লগইন করুন</NuxtLink>
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
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  layout: 'auth', // Uses unified luxury Islamic background from auth.vue
})

const { register, isAuthenticated } = useAuth()

const form = ref({
  name_bn: '',
  name_en: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
  tenant_id: null as string | null,
})

const agreeTerms = ref(true)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const error = ref('')
const success = ref('')
const loading = ref(false)

const isFormValid = computed(() => {
  return form.value.name_bn.trim().length >= 2 &&
    form.value.email.includes('@') &&
    form.value.password.length >= 8 &&
    form.value.password === form.value.password_confirmation &&
    agreeTerms.value
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
  if (!agreeTerms.value) {
    error.value = 'অনুগ্রহ করে প্ল্যাটফর্মের ব্যবহারবিধি ও শরীয়াহ নীতিমালায় সম্মতি প্রদান করুন।'
    return
  }

  if (!isFormValid.value) {
    error.value = 'সব প্রয়োজনীয় ঘর সঠিকভাবে পূরণ করুন: নাম (২+ অক্ষর), বৈধ ইমেইল, এবং মিলে যাওয়া ৮+ অক্ষরের পাসওয়ার্ড।'
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
    success.value = 'আপনার অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে! লগইন পেজে নিয়ে যাওয়া হচ্ছে...'
    form.value = {
      name_bn: '',
      name_en: '',
      email: '',
      password: '',
      password_confirmation: '',
      phone: '',
      tenant_id: null,
    }
    setTimeout(() => {
      navigateTo('/login')
    }, 1500)
  } catch (err: any) {
    error.value = err?.response?.data?.message ?? 'অ্যাকাউন্ট তৈরিতে সমস্যা হয়েছে। ইমেইল ইউনিক হতে হবে এবং পাসওয়ার্ড নিরাপদ হতে হবে।'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ============================================================
   Rihal — Register Page (Shares Unified Auth Background)
   ============================================================ */

.register-wrapper {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem 0;
}

.register-card {
  width: 100%;
  max-width: 1100px;
  background: var(--color-bg-card, #ffffff);
  border-radius: 24px;
  box-shadow: 
    0 24px 60px -12px rgba(9, 38, 23, 0.45),
    0 0 0 1px rgba(212, 175, 55, 0.25),
    0 8px 24px -4px rgba(0, 0, 0, 0.2);
  display: grid;
  grid-template-columns: 1fr 1.15fr;
  overflow: hidden;
  position: relative;
  transition: all 0.3s ease;
}

/* ------------------------------------------------------------
   LEFT COLUMN: Institutional Showcase
   ------------------------------------------------------------ */
.showcase-panel {
  background: linear-gradient(155deg, #092617 0%, #0e3b23 45%, #145032 100%);
  padding: 3rem 2.4rem;
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
  gap: 1.6rem;
  height: 100%;
}

/* Bismillah Vector */
.bismillah-wrap {
  display: flex;
  justify-content: center;
  padding-bottom: 0.2rem;
}

.bismillah-svg {
  width: 250px;
  height: auto;
  filter: drop-shadow(0 2px 8px rgba(212, 175, 55, 0.3));
}

/* Brand Crest */
.brand-crest {
  display: flex;
  align-items: center;
  gap: 1.15rem;
}

.emblem-container {
  width: 74px;
  height: 74px;
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
  width: 58px;
  height: 58px;
  filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.4));
}

.brand-titles {
  display: flex;
  flex-direction: column;
}

.brand-name-wrap {
  display: flex;
  align-items: center;
  gap: 0.55rem;
}

.brand-title {
  font-size: 2.2rem;
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
  font-size: 0.86rem;
  color: #cfe6da;
  margin: 0.3rem 0 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  line-height: 1.4;
}

/* Hadith Plaque */
.verse-card {
  background: rgba(8, 29, 18, 0.65);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(212, 175, 55, 0.3);
  border-radius: 14px;
  padding: 1.2rem 1.3rem;
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
  font-size: 1.28rem;
  color: #f3e5ab;
  margin: 0.25rem 0 0.4rem;
  direction: rtl;
  font-weight: bold;
  letter-spacing: 0.5px;
  line-height: 1.5;
}

.bengali-meaning {
  font-size: 0.84rem;
  color: #e8f3ec;
  margin: 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  font-style: italic;
  line-height: 1.45;
}

.verse-reference {
  display: block;
  font-size: 0.72rem;
  color: #d4af37;
  margin-top: 0.4rem;
  font-weight: 600;
}

/* Feature List */
.showcase-features {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  background: rgba(255, 255, 255, 0.04);
  padding: 0.65rem 0.85rem;
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
  font-size: 0.74rem;
  color: #b3d1c3;
}

/* Security Badge */
.security-footer {
  margin-top: auto;
  padding-top: 0.5rem;
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
   RIGHT COLUMN: Smart Registration Form
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
  padding: 2.8rem 2.6rem;
  display: flex;
  flex-direction: column;
  gap: 1.35rem;
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
  font-size: 1.7rem;
  font-weight: 800;
  color: #111827;
  margin: 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  letter-spacing: -0.01em;
}

.form-subtitle {
  font-size: 0.86rem;
  color: #6b7280;
  margin: 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

/* Custom Alert Messages */
.custom-alert {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.8rem 0.95rem;
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
.register-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* 2-Column Responsive Form Row */
.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.input-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #374151;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.required-star {
  color: #dc2626;
}

.optional-tag {
  font-size: 0.72rem;
  color: #9ca3af;
  font-weight: 400;
}

.input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.input-leading-icon {
  position: absolute;
  left: 0.85rem;
  color: #9ca3af;
  display: flex;
  align-items: center;
  pointer-events: none;
  transition: color 0.2s;
}

.custom-input {
  width: 100%;
  padding: 0.68rem 0.85rem 0.68rem 2.45rem;
  font-size: 0.92rem;
  color: #1f2937;
  background: #fcfdfd;
  border: 1.5px solid #e5e7eb;
  border-radius: 9px;
  transition: all 0.2s ease;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.custom-input.with-trailing-icon {
  padding-right: 2.55rem;
}

.custom-input:focus {
  outline: none;
  border-color: #145032;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(20, 80, 50, 0.12);
}

.input-container:focus-within .input-leading-icon {
  color: #145032;
}

.password-toggle-btn {
  position: absolute;
  right: 0.65rem;
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

.input-hint-error {
  font-size: 0.72rem;
  color: #dc2626;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.password-validation-indicator {
  font-size: 0.72rem;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.text-valid {
  color: #166534;
  font-weight: 600;
}

.text-invalid {
  color: #dc2626;
}

/* Terms Checkbox */
.terms-row {
  margin-top: 0.15rem;
}

.custom-checkbox {
  display: inline-flex;
  align-items: flex-start;
  gap: 0.55rem;
  cursor: pointer;
  font-size: 0.82rem;
  color: #4b5563;
  user-select: none;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  line-height: 1.4;
}

.custom-checkbox input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  width: 17px;
  height: 17px;
  border-radius: 4px;
  border: 1.5px solid #d1d5db;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
  flex-shrink: 0;
  margin-top: 1px;
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

.text-link {
  color: #145032;
  font-weight: 600;
  text-decoration: underline;
}

.text-link:hover {
  color: #d4af37;
}

/* Submit Button */
.submit-btn {
  width: 100%;
  padding: 0.82rem 1.5rem;
  background: linear-gradient(135deg, #145032 0%, #0d3b24 100%);
  color: #ffffff;
  border: none;
  border-radius: 10px;
  font-size: 0.98rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 4px 14px rgba(20, 80, 50, 0.25);
  transition: all 0.2s ease;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
  margin-top: 0.4rem;
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
  margin-top: 0.4rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  text-align: center;
}

.login-prompt {
  font-size: 0.86rem;
  color: #6b7280;
  margin: 0;
  font-family: var(--font-bn, 'Noto Sans Bengali', sans-serif);
}

.login-link {
  color: #145032;
  font-weight: 700;
  text-decoration: none;
  margin-left: 0.3rem;
  transition: color 0.15s;
}

.login-link:hover {
  color: #d4af37;
  text-decoration: underline;
}

.system-status {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.74rem;
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
  .register-card {
    grid-template-columns: 1fr;
    max-width: 540px;
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

  .form-row-2 {
    grid-template-columns: 1fr;
    gap: 0.85rem;
  }
}

@media (max-width: 480px) {
  .register-card {
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
}
</style>
