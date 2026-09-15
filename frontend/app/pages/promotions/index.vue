<template>
  <div class="page-wrapper slide-up-fade">
    <!-- Success / Error Toast Banner -->
    <div v-if="toastMessage" class="toast-banner no-print" :class="toastType">
      <div class="toast-content">
        <Icon :name="toastType === 'success' ? 'checkCircle' : 'alertCircle'" />
        <span>{{ toastMessage }}</span>
      </div>
      <button class="toast-close" @click="toastMessage = ''">×</button>
    </div>

    <!-- Career & Academic Lifecycle Navigation Bar -->
    <div class="lifecycle-nav-bar no-print">
      <div class="lifecycle-tabs">
        <NuxtLink to="/promotions" class="lifecycle-tab active">
          <Icon name="users" size="16" />
          <span>শ্রেণি প্রমোশন</span>
        </NuxtLink>
        <NuxtLink to="/promotions/alumni" class="lifecycle-tab">
          <Icon name="building" size="16" />
          <span>ফারেগীন ডিরেক্টরি</span>
        </NuxtLink>
        <NuxtLink to="/promotions/employed" class="lifecycle-tab">
          <Icon name="checkCircle" size="16" />
          <span>কর্মরত ফারেগীন</span>
        </NuxtLink>
        <NuxtLink to="/promotions/jobless" class="lifecycle-tab">
          <Icon name="alertCircle" size="16" />
          <span>বেকার ফারেগীন</span>
        </NuxtLink>
        <NuxtLink to="/promotions/higher-study" class="lifecycle-tab">
          <Icon name="book" size="16" />
          <span>উচ্চ শিক্ষা</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Page Header Row -->
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <span class="eyebrow">একাডেমিক ব্যবস্থাপনা</span>
        <h1>শিক্ষার্থী শ্রেণি প্রমোশন ও বহিখাতা</h1>
        <p class="page-subtitle">নতুন শিক্ষাবর্ষে উত্তীর্ণ শিক্ষার্থীদের শ্রেণি পরিবর্তন, বাল্ক প্রমোশন ও রেকর্ড পরিচালনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="printPromotions" title="প্রমোশন তালিকা প্রিন্ট করুন">
          <Icon name="printer" /> প্রিন্ট তালিকা
        </button>
        <button class="btn btn-success" @click="openBulkModal" title="একসাথে পুরো শ্রেণির শিক্ষার্থীদের প্রমোশন করুন">
          <Icon name="users" /> বাল্ক প্রমোশন
        </button>
        <button class="btn btn-primary" @click="openCreateModal" title="একক শিক্ষার্থী প্রমোশন">
          <Icon name="plus" /> নতুন প্রমোশন
        </button>
      </div>
    </div>

    <!-- Metric Summary Statistics Row -->
    <div class="stats-grid no-print">
      <div class="stat-card">
        <div class="stat-icon-wrap green"><Icon name="users" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ (promotions.total || 0).toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">মোট নিবন্ধিত প্রমোশন</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><Icon name="checkCircle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ approvedCount.toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">অনুমোদিত প্রমোশন</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><Icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ pendingCount.toLocaleString('bn-BD') }} জন</span>
          <span class="stat-label">অপেক্ষমান অনুমোদন</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><Icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ classWiseData.length.toLocaleString('bn-BD') }} টি</span>
          <span class="stat-label">সক্রিয় শ্রেণি তালিকা</span>
        </div>
      </div>
    </div>

    <!-- Main View Switcher Tabs -->
    <div class="view-tabs-container no-print">
      <div class="view-tabs">
        <button
          type="button"
          class="view-tab-btn"
          :class="{ active: currentTab === 'records' }"
          @click="currentTab = 'records'"
        >
          <Icon name="assignment" size="16" />
          <span>সকল প্রমোশন তালিকা</span>
          <span class="tab-badge">{{ promotions.total || 0 }}</span>
        </button>
        <button
          type="button"
          class="view-tab-btn"
          :class="{ active: currentTab === 'classWise' }"
          @click="switchToClassWise"
        >
          <Icon name="chart" size="16" />
          <span>শ্রেণিভিত্তিক প্রমোশন সারাংশ</span>
          <span class="tab-badge" v-if="classWiseData.length">{{ classWiseData.length }}</span>
        </button>
      </div>
    </div>

    <!-- TAB 1: Promotions Records Table -->
    <div v-show="currentTab === 'records'" class="table-card">
      <!-- Search & Filters Toolbar -->
      <div class="toolbar no-print">
        <div class="search-box">
          <Icon name="search" class="search-icon" />
          <input
            v-model="search"
            type="text"
            placeholder="শিক্ষার্থীর নাম, রোল বা দাখেলা নং দিয়ে খুঁজুন..."
            @input="debounceSearch"
          />
          <button v-if="search" @click="clearSearch" class="clear-search-btn" title="মুছে ফেলুন">
            <Icon name="close" />
          </button>
        </div>

        <div class="filter-selects-group">
          <div class="select-wrapper">
            <select v-model="statusFilter" class="form-select" @change="fetchPromotions(1)">
              <option value="">সব অবস্থা</option>
              <option value="approved">অনুমোদিত (Approved)</option>
              <option value="pending">মুলতুবি (Pending)</option>
              <option value="rejected">প্রত্যাখ্যান (Rejected)</option>
            </select>
          </div>

          <div class="select-wrapper">
            <select v-model="classFilter" class="form-select" @change="fetchPromotions(1)">
              <option value="">সকল পূর্ববর্তী শ্রেণি</option>
              <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div class="select-wrapper">
            <select v-model="yearFilter" class="form-select" @change="fetchPromotions(1)">
              <option value="">সকল শিক্ষাবর্ষ</option>
              <option value="২০২৬-২০২৭">২০২৬-২০২৭ শিক্ষাবর্ষ</option>
              <option value="২০২৫-২০২৬">২০২৫-২০২৬ শিক্ষাবর্ষ</option>
              <option value="২০২৪-২০২৫">২০২৪-২০২৫ শিক্ষাবর্ষ</option>
            </select>
          </div>

          <button v-if="hasActiveFilters" class="btn btn-sm btn-outline btn-reset" @click="resetFilters" title="ফিল্টার রিসেট">
            <Icon name="refresh" size="14" /> রিসেট
          </button>
        </div>

        <div class="pagination-info text-muted">
          মোট <strong class="highlight-count">{{ (promotions.total || 0).toLocaleString('bn-BD') }}</strong> টি রেকর্ড
        </div>
      </div>

      <!-- Printable Document Header (Appears only during print) -->
      <div class="print-header-block print-only">
        <div class="print-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</div>
        <h2>শিক্ষার্থী শ্রেণি প্রমোশন ও উত্তরণ বহিখাতা</h2>
        <p class="print-sub">
          শিক্ষাবর্ষ: {{ yearFilter || 'সকল শিক্ষাবর্ষ' }} | 
          মুদ্রণের তারিখ: {{ new Date().toLocaleDateString('bn-BD', { day: 'numeric', month: 'long', year: 'numeric' }) }}
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>প্রমোশন তালিকা লোড হচ্ছে...</p>
      </div>

      <!-- Data Table -->
      <div v-else-if="promotions.data?.length" class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th style="width: 45px;">ক্র.</th>
              <th>শিক্ষার্থী</th>
              <th style="width: 110px;">দাখেলা / রোল</th>
              <th style="min-width: 200px;">প্রমোশন ক্রম (শ্রেণি উত্তরণ)</th>
              <th style="width: 120px;">শিক্ষাবর্ষ</th>
              <th style="width: 110px;">প্রমোশনের তারিখ</th>
              <th style="width: 110px;">অবস্থা</th>
              <th class="text-right no-print" style="width: 140px;">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, index) in promotions.data" :key="p.id">
              <td class="text-center font-bold">
                {{ ((promotions.from || 1) + index).toLocaleString('bn-BD') }}
              </td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials no-print" :style="{ backgroundColor: getAvatarColor(getStudentName(p)) }">
                    {{ getStudentName(p).charAt(0) }}
                  </div>
                  <div class="user-meta-block">
                    <strong class="student-name">{{ getStudentName(p) }}</strong>
                    <div class="sub-text" v-if="p.student?.father_name_bn || p.student?.father_name">
                      পিতা: {{ p.student?.father_name_bn || p.student?.father_name }}
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <div class="id-wrapper">
                  <span class="roll-badge" v-if="p.student?.roll_number || p.student?.roll_no">
                    রোল: {{ p.student?.roll_number || p.student?.roll_no }}
                  </span>
                  <code class="mono id-badge">{{ p.student?.admission_number || p.student_id }}</code>
                </div>
              </td>
              <td>
                <div class="promotion-flow-badge">
                  <span class="class-node from">{{ getClassName(p.fromClass) }}</span>
                  <span class="flow-arrow">➔</span>
                  <span class="class-node to">{{ getClassName(p.toClass) }}</span>
                </div>
              </td>
              <td>
                <span class="session-tag">{{ p.academic_year || '—' }}</span>
              </td>
              <td>{{ formatDate(p.promotion_date) }}</td>
              <td>
                <span class="status-pill" :class="getStatusBadgeClass(p.status)">
                  <span class="status-dot"></span> {{ formatStatus(p.status) }}
                </span>
              </td>
              <td class="text-right no-print">
                <div class="action-btn-group">
                  <!-- 1-Click Approve Button for Pending Records -->
                  <button
                    v-if="p.status === 'pending'"
                    class="action-btn approve"
                    @click="quickApprove(p)"
                    title="অনুমোদন করুন"
                    :disabled="approvingId === p.id"
                  >
                    <Icon name="check" size="14" />
                  </button>
                  <button class="action-btn edit" @click="editPromotion(p)" title="সম্পাদনা">
                    <Icon name="pencil" size="14" />
                  </button>
                  <button class="action-btn delete" @click="deletePromotion(p)" title="মুছে ফেলুন">
                    <Icon name="delete" size="14" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-state-icon">
          <Icon name="users" size="48" />
        </div>
        <h3>কোনো প্রমোশন রেকর্ড পাওয়া যায়নি</h3>
        <p class="text-muted">
          {{ search || statusFilter || classFilter || yearFilter ? 'অনুসন্ধানের শর্ত অনুযায়ী কোনো শিক্ষার্থী মেলেনি।' : 'এখনও কোনো শিক্ষার্থী প্রমোশন রেকর্ড যোগ করা হয়নি।' }}
        </p>
        <div class="empty-actions">
          <button v-if="hasActiveFilters" class="btn btn-outline" @click="resetFilters">
            ফিল্টার রিসেট করুন
          </button>
          <button class="btn btn-success" @click="openBulkModal">
            <Icon name="users" /> বাল্ক প্রমোশন শুরু করুন
          </button>
          <button class="btn btn-primary" @click="openCreateModal">
            <Icon name="plus" /> নতুন প্রমোশন যোগ করুন
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="promotions.last_page > 1" class="pagination-wrapper no-print">
        <div class="pagination-info">
          {{ promotions.from }}–{{ promotions.to }} / মোট {{ promotions.total }} রেকর্ড
        </div>
        <div class="pagination-numbers">
          <button
            class="pagination-btn"
            :disabled="!promotions.prev_page_url || promotions.current_page === 1"
            @click="goPage(promotions.current_page - 1)"
          >
            <Icon name="chevronLeft" /> পূর্ববর্তী
          </button>
          <span class="page-info">পৃষ্ঠা {{ promotions.current_page }} / {{ promotions.last_page }}</span>
          <button
            class="pagination-btn"
            :disabled="!promotions.next_page_url || promotions.current_page === promotions.last_page"
            @click="goPage(promotions.current_page + 1)"
          >
            পরবর্তী <Icon name="chevronRight" />
          </button>
        </div>
      </div>
    </div>

    <!-- TAB 2: Class-Wise Overview Grid -->
    <div v-show="currentTab === 'classWise'" class="classwise-wrapper">
      <div class="classwise-header no-print">
        <div>
          <h3>শ্রেণিভিত্তিক প্রমোশন অগ্রগতি (Class-wise Overview)</h3>
          <p class="text-muted text-sm">প্রতিটি শ্রেণিতে উত্তীর্ণ শিক্ষার্থীদের সংখ্যা ও সংক্ষিপ্ত বিবরণ</p>
        </div>
        <button class="btn btn-outline btn-sm" @click="loadClassWiseData">
          <Icon name="refresh" size="14" /> তথ্য রিফ্রেশ করুন
        </button>
      </div>

      <div v-if="loadingClassWise" class="loading-state">
        <div class="spinner"></div>
        <p>শ্রেণিভিত্তিক তথ্য সংকলন করা হচ্ছে...</p>
      </div>

      <div v-else-if="classWiseData.length" class="classwise-grid">
        <div v-for="cls in classWiseData" :key="cls.id" class="classwise-card">
          <div class="classwise-card-top">
            <div class="class-icon-circle">
              <Icon name="book" size="20" />
            </div>
            <div class="class-title-block">
              <h4>{{ cls.name_bn || cls.name_en || cls.name }}</h4>
              <span class="class-type-badge" v-if="cls.class_type">{{ cls.class_type }}</span>
            </div>
          </div>
          <div class="classwise-count-row">
            <span class="count-number">{{ (cls.promotions_count || 0).toLocaleString('bn-BD') }}</span>
            <span class="count-label">জন উত্তীর্ণ শিক্ষার্থী</span>
          </div>
          <div class="classwise-card-footer">
            <button class="btn btn-outline btn-sm w-full" @click="filterByThisClass(cls.id)">
              <Icon name="eye" size="14" /> প্রমোশন তালিকা দেখুন
            </button>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        <h3>কোনো শ্রেণির তথ্য মেলেনি</h3>
        <p class="text-muted">শ্রেণি সেটিংস থেকে শ্রেণির তালিকা যাচাই করুন।</p>
      </div>
    </div>

    <!-- Modal 1: Smart Class Bulk Promotion Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showBulk" class="modal-overlay" @click.self="closeBulkModal">
          <div class="modal-card modal-lg">
            <div class="modal-header">
              <div class="modal-title-with-icon">
                <div class="icon-bubble green"><Icon name="users" /></div>
                <div>
                  <h3>স্মার্ট শ্রেণি বাল্ক প্রমোশন (Bulk Class Promotion)</h3>
                  <p class="modal-subtitle">একটি নির্দিষ্ট শ্রেণির সকল উত্তীর্ণ শিক্ষার্থীকে একসাথে পরবর্তী শ্রেণিতে উন্নীত করুন</p>
                </div>
              </div>
              <button class="modal-close" type="button" @click="closeBulkModal" title="বন্ধ করুন">
                <Icon name="close" />
              </button>
            </div>

            <div class="modal-body">
              <form @submit.prevent="submitBulkPromote">
                <!-- Source & Target Class Selection -->
                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">
                      যে শ্রেণি থেকে প্রমোশন হবে (পূর্ববর্তী শ্রেণি) <span class="required">*</span>
                    </label>
                    <select
                      v-model="bulkForm.from_class_id"
                      class="form-select"
                      @change="onBulkFromClassChange"
                      required
                    >
                      <option value="">শ্রেণি নির্বাচন করুন</option>
                      <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <span class="form-hint">শ্রেণি সিলেক্ট করলে শিক্ষার্থীদের তালিকা নিচে লোড হবে</span>
                  </div>

                  <div class="form-group">
                    <label class="form-label">
                      যে শ্রেণিতে উন্নীত হবে (পরবর্তী শ্রেণি) <span class="required">*</span>
                    </label>
                    <select v-model="bulkForm.to_class_id" class="form-select" required>
                      <option value="">উত্তীর্ণ শ্রেণি নির্বাচন করুন</option>
                      <option
                        v-for="c in classOptions"
                        :key="c.id"
                        :value="c.id"
                        :disabled="c.id === Number(bulkForm.from_class_id)"
                      >
                        {{ c.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Visual Class Promotion Flow Banner -->
                <div v-if="bulkForm.from_class_id && bulkForm.to_class_id" class="promotion-preview-flow">
                  <div class="flow-step from">
                    <span class="flow-label">পূর্ববর্তী শ্রেণি</span>
                    <strong>{{ getClassNameById(bulkForm.from_class_id) }}</strong>
                  </div>
                  <div class="flow-arrow">
                    <Icon name="arrowRight" />
                  </div>
                  <div class="flow-step to">
                    <span class="flow-label">উত্তীর্ণ পরবর্তী শ্রেণি</span>
                    <strong>{{ getClassNameById(bulkForm.to_class_id) }}</strong>
                  </div>
                </div>

                <!-- Session & Date Row -->
                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">নতুন শিক্ষাবর্ষ (Academic Session) <span class="required">*</span></label>
                    <input
                      v-model="bulkForm.academic_year"
                      type="text"
                      class="form-control"
                      placeholder="যেমন: ২০২৬-২০২৭"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label class="form-label">প্রমোশনের তারিখ <span class="required">*</span></label>
                    <input v-model="bulkForm.promotion_date" type="date" class="form-control" required />
                  </div>
                </div>

                <!-- Student Checklist Area -->
                <div class="bulk-students-panel">
                  <div class="panel-header-bar">
                    <div class="panel-title">
                      <strong>শিক্ষার্থীদের তালিকা ও নির্বাচন</strong>
                      <span class="badge-count" v-if="bulkClassStudents.length">
                        মোট {{ bulkClassStudents.length }} জন
                      </span>
                    </div>
                    <div class="panel-actions" v-if="bulkClassStudents.length">
                      <button type="button" class="btn btn-xs btn-outline" @click="selectAllBulkStudents">
                        সবাইকে নির্বাচন ({{ bulkClassStudents.length }})
                      </button>
                      <button type="button" class="btn btn-xs btn-outline" @click="deselectAllBulkStudents">
                        নির্বাচন বাতিল
                      </button>
                    </div>
                  </div>

                  <!-- Loading students for class -->
                  <div v-if="loadingBulkStudents" class="bulk-loading">
                    <div class="spinner-sm"></div>
                    <span>শিক্ষার্থীদের তালিকা লোড হচ্ছে...</span>
                  </div>

                  <!-- No class chosen yet -->
                  <div v-else-if="!bulkForm.from_class_id" class="bulk-placeholder">
                    <Icon name="users" size="32" />
                    <p>উপরের ড্রপডাউন থেকে পূর্ববর্তী শ্রেণি সিলেক্ট করুন</p>
                  </div>

                  <!-- Empty students for chosen class -->
                  <div v-else-if="bulkClassStudents.length === 0" class="bulk-placeholder">
                    <p>এই শ্রেণিতে কোনো সক্রিয় শিক্ষার্থী পাওয়া যায়নি।</p>
                  </div>

                  <!-- Interactive Students Checklist Table -->
                  <div v-else class="bulk-table-wrap">
                    <table class="bulk-checklist-table">
                      <thead>
                        <tr>
                          <th style="width: 40px; text-align: center;">
                            <input
                              type="checkbox"
                              :checked="isAllSelected"
                              @change="toggleSelectAll"
                              title="সবাইকে সিলেক্ট করুন"
                            />
                          </th>
                          <th style="width: 70px;">রোল</th>
                          <th>শিক্ষার্থীর পূর্ণ নাম</th>
                          <th>দাখেলা / ভর্তি নম্বর</th>
                          <th>পিতার নাম</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="st in bulkClassStudents"
                          :key="st.id"
                          :class="{ selected: selectedStudentIds.includes(st.id) }"
                          @click="toggleStudentSelection(st.id)"
                        >
                          <td style="text-align: center;" @click.stop>
                            <input
                              type="checkbox"
                              :value="st.id"
                              v-model="selectedStudentIds"
                            />
                          </td>
                          <td><span class="roll-pill">{{ st.roll_no || '—' }}</span></td>
                          <td><strong>{{ st.name }}</strong></td>
                          <td><code class="mono-sub">{{ st.admission_no || st.id }}</code></td>
                          <td class="text-muted">{{ st.father_name || '—' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Selection Counter Bar -->
                  <div class="bulk-selection-summary" v-if="bulkClassStudents.length">
                    <span>
                      নির্বাচিত শিক্ষার্থী: 
                      <strong class="count-highlight">{{ selectedStudentIds.length.toLocaleString('bn-BD') }}</strong> 
                      জন (মোট {{ bulkClassStudents.length.toLocaleString('bn-BD') }} জনের মধ্যে)
                    </span>
                    <span class="form-hint">যাদের প্রমোশন হবে না তাদের আনচেক (Uncheck) করুন</span>
                  </div>
                </div>
              </form>
            </div>

            <div class="modal-footer">
              <button class="btn btn-outline" @click="closeBulkModal" :disabled="bulkSaving">বাতিল</button>
              <button
                class="btn btn-success"
                @click="submitBulkPromote"
                :disabled="bulkSaving || selectedStudentIds.length === 0 || !bulkForm.to_class_id"
              >
                <Icon name="loader" v-if="bulkSaving" />
                <Icon name="check" v-else />
                {{ bulkSaving ? 'প্রমোশন সম্পন্ন হচ্ছে...' : `বাল্ক প্রমোশন সম্পাদন করুন (${selectedStudentIds.length} জন)` }}
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </ClientOnly>

    <!-- Modal 2: Single Create / Edit Promotion Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showCreate" class="modal-overlay" @click.self="closeCreateModal">
          <div class="modal-card">
            <div class="modal-header">
              <div class="modal-title-with-icon">
                <div class="icon-bubble green"><Icon name="user" /></div>
                <div>
                  <h3>{{ editingPromotion ? 'প্রমোশন রেকর্ড সম্পাদনা' : 'একক শিক্ষার্থী প্রমোশন' }}</h3>
                  <p class="modal-subtitle">{{ editingPromotion ? 'বিদ্যমান প্রমোশন রেকর্ডের তথ্য পরিবর্তন করুন' : 'নির্দিষ্ট একজন শিক্ষার্থীকে পরবর্তী শ্রেণিতে উন্নীত করুন' }}</p>
                </div>
              </div>
              <button class="modal-close" type="button" @click="closeCreateModal" title="বন্ধ করুন">
                <Icon name="close" />
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="saveSinglePromotion">
                <div class="form-group">
                  <label class="form-label">শিক্ষার্থী নির্বাচন করুন <span class="required">*</span></label>
                  <select
                    v-model="form.student_id"
                    class="form-select"
                    @change="onSingleStudentSelect"
                    required
                  >
                    <option value="">তালিকায় থাকা শিক্ষার্থী নির্বাচন করুন</option>
                    <option v-for="s in studentOptions" :key="s.id" :value="s.id">
                      {{ s.name }} (রোল: {{ s.roll_no }}) — {{ s.class?.name || s.class?.name_bn || '—' }}
                    </option>
                  </select>
                </div>

                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">পূর্ববর্তী শ্রেণি <span class="required">*</span></label>
                    <select v-model="form.from_class_id" class="form-select" required>
                      <option value="">শ্রেণি নির্বাচন করুন</option>
                      <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">উত্তীর্ণ পরবর্তী শ্রেণি <span class="required">*</span></label>
                    <select v-model="form.to_class_id" class="form-select" required>
                      <option value="">শ্রেণি নির্বাচন করুন</option>
                      <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                  </div>
                </div>

                <!-- Visual Class Promotion Flow Banner -->
                <div v-if="form.from_class_id && form.to_class_id" class="promotion-preview-flow">
                  <div class="flow-step from">
                    <span class="flow-label">পূর্ববর্তী শ্রেণি</span>
                    <strong>{{ getClassNameById(form.from_class_id) }}</strong>
                  </div>
                  <div class="flow-arrow">
                    <Icon name="arrowRight" />
                  </div>
                  <div class="flow-step to">
                    <span class="flow-label">উত্তীর্ণ পরবর্তী শ্রেণি</span>
                    <strong>{{ getClassNameById(form.to_class_id) }}</strong>
                  </div>
                </div>

                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">নতুন শিক্ষাবর্ষ <span class="required">*</span></label>
                    <input
                      v-model="form.academic_year"
                      type="text"
                      class="form-control"
                      placeholder="যেমন: ২০২৬-২০২৭"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label class="form-label">প্রমোশনের তারিখ <span class="required">*</span></label>
                    <input v-model="form.promotion_date" type="date" class="form-control" required />
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">প্রমোশনের অবস্থা</label>
                  <select v-model="form.status" class="form-select">
                    <option value="approved">অনুমোদিত (Approved)</option>
                    <option value="pending">মুলতুবি (Pending)</option>
                    <option value="rejected">প্রত্যাখ্যান (Rejected)</option>
                  </select>
                </div>

                <div class="form-group">
                  <label class="form-label">মন্তব্য (Comments)</label>
                  <textarea
                    v-model="form.comments"
                    class="form-control"
                    rows="2"
                    placeholder="প্রমোশন সংক্রান্ত কোনো বিশেষ নির্দেশনা বা মন্তব্য থাকলে লিখুন..."
                  ></textarea>
                </div>
              </form>
            </div>

            <div class="modal-footer">
              <button class="btn btn-outline" @click="closeCreateModal">বাতিল</button>
              <button class="btn btn-primary" @click="saveSinglePromotion" :disabled="saving">
                <Icon name="loader" v-if="saving" />
                <Icon name="save" v-else />
                {{ editingPromotion ? 'আপডেট সম্পন্ন করুন' : 'প্রমোশন সংরক্ষণ করুন' }}
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </ClientOnly>

    <!-- Modal 3: Delete Confirmation Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showDelete" class="modal-overlay" @click.self="showDelete = false">
          <div class="modal-card modal-sm">
            <div class="modal-header">
              <div class="modal-title-with-icon">
                <div class="icon-bubble red"><Icon name="delete" /></div>
                <div>
                  <h3>প্রমোশন রেকর্ড মুছে ফেলুন</h3>
                  <p class="modal-subtitle">রেকর্ডটি ডাটাবেজ থেকে স্থায়ীভাবে অপসারণ করা হবে</p>
                </div>
              </div>
              <button class="modal-close" type="button" @click="showDelete = false" title="বন্ধ করুন">
                <Icon name="close" />
              </button>
            </div>
            <div class="modal-body">
              <p>
                আপনি কি নিশ্চিত যে শিক্ষার্থী 
                "<strong>{{ getStudentName(deleteTarget) }}</strong>" এর প্রমোশন রেকর্ড মুছে ফেলতে চান?
              </p>
              <div class="delete-detail-box" v-if="deleteTarget">
                <div><strong>শিক্ষার্থী:</strong> {{ getStudentName(deleteTarget) }}</div>
                <div><strong>পূর্ববর্তী শ্রেণি:</strong> {{ deleteTarget.fromClass?.name_bn || deleteTarget.fromClass?.name_en || deleteTarget.fromClass?.name || '—' }}</div>
                <div><strong>উত্তীর্ণ শ্রেণি:</strong> {{ deleteTarget.toClass?.name_bn || deleteTarget.toClass?.name_en || deleteTarget.toClass?.name || '—' }}</div>
                <div><strong>শিক্ষাবর্ষ:</strong> {{ deleteTarget.academic_year || '—' }}</div>
              </div>
              <p class="text-muted text-xs mt-2" style="font-size: 0.8rem; color: #64748b; margin-top: 0.6rem;">
                এই রেকর্ড মুছে ফেললে শিক্ষার্থী পুনরায় পূর্বের শ্রেণিতে গণ্য হবে।
              </p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline" @click="showDelete = false">বাতিল</button>
              <button class="btn btn-danger" @click="confirmDelete" :disabled="deleting">
                <Icon name="loader" v-if="deleting" />
                <Icon name="delete" v-else />
                মুছে ফেলুন
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import Icon from '~/components/Icon.vue'

const api = useApiClient()

// Current View Tab
const currentTab = ref<'records' | 'classWise'>('records')

// Toast Notifications
const toastMessage = ref('')
const toastType = ref<'success' | 'error'>('success')
let toastTimer: any = null

function showToast(msg: string, type: 'success' | 'error' = 'success') {
  toastMessage.value = msg
  toastType.value = type
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toastMessage.value = ''
  }, 4500)
}

// Data State
const loading = ref(true)
const promotions = ref<any>({
  data: [],
  from: 0,
  to: 0,
  total: 0,
  current_page: 1,
  last_page: 1,
  prev_page_url: null,
  next_page_url: null
})

// Filters
const search = ref('')
const statusFilter = ref('')
const classFilter = ref('')
const yearFilter = ref('')
let searchTimeout: any = null
const per_page = 15

const hasActiveFilters = computed(() => {
  return !!(search.value || statusFilter.value || classFilter.value || yearFilter.value)
})

// Classes & Students Options
const classOptions = ref<any[]>([])
const studentOptions = ref<any[]>([])

// Class-wise Overview State
const classWiseData = ref<any[]>([])
const loadingClassWise = ref(false)

// Approving specific promotion
const approvingId = ref<number | null>(null)

// Single Create / Edit State
const showCreate = ref(false)
const editingPromotion = ref<any>(null)
const saving = ref(false)
const form = reactive({
  student_id: '',
  from_class_id: '',
  to_class_id: '',
  academic_year: '২০২৬-২০২৭',
  promotion_date: new Date().toISOString().split('T')[0],
  status: 'approved',
  comments: '',
})

// Delete State
const showDelete = ref(false)
const deleteTarget = ref<any>(null)
const deleting = ref(false)

// Smart Bulk Promote State
const showBulk = ref(false)
const bulkSaving = ref(false)
const bulkForm = reactive({
  from_class_id: '',
  to_class_id: '',
  academic_year: '২০২৬-২০২৭',
  promotion_date: new Date().toISOString().split('T')[0]
})
const bulkClassStudents = ref<any[]>([])
const selectedStudentIds = ref<number[]>([])
const loadingBulkStudents = ref(false)

// Computed Stats
const approvedCount = computed(() => {
  return promotions.value.data?.filter((p: any) => p.status === 'approved').length || 0
})

const pendingCount = computed(() => {
  return promotions.value.data?.filter((p: any) => p.status === 'pending').length || 0
})

const isAllSelected = computed(() => {
  return bulkClassStudents.value.length > 0 &&
    selectedStudentIds.value.length === bulkClassStudents.value.length
})

// Helper Getters
function getStudentName(p: any): string {
  if (!p) return 'শিক্ষার্থী'
  return p.student?.name_bn || p.student?.name_en || p.student?.name?.trim() || 'শিক্ষার্থী'
}

function getClassName(c: any): string {
  if (!c) return '—'
  return c.name_bn || c.name_en || c.name || '—'
}

function formatDate(date: string | null | undefined): string {
  if (!date) return '—'
  try {
    return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return date
  }
}

function formatStatus(s: string): string {
  const map: Record<string, string> = {
    pending: 'মুলতুবি',
    approved: 'অনুমোদিত',
    rejected: 'প্রত্যাখ্যান'
  }
  return map[s] || s
}

function getStatusBadgeClass(status: string): string {
  if (status === 'approved') return 'badge-approved'
  if (status === 'pending') return 'badge-pending'
  if (status === 'rejected') return 'badge-rejected'
  return 'badge-pending'
}

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string): string {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

// API Calls
async function fetchPromotions(page = 1) {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: String(page),
      per_page: String(per_page),
      ...(search.value ? { search: search.value } : {}),
      ...(statusFilter.value ? { status: statusFilter.value } : {}),
      ...(classFilter.value ? { class_id: classFilter.value } : {}),
      ...(yearFilter.value ? { year: yearFilter.value } : {})
    })
    const res = await api.get(`/promotions?${params}`).catch(() => null)
    promotions.value = res?.data?.data || res?.data || {
      data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null
    }
  } catch (err) {
    console.error('Fetch promotions failed:', err)
  } finally {
    loading.value = false
  }
}

