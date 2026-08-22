<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <h1>সেশন</h1>
        <p class="page-subtitle">একাডেমিক সেশন, শিক্ষাবর্ষ, পরীক্ষা সেশন ও ক্লাস নির্ধারণ</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary">+ নতুন সেশন</button>
      </div>
    </div>

    <div class="not-built-notice">
      <icon name="calendar" class="notice-icon" />
      <div>
        <h3>এই পেজ এখনও তৈরি করা হয়নি</h3>
        <p class="text-muted">সেশন তৈরি, সম্পাদনা ও পরীক্ষা সেশন পরিচালনার পেজটি শীঘ্রই যুক্ত হবে।</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'dashboard' })
</script>

<style scoped>
.page-wrapper { max-width: 900px; margin: 0 auto; padding: 1.5rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }
.header-actions { display: flex; gap: 0.5rem; }
.btn {
  padding: 0.55rem 1.1rem;
  border-radius: 10px;
  font-family: var(--font-bn);
  font-weight: 600;
  font-size: 0.82rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  border: 1px solid var(--color-border);
  background: white;
  color: var(--color-text);
  transition: all 0.15s ease;
}
.btn:hover { background: var(--color-bg-muted); }
.not-built-notice {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  padding: 2rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  box-shadow: var(--shadow-sm);
  margin-top: 0.5rem;
}
.notice-icon { width: 40px; height: 40px; color: var(--color-primary-400); flex-shrink: 0; margin-top: 0.1rem; }
.not-built-notice h3 { font-family: var(--font-bn); font-size: 1rem; margin: 0 0 0.3rem; color: var(--color-text); }
.not-built-notice p { font-size: 0.78rem; color: var(--color-text-muted); margin: 0; font-family: var(--font-bn); }
</style>