<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <h1>সিস্টেম সেটিংস</h1>
        <p class="page-subtitle">সিস্টেম কনফিগারেশন, ব্যবহারকারী, ভাষা ও বিজ্ঞপ্তি সেটিংস</p>
      </div>
    </div>

    <div class="settings-grid">
      <NuxtLink :to="'/settings'" class="setting-card">
        <div class="setting-header">
          <icon name="settings" class="setting-icon" />
          <div>
            <h3 class="setting-title">সাধারণ সেটিংস</h3>
            <p class="setting-desc">সিস্টেম কনফিগারেশন, ব্র্যান্ডিং, ভাষা, সময় অঞ্চল ও মুদ্রা</p>
          </div>
        </div>
      </NuxtLink>

      <NuxtLink :to="'/settings/admin-users'" class="setting-card">
        <div class="setting-header">
          <icon name="users" class="setting-icon" />
          <div>
            <h3 class="setting-title">ব্যবহারকারী ও ভূমিকা</h3>
            <p class="setting-desc">ব্যবহারকারী তৈরি, ভূমিকা নির্ধারণ ও অনুমতি পরিচালনা</p>
          </div>
        </div>
      </NuxtLink>

      <NuxtLink :to="'/settings/sessions'" class="setting-card">
        <div class="setting-header">
          <icon name="calendar" class="setting-icon" />
          <div>
            <h3 class="setting-title">সেশন সেটআপ</h3>
            <p class="setting-desc">শিক্ষাবর্ষ, সেশন ও পরীক্ষা সেশন তৈরি ও ব্যবস্থাপনা</p>
          </div>
        </div>
      </NuxtLink>

      <NuxtLink :to="'/settings/classes'" class="setting-card">
        <div class="setting-header">
          <icon name="academic" class="setting-icon" />
          <div>
            <h3 class="setting-title">শ্রেণি ব্যবস্থাপনা</h3>
            <p class="setting-desc">শ্রেণি, বিভাগ, শিক্ষক নিয়োগ ও সিট ব্যবস্থাপনা</p>
          </div>
        </div>
      </NuxtLink>

      <NuxtLink :to="'/settings/sections'" class="setting-card">
        <div class="setting-header">
          <icon name="academic" class="setting-icon" />
          <div>
            <h3 class="setting-title">বিভাগ ব্যবস্থাপনা</h3>
            <p class="setting-desc">বিভাগ, শিক্ষক-বিষয় বরাদ্দ ও সিট তৈরি</p>
          </div>
        </div>
      </NuxtLink>

      <NuxtLink :to="'/settings/subjects'" class="setting-card">
        <div class="setting-header">
          <icon name="academic" class="setting-icon" />
          <div>
            <h3 class="setting-title">বিষয় ব্যবস্থাপনা</h3>
            <p class="setting-desc">বিষয় তৈরি, পাঠ্যক্রম, বই ও শিক্ষক নিয়োগ</p>
          </div>
        </div>
      </NuxtLink>

      <NuxtLink :to="'/settings/subject-assignment'" class="setting-card">
        <div class="setting-header">
          <icon name="assignment" class="setting-icon" />
          <div>
            <h3 class="setting-title">বিষয় বরাদ্দ</h3>
            <p class="setting-desc">শিক্ষক-বিষয়-শ্রেণি বরাদ্দ ও ক্লাস রুটিন তৈরি</p>
          </div>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'dashboard' })
</script>

<style scoped>
.page-wrapper {
  max-width: 900px;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.page-header-row h1 {
  font-family: var(--font-bn);
  font-size: 1.6rem;
  color: var(--color-primary);
  margin: 0 0 0.3rem;
}

.page-subtitle {
  color: var(--color-text-light);
  font-family: var(--font-bn);
  margin: 0;
  font-size: 0.88rem;
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1rem;
}

.setting-card {
  display: block;
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  padding: 1.1rem;
  transition: all 0.15s ease;
  text-decoration: none;
  color: inherit;
}

.setting-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-3px);
  border-color: var(--color-primary-200);
}

.setting-header {
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
}

.setting-icon {
  width: 36px;
  height: 36px;
  color: var(--color-primary);
  background: var(--color-primary-100);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.setting-title {
  font-family: var(--font-bn);
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--color-text);
  margin: 0 0 0.2rem;
}

.setting-desc {
  font-family: var(--font-bn);
  font-size: 0.78rem;
  color: var(--color-text-light);
  margin: 0;
  line-height: 1.4;
}

@media (max-width: 600px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
}
</style>