async function fetchClassesAndStudents() {
  try {
    const [classRes, studentRes] = await Promise.all([
      api.get('/academic/classes?per_page=100').catch(() => null) || api.get('/settings/classes?per_page=100').catch(() => null),
      api.get('/students?per_page=100').catch(() => null)
    ])

    const classList = classRes?.data?.data?.data || classRes?.data?.data || []
    classOptions.value = classList.map((c: any) => ({
      id: c.id,
      name: c.name_bn || c.name_en || c.name,
      class_type: c.class_type
    }))

    const rawStudents = studentRes?.data?.data?.data || studentRes?.data?.data || []
    studentOptions.value = rawStudents.map((s: any) => ({
      id: s.id,
      name: s.name_bn || s.name_en,
      roll_no: s.roll_number || s.roll_no || s.id,
      admission_no: s.admission_number || `ADM-${s.id}`,
      class: s.academic_class || s.enrollments?.[0]?.class,
      class_id: s.academic_class?.id || s.enrollments?.[0]?.class?.id || s.class_id,
      father_name: s.father_name_bn || s.father_name
    }))
  } catch (err) {
    console.error('Fetch classes and students failed:', err)
  }
}

async function loadClassWiseData() {
  loadingClassWise.value = true
  try {
    const res = await api.get('/promotions/class-wise').catch(() => null)
    classWiseData.value = res?.data?.data || []
  } catch (err) {
    console.error('Failed to load class-wise promotions:', err)
  } finally {
    loadingClassWise.value = false
  }
}

