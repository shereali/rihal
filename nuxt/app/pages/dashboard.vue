<template>
  <div class="dashboard-page">
    <section class="hero-banner">
      <div class="hero-glow hero-glow-one" />
      <div class="hero-glow hero-glow-two" />
      <div class="hero-content">
        <div class="hero-emblem"><icon name="mosque" /></div>
        <div>
          <span class="eyebrow">{{ greeting }}</span>
          <h1>{{ tenantName }}</h1>
          <p>আপনার মাদ্রাসার প্রতিদিনের কার্যক্রম এক নজরে পরিচালনা করুন</p>
        </div>
      </div>
      <div class="hero-actions">
        <button class="hero-date" type="button" @click="refreshDashboard">
          <icon name="calendar" />
          <span>{{ todayLabel }}</span>
          <icon v-if="refreshing" name="loader" class="spin" />
        </button>
        <NuxtLink to="/attendance/bulk" class="btn btn-accent">
          <icon name="attendance" /> হাজিরা নিন
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

    <section class="kpi-grid">
      <article v-for="stat in kpis" :key="stat.label" class="kpi-card" :class="`tone-${stat.tone}`">
        <div class="kpi-topline">
          <span class="kpi-icon"><icon :name="stat.icon" /></span>
          <span class="kpi-trend" v-if="stat.trend">{{ stat.trend }}</span>
        </div>
        <div class="kpi-value">{{ stat.value }}</div>
        <div class="kpi-label">{{ stat.label }}</div>
        <div class="kpi-shine" />
      </article>
    </section>

    <section class="section-block">
      <div class="section-heading">
        <div><h2>ফান্ড ও আর্থিক অবস্থা</h2><p>সকল ফান্ডের বর্তমান ব্যালেন্স</p></div>
        <NuxtLink to="/finance" class="section-link">সম্পূর্ণ হিসাব <icon name="arrow-right" /></NuxtLink>
      </div>
      <div class="fund-grid">
        <article v-for="fund in funds" :key="fund.label" class="fund-card">
          <div class="fund-icon" :class="`fund-${fund.tone}`"><icon :name="fund.icon" /></div>
          <div class="fund-copy"><span>{{ fund.label }}</span><strong>{{ fund.value }}</strong><small>{{ fund.note }}</small></div>
        </article>
        <article v-if="!funds.length" class="empty-card">ফান্ডের তথ্য পাওয়া যায়নি</article>
      </div>
    </section>

    <section class="insight-grid">
      <article class="panel chart-panel">
        <div class="panel-heading"><div><span class="panel-icon green"><icon name="chart" /></span><div><h3>মাসিক সংগ্রহ ও খরচ</h3><p>গত ৬ মাসের আর্থিক প্রবাহ</p></div></div><NuxtLink to="/reports" class="icon-link"><icon name="external" /></NuxtLink></div>
        <div class="bar-chart" aria-label="মাসিক সংগ্রহ ও খরচের চার্ট">
          <div v-for="month in monthlyBars" :key="month.label" class="bar-column"><div class="bars"><span class="bar income" :style="{ height: `${month.income}%` }" /><span class="bar expense" :style="{ height: `${month.expense}%` }" /></div><small>{{ month.label }}</small></div>
        </div>
        <div class="chart-legend"><span><i class="dot income-dot" /> সংগ্রহ</span><span><i class="dot expense-dot" /> খরচ</span></div>
      </article>

      <article class="panel attendance-panel">
        <div class="panel-heading"><div><span class="panel-icon blue"><icon name="attendance" /></span><div><h3>আজকের হাজিরা</h3><p>শিক্ষার্থীদের উপস্থিতির সারাংশ</p></div></div><NuxtLink to="/attendance" class="icon-link"><icon name="external" /></NuxtLink></div>
        <div class="attendance-ring-row"><div class="attendance-ring" :style="{ '--rate': `${attendance.rate}%` }"><div><strong>{{ attendance.rate }}%</strong><small>উপস্থিতি</small></div></div><div class="attendance-numbers"><div><b class="present">{{ attendance.present }}</b><span>উপস্থিত</span></div><div><b class="late">{{ attendance.late }}</b><span>দেরিতে</span></div><div><b class="absent">{{ attendance.absent }}</b><span>অনুপস্থিত</span></div></div></div>
        <div class="attendance-progress"><span :style="{ width: `${attendance.rate}%` }" /></div>
      </article>
    </section>

    <section class="insight-grid lower-grid">
      <article class="panel class-panel"><div class="panel-heading"><div><span class="panel-icon purple"><icon name="academic" /></span><div><h3>শ্রেণিভিত্তিক শিক্ষার্থী</h3><p>{{ classSummary.total }} জন শিক্ষার্থী · {{ classSummary.items.length }} শ্রেণি</p></div></div><NuxtLink to="/academic" class="icon-link"><icon name="external" /></NuxtLink></div><div class="class-list"><div v-for="item in classSummary.items" :key="item.label" class="class-row"><span class="rank">{{ item.rank }}</span><div class="class-main"><div><b>{{ item.label }}</b><small>{{ item.count }} জন</small></div><div class="class-track"><i :style="{ width: `${item.percent}%` }" /></div></div></div><div v-if="!classSummary.items.length" class="empty-inline">শ্রেণির তথ্য পাওয়া যায়নি</div></div></article>
      <article class="panel notice-panel"><div class="panel-heading"><div><span class="panel-icon gold"><icon name="notice" /></span><div><h3>সাম্প্রতিক বিজ্ঞপ্তি</h3><p>গুরুত্বপূর্ণ ঘোষণা ও আপডেট</p></div></div><NuxtLink to="/notice" class="section-link">সব দেখুন</NuxtLink></div><div class="notice-list"><div v-for="notice in recentNotices" :key="notice.id" class="notice-row"><span class="notice-date">{{ shortDate(notice.published_at) }}</span><div><b>{{ notice.title_bn || notice.title_en || 'বিজ্ঞপ্তি' }}</b><small>{{ notice.content_bn ? notice.content_bn.slice(0, 80) : 'বিস্তারিত দেখতে ক্লিক করুন' }}</small></div><icon name="chevron-right" /></div><div v-if="!recentNotices.length" class="empty-inline">কোনো বিজ্ঞপ্তি নেই</div></div></article>
    </section>

    <section class="quick-actions">
      <div class="section-heading"><div><h2>দ্রুত কাজ</h2><p>সবচেয়ে বেশি ব্যবহৃত ফিচারগুলো এক ক্লিকে</p></div></div>
      <div class="action-grid"><NuxtLink v-for="action in quickActions" :key="action.to" :to="action.to" class="action-card"><span :class="`action-icon ${action.tone}`"><icon :name="action.icon" /></span><span><b>{{ action.label }}</b><small>{{ action.description }}</small></span><icon name="arrow-right" class="action-arrow" /></NuxtLink></div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
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
const quickActions = [{ to: '/students/create', label: 'নতুন ভর্তি', description: 'শিক্ষার্থী যোগ করুন', icon: 'user-plus', tone: 'green' }, { to: '/attendance/bulk', label: 'বাল্ক হাজিরা', description: 'একসাথে হাজিরা নিন', icon: 'attendance', tone: 'blue' }, { to: '/fees/collect', label: 'ফি সংগ্রহ', description: 'পেমেন্ট রেকর্ড করুন', icon: 'money', tone: 'gold' }, { to: '/reports', label: 'রিপোর্ট তৈরি', description: 'রিপোর্ট ও CSV এক্সপোর্ট', icon: 'chart', tone: 'purple' }]

