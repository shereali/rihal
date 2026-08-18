<template>
  <div class="students-page">
    <div class="page-header">
      <div class="header-left">
        <h1>ছাত্র তালিকা</h1>
        <p class="text-muted">{{ students?.data?.meta?.total || 0 }} জন ছাত্র</p>
      </div>
      <NuxtLink to="/students/create" class="btn btn-primary">
        <icon :name="mdiPlus" /> নতুন ছাত্র
      </NuxtLink>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state">
          <div class="spinner" />
          <p>ছাত্র তথ্য লোড হচ্ছে...</p>
        </div>

        <div v-else-if="(students?.data?.data || []).length === 0" class="empty-state">
          <p>কোনো ছাত্র নেই</p>
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
                  <span class="badge" :class="getClassBadge(student.class?.name_bn || student.class_name || 'unknown')">
                    {{ student.class?.name_bn || student.class_name }}
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
                <td>
                  <div class="btn-group btn-group-sm">
                    <NuxtLink :to="`/students/${student.id}`" class="btn btn-outline">
                      <icon :name="mdiEye" />
                    </NuxtLink>
                    <NuxtLink :to="`/students/${student.id}/edit`" class="btn btn-outline">
                      <icon :name="mdiPencil" />
                    </NuxtLink>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="students?.data?.meta && students.data.meta.total > students.data.per_page" class="pagination-wrapper">
          <div class="pagination">
            <button v-for="page in totalPages" :key="page" :class="['page-btn', { active: page === students?.data?.current_page }]" @click="goToPage(page)">
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const students = ref<any>(null)
const totalPages = ref(1)

async function loadStudents(page = 1) {
  loading.value = true
  try {
    const res = await api.get(`/students?page=${page}&per_page=20`)
    students.value = res.data
    totalPages.value = res.data.meta?.last_page || 1
  } catch (error) {
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
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.header-left h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
.table-responsive { overflow-x: auto; }
.student-avatar { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; border: 2px solid var(--color-border); }
.student-avatar img { width: 100%; height: 100%; object-fit: cover; }
.student-avatar-placeholder { width: 40px; height: 40px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 600; }
.pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 1rem; }
.page-btn { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text); border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s; }
.page-btn:hover:not(.active) { background: var(--color-bg-hover); border-color: var(--color-border-dark); }
.page-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
</style>