function switchToClassWise() {
  currentTab.value = 'classWise'
  if (classWiseData.value.length === 0) {
    loadClassWiseData()
  }
}

function filterByThisClass(classId: number) {
  classFilter.value = String(classId)
  currentTab.value = 'records'
  fetchPromotions(1)
}

function debounceSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchPromotions(1), 300)
}

function clearSearch() {
  search.value = ''
  fetchPromotions(1)
}

function resetFilters() {
  search.value = ''
  statusFilter.value = ''
  classFilter.value = ''
  yearFilter.value = ''
  fetchPromotions(1)
}

function goPage(page: number) {
  if (page < 1 || page > promotions.value.last_page) return
  fetchPromotions(page)
}

// 1-Click Approve Action
async function quickApprove(p: any) {
  approvingId.value = p.id
  try {
    const res = await api.put(`/promotions/${p.id}/approve`).catch(() => null)
    if (res?.status === 200 || res?.data) {
      p.status = 'approved'
      showToast(`${getStudentName(p)} এর প্রমোশন অনুমোদিত হয়েছে!`, 'success')
      fetchPromotions(promotions.value.current_page)
      loadClassWiseData()
    } else {
      showToast('অনুমোদনে সমস্যা দেখা দিয়েছে।', 'error')
    }
  } catch (err) {
    console.error(err)
    showToast('অনুমোদনে সমস্যা হয়েছে।', 'error')
  } finally {
    approvingId.value = null
  }
}

