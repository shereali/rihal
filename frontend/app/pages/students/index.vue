<template>
  <div class="students-page slide-up-fade">
    <div class="page-header">
      <div class="header-left">
        <h1>ছাত্র তালিকা</h1>
        <p class="text-muted">{{ totalStudents }} জন ছাত্রের তথ্য</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/enrollments/create" class="btn btn-outline">নতুন ভর্তি</NuxtLink>
        <NuxtLink to="/students/create" class="btn btn-primary">
          <icon name="plus" /> নতুন ছাত্র
        </NuxtLink>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state">
          <div class="spinner" />
          <p>ছাত্র তথ্য লোড হচ্ছে...</p>
        </div>

        <div v-else-if="loadError" class="empty-state" role="alert">
          <Icon name="mdi:alert-circle-outline" size="48" class="text-error mb-2" />
          <p>{{ loadError }}</p>
          <button type="button" class="btn btn-primary mt-3" @click="loadStudents(currentPage)">আবার চেষ্টা করুন</button>
        </div>

        <div v-else-if="(students?.data?.data || []).length === 0" class="empty-state">
          <Icon name="mdi:account-school-outline" size="48" style="color: var(--color-border);" />
          <p class="mt-2 font-semibold">কোনো ছাত্রের রেকর্ড পাওয়া যায়নি</p>
          <p class="text-muted text-sm mb-3">নতুন ছাত্র ভর্তি করাতে নিচের বাটনে ক্লিক করুন</p>
          <NuxtLink to="/students/create" class="btn btn-primary">প্রথম ছাত্র যোগ করুন</NuxtLink>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>ছবি</th>
                <th>নাম</th>
                <th>ভর্তি নং</th>
                <th>শ্রেণি</th>
                <th>ভবিষ্যৎ শ্রেণি</th>
                <th>ভর্তির তারিখ</th>
                <th>অবস্থা</th>
                <th>ক্রিয়া</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in students?.data?.data || []" :key="student.id" @click="openStudent(student)" class="cursor-pointer">
                <td>
                  <div class="student-avatar" v-if="student.user?.profile_image">
                    <img :src="student.user.profile_image" :alt="student.name_bn" />
                  </div>
                  <div v-else class="student-avatar-placeholder">
                    {{ (student.name_bn || student.name_en || '?').charAt(0) }}
                  </div>
                </td>
                <td>
                  <p class="font-weight-medium">{{ student.name_bn }}</p>
                  <p class="text-muted text-sm" v-if="student.name_en">{{ student.name_en }}</p>
                </td>
                <td>
                  <code v-if="student.admission_number">{{ student.admission_number }}</code>
                  <span class="text-muted" v-else>-</span>
                </td>
                <td>
                  <span class="badge" :class="getClassBadge(student.enrollments?.[0]?.class?.name_bn || student.enrollments?.[0]?.class?.name_en || student.class?.name_bn || student.class_name || 'unknown')">
                    {{ student.enrollments?.[0]?.class?.name_bn || student.enrollments?.[0]?.class?.name_en || student.class?.name_bn || student.class_name || '—' }}
                  </span>
                </td>
                <td>
                  <span class="badge badge-outline" :class="getClassBadge(student.next_class_name || 'unknown')">
                    {{ student.next_class_name }}
                  </span>
                </td>
                <td>{{ formatDate(student.admission_date) }}</td>
                <td>
                  <span class="status-badge" :class="student.is_active ? 'status-active' : 'status-inactive'">
                    {{ student.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                  </span>
                </td>
                <td @click.stop>
                  <div class="btn-group btn-group-sm">
                    <NuxtLink :to="`/students/${student.id}`" class="btn btn-outline" title="দেখুন">
                      <icon name="eye" :size="16" />
                    </NuxtLink>
                    <NuxtLink :to="`/students/${student.id}/edit`" class="btn btn-outline" title="সম্পাদনা">
                      <icon name="pencil" :size="16" />
                    </NuxtLink>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="totalPages > 1" class="pagination-wrapper">
          <div class="pagination">
            <button class="page-btn nav-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
              <Icon name="mdi:chevron-left" /> পূর্ববর্তী
            </button>
            <template v-for="(p, idx) in visiblePages" :key="idx">
              <span v-if="p === '...'" class="pagination-ellipsis">...</span>
              <button 
                v-else 
                :class="['page-btn', { active: p === currentPage }]" 
                @click="goToPage(Number(p))"
              >
                {{ p }}
              </button>
            </template>
            <button class="page-btn nav-btn" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
              পরবর্তী <Icon name="mdi:chevron-right" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const loadError = ref('')
const students = ref<any>(null)
const totalPages = ref(1)
const currentPage = ref(1)

const totalStudents = computed(() => {
  return students.value?.data?.total ?? students.value?.data?.meta?.total ?? students.value?.total ?? 0
})

const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages: (number | string)[] = []
  if (current <= 4) {
    pages.push(1, 2, 3, 4, 5, '...', total)
  } else if (current >= total - 3) {
    pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total)
  } else {
    pages.push(1, '...', current - 1, current, current + 1, '...', total)
  }
  return pages
})

async function loadStudents(page = 1) {
  loading.value = true
  loadError.value = ''
  try {
    const res = await api.get(`/students?page=${page}&per_page=20`)
    students.value = res.data
    currentPage.value = page
    totalPages.value = res.data?.data?.last_page || res.data?.last_page || res.data?.meta?.last_page || 1
  } catch (error: any) {
    students.value = null
    loadError.value = error?.code === 'ECONNABORTED'
      ? 'সার্ভার থেকে সাড়া পেতে বেশি সময় লেগেছে। নেটওয়ার্ক ঠিক আছে কিনা দেখে আবার চেষ্টা করুন।'
      : 'ছাত্র তালিকা লোড করা যায়নি। আবার চেষ্টা করুন।'
    console.error('Failed to load students:', error)
  } finally {
    loading.value = false
  }
}

const goToPage = (page: number) => loadStudents(page)
const openStudent = (student: any) => navigateTo(`/students/${student.id}`)
const formatDate = (date: string | null | undefined): string => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
}
const getClassBadge = (className: string): string => {
  const cls = (className || '').toLowerCase()
  if (cls.includes('shishu') || cls.includes('nursery')) return 'badge-success'
  if (cls.includes('5') || cls.includes('six')) return 'badge-primary'
  if (cls.includes('9') || cls.includes('ten')) return 'badge-warning'
  return 'badge-info'
}

loadStudents()
</script>

<style scoped>
.students-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-actions { display: flex; gap: 0.75rem; align-items: center; }
.header-left h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.student-avatar { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; border: 2px solid var(--color-border); }
.student-avatar img { width: 100%; height: 100%; object-fit: cover; }
.student-avatar-placeholder { width: 40px; height: 40px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 600; }
.pagination { display: flex; gap: 0.35rem; justify-content: center; align-items: center; margin-top: 1.25rem; flex-wrap: wrap; }
.page-btn { padding: 0.45rem 0.75rem; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text); border-radius: var(--radius-sm); font-size: 0.9rem; font-family: var(--font-bn); cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.25rem; }
.page-btn:hover:not(.active):not(:disabled) { background: var(--color-bg-muted); border-color: var(--color-primary); color: var(--color-primary); }
.page-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
.page-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pagination-ellipsis { padding: 0 0.3rem; color: var(--color-text-muted); }
.empty-state { text-align: center; padding: 3rem 1rem; color: var(--color-text-muted); }
</style>
