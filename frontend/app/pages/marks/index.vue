<template>
  <div class="page-wrapper slide-up-fade">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">পরীক্ষা ও ফলাফল</span>
        <h1>মার্কস এন্ট্রি ও বিবরণ</h1>
        <p class="page-subtitle">শিক্ষার্থীদের পরীক্ষার মার্কস এন্ট্রি, দেখা এবং পরিচালনা করুন</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/marks/create" class="btn btn-primary">
          <Icon name="mdi:playlist-edit" /> নতুন মার্কস এন্ট্রি করুন
        </NuxtLink>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><Icon name="mdi:file-document-edit-outline" /></div>
        <div class="stat-content">
          <div class="stat-value">১,২৪০</div>
          <div class="stat-label">সর্বমোট এন্ট্রি</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><Icon name="mdi:check-all" /></div>
        <div class="stat-content">
          <div class="stat-value">৯৮%</div>
          <div class="stat-label">এন্ট্রি সম্পন্ন</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><Icon name="mdi:alert-circle-outline" /></div>
        <div class="stat-content">
          <div class="stat-value">১২</div>
          <div class="stat-label">অসম্পূর্ণ খাতা</div>
        </div>
      </div>
    </div>

    <div class="table-card">
      <div class="toolbar">
        <div class="search-box">
          <Icon name="mdi:magnify" class="search-icon" />
          <input type="text" v-model="searchQuery" placeholder="শিক্ষার্থীর নাম বা রোল নম্বর খুঁজুন..." />
          <button v-if="searchQuery" @click="searchQuery = ''" class="clear-search-btn" title="Clear search">
            <Icon name="mdi:close" />
          </button>
        </div>
        <div class="select-wrapper">
          <select v-model="classFilter" class="form-select">
            <option value="all">সকল শ্রেণি</option>
            <option value="মক্তব">মক্তব</option>
            <option value="হিফজুল কুরআন">হিফজুল কুরআন</option>
            <option value="ইবতেদায়ী">ইবতেদায়ী</option>
          </select>
        </div>
        <div class="select-wrapper">
          <select v-model="examFilter" class="form-select">
            <option value="all">সকল পরীক্ষা</option>
            <option value="প্রথম সাময়িক">প্রথম সাময়িক</option>
            <option value="দ্বিতীয় সাময়িক">দ্বিতীয় সাময়িক</option>
            <option value="বার্ষিক">বার্ষিক পরীক্ষা</option>
          </select>
        </div>
        <div class="pagination-info">
          মোট <span class="highlight">{{ filteredMarks.length }}</span> টি রেকর্ড পাওয়া গেছে
        </div>
      </div>

      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>রোল</th>
              <th>শিক্ষার্থীর নাম</th>
              <th>শ্রেণি</th>
              <th>পরীক্ষার নাম</th>
              <th>বিষয়</th>
              <th>প্রাপ্ত নম্বর</th>
              <th>গ্রেড</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="mark in paginatedMarks" :key="mark.id">
              <td><strong>{{ mark.roll }}</strong></td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(mark.name) }">
                    {{ mark.name.charAt(0) }}
                  </div>
                  <div class="user-info">
                    <span class="user-name">{{ mark.name }}</span>
                  </div>
                </div>
              </td>
              <td>{{ mark.className }}</td>
              <td><span class="badge badge-secondary">{{ mark.exam }}</span></td>
              <td>{{ mark.subject }}</td>
              <td>
                <strong>{{ mark.obtainedMarks }}</strong> <small class="text-muted">/ {{ mark.totalMarks }}</small>
              </td>
              <td>
                <span class="status-pill" :class="getGradeClass(mark.grade)">
                  {{ mark.grade }}
                </span>
              </td>
              <td class="text-right">
                <div class="flex flex-end gap-1" style="justify-content: flex-end;">
                  <button class="action-btn edit" title="এডিট করুন">
                    <Icon name="mdi:pencil-outline" />
                  </button>
                  <button class="action-btn delete" title="মুছে ফেলুন">
                    <Icon name="mdi:trash-can-outline" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="paginatedMarks.length === 0">
              <td colspan="8">
                <div class="empty-state">
                  <Icon name="mdi:text-box-search-outline" size="48" style="color: var(--color-border);" />
                  <p class="mt-2">কোনো মার্কস রেকর্ড পাওয়া যায়নি।</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination-wrapper">
        <button class="pagination-btn" :disabled="currentPage === 1" @click="currentPage--">
          <Icon name="mdi:chevron-left" /> পূর্ববর্তী
        </button>
        <div class="pagination-numbers">
          <button 
            v-for="page in totalPages" 
            :key="page" 
            class="pagination-num" 
            :class="{ active: currentPage === page }"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
        </div>
        <button class="pagination-btn" :disabled="currentPage === totalPages" @click="currentPage++">
          পরবর্তী <Icon name="mdi:chevron-right" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated } = useAuth()