// Single Promotion Modal Handling
function openCreateModal() {
  editingPromotion.value = null
  form.student_id = ''
  form.from_class_id = ''
  form.to_class_id = ''
  form.academic_year = '২০২৬-২০২৭'
  form.promotion_date = new Date().toISOString().split('T')[0]
  form.status = 'approved'
  form.comments = ''
  showCreate.value = true
}

function editPromotion(p: any) {
  editingPromotion.value = p
  form.student_id = String(p.student_id || '')
  form.from_class_id = String(p.from_class_id || '')
  form.to_class_id = String(p.to_class_id || '')
  form.academic_year = p.academic_year || '২০২৬-২০২৭'
  form.promotion_date = p.promotion_date ? p.promotion_date.split('T')[0] : ''
  form.status = p.status || 'approved'
  form.comments = p.comments || ''
  showCreate.value = true
}

function closeCreateModal() {
  showCreate.value = false
  editingPromotion.value = null
}

function getClassNameById(id: any) {
  if (!id) return '—'
  const found = classOptions.value.find(c => c.id === Number(id))
  return found ? (found.name_bn || found.name_en || found.name) : '—'
}

function onSingleStudentSelect() {
  if (!form.student_id) return
  const st = studentOptions.value.find(s => s.id === Number(form.student_id))
  if (st && st.class_id) {
    form.from_class_id = Number(st.class_id)
    const currentIndex = classOptions.value.findIndex(c => c.id === Number(st.class_id))
    if (currentIndex !== -1 && currentIndex + 1 < classOptions.value.length) {
      form.to_class_id = classOptions.value[currentIndex + 1].id
    }
  }
}