const tenantName = computed(() => currentUser.value?.tenant?.name_bn || 'রিহাল মাদ্রাসা ব্যবস্থাপনা')
const greeting = computed(() => { const h = new Date().getHours(); return h < 12 ? 'সুপ্রভাত' : h < 17 ? 'শুভ অপরাহ্ন' : 'শুভ সন্ধ্যা' })
const todayLabel = computed(() => new Date().toLocaleDateString('bn-BD', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }))
const s = computed(() => dashboard.value || {})
const attendance = computed(() => { const a = s.value.attendance || {}; return { present: Number(a.present || 0), absent: Number(a.absent || 0), late: Number(a.late || 0), rate: Number(a.attendance_rate || a.rate || 0) } })
const kpis = computed(() => [{ label: 'মোট শিক্ষার্থী', value: Number(s.value.total_students || 0).toLocaleString('bn-BD'), icon: 'students', tone: 'green', trend: 'সক্রিয়' }, { label: 'মোট শিক্ষক', value: Number(s.value.total_teachers || 0).toLocaleString('bn-BD'), icon: 'users', tone: 'blue', trend: 'টিম' }, { label: 'মোট পরীক্ষা', value: Number(s.value.total_exams || 0).toLocaleString('bn-BD'), icon: 'exam', tone: 'purple', trend: 'একাডেমিক' }, { label: 'অপ্রকাশিত ফলাফল', value: Number(s.value.unpublished_results || 0).toLocaleString('bn-BD'), icon: 'alert', tone: 'amber', trend: 'অ্যাকশন' }, { label: 'আজকের উপস্থিতি', value: `${attendance.value.rate}%`, icon: 'attendance', tone: 'teal', trend: 'আজ' }, { label: 'নিট ব্যালেন্স', value: `৳${Number(s.value.finance?.net_balance || finance.value?.net_balance || 0).toLocaleString('bn-BD')}`, icon: 'money', tone: 'gold', trend: 'হিসাব' }])
const funds = computed(() => { const raw = finance.value?.funds || finance.value?.data?.funds || []; return Array.isArray(raw) ? raw.slice(0, 8).map((f: any, i: number) => ({ label: f.name_bn || f.name || f.title || ['ভর্তি ফান্ড', 'বেতন ফান্ড', 'সাধারণ ফান্ড', 'যাকাত ফান্ড'][i % 4], value: `৳${Number(f.balance ?? f.amount ?? 0).toLocaleString('bn-BD')}`, note: f.type_bn || 'সাধারণ ফান্ড', icon: i % 3 === 0 ? 'money' : i % 3 === 1 ? 'wallet' : 'mosque', tone: ['green', 'blue', 'gold', 'purple'][i % 4] })) : [] })
const classSummary = computed(() => { const raw = dashboard.value?.classes || dashboard.value?.class_distribution || []; const items = Array.isArray(raw) ? raw.map((x: any, i: number) => ({ rank: i + 1, label: x.name_bn || x.class_name || x.label || `শ্রেণি ${i + 1}`, count: Number(x.count || x.total || 0), percent: Number(x.percent || 0) })).slice(0, 6) : []; const max = Math.max(...items.map((x: any) => x.count), 1); items.forEach((x: any) => { x.percent = x.percent || Math.round((x.count / max) * 100) }); return { items, total: items.reduce((n: number, x: any) => n + x.count, 0) } })

