<template>
  <article class="panel activity-panel">
    <div class="panel-heading">
      <div>
        <span class="panel-icon purple"><Icon name="clock" /></span>
        <div>
          <h3>সাম্প্রতিক কার্যকলাপ</h3>
          <p>সিস্টেমের সর্বশেষ পরিবর্তনসমূহ</p>
        </div>
      </div>
      <NuxtLink to="/activity-log" class="icon-link"><Icon name="external" /></NuxtLink>
    </div>
    
    <div class="activity-feed">
      <div v-for="(activity, index) in activities" :key="index" class="activity-item" :class="`activity-tone-${activity.tone}`">
        <div class="activity-timeline">
          <div class="activity-dot"></div>
          <div class="activity-line" v-if="index !== activities.length - 1"></div>
        </div>
        <div class="activity-content">
          <p class="activity-text" v-html="activity.text"></p>
          <span class="activity-time">{{ activity.time }}</span>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Icon from '~/components/Icon.vue'

// Mocked recent activities
const activities = ref([
  {
    text: '<strong>আব্দুল্লাহ</strong> এর ৳৩,০০০ বেতন জমা নেওয়া হয়েছে।',
    time: '১০ মিনিট আগে',
    tone: 'gold'
  },
  {
    text: '<strong>রহিম উদ্দিন</strong> নতুন শিক্ষার্থী হিসেবে ভর্তি হয়েছে।',
    time: '১ ঘণ্টা আগে',
    tone: 'green'
  },
  {
    text: 'প্রথম সাময়িক পরীক্ষার ফলাফল প্রকাশিত হয়েছে।',
    time: '৩ ঘণ্টা আগে',
    tone: 'blue'
  },
  {
    text: '<strong>মো. হাসান</strong> এর ছুটি মঞ্জুর করা হয়েছে।',
    time: 'গতকাল',
    tone: 'purple'
  },
  {
    text: 'নতুন নোটিশ "আগামীকালের ছুটি" যোগ করা হয়েছে।',
    time: 'গতকাল',
    tone: 'amber'
  }
])
</script>

<style scoped>
.panel { min-width: 0; background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 18px; box-shadow: var(--shadow-sm); padding: 1.25rem; transition: transform .3s, box-shadow .3s; }
.panel:hover { box-shadow: var(--shadow-md); }
.panel-heading { margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; }
.panel-heading > div:first-child { display: flex; align-items: center; gap: .75rem; }
.panel-heading h3 { font: 700 1rem var(--font-bn); color: var(--color-text); }
.panel-heading p { margin: .18rem 0 0; color: var(--color-text-muted); font: .74rem var(--font-bn); }
.panel-icon { display: grid; place-items: center; width: 40px; height: 40px; border-radius: 12px; font-size: 1rem; }
.panel-icon.purple { color: #7857a9; background: #f0eafb; }
.icon-link { display: inline-flex; width: 30px; height: 30px; justify-content: center; align-items: center; border-radius: 9px; background: var(--color-bg-muted); color: var(--color-primary); }
.icon-link:hover { background: var(--color-primary-50); }

.activity-feed { display: flex; flex-direction: column; padding-top: 0.5rem; }
.activity-item { display: flex; gap: 1rem; position: relative; }
.activity-timeline { display: flex; flex-direction: column; align-items: center; width: 24px; flex-shrink: 0; }
.activity-dot { width: 10px; height: 10px; border-radius: 50%; border: 2px solid var(--color-primary); background: var(--color-bg-card); z-index: 2; margin-top: 5px; transition: transform .2s; }
.activity-item:hover .activity-dot { transform: scale(1.3); }
.activity-line { width: 2px; flex: 1; background: var(--color-border-light); margin-top: 4px; margin-bottom: 4px; min-height: 20px; }

.activity-content { padding-bottom: 1.25rem; }
.activity-text { font: .82rem var(--font-bn); color: var(--color-text); margin: 0 0 .25rem 0; line-height: 1.4; }
.activity-time { font: .7rem var(--font-bn); color: var(--color-text-muted); }

/* Tone variations for dots */
.activity-tone-green .activity-dot { border-color: #168152; }
.activity-tone-blue .activity-dot { border-color: #2b719e; }
.activity-tone-gold .activity-dot { border-color: #b7902e; }
.activity-tone-purple .activity-dot { border-color: #7857a9; }
.activity-tone-amber .activity-dot { border-color: #bd7620; }
</style>