async function saveSinglePromotion() {
  if (!form.student_id || !form.from_class_id || !form.to_class_id) {
    showToast('দয়া করে শিক্ষার্থী ও শ্রেণি নির্বাচন করুন।', 'error')
    return
  }
  saving.value = true
  try {
    const url = editingPromotion.value ? `/promotions/${editingPromotion.value.id}` : '/promotions'
    const payload = {
      ...form,
      student_id: Number(form.student_id),
      from_class_id: Number(form.from_class_id),
      to_class_id: Number(form.to_class_id)
    }
    const res = editingPromotion.value
      ? await api.put(url, payload).catch(() => null)
      : await api.post(url, payload).catch(() => null)

    if (res?.status === 200 || res?.status === 201 || res?.data) {
      showToast(editingPromotion.value ? 'প্রমোশন সফলভাবে আপডেট করা হয়েছে!' : 'প্রমোশন সফলভাবে সংরক্ষণ করা হয়েছে!', 'success')
      closeCreateModal()
      fetchPromotions(promotions.value.current_page)
      loadClassWiseData()
    } else {
      showToast('সংরক্ষণে সমস্যা দেখা দিয়েছে। তথ্য যাচাই করুন।', 'error')
    }
  } catch (err) {
    console.error('Save failed:', err)
    showToast('সংরক্ষণে সমস্যা দেখা দিয়েছে।', 'error')
  } finally {
    saving.value = false
  }
}

