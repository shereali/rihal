<template>
  <div class="audit-page">
    <div class="page-header-row">
      <div><NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> অ্যাকাউন্টিং</NuxtLink><span class="eyebrow">স্বচ্ছ ও যাচাইযোগ্য লেনদেন</span><h1>আর্থিক অডিট ট্রেইল</h1><p>ঋণ ও স্পন্সরশিপের প্রতিটি সংবেদনশীল পরিবর্তনের পূর্ববর্তী ও পরবর্তী অবস্থা।</p></div>
      <button class="btn" @click="load"><icon name="refresh" /> হালনাগাদ</button>
    </div>
    <div class="filters"><input v-model="filters.from" type="date" @change="load"/><input v-model="filters.to" type="date" @change="load"/><select v-model="filters.action" @change="load"><option value="">সব কার্যক্রম</option><option value="loan.payment_recorded">ঋণ কিস্তি</option><option value="orphan.payment_recorded">স্পন্সরশিপ প্রদান</option></select></div>
    <div v-if="loading" class="state">লোড হচ্ছে...</div>
    <div v-else-if="!logs.length" class="state">এখনও কোনো আর্থিক অডিট রেকর্ড নেই।</div>
    <div v-else class="timeline">
      <article v-for="log in logs" :key="log.id" class="audit-card">
        <div class="audit-icon"><icon :name="log.action.startsWith('loan.') ? 'cash' : 'child'" /></div>
        <div class="audit-content"><div class="audit-title"><strong>{{ actionLabel(log.action) }}</strong><span>#{{ log.entity_id }}</span></div><p>{{ log.description }}</p><div class="meta"><span>{{ log.user?.name_bn || log.user?.email || 'সিস্টেম' }}</span><time>{{ new Date(log.created_at).toLocaleString('bn-BD') }}</time></div><details><summary>পরিবর্তনের বিস্তারিত</summary><pre>{{ JSON.stringify(log.changes, null, 2) }}</pre></details></div>
      </article>
    </div>
    <div v-if="pagination.last_page > 1" class="pagination"><button :disabled="pagination.current_page <= 1" @click="go(pagination.current_page - 1)">আগে</button><span>{{ pagination.current_page }} / {{ pagination.last_page }}</span><button :disabled="pagination.current_page >= pagination.last_page" @click="go(pagination.current_page + 1)">পরে</button></div>
  </div>
</template>
<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
const api=useApiClient(); const loading=ref(true); const logs=ref<any[]>([]); const filters=reactive({from:'',to:'',action:''}); const pagination=reactive({current_page:1,last_page:1})
async function load(page=1){loading.value=true;try{const q=new URLSearchParams({page:String(page)});if(filters.from)q.set('from',filters.from);if(filters.to)q.set('to',filters.to);if(filters.action)q.set('action',filters.action);const r=await api.get(`/financial-audit?${q}`);const d=r.data?.data||{};logs.value=d.data||[];pagination.current_page=d.current_page||1;pagination.last_page=d.last_page||1}finally{loading.value=false}}
function go(page:number){load(page)} function actionLabel(action:string){return action==='loan.payment_recorded'?'ঋণের কিস্তি গ্রহণ':'অর্ফান স্পন্সরশিপ প্রদান'} onMounted(()=>load())
</script>
<style scoped>.audit-page{padding:1.5rem}.page-header-row{display:flex;justify-content:space-between;gap:1rem;align-items:end;margin-bottom:1.25rem}.back-link{display:block;color:#145032;text-decoration:none;margin-bottom:.8rem}.eyebrow{font-size:.75rem;font-weight:800;color:#9a7621;text-transform:uppercase;letter-spacing:.08em}h1{margin:.25rem 0}.page-header-row p{margin:0;color:#6f7d73}.btn,.pagination button{border:1px solid #cbd7cf;background:#fff;border-radius:9px;padding:.6rem .85rem}.filters{display:flex;gap:.7rem;flex-wrap:wrap;background:#fff;border:1px solid #e0e7e2;padding:1rem;border-radius:13px;margin-bottom:1rem}.filters input,.filters select{padding:.55rem;border:1px solid #d5ddd7;border-radius:8px}.timeline{display:grid;gap:.8rem}.audit-card{display:flex;gap:1rem;padding:1rem;background:#fff;border:1px solid #e1e7e3;border-radius:14px}.audit-icon{width:40px;height:40px;border-radius:12px;background:#e4f1e8;color:#145032;display:grid;place-items:center}.audit-content{flex:1;min-width:0}.audit-title,.meta{display:flex;justify-content:space-between;gap:1rem}.audit-title span,.meta{color:#748078;font-size:.8rem}.audit-content p{margin:.4rem 0}.audit-content details{margin-top:.7rem}.audit-content pre{white-space:pre-wrap;background:#f6f8f6;padding:.75rem;border-radius:8px;overflow:auto}.state{text-align:center;padding:3rem;background:#fff;border-radius:14px;color:#748078}.pagination{display:flex;justify-content:center;gap:.8rem;align-items:center;margin-top:1rem}@media(max-width:640px){.audit-page{padding:1rem}.page-header-row{align-items:flex-start;flex-direction:column}.audit-title,.meta{flex-direction:column;gap:.2rem}}</style>
