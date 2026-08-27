<template>
  <div class="dashboard-page">
    <section class="hero-banner">
      <div class="hero-glow hero-glow-one" />
      <div class="hero-glow hero-glow-two" />
      <div class="hero-content">
        <div class="hero-emblem"><Icon name="mdi:mosque" /></div>
        <div>
          <span class="eyebrow">{{ greeting }}</span>
          <h1>{{ tenantName }}</h1>
          <p>আপনার মাদ্রাসার প্রতিদিনের কার্যক্রম এক নজরে পরিচালনা করুন</p>
        </div>
      </div>
      <div class="hero-actions">
        <button class="hero-date" type="button" @click="refreshDashboard">
          <Icon name="mdi:calendar" />
          <span>{{ todayLabel }}</span>
          <Icon v-if="refreshing" name="mdi:loading" class="spin" />
        </button>
        <NuxtLink to="/attendance/bulk" class="btn btn-accent">
          <Icon name="mdi:clipboard-check-outline" /> হাজিরা নিন
        </NuxtLink>
      </div>
    </section>

    <div class="dashboard-toolbar">
      <div>
        <h2>প্রধান পরিসংখ্যান</h2>
        <p>সিস্টেমের গুরুত্বপূর্ণ সূচক ও আর্থিক সারাংশ</p>
      </div>
      <div class="period-switcher" role="group" aria-label="সময়কাল নির্বাচন">
        <button v-for="period in periods" :key="period.value" type="button" :class="{ active: selectedPeriod === period.value }" @click="selectedPeriod = period.value">
          {{ period.label }}
        </button>
      </div>
    </div>

    <DashboardKPIs :kpis="kpis" />

    <DashboardLicenseModal :isOpen="licenseOpen" :license="license" @close="licenseOpen = false" />

    <section class="section-block mt-3">
      <div class="section-heading">
        <div><h2>ফান্ড ও আর্থিক অবস্থা</h2><p>সকল ফান্ডের বর্তমান ব্যালেন্স</p></div>
        <NuxtLink to="/finance" class="section-link">সম্পূর্ণ হিসাব <Icon name="mdi:arrow-right" /></NuxtLink>
      </div>
      <div class="fund-grid">
        <article v-for="fund in funds" :key="fund.label" class="fund-card">
          <div class="fund-icon" :class="`fund-${fund.tone}`"><Icon :name="fund.icon" /></div>
          <div class="fund-copy"><span>{{ fund.label }}</span><strong>{{ fund.value }}</strong><small>{{ fund.note }}</small></div>
        </article>
        <article v-if="!funds.length" class="empty-card">ফান্ডের তথ্য পাওয়া যায়নি</article>
      </div>
    </section>

    <section class="insight-grid">
      <article class="panel slide-up-fade chart-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-icon green"><Icon name="mdi:chart-bar" /></span>
            <div><h3>মাসিক সংগ্রহ ও খরচ</h3><p>গত ৬ মাসের আর্থিক প্রবাহ</p></div>
          </div>
          <NuxtLink to="/reports" class="icon-link"><Icon name="mdi:open-in-new" /></NuxtLink>
        </div>
        <div class="bar-chart" aria-label="মাসিক সংগ্রহ ও খরচের চার্ট">
          <div v-for="month in monthlyBars" :key="month.label" class="bar-column">
            <div class="bars">
              <span class="bar income" :style="{ height: `${month.income}%` }" />
              <span class="bar expense" :style="{ height: `${month.expense}%` }" />
            </div>
            <small>{{ month.label }}</small>
          </div>
        </div>
        <div class="chart-legend">
          <span><i class="dot income-dot" /> সংগ্রহ</span>
          <span><i class="dot expense-dot" /> খরচ</span>
        </div>
      </article>

      <article class="panel slide-up-fade attendance-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-icon blue"><Icon name="mdi:account-group-outline" /></span>
            <div><h3>আজকের হাজিরা</h3><p>শিক্ষার্থীদের উপস্থিতির সারাংশ</p></div>
          </div>
          <NuxtLink to="/attendance" class="icon-link"><Icon name="mdi:open-in-new" /></NuxtLink>
        </div>
        <div class="attendance-ring-row">
          <div class="attendance-ring" :style="{ '--rate': `${attendance.rate}%` }">
            <div>
              <strong>{{ attendance.rate }}%</strong>
              <small>উপস্থিতি</small>
            </div>
          </div>
          <div class="attendance-numbers">
            <div><b class="present">{{ attendance.present }}</b><span>উপস্থিত</span></div>
            <div><b class="late">{{ attendance.late }}</b><span>দেরিতে</span></div>
            <div><b class="absent">{{ attendance.absent }}</b><span>অনুপস্থিত</span></div>
          </div>
        </div>
        <div class="attendance-progress"><span :style="{ width: `${attendance.rate}%` }" /></div>
      </article>
    </section>

    <section class="insight-grid mid-grid">
      <article class="panel slide-up-fade dues-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-icon gold"><Icon name="mdi:cash-clock" /></span>
            <div><h3>এই মাসের শ্রেণি-ভিত্তিক বকেয়া ফি</h3><p>বর্তমান মাসে বকেয়া ফির সারাংশ</p></div>
          </div>
          <NuxtLink to="/fees" class="icon-link"><Icon name="mdi:open-in-new" /></NuxtLink>
        </div>
        <div v-if="classWiseDues.length" class="dues-list">
          <div v-for="d in classWiseDues" :key="d.class_name" class="dues-row">
            <div class="dues-class"><b>{{ d.class_name }}</b><small>{{ d.due_count }} জন শিক্ষার্থী</small></div>
            <div class="dues-amount"><strong>{{ formatCurrency(d.due_amount) }}</strong><small>বকেয়া</small></div>
          </div>
        </div>
        <div v-else class="empty-inline">এই মাসে কোনো বকেয়া ফি নেই</div>
        <div class="dues-total">
          <span>মোট বকেয়া: {{ formatCurrency(totalDues) }}</span>
          <span class="dues-count-label">{{ totalDueCount }} জন শিক্ষার্থীর ফি বকেয়া</span>
        </div>
      </article>

      <article class="panel slide-up-fade dues-chart-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-icon green"><Icon name="mdi:chart-timeline-variant" /></span>
            <div><h3>মাসিক ফি বকেয়া চার্ট</h3><p>গত ৬ মাসের বকেয়া ফির পরিমাণ</p></div>
          </div>
          <NuxtLink to="/reports" class="icon-link"><Icon name="mdi:open-in-new" /></NuxtLink>
        </div>
        <div class="bar-chart dues-chart" aria-label="মাসিক ফি বকেয়ার চার্ট">
          <div v-for="m in monthlyDuesWithPct" :key="m.month" class="bar-column">
            <div class="bars dues-bars">
              <span class="bar dues-bar" :style="{ height: `${m.dueAmountPct}%` }" />
            </div>
            <small>{{ m.month }}</small>
            <span class="bar-value">{{ formatCurrency(m.due_amount) }}</span>
          </div>
        </div>
        <div class="chart-legend dues-legend"><span><i class="dot dues-dot" /> ফি বকেয়া</span></div>
      </article>
    </section>

    <section class="insight-grid lower-grid">
      <article class="panel slide-up-fade top-funds-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-icon blue"><Icon name="mdi:wallet" /></span>
            <div><h3>শীর্ষ ৩ ফান্ড (ব্যালেন্স অনুযায়ী)</h3><p>সর্বোচ্চ ব্যালেন্সের ফান্ডগুলো</p></div>
          </div>
          <NuxtLink to="/finance" class="icon-link"><Icon name="mdi:open-in-new" /></NuxtLink>
        </div>
        <div v-if="topFunds.length" class="top-funds-list">
          <div v-for="(f, idx) in topFunds" :key="f.name" class="top-fund-row">
            <span class="fund-rank">{{ idx + 1 }}</span>
            <div class="fund-main">
              <div><b>{{ f.name }}</b><small>{{ formatCurrency(f.balance) }}</small></div>
              <div class="fund-track"><i :style="{ width: `${f.percent_of_total}%` }" /></div>
            </div>
            <span class="fund-pct">{{ f.percent_of_total }}%</span>
          </div>
        </div>
        <div v-else class="empty-inline">ফান্ড তথ্য পাওয়া যায়নি</div>
      </article>

      <article class="panel slide-up-fade attendance-detail-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-icon blue"><Icon name="mdi:clipboard-text-clock-outline" /></span>
            <div><h3>আজকের হাজিরার বিস্তারিত</h3><p>শ্রেণি-ভিত্তিক উপস্থিতি ও ছুটির বিবরণ</p></div>
          </div>
          <NuxtLink to="/attendance" class="icon-link"><Icon name="mdi:open-in-new" /></NuxtLink>
        </div>
        <div v-if="classWiseAttendance.length" class="attendance-detail-table">
          <div class="ad-table-head"><span>শ্রেণি</span><span>উপস্থিত</span><span>অনুপস্থিত</span><span>দেরিতে</span><span>ছুটি</span><span>মোট</span></div>
          <div v-for="row in classWiseAttendance" :key="row.class_name" class="ad-table-row">
            <span class="ad-class-name">{{ row.class_name }}</span>
            <span class="ad-present">{{ row.present }}</span>
            <span class="ad-absent">{{ row.absent }}</span>
            <span class="ad-late">{{ row.late }}</span>
            <span class="ad-leave">{{ row.leave }}</span>
            <span class="ad-total">{{ row.total }}</span>
          </div>
        </div>
        <div v-else class="empty-inline">আজকার হাজিরার তথ্য পাওয়া যায়নি</div>
      </article>

      <article class="panel slide-up-fade class-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-icon purple"><Icon name="mdi:google-classroom" /></span>
            <div><h3>শ্রেণিভিত্তিক শিক্ষার্থী</h3><p>{{ classSummary.total }} জন শিক্ষার্থী · {{ classSummary.items.length }} শ্রেণি</p></div>
          </div>
          <NuxtLink to="/academic" class="icon-link"><Icon name="mdi:open-in-new" /></NuxtLink>
        </div>
        <div class="class-list">
          <div v-for="item in classSummary.items" :key="item.label" class="class-row">
            <span class="rank">{{ item.rank }}</span>
            <div class="class-main">
              <div><b>{{ item.label }}</b><small>{{ item.count }} জন</small></div>
              <div class="class-track"><i :style="{ width: `${item.percent}%` }" /></div>
            </div>
          </div>
          <div v-if="!classSummary.items.length" class="empty-inline">শ্রেণির তথ্য পাওয়া যায়নি</div>
        </div>
      </article>

      <article class="panel slide-up-fade gender-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-icon purple"><Icon name="mdi:gender-male-female" /></span>
            <div><h3>লিঙ্গ অনুপাত</h3><p>শিক্ষার্থীদের লিঙ্গ ভিত্তিক বন্টন</p></div>
          </div>
        </div>
        <div v-if="genderRatio.total > 0" class="gender-visual">
          <div class="gender-bar">
            <div class="gender-segment gender-male" :style="{ width: `${genderRatio.male_percent}%` }" />
            <div class="gender-segment gender-female" :style="{ width: `${genderRatio.female_percent}%` }" />
            <div class="gender-segment gender-other" :style="{ width: `${otherPercent}%` }" />
          </div>
          <div class="gender-labels">
            <div class="gender-label"><span class="gender-dot male-dot" /> ছাত্র: {{ genderRatio.male }} ({{ genderRatio.male_percent }}%)</div>
            <div class="gender-label"><span class="gender-dot female-dot" /> ছাত্রী: {{ genderRatio.female }} ({{ genderRatio.female_percent }}%)</div>
            <div class="gender-label" v-if="otherPercent > 0"><span class="gender-dot other-dot" /> অন্যান্য: {{ genderRatio.other }} ({{ otherPercent }}%)</div>
          </div>
        </div>
        <div v-else class="empty-inline">লিঙ্গ তথ্য পাওয়া যায়নি</div>
      </article>
    </section>

    <section class="insight-grid lower-grid">
      <article class="panel slide-up-fade notice-panel">
        <div class="panel-heading">
          <div>
            <span class="panel-icon gold"><Icon name="mdi:bell-ring-outline" /></span>
            <div><h3>সাম্প্রতিক বিজ্ঞপ্তি</h3><p>গুরুত্বপূর্ণ ঘোষণা ও আপডেট</p></div>
          </div>
          <NuxtLink to="/notice" class="section-link">সব দেখুন</NuxtLink>
        </div>
        <div class="notice-list">
          <div v-for="notice in recentNotices" :key="notice.id" class="notice-row">
            <span class="notice-date">{{ shortDate(notice.published_at) }}</span>
            <div>
              <b>{{ notice.title_bn || notice.title_en || 'বিজ্ঞপ্তি' }}</b>
              <small>{{ notice.content_bn ? notice.content_bn.slice(0, 80) : 'বিস্তারিত দেখতে ক্লিক করুন' }}</small>
            </div>
            <Icon name="mdi:chevron-right" />
          </div>
          <div v-if="!recentNotices.length" class="empty-inline">কোনো বিজ্ঞপ্তি নেই</div>
        </div>
      </article>
      <DashboardActivityFeed />
    </section>

    <section class="quick-actions">
      <div class="section-heading">
        <div><h2>দ্রুত কাজ</h2><p>সবচেয়ে বেশি ব্যবহৃত ফিচারগুলো এক ক্লিকে</p></div>
      </div>
      <div class="action-grid">
        <NuxtLink v-for="action in quickActions" :key="action.to" :to="action.to" class="action-card">
          <span :class="`action-icon ${action.tone}`"><Icon :name="action.icon" /></span>
          <span><b>{{ action.label }}</b><small>{{ action.description }}</small></span>
          <Icon name="mdi:arrow-right" class="action-arrow" />
        </NuxtLink>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import DashboardKPIs from '~/components/dashboard/DashboardKPIs.vue'