// Delete Confirmation
function deletePromotion(p: any) {
  deleteTarget.value = p
  showDelete.value = true
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`/promotions/${deleteTarget.value.id}`).catch(() => null)
    showToast('প্রমোশন রেকর্ড সফলভাবে মুছে ফেলা হয়েছে!', 'success')
    showDelete.value = false
    deleteTarget.value = null
    fetchPromotions(promotions.value.current_page)
    loadClassWiseData()
  } catch (err) {
    console.error('Delete failed:', err)
    showToast('রেকর্ড মুছতে সমস্যা হয়েছে।', 'error')
  } finally {
    deleting.value = false
  }
}

// Smart Class Bulk Promotion Handling
function openBulkModal() {
  bulkForm.from_class_id = ''
  bulkForm.to_class_id = ''
  bulkForm.academic_year = '২০২৬-২০২৭'
  bulkForm.promotion_date = new Date().toISOString().split('T')[0]
  bulkClassStudents.value = []
  selectedStudentIds.value = []
  showBulk.value = true
}

function closeBulkModal() {
  showBulk.value = false
  bulkClassStudents.value = []
  selectedStudentIds.value = []
}

async function onBulkFromClassChange() {
  if (!bulkForm.from_class_id) {
    bulkClassStudents.value = []
    selectedStudentIds.value = []
    return
  }

  loadingBulkStudents.value = true
  try {
    // Attempt fetching students enrolled in this class
    const res = await api.get(`/students?class_id=${bulkForm.from_class_id}&per_page=100`).catch(() => null)
    const rawList = res?.data?.data?.data || res?.data?.data || []
    
    if (rawList.length > 0) {
      bulkClassStudents.value = rawList.map((s: any) => ({
        id: s.id,
        name: s.name_bn || s.name_en || s.name,
        roll_no: s.roll_number || s.roll_no || s.id,
        admission_no: s.admission_number || `ADM-${s.id}`,
        father_name: s.father_name_bn || s.father_name
      }))
    } else {
      // Fallback to in-memory matching
      bulkClassStudents.value = studentOptions.value.filter(
        s => String(s.class_id) === String(bulkForm.from_class_id)
      )
    }

    // Default: select all students of this class
    selectedStudentIds.value = bulkClassStudents.value.map(s => s.id)
  } catch (err) {
    console.error('Failed to load class students for bulk promote:', err)
    bulkClassStudents.value = studentOptions.value.filter(
      s => String(s.class_id) === String(bulkForm.from_class_id)
    )
    selectedStudentIds.value = bulkClassStudents.value.map(s => s.id)
  } finally {
    loadingBulkStudents.value = false
  }
}

function toggleStudentSelection(id: number) {
  const index = selectedStudentIds.value.indexOf(id)
  if (index > -1) {
    selectedStudentIds.value.splice(index, 1)
  } else {
    selectedStudentIds.value.push(id)
  }
}

function selectAllBulkStudents() {
  selectedStudentIds.value = bulkClassStudents.value.map(s => s.id)
}

function deselectAllBulkStudents() {
  selectedStudentIds.value = []
}

function toggleSelectAll(event: any) {
  if (event.target.checked) {
    selectAllBulkStudents()
  } else {
    deselectAllBulkStudents()
  }
}

async function submitBulkPromote() {
  if (selectedStudentIds.value.length === 0) {
    showToast('দয়া করে অন্তত একজন শিক্ষার্থী নির্বাচন করুন।', 'error')
    return
  }
  if (!bulkForm.from_class_id || !bulkForm.to_class_id) {
    showToast('পূর্ববর্তী ও পরবর্তী শ্রেণি নির্বাচন করুন।', 'error')
    return
  }

  bulkSaving.value = true
  try {
    const payload = {
      from_class_id: Number(bulkForm.from_class_id),
      to_class_id: Number(bulkForm.to_class_id),
      academic_year: bulkForm.academic_year,
      promotion_date: bulkForm.promotion_date,
      student_ids: selectedStudentIds.value
    }

    const res = await api.post('/promotions/bulk-promote', payload).catch(() => null)
    
    if (res?.status === 200 || res?.data) {
      showToast(`${selectedStudentIds.value.length} জন শিক্ষার্থীর বাল্ক প্রমোশন সফলভাবে সম্পন্ন হয়েছে!`, 'success')
      closeBulkModal()
      fetchPromotions(1)
      loadClassWiseData()
    } else {
      showToast('বাল্ক প্রমোশনে সমস্যা হয়েছে। তথ্য যাচাই করুন।', 'error')
    }
  } catch (err) {
    console.error('Bulk promote error:', err)
    showToast('বাল্ক প্রমোশন ব্যর্থ হয়েছে।', 'error')
  } finally {
    bulkSaving.value = false
  }
}

function printPromotions() {
  window.print()
}

onMounted(() => {
  fetchPromotions()
  fetchClassesAndStudents()
  loadClassWiseData()
})
</script>

<style scoped lang="scss">
/* Toast Banner */
.toast-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.25rem;
  border-radius: var(--radius-md);
  margin-bottom: 1.25rem;
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  box-shadow: var(--elevation-1);
  animation: slideInDown 0.25s ease-out;

  &.success {
    background: #ecfdf5;
    border: 1px solid #10b981;
    color: #065f46;
  }

  &.error {
    background: #fef2f2;
    border: 1px solid #ef4444;
    color: #991b1b;
  }
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.toast-close {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: inherit;
  cursor: pointer;
}

/* Lifecycle Navigation Bar */
.lifecycle-nav-bar {
  margin-bottom: 1.25rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  padding: 0.35rem 0.5rem;
  box-shadow: var(--elevation-1);
}

.lifecycle-tabs {
  display: flex;
  gap: 0.35rem;
  overflow-x: auto;
  scrollbar-width: none;

  &::-webkit-scrollbar {
    display: none;
  }
}

.lifecycle-tab {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-md);
  font-family: var(--font-bn);
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-muted);
  text-decoration: none;
  white-space: nowrap;
  transition: all var(--transition-fast);

  &:hover {
    color: var(--color-primary);
    background: var(--color-bg-muted);
  }

  &.active {
    background: var(--color-primary);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(20, 80, 50, 0.25);
  }
}

