<template>
  <div class="page-wrapper slide-up-fade">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">ডিজিটাল হাজিরা</span>
        <h1>ডিজিটাল হাজিরা ড্যাশবোর্ড</h1>
        <p class="page-subtitle">ডিভাইস, কার্ড, এবং আঙুলের ছাপ ভিত্তিক হাজিরার রিয়েল-টাইম তথ্য</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/digital-attendance/devices" class="btn btn-primary">
          <Icon name="mdi:router-wireless" /> ডিভাইস পরিচালনা
        </NuxtLink>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <NuxtLink to="/digital-attendance/rfid-cards" class="action-card blue">
        <Icon name="mdi:card-account-details-outline" size="32" />
        <div class="action-content">
          <h4>RFID কার্ড ম্যানেজমেন্ট</h4>
          <p>নতুন কার্ড ইস্যু বা ব্লক করুন</p>
        </div>
      </NuxtLink>
      <NuxtLink to="/digital-attendance/fingerprint-verify" class="action-card teal">
        <Icon name="mdi:fingerprint" size="32" />
        <div class="action-content">
          <h4>আঙুলের ছাপ যাচাই</h4>
          <p>ছাত্র ও স্টাফদের ছাপ নিবন্ধন করুন</p>
        </div>
      </NuxtLink>
      <NuxtLink to="/digital-attendance/adms-commands" class="action-card amber">
        <Icon name="mdi:sync" size="32" />
        <div class="action-content">
          <h4>ADMS কমান্ড</h4>
          <p>ডিভাইস সিঙ্ক ও ডেটা রিফ্রেশ</p>
        </div>
      </NuxtLink>
      <NuxtLink to="/digital-attendance/tools" class="action-card purple">
        <Icon name="mdi:tools" size="32" />
        <div class="action-content">
          <h4>ডিভাইস টুলস</h4>
          <p>সিস্টেম লিনিয়ার ও ডায়াগনস্টিক</p>
        </div>
      </NuxtLink>
    </div>

    <div class="attendance-split">
      <div class="table-card recent-logs">
        <div class="card-header">
          <h3 class="card-title">রিয়েল-টাইম লগ</h3>
          <span class="badge badge-success"><Icon name="mdi:circle-medium" class="pulse" /> লাইভ কানেক্টেড</span>
        </div>
        <div class="table-responsive">
          <table class="premium-table">
            <thead>
              <tr>
                <th>সময়</th>
                <th>নাম</th>
                <th>ধরণ</th>
                <th>ডিভাইস</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in recentLogs" :key="log.id">
                <td>{{ log.time }}</td>
                <td>
                  <div class="user-info-sm">
                    <strong>{{ log.name }}</strong>
                    <span class="text-muted">{{ log.role }}</span>
                  </div>
                </td>
                <td>
                  <span class="badge-outline" :class="log.type === 'IN' ? 'success' : 'warning'">
                    {{ log.type === 'IN' ? 'প্রবেশ' : 'বাহির' }}
                  </span>
                </td>
                <td>{{ log.device }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="stats-panel">
        <div class="card-header">
          <h3 class="card-title">আজকের সারসংক্ষেপ</h3>
        </div>
        <div class="attendance-summary">
          <div class="summary-item">
            <div class="icon-box bg-green-light text-green"><Icon name="mdi:account-check" /></div>
            <div class="summary-text">
              <span class="val">৮৪৫</span>
              <span class="lbl">উপস্থিত</span>
            </div>
          </div>
          <div class="summary-item">
            <div class="icon-box bg-red-light text-red"><Icon name="mdi:account-cancel" /></div>
            <div class="summary-text">
              <span class="val">৪২</span>
              <span class="lbl">অনুপস্থিত</span>
            </div>
          </div>
          <div class="summary-item">
            <div class="icon-box bg-blue-light text-blue"><Icon name="mdi:router-network" /></div>
            <div class="summary-text">
              <span class="val">৪/৪</span>
              <span class="lbl">ডিভাইস সক্রিয়</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const recentLogs = ref([
  { id: 1, time: '০৯:৪৫ এএম', name: 'আব্দুল্লাহ আল নোমান', role: 'ছাত্র', type: 'IN', device: 'Main Gate ZKTeco' },
  { id: 2, time: '০৯:৪৩ এএম', name: 'মোঃ ফাহিম আহমেদ', role: 'ছাত্র', type: 'IN', device: 'Main Gate ZKTeco' },
  { id: 3, time: '০৯:৪০ এএম', name: 'হাফেজ জুবায়ের', role: 'শিক্ষক', type: 'IN', device: 'Staff Entrance' },
  { id: 4, time: '০৯:৩৫ এএম', name: 'আরিফুর রহমান', role: 'ছাত্র', type: 'IN', device: 'Hostel Gate' },
  { id: 5, time: '০৯:৩০ এএম', name: 'রহমতুল্লাহ', role: 'ছাত্র', type: 'OUT', device: 'Hostel Gate' },
  { id: 6, time: '০৯:২৫ এএম', name: 'ইমরান খান', role: 'স্টাফ', type: 'IN', device: 'Staff Entrance' },
])
</script>

<style scoped>
.quick-actions {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}
.action-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.5rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  text-decoration: none;
  color: var(--color-text);
  transition: all var(--transition-normal);
  box-shadow: var(--shadow-sm);
}
.action-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
  border-color: var(--color-primary-light);
}
.action-card.blue { border-bottom: 3px solid #3b82f6; }
.action-card.teal { border-bottom: 3px solid #14b8a6; }
.action-card.amber { border-bottom: 3px solid #f59e0b; }
.action-card.purple { border-bottom: 3px solid #8b5cf6; }

.action-card svg {
  color: var(--color-primary);
  opacity: 0.8;
}

.action-content h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  font-family: var(--font-bn);
}
.action-content p {
  margin: 0;
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

.attendance-split {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--color-border-light);
}
.card-title {
  margin: 0;
  font-size: 1.2rem;
}

.pulse {
  animation: pulse-animation 2s infinite;
}
@keyframes pulse-animation {
  0% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.2); }
  100% { opacity: 1; transform: scale(1); }
}

.user-info-sm strong { display: block; font-size: 0.95rem; }
.user-info-sm span { font-size: 0.8rem; }

.badge-outline {
  padding: 0.25rem 0.5rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid currentColor;
}
.badge-outline.success { color: #10b981; background: rgba(16,185,129,0.1); }
.badge-outline.warning { color: #f59e0b; background: rgba(245,158,11,0.1); }

.stats-panel {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
}

.attendance-summary {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.summary-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--color-bg-body);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
}

.icon-box {
  width: 48px;
  height: 48px;
  display: grid;
  place-items: center;
  border-radius: var(--radius-sm);
  font-size: 1.5rem;
}

.bg-green-light { background: rgba(16,185,129,0.15); }
.text-green { color: #10b981; }
.bg-red-light { background: rgba(239,68,68,0.15); }
.text-red { color: #ef4444; }
.bg-blue-light { background: rgba(59,130,246,0.15); }
.text-blue { color: #3b82f6; }

.summary-text .val {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  font-family: var(--font-sans);
  line-height: 1;
  margin-bottom: 0.25rem;
}
.summary-text .lbl {
  font-size: 0.9rem;
  color: var(--color-text-muted);
}

@media (max-width: 1024px) {
  .quick-actions { grid-template-columns: repeat(2, 1fr); }
  .attendance-split { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .quick-actions { grid-template-columns: 1fr; }
}
</style>