import DashboardLicenseModal from '~/components/dashboard/DashboardLicenseModal.vue'
import DashboardActivityFeed from '~/components/dashboard/DashboardActivityFeed.vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { currentUser } = useAuth()
const selectedPeriod = ref('all')
const refreshing = ref(false)
const dashboard = ref<any>(null)
const recentNotices = ref<any[]>([])
const finance = ref<any>(null)
const periods = [{ value: 'all', label: 'সর্বকাল' }, { value: 'today', label: 'আজ' }, { value: 'week', label: 'এই সপ্তাহ' }, { value: 'month', label: 'এই মাস' }, { value: 'year', label: 'এই বছর' }]
const monthlyBars = [{ label: 'মার্চ', income: 58, expense: 32 }, { label: 'এপ্রিল', income: 78, expense: 45 }, { label: 'মে', income: 64, expense: 39 }, { label: 'জুন', income: 88, expense: 52 }, { label: 'জুলাই', income: 72, expense: 36 }, { label: 'আগস্ট', income: 94, expense: 48 }]
const quickActions = [{ to: '/students/create', label: 'নতুন ভর্তি', description: 'শিক্ষার্থী যোগ করুন', icon: 'mdi:account-plus-outline', tone: 'green' }, { to: '/attendance/bulk', label: 'বাল্ক হাজিরা', description: 'একসাথে হাজিরা নিন', icon: 'mdi:clipboard-check-outline', tone: 'blue' }, { to: '/fees/collect', label: 'ফি সংগ্রহ', description: 'পেমেন্ট রেকর্ড করুন', icon: 'mdi:cash-register', tone: 'gold' }, { to: '/reports', label: 'রিপোর্ট তৈরি', description: 'রিপোর্ট ও CSV এক্সপোর্ট', icon: 'mdi:file-chart-outline', tone: 'purple' }]