/* Page Header */
.page-wrapper {
  max-width: 1380px;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-title-block h1 {
  font-size: 1.6rem;
  font-weight: 800;
  color: var(--color-text);
  margin: 0.2rem 0 0.35rem;
}

.eyebrow {
  font-size: 0.76rem;
  font-weight: 700;
  color: var(--color-primary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.page-subtitle {
  color: var(--color-text-light);
  font-size: 0.88rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  align-items: center;
  flex-wrap: wrap;
}

/* Stats Cards Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  padding: 1.15rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: var(--elevation-1);
}

.stat-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;

  &.green { background: rgba(16, 185, 129, 0.12); color: #059669; }
  &.blue { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
  &.amber { background: rgba(245, 158, 11, 0.12); color: #d97706; }
  &.purple { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
}

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--color-text);
  font-family: var(--font-bn);
  line-height: 1.2;
}

.stat-label {
  font-size: 0.78rem;
  color: var(--color-text-light);
  margin-top: 0.15rem;
}

/* Main View Tabs */
.view-tabs-container {
  margin-bottom: 1.25rem;
}

.view-tabs {
  display: inline-flex;
  background: var(--color-bg-muted);
  padding: 4px;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
  gap: 4px;
}

.view-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.45rem 0.95rem;
  border-radius: var(--radius-sm);
  border: none;
  background: transparent;
  font-family: var(--font-bn);
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-light);
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    color: var(--color-text);
  }

  &.active {
    background: var(--color-bg-card);
    color: var(--color-primary);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
  }
}

.tab-badge {
  background: rgba(0, 0, 0, 0.06);
  padding: 0.1rem 0.45rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-family: monospace;
}

/* Toolbar & Filters */
.table-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  box-shadow: var(--elevation-1);
  overflow: hidden;
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.15rem 1.5rem;
  border-bottom: 1px solid var(--color-border-light);
  flex-wrap: wrap;
  gap: 0.75rem;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  min-width: 260px;
  flex: 1;
  max-width: 380px;

  input {
    width: 100%;
    padding: 0.55rem 2rem 0.55rem 2.25rem;
    border: 1px solid var(--color-border);
    border-radius: 9px;
    font-size: 0.85rem;
    background: var(--color-bg);
    color: var(--color-text);
    outline: none;
    transition: border-color var(--transition-fast);

    &:focus {
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px var(--color-primary-100);
    }
  }

  .search-icon {
    position: absolute;
    left: 0.75rem;
    color: var(--color-text-muted);
  }

  .clear-search-btn {
    position: absolute;
    right: 0.5rem;
    background: none;
    border: none;
    color: var(--color-text-muted);
    cursor: pointer;
  }
}

.filter-selects-group {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-wrap: wrap;
}

.select-wrapper {
  position: relative;
}

.btn-reset {
  padding: 0.45rem 0.75rem;
  font-size: 0.8rem;
}

.highlight-count {
  color: var(--color-primary);
  font-size: 0.95rem;
}

/* Table Elements */
.user-cell {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.user-avatar-initials {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #ffffff;
  font-size: 0.82rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-meta-block {
  display: flex;
  flex-direction: column;
}

.student-name {
  font-size: 0.88rem;
  color: var(--color-text);
}

.id-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.roll-badge {
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--color-primary);
  background: var(--color-primary-50);
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  display: inline-block;
  width: fit-content;
}

.id-badge {
  background: var(--color-bg-muted);
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
  font-size: 0.76rem;
  color: var(--color-text-muted);
  width: fit-content;
}

/* Promotion Flow Badge */
.promotion-flow-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: var(--color-bg-muted);
  border: 1px solid var(--color-border-light);
  padding: 0.3rem 0.65rem;
  border-radius: 8px;
}

.class-node {
  font-size: 0.82rem;
  font-weight: 600;
  font-family: var(--font-bn);

  &.from {
    color: var(--color-text-light);
  }

  &.to {
    color: var(--color-primary);
    font-weight: 700;
  }
}

.flow-arrow {
  color: var(--color-primary);
  font-size: 0.75rem;
}

.session-tag {
  display: inline-block;
  padding: 0.2rem 0.55rem;
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
}

/* Status Pills */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 700;
  font-family: var(--font-bn);

  .status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
  }

  &.badge-approved {
    background: #ecfdf5;
    color: #059669;
    .status-dot { background: #10b981; }
  }

  &.badge-pending {
    background: #fefce8;
    color: #b45309;
    .status-dot { background: #f59e0b; }
  }

  &.badge-rejected {
    background: #fef2f2;
    color: #dc2626;
    .status-dot { background: #ef4444; }
  }
}

/* Action Buttons */
.action-btn-group {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.35rem;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  background: var(--color-bg-card);
  color: var(--color-text);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    background: var(--color-bg-muted);
  }

  &.approve {
    color: #059669;
    border-color: #a7f3d0;
    background: #ecfdf5;
    &:hover { background: #10b981; color: #fff; }
  }

  &.edit {
    color: var(--color-primary);
    &:hover { border-color: var(--color-primary); background: var(--color-primary-50); }
  }

  &.delete {
    color: var(--color-error);
    &:hover { border-color: var(--color-error); background: #fef2f2; }
  }
}

/* Class-wise Grid */
.classwise-wrapper {
  margin-top: 0.5rem;
}

.classwise-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.classwise-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.25rem;
}

.classwise-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  padding: 1.25rem;
  box-shadow: var(--elevation-1);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.classwise-card-top {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.class-icon-circle {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: var(--color-primary-50);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
}

.class-title-block h4 {
  margin: 0;
  font-size: 1.05rem;
  color: var(--color-text);
}

.class-type-badge {
  font-size: 0.72rem;
  color: var(--color-text-light);
  background: var(--color-bg-muted);
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
}

.classwise-count-row {
  display: flex;
  align-items: baseline;
  gap: 0.45rem;
  margin-bottom: 1.25rem;
}

.count-number {
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--color-primary);
  font-family: var(--font-bn);
}

.count-label {
  font-size: 0.82rem;
  color: var(--color-text-muted);
}

/* Bulk Modal Student Selection Panel */
.bulk-students-panel {
  margin-top: 1.25rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-bg);
  overflow: hidden;
}

.panel-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.65rem 1rem;
  background: var(--color-bg-muted);
  border-bottom: 1px solid var(--color-border);
}

.panel-title {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.88rem;
}

.badge-count {
  background: var(--color-primary-100);
  color: var(--color-primary);
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
}

.panel-actions {
  display: flex;
  gap: 0.4rem;
}

.btn-xs {
  padding: 0.25rem 0.55rem;
  font-size: 0.74rem;
  border-radius: 5px;
}

.bulk-table-wrap {
  max-height: 260px;
  overflow-y: auto;
}

.bulk-checklist-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.84rem;

  th {
    padding: 0.55rem 0.75rem;
    background: var(--color-bg-card);
    border-bottom: 1px solid var(--color-border);
    font-weight: 700;
    color: var(--color-text-light);
    font-size: 0.78rem;
  }

  td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--color-border-light);
    cursor: pointer;
  }

  tr:hover td {
    background: var(--color-primary-50);
  }

  tr.selected td {
    background: rgba(20, 80, 50, 0.05);
  }
}

