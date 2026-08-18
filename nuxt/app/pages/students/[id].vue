<template>
  <div class="student-detail">
    <div class="page-header">
      <NuxtLink to="/students" class="btn btn-outline btn-sm">
        <icon :name="mdiArrowLeft" /> ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <NuxtLink :to="`/students/${student?.id}/edit`" class="btn btn-primary btn-sm">
          <icon :name="mdiPencil" /> সম্পাদনা
        </NuxtLink>
        <button class="btn btn-outline-danger btn-sm" @click="confirmDelete">
          <icon :name="mdiDelete" /> মুছুন
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner" />
      <p>ছাত্রের তথ্য লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!student" class="empty-state">
      <p>ছাত্র পাওয়া যায়নি</p>
      <NuxtLink to="/students" class="btn btn-primary">ছাত্র তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="detail-layout">
      <div class="card">
        <div class="card-header"><h3>ছাত্রের তথ্য</h3></div>
        <div class="card-body">
          <div class="detail-grid">
            <div class="detail-item">
              <label class="detail-label">ফটো</label>
              <div class="student-photo">
                <img v-if="student.user?.profile_image" :src="student.user.profile_image" :alt="student.name_bn" />
                <div v-else class="photo-placeholder">{{ (student.name_bn || '?').charAt(0) }}</div>
              </div>
            </div>
            <div class="detail-item">
              <label class="detail-label">নাম (বাংলা)</label>
              <p class="detail-value">{{ student.name_bn }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">নাম (ইংরেজি)</label>
              <p class="detail-value">{{ student.name_en || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">ভর্তি নং</label>
              <p class="detail-value">{{ student.admission_number || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">রোল নং</label>
              <p class="detail-value">{{ student.roll_number || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">শ্রেণি</label>
              <p class="detail-value">{{ student.class?.name_bn || student.class_name || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">অংশ</label>
              <p class="detail-value">{{ student.section?.name_bn || student.section_name || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">জন্ম তারিখ</label>
              <p class="detail-value">{{ formatDate(student.date_of_birth) }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">লিঙ্গ</label>
              <p class="detail-value">{{ student.gender || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">রক্তের গ্রুপ</label>
              <p class="detail-value">{{ student.blood_group || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">অভিভাবক</label>
              <p class="detail-value">{{ student.guardian?.name_bn || student.guardian_name || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">ভবিষ্যৎ শ্রেণি</label>
              <p class="detail-value">{{ student.next_class_name || '-' }}</p>
            </div>
            <div class="detail-item">
              <label class="detail-label">ভর্তির তারিখ</label>
              <p class="detail-value">{{ formatDate(student.admission_date) }}</p>
            </div>
            <div class="detail-item detail-item-full">
              <label class="detail-label">ঠিকানা</label>
              <p class="detail-value detail-address">{{ student.address_bn || '-' }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>অতিরিক্ত তথ্য</h3></div>
        <div class="card-body">
          <div class="meta-grid">
            <div class="meta-item"><span class="meta-label">আইডি</span><span class="meta-value">{{ student.id }}</span></div>
            <div class="meta-item"><span class="meta-label">ইমেইল</span><span class="meta-value">{{ student.user?.email || '-' }}</span></div>
            <div class="meta-item"><span class="meta-label">ফোন</span><span class="meta-value">{{ student.user?.phone || student.phone || '-' }}</span></div>
            <div class="meta-item">
              <span class="meta-label">অবস্থা</span>
              <span class="badge" :class="student.is_active ? 'badge-success' : 'badge-secondary'">{{ student.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
            </div>
            <div class="meta-item"><span class="meta-label">তৈরি করা হয়েছে</span><span class="meta-value">{{ formatDateTime(student.created_at) }}</span></div>
            <div class="meta-item"><span class="meta-label">আপডেট করা হয়েছে</span><span class="meta-value">{{ formatDateTime(student.updated_at) }}</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const loading = ref(true)
const student = ref<any>(null)

async function loadStudent() {
  loading.value = true
  try {
    const res = await api.get(`/students/${route.params.id}`)
    student.value = res.data.data
  } catch (error) { console.error('Failed to load student:', error) }
  finally { loading.value = false }
}

const confirmDelete = () => {
  if (confirm(`"${student.value?.name_bn}" ছাত্রটিকে মুছে ফেলতে চান?`)) {
    api.delete(`/students/${student.value?.id}`).then(() => navigateTo('/students'))
  }
}

const formatDate = (d: string | null | undefined) => d ? new Date(d).toLocaleDateString('bn-BD', { day:'numeric', month:'short', year:'numeric' }) : '-'
const formatDateTime = (t: string | null | undefined) => t ? new Date(t).toLocaleString('bn-BD', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '-'

onMounted(() => { loadStudent() })
</script>

<style scoped>
.student-detail { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
.header-actions { display: flex; gap: 0.5rem; }
.detail-layout { display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); gap: 1rem; }
.detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem 1rem; }
.detail-item { display: flex; flex-direction: column; gap: 0.25rem; }
.detail-item-full { grid-column: span 2; }
.detail-label { font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; }
.detail-value { font-size: 0.9375rem; color: var(--color-text); margin: 0; }
.detail-address { white-space: pre-wrap; }
.student-photo { width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 2px solid var(--color-border); }
.student-photo img { width: 100%; height: 100%; object-fit: cover; }
.photo-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 600; color: white; background: var(--color-primary); }
.meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem 1rem; }
.meta-item { display: flex; flex-direction: column; gap: 0.25rem; }
.meta-label { font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; }
.meta-value { font-size: 0.9375rem; color: var(--color-text); margin: 0; }
</style>