const tenantName = computed(() => currentUser.value?.tenant?.name_bn || 'রিহাল মাদ্রাসা ব্যবস্থাপনা')
const greeting = computed(() => { const h = new Date().getHours(); return h < 12 ? 'সুপ্রভাত' : h < 17 ? 'শুভ অপরাহ্ন' : 'শুভ সন্ধ্যা' })
const todayLabel = computed(() => new Date().toLocaleDateString('bn-BD', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }))
const s = computed(() => dashboard.value || {})
const attendance = computed(() => { const a = s.value.attendance || {}; return { present: Number(a.present || 0), absent: Number(a.absent || 0), late: Number(a.late || 0), rate: Number(a.attendance_rate || a.rate || 0) } })
const license = computed(() => s.value.license || {})
const licenseOpen = ref(false)

const licenseStatusLabel = computed(() => {
  const st = license.value.status
  if (st === 'active') return '✅ সক্রিয়'
  if (st === 'trial') return '⏳ ট্রায়াল'
  if (st === 'expired') return '❌ মেয়াদ উত্তীর্ণ'
  return '⏸ নিষ্ক্রিয়'
})
const licenseRemaining = computed(() => Number(license.value.remaining_days ?? 0))

const kpis = computed(() => [
  { label: 'মোট শিক্ষার্থী', value: Number(s.value.total_students || 0).toLocaleString('bn-BD'), icon: 'mdi:account-school-outline', tone: 'green', trend: 'সক্রিয়', onClick: () => navigateTo('/students') },
  { label: 'মোট শিক্ষক', value: Number(s.value.total_teachers || 0).toLocaleString('bn-BD'), icon: 'mdi:teach', tone: 'blue', trend: 'টিম', onClick: () => navigateTo('/hr') },
  { label: 'মোট পরীক্ষা', value: Number(s.value.total_exams || 0).toLocaleString('bn-BD'), icon: 'mdi:file-document-edit-outline', tone: 'purple', trend: 'একাডেমিক', onClick: () => navigateTo('/exams') },
  { label: 'অপ্রকাশিত ফলাফল', value: Number(s.value.unpublished_results || 0).toLocaleString('bn-BD'), icon: 'mdi:alert-circle-outline', tone: 'amber', trend: 'অ্যাকশন', onClick: () => navigateTo('/results') },
  { label: 'আজকের উপস্থিতি', value: `${attendance.value.rate}%`, icon: 'mdi:clipboard-check-outline', tone: 'teal', trend: 'আজ', onClick: () => navigateTo('/attendance') },
  { label: 'নিট ব্যালেন্স', value: `৳${Number(s.value.finance?.net_balance || finance.value?.net_balance || 0).toLocaleString('bn-BD')}`, icon: 'mdi:bank-outline', tone: 'gold', trend: 'হিসাব', onClick: () => navigateTo('/finance') },
  {
    label: 'লাইসেন্স',
    value: `${licenseRemaining.value} দিন বাকি`,
    icon: 'mdi:certificate-outline',
    tone: 'green',
    trend: licenseStatusLabel.value,
    onClick: () => { licenseOpen.value = true },
  },
])
const classWiseDues = computed(() => Array.isArray(s.value.class_wise_dues) ? s.value.class_wise_dues.map((d: any) => ({ class_name: String(d.class_name ?? 'অজ্ঞাত শ্রেণি'), due_count: Number(d.due_count ?? 0), due_amount: Number(d.due_amount ?? 0) })) : [])
const totalDueCount = computed(() => classWiseDues.value.reduce((n: number, d: any) => n + d.due_count, 0))
const totalDues = computed(() => classWiseDues.value.reduce((n: number, d: any) => n + d.due_amount, 0))
const monthlyDues = computed(() => Array.isArray(s.value.monthly_dues) ? s.value.monthly_dues.map((m: any) => ({ month: String(m.month ?? 'মাস'), due_amount: Number(m.due_amount ?? 0) })).slice(0, 6) : [])
const maxMonthlyDues = computed(() => Math.max(...monthlyDues.value.map((m: any) => m.due_amount), 1))
const monthlyDuesWithPct = computed(() => monthlyDues.value.map((m: any) => ({ ...m, dueAmountPct: maxMonthlyDues.value > 0 ? Math.round((m.due_amount / maxMonthlyDues.value) * 100) : 0 })))
const classWiseAttendance = computed(() => Array.isArray(s.value.class_wise_attendance_detail) ? s.value.class_wise_attendance_detail.map((r: any) => ({ class_name: String(r.class_name ?? 'অজ্ঞাত শ্রেণি'), present: Number(r.present ?? 0), absent: Number(r.absent ?? 0), late: Number(r.late ?? 0), leave: Number(r.leave ?? 0), total: Number(r.total ?? (r.present + r.absent + r.late + r.leave)) })) : [])
const genderRatio = computed(() => s.value.gender_ratio || { male: 0, female: 0, other: 0, male_percent: 0, female_percent: 0, total: 0 })
const otherPercent = computed(() => Math.max(0, 100 - Number(genderRatio.value.male_percent ?? 0) - Number(genderRatio.value.female_percent ?? 0)))
const topFunds = computed(() => Array.isArray(s.value.top_funds) ? s.value.top_funds.map((f: any) => ({ name: String(f.name ?? 'ফান্ড'), balance: Number(f.balance ?? 0), percent_of_total: Number(f.percent_of_total ?? 0) })) : [])
const funds = computed(() => { const raw = finance.value?.funds || finance.value?.data?.funds || []; return Array.isArray(raw) ? raw.slice(0, 8).map((f: any, i: number) => ({ label: f.name_bn || f.name || f.title || ['ভর্তি ফান্ড', 'বেতন ফান্ড', 'সাধারণ ফান্ড', 'যাকাত ফান্ড'][i % 4], value: `৳${Number(f.balance ?? f.amount ?? 0).toLocaleString('bn-BD')}`, note: f.type_bn || 'সাধারণ ফান্ড', icon: i % 3 === 0 ? 'mdi:cash-multiple' : i % 3 === 1 ? 'mdi:wallet' : 'mdi:mosque', tone: ['green', 'blue', 'gold', 'purple'][i % 4] })) : [] })
const classSummary = computed(() => { const raw = dashboard.value?.classes || dashboard.value?.class_distribution || []; const items = Array.isArray(raw) ? raw.map((x: any, i: number) => ({ rank: i + 1, label: x.name_bn || x.class_name || x.label || `শ্রেণি ${i + 1}`, count: Number(x.count || x.total || 0), percent: Number(x.percent || 0) })).slice(0, 6) : []; const max = Math.max(...items.map((x: any) => x.count), 1); items.forEach((x: any) => { x.percent = x.percent || Math.round((x.count / max) * 100) }); return { items, total: items.reduce((n: number, x: any) => n + x.count, 0) } })