.roll-pill {
  font-size: 0.75rem;
  font-weight: 700;
  background: var(--color-bg-muted);
  padding: 0.1rem 0.45rem;
  border-radius: 4px;
}

.mono-sub {
  font-family: monospace;
  font-size: 0.78rem;
  color: var(--color-text-muted);
}

.bulk-placeholder {
  text-align: center;
  padding: 2.5rem 1rem;
  color: var(--color-text-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.88rem;
}

.bulk-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.65rem;
  padding: 2rem;
  color: var(--color-text-muted);
  font-size: 0.85rem;
}

.bulk-selection-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6rem 1rem;
  background: var(--color-bg-card);
  border-top: 1px solid var(--color-border);
  font-size: 0.84rem;
}

.count-highlight {
  color: var(--color-primary);
  font-size: 0.95rem;
}

/* =======================================================
   MODAL & FORM SYSTEM (PREMIUM SCOPED FIXES)
   ======================================================= */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  inset: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(10, 35, 20, 0.65);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999;
  padding: 1.5rem;
  animation: modalFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-card {
  background: #ffffff;
  border: 1px solid var(--color-border-light, #e2e8f0);
  border-radius: var(--radius-lg, 12px);
  width: 100%;
  max-width: 580px;
  max-height: min(90vh, 720px);
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  animation: modalScaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  margin: auto;

  &.modal-lg {
    max-width: 860px;
  }
  &.modal-sm {
    max-width: 440px;
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.15rem 1.5rem;
  border-bottom: 1px solid var(--color-border-light, #e2e8f0);
  background: var(--color-bg-card, #ffffff);
  flex-shrink: 0;

  h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--color-text, #1e293b);
    font-family: var(--font-bn);
    line-height: 1.3;
  }
}

.modal-title-with-icon {
  display: flex;
  align-items: center;
  gap: 0.75rem;

  .icon-bubble {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;

    &.green {
      background: rgba(20, 80, 50, 0.1);
      color: #145032;
    }
    &.red {
      background: #fef2f2;
      color: #dc2626;
    }
    &.blue {
      background: #eff6ff;
      color: #2563eb;
    }
  }

  h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--color-text, #1e293b);
    font-family: var(--font-bn);
    line-height: 1.3;
  }

  .modal-subtitle {
    margin: 0.15rem 0 0;
    font-size: 0.8rem;
    color: var(--color-text-light, #64748b);
    font-family: var(--font-bn);
  }
}

.modal-close {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid var(--color-border-light, #e2e8f0);
  background: var(--color-bg-muted, #f8fafc);
  color: var(--color-text-light, #64748b);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
  flex-shrink: 0;

  &:hover {
    background: #fee2e2;
    color: #ef4444;
    border-color: #fca5a5;
  }
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border-light, #e2e8f0);
  background: var(--color-bg-muted, #f8fafc);
  border-bottom-left-radius: var(--radius-lg, 12px);
  border-bottom-right-radius: var(--radius-lg, 12px);
  flex-shrink: 0;
}

/* Form Styles */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-bottom: 1.1rem;

  &:last-child {
    margin-bottom: 0;
  }
}

.form-row-2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 0;

  @media (max-width: 600px) {
    grid-template-columns: 1fr;
  }
}

.form-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text, #1e293b);
  font-family: var(--font-bn);
  display: flex;
  align-items: center;
  gap: 0.25rem;

  .required {
    color: #ef4444;
    font-weight: 700;
  }
}

.form-hint {
  font-size: 0.75rem;
  color: var(--color-text-light, #64748b);
  font-family: var(--font-bn);
  margin-top: 0.2rem;
}

.form-control,
.form-select,
textarea.form-control {
  width: 100%;
  padding: 0.6rem 0.85rem;
  border: 1px solid var(--color-border, #cbd5e1);
  border-radius: var(--radius-sm, 8px);
  font-family: var(--font-bn);
  font-size: 0.9rem;
  background: #ffffff;
  color: var(--color-text, #1e293b);
  transition: border-color 0.15s ease, box-shadow 0.15s ease;

  &:focus {
    outline: none;
    border-color: var(--color-primary, #145032);
    box-shadow: 0 0 0 3px rgba(20, 80, 50, 0.12);
  }

  &:disabled {
    background: #f1f5f9;
    cursor: not-allowed;
    opacity: 0.7;
  }
}

textarea.form-control {
  resize: vertical;
  min-height: 75px;
}

/* Visual Class Transition Banner */
.promotion-preview-flow {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.25rem;
  background: rgba(20, 80, 50, 0.04);
  border: 1px dashed rgba(20, 80, 50, 0.35);
  border-radius: var(--radius-md, 8px);
  margin-bottom: 1.1rem;
  gap: 0.75rem;

  .flow-step {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;

    .flow-label {
      font-size: 0.72rem;
      color: var(--color-text-light, #64748b);
      font-family: var(--font-bn);
      font-weight: 500;
    }

    strong {
      font-size: 0.95rem;
      color: var(--color-primary, #145032);
      font-family: var(--font-bn);
    }

    &.to strong {
      color: #059669;
    }
  }

  .flow-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary, #145032);
    font-size: 1.2rem;
    opacity: 0.8;
  }
}

.delete-detail-box {
  margin-top: 0.85rem;
  padding: 0.85rem 1rem;
  background: var(--color-bg-muted, #f8fafc);
  border: 1px solid var(--color-border-light, #e2e8f0);
  border-radius: var(--radius-sm, 8px);
  font-size: 0.88rem;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-family: var(--font-bn);

  strong {
    color: var(--color-text, #1e293b);
  }
}

/* Buttons */
.btn {
  padding: 0.58rem 1.15rem;
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  transition: all 0.2s ease;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #145032 0%, #1a6b43 100%);
  color: #fff;
  box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25);
}

.btn-success {
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
  color: #fff;
  box-shadow: 0 3px 10px rgba(16, 185, 129, 0.25);
}

.btn-danger {
  background: #dc2626;
  color: #fff;
}

.btn-outline {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-sm {
  padding: 0.4rem 0.8rem;
  font-size: 0.82rem;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid rgba(20, 80, 50, 0.15);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 0.75rem;
}

.spinner-sm {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(20, 80, 50, 0.15);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes slideInDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}

.print-only {
  display: none !important;
}

/* Print Media Styles */
@media print {
  @page {
    size: A4 landscape;
    margin: 8mm 10mm;
  }

  .no-print { display: none !important; }
  .print-only { display: block !important; }

  .page-wrapper {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    background: #ffffff !important;
  }

  .table-card {
    border: none !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .print-header-block {
    text-align: center;
    margin-bottom: 12px;
    border-bottom: 2px solid #145032;
    padding-bottom: 6px;
  }

  .print-bismillah {
    font-family: 'Amiri', serif;
    font-size: 14px;
    color: #145032;
    font-weight: 700;
  }

  .print-header-block h2 {
    font-size: 18px;
    color: #145032;
    margin: 2px 0;
  }

  .print-sub {
    font-size: 11px;
    color: #475569;
    margin: 0;
  }

  .premium-table {
    width: 100% !important;
    border: 1.5px solid #222 !important;
    font-size: 11px !important;

    th, td {
      border: 1px solid #444 !important;
      padding: 5px 6px !important;
      color: #000 !important;
    }

    th {
      background: #e2e8f0 !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
  }

  .promotion-flow-badge {
    background: none !important;
    border: none !important;
    padding: 0 !important;
  }
}
</style>