async function refreshDashboard() { refreshing.value = true; try { const q = selectedPeriod.value === 'all' ? '' : `?period=${selectedPeriod.value}`; const [stats, notices, summary] = await Promise.all([api.get(`/dashboard/stats${q}`), api.get('/notices?per_page=5'), api.get('/finance/summary')]); dashboard.value = stats.data?.data || {}; recentNotices.value = notices.data?.data?.data || notices.data?.data || []; finance.value = summary.data?.data || {} } catch (e) { console.error('Dashboard load error', e) } finally { refreshing.value = false } }
function shortDate(value: string) { return value ? new Date(value).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short' }) : '—' }
onMounted(refreshDashboard)
</script>

<style scoped>
.dashboard-page { max-width: 1600px; margin: 0 auto; padding: 0 0 2rem; }
.hero-banner { position: relative; overflow: hidden; display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; min-height: 178px; padding: 2rem 2.25rem; margin: -0.25rem 0 1.75rem; border-radius: 22px; background: linear-gradient(110deg, #0e3b26 0%, #145032 52%, #1c7450 100%); color: #fff; box-shadow: 0 18px 42px rgba(13,59,36,.2); }
.hero-glow { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(2px); }.hero-glow-one { width: 240px; height: 240px; right: 18%; top: -150px; background: rgba(212,175,55,.22); }.hero-glow-two { width: 320px; height: 320px; right: -120px; bottom: -230px; background: rgba(255,255,255,.08); }
.hero-content, .hero-actions { position: relative; z-index: 1; display: flex; align-items: center; }.hero-content { gap: 1.1rem; }.hero-emblem { display: grid; place-items: center; width: 62px; height: 62px; border: 1px solid rgba(255,255,255,.3); border-radius: 18px; color: #f0cf64; background: rgba(255,255,255,.1); font-size: 1.8rem; }.eyebrow { display: block; color: #e8d48b; font: 600 .82rem var(--font-bn); margin-bottom: .3rem; }.hero-banner h1 { color: #fff; font: 700 1.65rem var(--font-bn); margin: 0; }.hero-banner p { margin: .4rem 0 0; color: rgba(255,255,255,.78); font: .94rem var(--font-bn); }.hero-actions { gap: .75rem; flex-wrap: wrap; justify-content: flex-end; }.hero-date { display: inline-flex; align-items: center; gap: .5rem; padding: .72rem .9rem; color: rgba(255,255,255,.85); background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18); border-radius: 12px; font: .82rem var(--font-bn); cursor: pointer; }.hero-date:hover { background: rgba(255,255,255,.17); }.btn-accent { border: 0; }
.dashboard-toolbar, .section-heading, .panel-heading { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }.dashboard-toolbar { margin-bottom: 1rem; }.dashboard-toolbar h2, .section-heading h2 { font: 700 1.25rem var(--font-bn); color: var(--color-primary); }.dashboard-toolbar p, .section-heading p { margin: .25rem 0 0; color: var(--color-text-light); font: .85rem var(--font-bn); }.period-switcher { display: flex; gap: .25rem; padding: .25rem; background: #fff; border: 1px solid var(--color-border-light); border-radius: 12px; box-shadow: var(--shadow-sm); }.period-switcher button { border: 0; background: transparent; color: var(--color-text-light); padding: .5rem .75rem; border-radius: 9px; cursor: pointer; font: .78rem var(--font-bn); }.period-switcher button.active { background: var(--color-primary); color: #fff; box-shadow: 0 3px 8px rgba(20,80,50,.2); }
.kpi-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .9rem; margin-bottom: 2rem; }.kpi-card { position: relative; overflow: hidden; min-height: 152px; padding: 1rem; border: 1px solid rgba(255,255,255,.35); border-radius: 17px; color: #fff; box-shadow: var(--shadow-md); transition: transform .2s, box-shadow .2s; }.kpi-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }.tone-green { background: linear-gradient(145deg,#176b45,#0e452d); }.tone-blue { background: linear-gradient(145deg,#17658f,#103b60); }.tone-purple { background: linear-gradient(145deg,#6e4da5,#41316f); }.tone-amber { background: linear-gradient(145deg,#bd7620,#825012); }.tone-teal { background: linear-gradient(145deg,#087d78,#07524f); }.tone-gold { background: linear-gradient(145deg,#b7902e,#775b16); }.kpi-topline { display: flex; justify-content: space-between; align-items: center; }.kpi-icon { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 11px; background: rgba(255,255,255,.15); color: rgba(255,255,255,.95); }.kpi-trend { padding: .2rem .45rem; border-radius: 999px; color: rgba(255,255,255,.8); background: rgba(255,255,255,.12); font: .7rem var(--font-bn); }.kpi-value { margin-top: 1.15rem; font: 800 1.42rem var(--font-sans); letter-spacing: -.03em; }.kpi-label { color: rgba(255,255,255,.8); font: .78rem var(--font-bn); }.kpi-shine { position: absolute; width: 130px; height: 130px; right: -45px; bottom: -75px; border: 1px solid rgba(255,255,255,.12); border-radius: 50%; }
.section-block { margin-bottom: 2rem; }.section-heading { margin-bottom: .9rem; }.section-link, .icon-link { display: inline-flex; align-items: center; gap: .35rem; color: var(--color-primary); font: 600 .82rem var(--font-bn); }.fund-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .9rem; }.fund-card { display: flex; gap: .75rem; align-items: center; padding: 1rem; background: #fff; border: 1px solid var(--color-border-light); border-radius: 15px; box-shadow: var(--shadow-sm); transition: transform .2s, box-shadow .2s; }.fund-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }.fund-icon { display: grid; place-items: center; width: 40px; height: 40px; flex: 0 0 40px; border-radius: 12px; }.fund-green { color: #167344; background: #e4f3ea; }.fund-blue { color: #236a9e; background: #e3f0fa; }.fund-gold { color: #9b7415; background: #fff4cf; }.fund-purple { color: #7255a5; background: #f0eafa; }.fund-copy { min-width: 0; }.fund-copy span, .fund-copy strong, .fund-copy small { display: block; }.fund-copy span { color: var(--color-text-light); font: .78rem var(--font-bn); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }.fund-copy strong { margin: .12rem 0; color: var(--color-text); font: 700 1rem var(--font-sans); }.fund-copy small { color: var(--color-text-muted); font: .68rem var(--font-bn); }.empty-card { grid-column: 1/-1; padding: 2rem; background: #fff; border: 1px dashed var(--color-border); border-radius: 15px; text-align: center; color: var(--color-text-light); font: .9rem var(--font-bn); }
.insight-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1rem; }.panel { min-width: 0; background: #fff; border: 1px solid var(--color-border-light); border-radius: 18px; box-shadow: var(--shadow-sm); padding: 1.25rem; }.panel-heading { margin-bottom: 1.25rem; }.panel-heading > div:first-child { display: flex; align-items: center; gap: .75rem; }.panel-heading h3 { font: 700 1rem var(--font-bn); }.panel-heading p { margin: .18rem 0 0; color: var(--color-text-muted); font: .74rem var(--font-bn); }.panel-icon { display: grid; place-items: center; width: 40px; height: 40px; border-radius: 12px; font-size: 1rem; }.panel-icon.green { color: #147046; background: #e4f4ea; }.panel-icon.blue { color: #2b719e; background: #e3f2fa; }.panel-icon.purple { color: #7857a9; background: #f0eafb; }.panel-icon.gold { color: #9a7414; background: #fff4d5; }.icon-link { width: 30px; height: 30px; justify-content: center; border-radius: 9px; background: var(--color-bg-muted); }.icon-link:hover { background: var(--color-primary-50); }
.bar-chart { display: flex; align-items: flex-end; justify-content: space-around; height: 190px; padding: 0 1rem .5rem; border-bottom: 1px solid var(--color-border); background: repeating-linear-gradient(to top, transparent 0, transparent 37px, #f0f3f4 38px); }.bar-column { display: flex; flex-direction: column; align-items: center; gap: .45rem; height: 100%; justify-content: flex-end; }.bars { display: flex; gap: 4px; align-items: flex-end; height: 155px; }.bar { display: block; width: 10px; min-height: 3px; border-radius: 5px 5px 2px 2px; }.bar.income { background: linear-gradient(#1f8a5c,#145032); }.bar.expense { background: linear-gradient(#e3c45d,#b38c22); }.bar-column small { color: var(--color-text-muted); font: .68rem var(--font-bn); }.chart-legend { display: flex; gap: 1rem; justify-content: center; margin-top: .8rem; color: var(--color-text-light); font: .75rem var(--font-bn); }.dot { display: inline-block; width: 8px; height: 8px; margin-right: .25rem; border-radius: 50%; }.income-dot { background: #176b45; }.expense-dot { background: #c9a227; }
.attendance-ring-row { display: flex; align-items: center; justify-content: space-around; gap: 1rem; }.attendance-ring { display: grid; place-items: center; width: 156px; height: 156px; border-radius: 50%; background: conic-gradient(var(--color-primary) var(--rate), #e8eef0 0); position: relative; }.attendance-ring::after { content: ''; position: absolute; inset: 13px; border-radius: 50%; background: #fff; }.attendance-ring > div { position: relative; z-index: 1; text-align: center; }.attendance-ring strong, .attendance-ring small { display: block; }.attendance-ring strong { color: var(--color-primary); font: 800 1.55rem var(--font-sans); }.attendance-ring small { color: var(--color-text-muted); font: .72rem var(--font-bn); }.attendance-numbers { display: grid; gap: .65rem; }.attendance-numbers div { display: grid; grid-template-columns: 38px auto; align-items: center; gap: .45rem; }.attendance-numbers b { font: 800 1rem var(--font-sans); }.attendance-numbers span { color: var(--color-text-light); font: .75rem var(--font-bn); }.present { color: #168152; }.late { color: #c58c19; }.absent { color: #c94e4e; }.attendance-progress { height: 8px; margin-top: 1.25rem; overflow: hidden; background: #edf1f2; border-radius: 99px; }.attendance-progress span { display: block; height: 100%; background: linear-gradient(90deg,#176b45,#45ae7e); border-radius: inherit; }
.lower-grid { margin-bottom: 2rem; }.class-list { display: grid; gap: .9rem; }.class-row { display: flex; align-items: center; gap: .65rem; }.rank { display: grid; place-items: center; width: 24px; height: 24px; border-radius: 8px; color: var(--color-primary); background: var(--color-primary-50); font: 700 .7rem var(--font-sans); }.class-main { flex: 1; }.class-main > div:first-child { display: flex; justify-content: space-between; }.class-main b, .class-main small { font: .78rem var(--font-bn); }.class-main b { color: var(--color-text); }.class-main small { color: var(--color-text-muted); }.class-track { height: 7px; margin-top: .35rem; overflow: hidden; background: #edf1f2; border-radius: 99px; }.class-track i { display: block; height: 100%; background: linear-gradient(90deg,#8265b5,#b89cdf); border-radius: inherit; }.notice-list { display: grid; gap: .2rem; }.notice-row { display: grid; grid-template-columns: 52px 1fr 20px; align-items: center; gap: .7rem; padding: .7rem .35rem; border-bottom: 1px solid var(--color-border-light); }.notice-row:last-child { border-bottom: 0; }.notice-date { padding: .3rem .2rem; border-radius: 8px; color: var(--color-primary); background: var(--color-primary-50); text-align: center; font: .68rem var(--font-bn); }.notice-row b, .notice-row small { display: block; }.notice-row b { overflow: hidden; color: var(--color-text); font: 600 .8rem var(--font-bn); text-overflow: ellipsis; white-space: nowrap; }.notice-row small { margin-top: .18rem; overflow: hidden; color: var(--color-text-muted); font: .7rem var(--font-bn); text-overflow: ellipsis; white-space: nowrap; }.notice-row > svg { color: var(--color-text-muted); }.empty-inline { padding: 1.5rem; color: var(--color-text-muted); text-align: center; font: .8rem var(--font-bn); }
.quick-actions { margin-top: .25rem; }.action-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .9rem; }.action-card { display: flex; align-items: center; gap: .75rem; padding: 1rem; background: #fff; border: 1px solid var(--color-border-light); border-radius: 15px; box-shadow: var(--shadow-sm); transition: transform .2s, box-shadow .2s, border-color .2s; }.action-card:hover { transform: translateY(-3px); border-color: var(--color-primary-100); box-shadow: var(--shadow-md); }.action-icon { display: grid; place-items: center; width: 38px; height: 38px; flex: 0 0 38px; border-radius: 11px; }.action-icon.green { color: #157247; background: #e4f4ea; }.action-icon.blue { color: #286c9b; background: #e3f2fa; }.action-icon.gold { color: #9a7414; background: #fff4d5; }.action-icon.purple { color: #7857a9; background: #f0eafb; }.action-card b, .action-card small { display: block; }.action-card b { color: var(--color-text); font: 600 .82rem var(--font-bn); }.action-card small { margin-top: .18rem; color: var(--color-text-muted); font: .68rem var(--font-bn); }.action-arrow { margin-left: auto; color: var(--color-text-muted); }
@media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }.fund-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 820px) { .hero-banner, .dashboard-toolbar { align-items: flex-start; flex-direction: column; }.hero-actions { width: 100%; justify-content: flex-start; }.period-switcher { width: 100%; overflow-x: auto; }.period-switcher button { flex: 1; }.insight-grid { grid-template-columns: 1fr; }.action-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 560px) { .hero-banner { padding: 1.25rem; border-radius: 16px; }.hero-emblem { width: 48px; height: 48px; font-size: 1.3rem; }.hero-banner h1 { font-size: 1.15rem; }.hero-banner p { font-size: .76rem; }.hero-actions .hero-date, .hero-actions .btn { flex: 1; }.kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; }.kpi-card { min-height: 128px; padding: .75rem; }.kpi-value { margin-top: .75rem; font-size: 1.12rem; }.kpi-label { font-size: .7rem; }.fund-grid, .action-grid { grid-template-columns: 1fr; }.attendance-ring-row { flex-direction: column; }.panel { padding: 1rem; } }
</style>