async function refreshDashboard() { refreshing.value = true; try { const q = selectedPeriod.value === 'all' ? '' : `?period=${selectedPeriod.value}`; const [stats, notices, summary] = await Promise.all([api.get(`/dashboard/stats${q}`), api.get('/notices?per_page=5'), api.get('/finance/summary')]); dashboard.value = stats.data?.data || {}; recentNotices.value = notices.data?.data?.data || notices.data?.data || []; finance.value = summary.data?.data || {} } catch (e) { console.error('Dashboard load error', e) } finally { refreshing.value = false } }
function formatCurrency(value: number): string { return `৳${Number(value || 0).toLocaleString('bn-BD')}` }
function shortDate(value: string) { return value ? new Date(value).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short' }) : '—' }
onMounted(refreshDashboard)
</script>

<style scoped>
.dashboard-page { max-width: 1600px; margin: 0 auto; padding: 0 0 2rem; }
.hero-banner { position: relative; overflow: hidden; display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; min-height: 178px; padding: 2rem 2.25rem; margin: -0.25rem 0 1.75rem; border-radius: 22px; background: linear-gradient(110deg, #0e3b26 0%, #145032 52%, #1c7450 100%); color: #fff; box-shadow: 0 18px 42px rgba(13,59,36,.2); }
.hero-glow { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(2px); }.hero-glow-one { width: 240px; height: 240px; right: 18%; top: -150px; background: rgba(212,175,55,.22); }.hero-glow-two { width: 320px; height: 320px; right: -120px; bottom: -230px; background: rgba(255,255,255,.08); }
.hero-content, .hero-actions { position: relative; z-index: 1; display: flex; align-items: center; }.hero-content { gap: 1.1rem; }.hero-emblem { display: grid; place-items: center; width: 62px; height: 62px; border: 1px solid rgba(255,255,255,.3); border-radius: 18px; color: #f0cf64; background: rgba(255,255,255,.1); font-size: 1.8rem; }.eyebrow { display: block; color: #e8d48b; font: 600 .82rem var(--font-bn); margin-bottom: .3rem; }.hero-banner h1 { color: #fff; font: 700 1.65rem var(--font-bn); margin: 0; }.hero-banner p { margin: .4rem 0 0; color: rgba(255,255,255,.78); font: .94rem var(--font-bn); }.hero-actions { gap: .75rem; flex-wrap: wrap; justify-content: flex-end; }.hero-date { display: inline-flex; align-items: center; gap: .5rem; padding: .72rem .9rem; color: rgba(255,255,255,.85); background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18); border-radius: 12px; font: .82rem var(--font-bn); cursor: pointer; transition: background 0.2s ease; }.hero-date:hover { background: rgba(255,255,255,.17); }.btn-accent { border: 0; }
.dashboard-toolbar, .section-heading, .panel-heading { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }.dashboard-toolbar { margin-bottom: 1rem; }.dashboard-toolbar h2, .section-heading h2 { font: 700 1.25rem var(--font-bn); color: var(--color-primary); }.dashboard-toolbar p, .section-heading p { margin: .25rem 0 0; color: var(--color-text-light); font: .85rem var(--font-bn); }.period-switcher { display: flex; gap: .25rem; padding: .25rem; background: #fff; border: 1px solid var(--color-border-light); border-radius: 12px; box-shadow: var(--shadow-sm); }.period-switcher button { border: 0; background: transparent; color: var(--color-text-light); padding: .5rem .75rem; border-radius: 9px; cursor: pointer; font: .78rem var(--font-bn); transition: all 0.2s; }.period-switcher button.active { background: var(--color-primary); color: #fff; box-shadow: 0 3px 8px rgba(20,80,50,.2); }
.kpi-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.section-block { margin-bottom: 2rem; }.section-heading { margin-bottom: .9rem; }.section-link, .icon-link { display: inline-flex; align-items: center; gap: .35rem; color: var(--color-primary); font: 600 .82rem var(--font-bn); transition: color 0.2s; } .section-link:hover { color: var(--color-primary-light); }
.fund-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .9rem; }.fund-card { display: flex; gap: .75rem; align-items: center; padding: 1rem; background: #fff; border: 1px solid var(--color-border-light); border-radius: 15px; box-shadow: var(--shadow-sm); transition: transform .2s, box-shadow .2s; }.fund-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }.fund-icon { display: grid; place-items: center; width: 40px; height: 40px; flex: 0 0 40px; border-radius: 12px; font-size: 1.25rem; }.fund-green { color: #167344; background: #e4f3ea; }.fund-blue { color: #236a9e; background: #e3f0fa; }.fund-gold { color: #9b7415; background: #fff4cf; }.fund-purple { color: #7255a5; background: #f0eafa; }.fund-copy { min-width: 0; }.fund-copy span, .fund-copy strong, .fund-copy small { display: block; }.fund-copy span { color: var(--color-text-light); font: .78rem var(--font-bn); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }.fund-copy strong { margin: .12rem 0; color: var(--color-text); font: 700 1rem var(--font-sans); }.fund-copy small { color: var(--color-text-muted); font: .68rem var(--font-bn); }.empty-card { grid-column: 1/-1; padding: 2rem; background: #fff; border: 1px dashed var(--color-border); border-radius: 15px; text-align: center; color: var(--color-text-light); font: .9rem var(--font-bn); }
.insight-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem; }.panel { min-width: 0; background: #fff; border: 1px solid var(--color-border-light); border-radius: 18px; box-shadow: var(--shadow-sm); padding: 1.25rem; }.panel-heading { margin-bottom: 1.25rem; }.panel-heading > div:first-child { display: flex; align-items: center; gap: .75rem; }.panel-heading h3 { font: 700 1rem var(--font-bn); }.panel-heading p { margin: .18rem 0 0; color: var(--color-text-muted); font: .74rem var(--font-bn); }.panel-icon { display: grid; place-items: center; width: 40px; height: 40px; border-radius: 12px; font-size: 1.25rem; }.panel-icon.green { color: #147046; background: #e4f4ea; }.panel-icon.blue { color: #2b719e; background: #e3f2fa; }.panel-icon.purple { color: #7857a9; background: #f0eafb; }.panel-icon.gold { color: #9a7414; background: #fff4d5; }.icon-link { width: 32px; height: 32px; justify-content: center; border-radius: 9px; background: var(--color-bg-muted); transition: background 0.2s, color 0.2s; }.icon-link:hover { background: var(--color-primary-50); color: var(--color-primary); }
.bar-chart { display: flex; align-items: flex-end; justify-content: space-around; height: 190px; padding: 0 1rem .5rem; border-bottom: 1px solid var(--color-border); background: repeating-linear-gradient(to top, transparent 0, transparent 37px, #f0f3f4 38px); }.bar-column { display: flex; flex-direction: column; align-items: center; gap: .45rem; height: 100%; justify-content: flex-end; }.bars { display: flex; gap: 4px; align-items: flex-end; height: 155px; }.bar { display: block; width: 10px; min-height: 3px; border-radius: 5px 5px 2px 2px; transition: height 0.5s ease; }.bar.income { background: linear-gradient(#1f8a5c,#145032); }.bar.expense { background: linear-gradient(#e3c45d,#b38c22); }.bar-column small { color: var(--color-text-muted); font: .68rem var(--font-bn); }.chart-legend { display: flex; gap: 1rem; justify-content: center; margin-top: .8rem; color: var(--color-text-light); font: .75rem var(--font-bn); }.dot { display: inline-block; width: 8px; height: 8px; margin-right: .25rem; border-radius: 50%; }.income-dot { background: #176b45; }.expense-dot { background: #c9a227; }
.attendance-ring-row { display: flex; align-items: center; justify-content: space-around; gap: 1rem; padding: 1rem 0; }.attendance-ring { display: grid; place-items: center; width: 140px; height: 140px; border-radius: 50%; background: conic-gradient(var(--color-primary) var(--rate), #e8eef0 0); position: relative; }.attendance-ring::after { content: ''; position: absolute; inset: 12px; border-radius: 50%; background: #fff; }.attendance-ring > div { position: relative; z-index: 1; text-align: center; }.attendance-ring strong, .attendance-ring small { display: block; }.attendance-ring strong { color: var(--color-primary); font: 800 1.55rem var(--font-sans); }.attendance-ring small { color: var(--color-text-muted); font: .72rem var(--font-bn); }.attendance-numbers { display: grid; gap: .65rem; }.attendance-numbers div { display: grid; grid-template-columns: 38px auto; align-items: center; gap: .45rem; }.attendance-numbers b { font: 800 1rem var(--font-sans); }.attendance-numbers span { color: var(--color-text-light); font: .75rem var(--font-bn); }.present { color: #168152; }.late { color: #c58c19; }.absent { color: #c94e4e; }.attendance-progress { height: 8px; margin-top: 1.25rem; overflow: hidden; background: #edf1f2; border-radius: 99px; }.attendance-progress span { display: block; height: 100%; background: linear-gradient(90deg,#176b45,#45ae7e); border-radius: inherit; transition: width 0.8s ease; }
.lower-grid { margin-bottom: 2rem; }.class-list { display: grid; gap: .9rem; }.class-row { display: flex; align-items: center; gap: .65rem; }.rank { display: grid; place-items: center; width: 24px; height: 24px; border-radius: 8px; color: var(--color-primary); background: var(--color-primary-50); font: 700 .7rem var(--font-sans); }.class-main { flex: 1; }.class-main > div:first-child { display: flex; justify-content: space-between; }.class-main b, .class-main small { font: .78rem var(--font-bn); }.class-main b { color: var(--color-text); }.class-main small { color: var(--color-text-muted); }.class-track { height: 7px; margin-top: .35rem; overflow: hidden; background: #edf1f2; border-radius: 99px; }.class-track i { display: block; height: 100%; background: linear-gradient(90deg,#8265b5,#b89cdf); border-radius: inherit; transition: width 0.8s ease; }.notice-list { display: grid; gap: .2rem; }.notice-row { display: grid; grid-template-columns: 52px 1fr 24px; align-items: center; gap: .7rem; padding: .7rem .35rem; border-bottom: 1px solid var(--color-border-light); cursor: pointer; transition: background 0.2s; border-radius: 8px; }.notice-row:hover { background: var(--color-bg-muted); }.notice-row:last-child { border-bottom: 0; }.notice-date { padding: .3rem .2rem; border-radius: 8px; color: var(--color-primary); background: var(--color-primary-50); text-align: center; font: .68rem var(--font-bn); }.notice-row b, .notice-row small { display: block; }.notice-row b { overflow: hidden; color: var(--color-text); font: 600 .8rem var(--font-bn); text-overflow: ellipsis; white-space: nowrap; }.notice-row small { margin-top: .18rem; overflow: hidden; color: var(--color-text-muted); font: .7rem var(--font-bn); text-overflow: ellipsis; white-space: nowrap; }.notice-row > svg { color: var(--color-text-muted); }.attendance-detail-panel { min-width: 0; }
.attendance-detail-table { display: flex; flex-direction: column; gap: .4rem; }
.ad-table-head, .ad-table-row { display: grid; grid-template-columns: 1fr repeat(5, 52px); align-items: center; gap: .5rem; padding: .6rem .5rem; border-radius: 8px; font: .72rem var(--font-bn); }
.ad-table-head { background: var(--color-primary-50); color: var(--color-primary); font-weight: 700; border-bottom: 1px solid var(--color-border-light); }
.ad-table-row { background: #fff; border-bottom: 1px solid var(--color-border-light); transition: background 0.2s; }
.ad-table-row:hover { background: var(--color-bg-muted); }
.ad-table-row:last-child { border-bottom: 0; }
.ad-class-name { color: var(--color-text); font-weight: 600; }
.ad-present { color: #168152; font-weight: 700; text-align: center; }
.ad-absent { color: #c94e4e; font-weight: 700; text-align: center; }
.ad-late { color: #c58c19; font-weight: 700; text-align: center; }
.ad-leave { color: #5a6b87; font-weight: 700; text-align: center; }
.ad-total { color: var(--color-text-light); font-weight: 700; text-align: center; }
.dues-panel { min-width: 0; }
.dues-list { display: flex; flex-direction: column; gap: .5rem; }
.dues-row { display: flex; justify-content: space-between; align-items: center; padding: .7rem .6rem; background: #fff9e6; border: 1px solid #f0e6c5; border-radius: 11px; transition: transform 0.2s, box-shadow 0.2s; }
.dues-row:hover { transform: translateY(-2px); box-shadow: var(--shadow-sm); }
.dues-class b { color: var(--color-text); font: 600 .82rem var(--font-bn); }
.dues-class small { color: var(--color-text-muted); font: .7rem var(--font-bn); margin-left: .4rem; }
.dues-amount strong { color: #8b6914; font: 700 .9rem var(--font-sans); }
.dues-amount small { color: var(--color-text-muted); font: .68rem var(--font-bn); }
.dues-total { margin-top: .8rem; padding-top: .6rem; border-top: 1px solid var(--color-border-light); display: flex; justify-content: space-between; align-items: center; color: var(--color-text-light); font: .78rem var(--font-bn); }
.dues-count-label { color: var(--color-text-muted); }
.dues-chart-panel { min-width: 0; }
.dues-chart .bar { background: linear-gradient(#d4af37, #9b7415); }
.dues-chart .bar-value { color: var(--color-text); font: 700 .72rem var(--font-sans); margin-top: .2rem; }
.dues-legend { margin-top: 0; }
.gender-panel { min-width: 0; }
.gender-visual { padding: .5rem 0; }
.gender-bar { display: flex; height: 32px; border-radius: 10px; overflow: hidden; background: #edf1f2; margin-bottom: 1rem; border: 1px solid var(--color-border-light); }
.gender-segment { height: 100%; transition: width .8s ease; }
.gender-male { background: linear-gradient(90deg, #2b719e, #1a5276); }
.gender-female { background: linear-gradient(90deg, #c56bc4, #8e3ba9); }
.gender-other { background: linear-gradient(90deg, #8b8d94, #5a5d64); }
.gender-labels { display: flex; flex-direction: column; gap: .4rem; }
.gender-label { display: flex; align-items: center; gap: .5rem; color: var(--color-text-light); font: .75rem var(--font-bn); }
.gender-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex: 0 0 10px; }
.male-dot { background: #2b719e; }
.female-dot { background: #c56bc4; }
.other-dot { background: #8b8d94; }
.top-funds-panel { min-width: 0; }
.top-funds-list { display: flex; flex-direction: column; gap: .7rem; }
.top-fund-row { display: flex; align-items: center; gap: .7rem; padding: .65rem .7rem; background: #fff; border: 1px solid var(--color-border-light); border-radius: 12px; transition: transform .2s, box-shadow .2s; }
.top-fund-row:hover { transform: translateX(4px); box-shadow: var(--shadow-sm); }
.fund-rank { display: grid; place-items: center; width: 28px; height: 28px; border-radius: 9px; color: var(--color-primary); background: var(--color-primary-50); font: 800 .85rem var(--font-sans); }
.fund-main { flex: 1; min-width: 0; }
.fund-main b { color: var(--color-text); font: 600 .85rem var(--font-bn); }
.fund-main small { color: var(--color-text-muted); font: .72rem var(--font-bn); margin-top: .1rem; }
.fund-track { height: 6px; margin-top: .35rem; overflow: hidden; background: #edf1f2; border-radius: 99px; }
.fund-track i { display: block; height: 100%; background: linear-gradient(90deg, #176b45, #45ae7e); border-radius: inherit; transition: width 0.8s ease; }
.fund-pct { color: var(--color-text-light); font: 600 .78rem var(--font-sans); flex: 0 0 48px; text-align: right; }
.empty-inline { padding: 1.5rem; color: var(--color-text-muted); text-align: center; font: .8rem var(--font-bn); }
.quick-actions { margin-top: .25rem; }.action-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .9rem; }.action-card { display: flex; align-items: center; gap: .75rem; padding: 1rem; background: #fff; border: 1px solid var(--color-border-light); border-radius: 15px; box-shadow: var(--shadow-sm); transition: transform .2s, box-shadow .2s, border-color .2s; }.action-card:hover { transform: translateY(-3px); border-color: var(--color-primary-100); box-shadow: var(--shadow-md); }.action-icon { display: grid; place-items: center; width: 38px; height: 38px; flex: 0 0 38px; border-radius: 11px; font-size: 1.25rem; }.action-icon.green { color: #157247; background: #e4f4ea; }.action-icon.blue { color: #286c9b; background: #e3f2fa; }.action-icon.gold { color: #9a7414; background: #fff4d5; }.action-icon.purple { color: #7857a9; background: #f0eafb; }.action-card b, .action-card small { display: block; }.action-card b { color: var(--color-text); font: 600 .82rem var(--font-bn); }.action-card small { margin-top: .18rem; color: var(--color-text-muted); font: .68rem var(--font-bn); }.action-arrow { margin-left: auto; color: var(--color-text-muted); font-size: 1.25rem; transition: transform 0.2s; }
.action-card:hover .action-arrow { transform: translateX(4px); color: var(--color-primary); }

@media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }.fund-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 820px) { .hero-banner, .dashboard-toolbar { align-items: flex-start; flex-direction: column; }.hero-actions { width: 100%; justify-content: flex-start; }.period-switcher { width: 100%; overflow-x: auto; }.period-switcher button { flex: 1; }.insight-grid { grid-template-columns: 1fr; }.action-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ad-table-head, .ad-table-row { grid-template-columns: 1fr repeat(5, 42px); font-size: 0.65rem; } }
@media (max-width: 560px) { .hero-banner { padding: 1.25rem; border-radius: 16px; }.hero-emblem { width: 48px; height: 48px; font-size: 1.3rem; }.hero-banner h1 { font-size: 1.15rem; }.hero-banner p { font-size: .76rem; }.hero-actions .hero-date, .hero-actions .btn { flex: 1; }.kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; }.kpi-card { min-height: 128px; padding: .75rem; }.kpi-value { margin-top: .75rem; font-size: 1.12rem; }.kpi-label { font-size: .7rem; }.fund-grid, .action-grid { grid-template-columns: 1fr; }.attendance-ring-row { flex-direction: column; }.panel { padding: 1rem; } .ad-table-head, .ad-table-row { grid-template-columns: 1fr repeat(5, 36px); gap: 0.2rem; } }

/* License modal */
.license-overlay { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background: rgba(10, 30, 20, .55); backdrop-filter: blur(3px); padding: 1rem; }
.license-modal { position: relative; width: 100%; max-width: 480px; background: #fff; border-radius: 20px; box-shadow: 0 24px 60px rgba(0,0,0,.3); overflow: hidden; animation: license-modal-in .22s ease-out; }
@keyframes license-modal-in { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
.license-close { position: absolute; top: 12px; right: 12px; z-index: 2; width: 32px; height: 32px; display: grid; place-items: center; border: 0; background: var(--color-bg-muted); color: var(--color-text-light); border-radius: 10px; cursor: pointer; font-size: 1rem; transition: background .15s; }
.license-close:hover { background: var(--color-primary-100); color: var(--color-primary); }
.license-header { display: flex; align-items: center; gap: 1rem; padding: 1.25rem 1.5rem 1rem; border-bottom: 1px solid var(--color-border-light); }
.license-badge { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .9rem; border-radius: 999px; font: 600 .82rem var(--font-bn); }
.badge-green { background: #d7f4e0; color: #145032; }
.badge-amber { background: #f5e9c0; color: #8a6512; }
.badge-red { background: #f8ddd8; color: #a31b2a; }
.license-plan { flex: 1; font: 700 1.15rem var(--font-sans); color: var(--color-text); }
.license-body { padding: 1.25rem 1.5rem 1.75rem; display: flex; flex-direction: column; gap: 1rem; }
.license-row { display: grid; grid-template-columns: 120px 1fr; gap: .75rem; padding: .55rem 0; border-bottom: 1px solid var(--color-border-light); }
.license-item-label { color: var(--color-text-muted); font: .78rem var(--font-bn); }
.license-item-value { color: var(--color-text); font: 600 .82rem var(--font-sans); text-align: right; }
.license-remaining { color: #145032; font-weight: 800; }
.license-progress-wrap { padding: .65rem 0 .5rem; border-top: 1px solid var(--color-border-light); border-bottom: 1px solid var(--color-border-light); }
.license-progress-header { display: flex; justify-content: space-between; font: .74rem var(--font-bn); color: var(--color-text-muted); margin-bottom: .5rem; }
.license-progress-bar { height: 14px; overflow: hidden; background: #edf1f2; border-radius: 99px; position: relative; }
.license-progress-bar span { display: block; height: 100%; background: linear-gradient(90deg,#176b45,#45ae7e); border-radius: inherit; transition: width .4s ease; }
.license-progress-footer { text-align: right; font: 600 .74rem var(--font-bn); color: var(--color-text-muted); margin-top: .4rem; }
.license-renewal { display: flex; gap: .9rem; padding: .7rem .75rem; background: #f4f8f5; border-radius: 14px; border: 1px solid var(--color-border-light); }
.renewal-icon { flex: 0 0 40px; display: grid; place-items: center; width: 40px; height: 40px; background: #fff; border-radius: 12px; color: #145032; font-size: 1.2rem; border: 1px solid var(--color-border-light); }
.license-renewal strong { display: block; color: var(--color-text); font: 700 .88rem var(--font-bn); margin-bottom: .2rem; }
.license-renewal p { margin: 0; color: var(--color-text-muted); font: .72rem var(--font-bn); }
.license-whatsapp { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; padding: .75rem; border: 0; border-radius: 13px; background: #25d366; color: #fff; font: 600 .86rem var(--font-bn); text-decoration: none; box-shadow: 0 4px 14px rgba(37,211,102,.3); transition: transform .15s, box-shadow .15s; }
.license-whatsapp:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,211,102,.4); }
.license-whatsapp svg { width: 20px; height: 20px; }
</style>