const loading = ref(false)

const searchQuery = ref('')
const classFilter = ref('all')
const examFilter = ref('all')
const currentPage = ref(1)
const itemsPerPage = 8

const allMarks = ref<any[]>([])

async function loadMarks() {
  loading.value = true
  try {
    const res = await api.get('/mark-entries?per_page=100')
    const entries = res.data?.data?.data || res.data?.data || []
    allMarks.value = entries.map((entry: any) => {
       const obtained = entry.marks_obtained || 0;
       const max = entry.max_marks || 100;
       const percentage = max > 0 ? (obtained / max) * 100 : 0;
       let grade = 'F';
       if(percentage >= 80) grade = 'A+';
       else if(percentage >= 70) grade = 'A';
       else if(percentage >= 60) grade = 'A-';
       else if(percentage >= 50) grade = 'B';
       else if(percentage >= 40) grade = 'C';
       else if(percentage >= 33) grade = 'D';

       return {
         id: entry.id,
         roll: entry.student?.id || 'N/A', // Use ID as fallback if roll not present on user
         name: entry.student?.name_bn || entry.student?.name_en || 'অজানা ছাত্র',
         className: entry.exam?.class_id || 'অজানা শ্রেণি',
         exam: entry.exam?.name_bn || 'অজানা পরীক্ষা',
         subject: entry.subject?.name_bn || 'অজানা বিষয়',
         obtainedMarks: obtained,
         totalMarks: max,
         grade: grade
       }
    })
  } catch (err) {
    console.error('Failed to load marks:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (isAuthenticated.value) {
    loadMarks()
  }
})

const filteredMarks = computed(() => {
  return allMarks.value.filter(m => {
    const matchesSearch = m.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || m.roll.toString().includes(searchQuery.value)
    const matchesClass = classFilter.value === 'all' || m.className.toString().includes(classFilter.value)
    const matchesExam = examFilter.value === 'all' || m.exam.includes(examFilter.value)
    return matchesSearch && matchesClass && matchesExam
  })
})

const totalPages = computed(() => Math.ceil(filteredMarks.value.length / itemsPerPage))

const paginatedMarks = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredMarks.value.slice(start, end)
})

function getGradeClass(grade: string) {
  if (grade === 'A+' || grade === 'A') return 'badge-success'
  if (grade === 'A-' || grade === 'B') return 'badge-info'
  if (grade === 'C' || grade === 'D') return 'badge-warning'
  if (grade === 'F') return 'badge-danger'
  return 'badge-secondary'
}

function getAvatarColor(name: string) {
  const colors = ['#2b719e', '#167344', '#9b7415', '#7255a5', '#c56bc4']
  const charCode = name.charCodeAt(name.length - 1) || 0
  return colors[charCode % colors.length]
}
</script>

<style scoped>
.pagination-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border-light);
  background: var(--color-bg-card);
}
.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.5rem 0.85rem;
  border-radius: var(--radius-sm);
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  color: var(--color-text);
  font-family: var(--font-bn);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
}
.pagination-btn:hover:not(:disabled) {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-primary-50);
}
.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: var(--color-bg-muted);
}
.pagination-numbers {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}
.pagination-num {
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  border-radius: var(--radius-sm);
  border: 1px solid transparent;
  background: transparent;
  color: var(--color-text-light);
  font-family: var(--font-sans);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
}
.pagination-num:hover {
  background: var(--color-bg-muted);
  color: var(--color-text);
}
.pagination-num.active {
  background: var(--color-primary);
  color: #fff;
  border-color: var(--color-primary);
  box-shadow: 0 4px 10px rgba(20, 80, 50, 0.2);
}
</style>
