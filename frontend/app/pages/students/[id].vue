<template>
  <div class="page-wrapper slide-up-fade">
    <!-- Top Navigation & Breadcrumb -->
    <div class="top-nav-bar">
      <div class="breadcrumb">
        <NuxtLink to="/students" class="back-link">
          <Icon name="arrowLeft" /> ছাত্র তালিকায় ফিরে যান
        </NuxtLink>
        <span class="sep">/</span>
        <span class="breadcrumb-current">{{ student?.name_bn || student?.name_en || 'ছাত্রের বিবরণ' }}</span>
      </div>

      <div class="header-actions" v-if="student">
        <button class="btn btn-outline" @click="showIdCardModal = true" title="আইডি কার্ড দেখুন বা প্রিন্ট করুন">
          <Icon name="printer" /> আইডি কার্ড
        </button>
        <NuxtLink :to="`/enrollments/create?student_id=${student.id}`" class="btn btn-outline" title="নতুন ক্লাসে ভর্তি">
          <Icon name="plus" /> ভর্তি করুন
        </NuxtLink>
        <NuxtLink :to="`/students/${student.id}/edit`" class="btn btn-primary" title="তথ্য সম্পাদনা করুন">
          <Icon name="pencil" /> সম্পাদনা
        </NuxtLink>
        <button class="btn btn-danger-outline" @click="showDeleteModal = true" title="ছাত্রের রেকর্ড মুছুন">
          <Icon name="delete" /> মুছুন
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner" />
      <p>ছাত্রের পূর্ণাঙ্গ প্রোফাইল লোড হচ্ছে...</p>
    </div>

    <!-- Error / Empty State -->
    <div v-else-if="!student" class="empty-state">
      <Icon name="alertCircle" size="48" style="color: var(--color-error); margin-bottom: 1rem;" />
      <h3>ছাত্র পাওয়া যায়নি</h3>
      <p class="text-muted">অনুরোধকৃত ছাত্রের আইডিটি সিস্টেমে বিদ্যমান নেই অথবা মুছে ফেলা হয়েছে।</p>
      <NuxtLink to="/students" class="btn btn-primary mt-2">সকল ছাত্রের তালিকা দেখুন</NuxtLink>
    </div>

    <!-- Profile Content -->
    <div v-else class="profile-container">
      <!-- Soft-deleted notice if applicable -->
      <div v-if="student.deleted_at" class="alert-deleted-banner">
        <div class="alert-deleted-text">
          <Icon name="alertCircle" size="20" />
          <span><strong>সতর্কতা:</strong> এই ছাত্রের রেকর্ডটি সফট-ডিলিট (মুছে ফেলা) অবস্থায় রয়েছে।</span>
        </div>
        <button class="btn btn-outline-success btn-sm" @click="restoreStudent" :disabled="restoring">
          <Icon name="refresh" />
          {{ restoring ? 'পুনরুদ্ধার হচ্ছে...' : 'পুনরুদ্ধার করুন (Restore)' }}
        </button>
      </div>

      <!-- Hero Banner Card -->
      <div class="hero-card">
        <div class="hero-content">
          <!-- Avatar & Status -->
          <div class="avatar-column">
            <div class="student-avatar-wrap">
              <img
                v-if="student.user?.profile_image || student.photo_url || student.user?.avatar_url"
                :src="student.user?.profile_image || student.photo_url || student.user?.avatar_url"
                :alt="student.name_bn"
                class="student-avatar-img"
              />
              <div v-else class="student-avatar-initials">
                {{ (student.name_bn || student.name_en || '?').charAt(0) }}
              </div>
            </div>
            <div class="avatar-badge-wrap">
              <span class="status-pill" :class="student.status === 'active' || student.is_active ? 'badge-approved' : 'badge-rejected'">
                <span class="status-dot"></span>
                {{ student.status === 'active' || student.is_active ? 'সক্রিয় ছাত্র' : 'নিষ্ক্রিয়' }}
              </span>
            </div>
          </div>

          <!-- Identity Details -->
          <div class="identity-column">
            <div class="identity-header">
              <div class="identity-names">
                <h1 class="student-name-bn">{{ student.name_bn }}</h1>
                <p class="student-name-en" v-if="student.name_en">{{ student.name_en }}</p>
              </div>
            </div>

            <!-- Meta Tags Row -->
            <div class="meta-tags-row">
              <div class="meta-tag">
                <span class="tag-icon"><Icon name="academic" size="14" /></span>
                <span class="tag-label">ভর্তি নং:</span>
                <strong class="tag-value">{{ student.admission_number || '—' }}</strong>
              </div>

              <div class="meta-tag">
                <span class="tag-icon"><Icon name="book" size="14" /></span>
                <span class="tag-label">শ্রেণি:</span>
                <strong class="tag-value">
                  {{ currentEnrollment?.class?.name_bn || currentEnrollment?.class?.name_en || student.class?.name_bn || student.class_name || 'ভর্তি প্রক্রিয়াধীন' }}
                </strong>
              </div>

              <div class="meta-tag" v-if="student.roll_number || currentEnrollment?.roll_number">
                <span class="tag-icon"><Icon name="tag" size="14" /></span>
                <span class="tag-label">রোল:</span>
                <strong class="tag-value">{{ student.roll_number || currentEnrollment?.roll_number }}</strong>
              </div>

              <div class="meta-tag" v-if="student.blood_group">
                <span class="tag-icon" style="color: #dc2626;">🩸</span>
                <span class="tag-label">রক্ত:</span>
                <strong class="tag-value">{{ student.blood_group }}</strong>
              </div>

              <div class="meta-tag">
                <span class="tag-icon"><Icon name="calendar" size="14" /></span>
                <span class="tag-label">ভর্তির তারিখ:</span>
                <span class="tag-value">{{ formatDate(student.admission_date) }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Stats Column -->
          <div class="hero-stats-column">
            <div class="stat-pill-box">
              <span class="stat-box-label">মোট ভর্তি</span>
              <strong class="stat-box-val">{{ (enrollmentsList || []).length }} বার</strong>
            </div>
            <div class="stat-pill-box">
              <span class="stat-box-label">পরীক্ষার ফলাফল</span>
              <strong class="stat-box-val">{{ (resultsList || []).length }} টি</strong>
            </div>
            <div class="stat-pill-box">
              <span class="stat-box-label">শিক্ষাবর্ষ</span>
              <strong class="stat-box-val">{{ currentEnrollment?.session?.name_bn || '২০২৫-২০২৬' }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Segmented Pill Tabs Navigation -->
      <div class="tabs-nav mb-3">
        <button
          v-for="t in tabs"
          :key="t.key"
          :class="['tab-btn', { active: activeTab === t.key }]"
          @click="activeTab = t.key"
        >
          <Icon :name="t.icon" size="16" />
          {{ t.label }}
          <span v-if="t.badge !== undefined" class="tab-badge">{{ t.badge }}</span>
        </button>
      </div>

      <!-- Tab 1: মূল তথ্য (Overview & Details) -->
      <div v-if="activeTab === 'overview'" class="tab-pane">
        <div class="profile-grid">
          <!-- Card 1: ব্যক্তিগত তথ্য -->
          <div class="card info-card">
            <div class="card-header-clean">
              <div class="header-icon-title">
                <span class="section-icon green"><Icon name="user" size="18" /></span>
                <h3>ব্যক্তিগত তথ্য</h3>
              </div>
            </div>
            <div class="card-body">
              <div class="data-row">
                <span class="data-label">পূর্ণ নাম (বাংলা)</span>
                <span class="data-val font-medium">{{ student.name_bn || '—' }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">পূর্ণ নাম (ইংরেজি)</span>
                <span class="data-val">{{ student.name_en || '—' }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">জন্ম তারিখ</span>
                <span class="data-val">{{ formatDate(student.date_of_birth) }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">লিঙ্গ</span>
                <span class="data-val">{{ formatGender(student.gender) }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">রক্তের গ্রুপ</span>
                <span class="data-val">
                  <span v-if="student.blood_group" class="blood-badge">{{ student.blood_group }}</span>
                  <span v-else>—</span>
                </span>
              </div>
              <div class="data-row">
                <span class="data-label">জাতীয়তা</span>
                <span class="data-val">{{ student.nationality || 'বাংলাদেশী' }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">স্বাস্থ্য ও শারীরিক তথ্য</span>
                <span class="data-val">{{ student.health_summary || 'স্বাভাবিক' }}</span>
              </div>
            </div>
          </div>

          <!-- Card 2: একাডেমিক তথ্য -->
          <div class="card info-card">
            <div class="card-header-clean">
              <div class="header-icon-title">
                <span class="section-icon blue"><Icon name="book" size="18" /></span>
                <h3>একাডেমিক তথ্য</h3>
              </div>
            </div>
            <div class="card-body">
              <div class="data-row">
                <span class="data-label">ভর্তি নম্বর (Admission No.)</span>
                <span class="data-val"><code class="mono-badge">{{ student.admission_number || '—' }}</code></span>
              </div>
              <div class="data-row">
                <span class="data-label">বর্তমান শ্রেণি</span>
                <span class="data-val font-medium">
                  {{ currentEnrollment?.class?.name_bn || currentEnrollment?.class?.name_en || student.class?.name_bn || 'ভর্তি সম্পন্ন হয়নি' }}
                </span>
              </div>
              <div class="data-row">
                <span class="data-label">শাখা / সেকশন</span>
                <span class="data-val">
                  {{ currentEnrollment?.section?.name_bn || currentEnrollment?.section?.name_en || 'সাধারণ' }}
                </span>
              </div>
              <div class="data-row">
                <span class="data-label">শ্রেণি রোল</span>
                <span class="data-val">{{ student.roll_number || currentEnrollment?.roll_number || '—' }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">ভবিষ্যৎ শ্রেণি</span>
                <span class="data-val">{{ student.next_class_name || '—' }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">ভর্তির তারিখ</span>
                <span class="data-val">{{ formatDate(student.admission_date) }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">বর্তমান অবস্থা</span>
                <span class="data-val">
                  <span class="status-pill" :class="student.status === 'active' || student.is_active ? 'badge-approved' : 'badge-rejected'">
                    <span class="status-dot"></span>
                    {{ student.status === 'active' || student.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                  </span>
                </span>
              </div>
            </div>
          </div>

          <!-- Card 3: যোগাযোগ ও ঠিকানা -->
          <div class="card info-card full-width">
            <div class="card-header-clean">
              <div class="header-icon-title">
                <span class="section-icon amber"><Icon name="phone" size="18" /></span>
                <h3>যোগাযোগ ও ঠিকানা</h3>
              </div>
            </div>
            <div class="card-body grid-contact">
              <div class="contact-item">
                <span class="contact-label"><Icon name="phone" size="14" /> শিক্ষার্থী/অভিভাবক মোবাইল</span>
                <span class="contact-value">{{ student.user?.phone || student.phone || student.guardian_phone || student.father_phone || '—' }}</span>
              </div>
              <div class="contact-item">
                <span class="contact-label"><Icon name="mail" size="14" /> ইমেইল ঠিকানা</span>
                <span class="contact-value">{{ student.user?.email || student.email || '—' }}</span>
              </div>
              <div class="contact-item">
                <span class="contact-label"><Icon name="user" size="14" /> জরুরি যোগাযোগ ব্যক্তি</span>
                <span class="contact-value">{{ student.emergency_contact_name || student.guardian_name || student.father_name || '—' }}</span>
              </div>
              <div class="contact-item">
                <span class="contact-label"><Icon name="phone" size="14" /> জরুরি ফোন</span>
                <span class="contact-value">{{ student.emergency_contact_phone || student.guardian_phone || student.father_phone || '—' }}</span>
              </div>
              <div class="contact-item full">
                <span class="contact-label"><Icon name="mapPin" size="14" /> স্থায়ী ও বর্তমান ঠিকানা</span>
                <p class="contact-address">{{ student.address_bn || student.address || 'কোনো ঠিকানা লিপিবদ্ধ নেই' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 2: ভর্তির ইতিহাস (Enrollment History) -->
      <div v-if="activeTab === 'enrollments'" class="tab-pane">
        <div class="table-card">
          <div class="toolbar">
            <div class="toolbar-title">
              <h3 class="font-bold text-md">মাদ্রাসায় ভর্তির পূর্ণাঙ্গ ইতিহাস</h3>
              <p class="text-muted text-xs">ছাত্রের সকল শিক্ষাবর্ষ ও শ্রেণির ভর্তির রেকর্ড</p>
            </div>
            <div class="toolbar-actions">
              <NuxtLink :to="`/enrollments/create?student_id=${student.id}`" class="btn btn-primary btn-sm">
                <Icon name="plus" /> নতুন ভর্তি যোগ করুন
              </NuxtLink>
            </div>
          </div>

          <div v-if="enrollLoading" class="loading-state">
            <div class="spinner" />
            <p>ভর্তির তথ্য লোড হচ্ছে...</p>
          </div>

          <div v-else-if="enrollmentsList.length === 0" class="empty-state">
            <Icon name="academic" size="40" style="color: var(--color-primary); margin-bottom: 0.5rem;" />
            <h4>কোনো ভর্তি রেকর্ড পাওয়া যায়নি</h4>
            <p class="text-muted">এই ছাত্রের জন্য এখনও কোনো আনুষ্ঠানিক শ্রেণি ভর্তি সম্পন্ন করা হয়নি।</p>
            <NuxtLink :to="`/enrollments/create?student_id=${student.id}`" class="btn btn-primary mt-2">
              প্রথম ভর্তি নিবন্ধন করুন
            </NuxtLink>
          </div>

          <div v-else class="table-responsive">
            <table class="premium-table">
              <thead>
                <tr>
                  <th>ভর্তি নম্বর</th>
                  <th>শ্রেণি</th>
                  <th>শাখা</th>
                  <th>শিক্ষাবর্ষ</th>
                  <th>ভর্তির তারিখ</th>
                  <th>অবস্থা</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="e in enrollmentsList" :key="e.id">
                  <td><code class="mono-badge">#{{ e.enrollment_number || e.id }}</code></td>
                  <td><strong>{{ e.class?.name_bn || e.class?.name_en || e.className || '—' }}</strong></td>
                  <td>{{ e.section?.name_bn || e.section?.name_en || 'সাধারণ' }}</td>
                  <td>{{ e.session?.name_bn || e.session?.name_en || e.academic_year || '২০২৫-২০২৬' }}</td>
                  <td>{{ formatDate(e.enrollment_date || e.created_at) }}</td>
                  <td>
                    <span class="status-pill" :class="getStatusBadgeClass(e.status)">
                      <span class="status-dot"></span> {{ getStatusLabel(e.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab 3: পিতা-মাতা ও অভিভাবক (Guardian & Family) -->
      <div v-if="activeTab === 'guardian'" class="tab-pane">
        <div class="profile-grid">
          <!-- পিতা -->
          <div class="card info-card">
            <div class="card-header-clean">
              <div class="header-icon-title">
                <span class="section-icon green"><Icon name="user" size="18" /></span>
                <h3>পিতার তথ্য</h3>
              </div>
            </div>
            <div class="card-body">
              <div class="data-row">
                <span class="data-label">পিতার নাম</span>
                <span class="data-val font-medium">{{ student.father_name || student.guardian?.father_name || '—' }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">পিতার মোবাইল</span>
                <span class="data-val">{{ student.father_phone || '—' }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">পেশা</span>
                <span class="data-val">{{ student.father_occupation || '—' }}</span>
              </div>
            </div>
          </div>

          <!-- মাতা -->
          <div class="card info-card">
            <div class="card-header-clean">
              <div class="header-icon-title">
                <span class="section-icon purple"><Icon name="user" size="18" /></span>
                <h3>মাতার তথ্য</h3>
              </div>
            </div>
            <div class="card-body">
              <div class="data-row">
                <span class="data-label">মাতার নাম</span>
                <span class="data-val font-medium">{{ student.mother_name || student.guardian?.mother_name || '—' }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">মাতার মোবাইল</span>
                <span class="data-val">{{ student.mother_phone || '—' }}</span>
              </div>
              <div class="data-row">
                <span class="data-label">পেশা</span>
                <span class="data-val">{{ student.mother_occupation || 'গৃহিণী' }}</span>
              </div>
            </div>
          </div>

          <!-- স্থানীয় অভিভাবক -->
          <div class="card info-card full-width">
            <div class="card-header-clean">
              <div class="header-icon-title">
                <span class="section-icon amber"><Icon name="users" size="18" /></span>
                <h3>স্থানীয়/প্রধান অভিভাবক</h3>
              </div>
            </div>
            <div class="card-body grid-contact">
              <div class="contact-item">
                <span class="contact-label">অভিভাবকের নাম</span>
                <strong class="contact-value">{{ student.guardian?.name_bn || student.guardian_name || student.father_name || '—' }}</strong>
              </div>
              <div class="contact-item">
                <span class="contact-label">ছাত্রের সাথে সম্পর্ক</span>
                <span class="contact-value">{{ student.guardian?.relation || student.guardian_relation || 'পিতা' }}</span>
              </div>
              <div class="contact-item">
                <span class="contact-label">অভিভাবকের মোবাইল</span>
                <span class="contact-value">{{ student.guardian?.phone || student.guardian_phone || student.father_phone || '—' }}</span>
              </div>
              <div class="contact-item">
                <span class="contact-label">জরুরি যোগাযোগ মোবাইল</span>
                <span class="contact-value">{{ student.emergency_contact_phone || student.guardian_phone || student.father_phone || '—' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 4: পরীক্ষা ও ফলাফল (Results) -->
      <div v-if="activeTab === 'results'" class="tab-pane">
        <div class="table-card">
          <div class="toolbar">
            <div class="toolbar-title">
              <h3 class="font-bold text-md">পরীক্ষার ফলাফল বিবরণী</h3>
              <p class="text-muted text-xs">ছাত্রের সকল পরীক্ষা ও মূল্যায়ন ফলাফল</p>
            </div>
          </div>

          <div v-if="resultsLoading" class="loading-state">
            <div class="spinner" />
            <p>ফলাফল লোড হচ্ছে...</p>
          </div>

          <div v-else-if="resultsList.length === 0" class="empty-state">
            <Icon name="exam" size="40" style="color: var(--color-primary); margin-bottom: 0.5rem;" />
            <h4>কোনো পরীক্ষার ফলাফল পাওয়া যায়নি</h4>
            <p class="text-muted">এই ছাত্রের জন্য এখনও কোনো পরীক্ষার ফলাফল প্রকাশ করা হয়নি।</p>
          </div>

          <div v-else class="table-responsive">
            <table class="premium-table">
              <thead>
                <tr>
                  <th>পরীক্ষা</th>
                  <th>প্রাপ্ত নম্বর</th>
                  <th>মোট নম্বর</th>
                  <th>শতকরা</th>
                  <th>অবস্থা</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in resultsList" :key="r.id">
                  <td><strong>{{ r.exam?.name_bn || r.exam_name || '—' }}</strong></td>
                  <td>{{ r.marks_obtained ?? '—' }}</td>
                  <td>{{ r.total_marks ?? '—' }}</td>
                  <td><strong>{{ r.percentage ?? '—' }}%</strong></td>
                  <td>
                    <span class="status-pill" :class="r.is_published ? 'badge-approved' : 'badge-pending'">
                      <span class="status-dot"></span> {{ r.is_published ? 'প্রকাশিত' : 'অপ্রকাশিত' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ID Card Modal -->
    <div v-if="showIdCardModal" class="modal-overlay" @click.self="showIdCardModal = false">
      <div class="modal-card id-card-modal">
        <div class="modal-header">
          <h3>ছাত্র পরিচয়পত্র (Student ID Card)</h3>
          <button class="action-btn" @click="showIdCardModal = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <!-- The Physical Card Mockup -->
          <div class="id-card-preview" id="printable-id-card">
            <div class="id-card-top-strip">
              <div class="id-madrasa-brand">
                <span class="id-madrasa-logo">🕌</span>
                <div class="id-madrasa-titles">
                  <h4>দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h4>
                  <small>মাদ্রাসা শিক্ষাবোর্ড আইডি কার্ড</small>
                </div>
              </div>
            </div>
            <div class="id-card-body">
              <div class="id-avatar-box">
                <img
                  v-if="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                  :src="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                  alt="Avatar"
                />
                <div v-else class="id-avatar-placeholder">
                  {{ (student?.name_bn || '?').charAt(0) }}
                </div>
              </div>
              <div class="id-info-box">
                <h3 class="id-student-name">{{ student?.name_bn }}</h3>
                <p class="id-student-en" v-if="student?.name_en">{{ student?.name_en }}</p>
                <div class="id-meta-grid">
                  <div><span>ভর্তি নং:</span> <strong>{{ student?.admission_number || '—' }}</strong></div>
                  <div><span>শ্রেণি:</span> <strong>{{ currentEnrollment?.class?.name_bn || student?.class?.name_bn || 'প্রথম শ্রেণি' }}</strong></div>
                  <div><span>রোল নং:</span> <strong>{{ student?.roll_number || '—' }}</strong></div>
                  <div><span>রক্তের গ্রুপ:</span> <strong>{{ student?.blood_group || '—' }}</strong></div>
                  <div><span>মোবাইল:</span> <span>{{ student?.user?.phone || student?.phone || student?.guardian_phone || '—' }}</span></div>
                  <div><span>শিক্ষাবর্ষ:</span> <span>{{ currentEnrollment?.session?.name_bn || '২০২৫-২০২৬' }}</span></div>
                </div>
              </div>
            </div>
            <div class="id-card-footer">
              <div class="id-barcode-mockup">||| | |||| | ||||| || |||</div>
              <div class="id-signature-box">অধ্যক্ষের স্বাক্ষর</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showIdCardModal = false">বাতিল</button>
          <button class="btn btn-primary" @click="printIdCard">
            <Icon name="printer" /> প্রিন্ট করুন
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-card delete-modal-card">
        <div class="modal-header">
          <h3>আপনি কি নিশ্চিত?</h3>
          <button class="action-btn" @click="showDeleteModal = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <p>
            আপনি "<strong>{{ student?.name_bn }}</strong>" ছাত্রটির সমস্ত রেকর্ড মুছে ফেলতে চাচ্ছেন। এই কাজটি স্থায়ী এবং পূর্বাবস্থায় ফিরিয়ে আনা যাবে না।
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDeleteModal = false">বাতিল</button>
          <button class="btn btn-danger" @click="confirmDeleteStudent" :disabled="deleting">
            <Icon name="loader" v-if="deleting" />
            হ্যাঁ, মুছে ফেলুন
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApiClient } from '~/utils/api'
import Icon from '~/components/Icon.vue'

const route = useRoute()
const router = useRouter()
const api = useApiClient()

const loading = ref(true)
const student = ref<any>(null)
const enrollments = ref<any>(null)
const results = ref<any>(null)
const enrollLoading = ref(false)
const resultsLoading = ref(false)
const deleting = ref(false)

const showIdCardModal = ref(false)
const showDeleteModal = ref(false)

const activeTab = ref('overview')

const enrollmentsList = computed(() => {
  return enrollments.value?.data?.data || enrollments.value?.data || student.value?.enrollments || []
})

const resultsList = computed(() => {
  return results.value?.data?.data || results.value?.data || []
})

const currentEnrollment = computed(() => {
  const list = enrollmentsList.value
  return list.length > 0 ? list[0] : null
})

const tabs = computed(() => [
  { key: 'overview', label: 'মূল তথ্য', icon: 'user' },
  { key: 'enrollments', label: 'ভর্তির ইতিহাস', icon: 'academic', badge: enrollmentsList.value.length || undefined },
  { key: 'guardian', label: 'পিতা-মাতা ও পরিবার', icon: 'users' },
  { key: 'results', label: 'পরীক্ষা ও ফলাফল', icon: 'exam', badge: resultsList.value.length || undefined },
])

async function loadStudent() {
  loading.value = true
  try {
    const res = await api.get(`/students/${route.params.id}`).catch(() => null)
    if (res?.data?.data) {
      student.value = res.data.data
    } else if (res?.data) {
      student.value = res.data
    }
    await Promise.all([loadEnrollments(), loadResults()])
  } catch (error) {
    console.error('Failed to load student:', error)
  } finally {
    loading.value = false
  }
}

async function loadEnrollments() {
  enrollLoading.value = true
  try {
    const res = await api.get(`/enrollments?student_id=${route.params.id}`).catch(() => null)
    if (res?.data) {
      enrollments.value = res.data
    }
  } catch (e) {
    console.error('enrollments fetch error:', e)
  } finally {
    enrollLoading.value = false
  }
}

async function loadResults() {
  resultsLoading.value = true
  try {
    const res = await api.get(`/exam-results?student_id=${route.params.id}`).catch(() => null)
    if (res?.data) {
      results.value = res.data
    }
  } catch (e) {
    console.error('results fetch error:', e)
  } finally {
    resultsLoading.value = false
  }
}

async function confirmDeleteStudent() {
  if (!student.value) return
  deleting.value = true
  try {
    await api.delete(`/students/${student.value.id}`)
    showDeleteModal.value = false
    router.push('/students')
  } catch (err) {
    console.error('Delete failed:', err)
    alert('ছাত্র মুছে ফেলা ব্যর্থ হয়েছে')
  } finally {
    deleting.value = false
  }
}

const restoring = ref(false)
async function restoreStudent() {
  if (!student.value) return
  restoring.value = true
  try {
    const res = await api.post(`/students/${student.value.id}/restore`)
    if (res?.data?.data) {
      student.value = res.data.data
    }
    await loadStudent()
    alert('ছাত্রের রেকর্ড সফলভাবে পুনরুদ্ধার করা হয়েছে!')
  } catch (err) {
    console.error('Restore failed:', err)
    alert('পুনরুদ্ধার ব্যর্থ হয়েছে')
  } finally {
    restoring.value = false
  }
}

function printIdCard() {
  window.print()
}

function formatDate(d: string | null | undefined) {
  if (!d) return '—'
  try {
    return new Date(d).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return d
  }
}

function formatGender(g: string | null | undefined) {
  if (!g) return '—'
  const map: Record<string, string> = { male: 'ছাত্র / পুরুষ', female: 'ছাত্রী / মহিলা', other: 'অন্যান্য' }
  return map[g.toLowerCase()] || g
}

function getStatusLabel(status: string) {
  if (status === 'active' || status === 'approved' || status === 'enrolled') return 'অনুমোদিত'
  if (status === 'pending') return 'অপেক্ষমান'
  if (status === 'rejected') return 'বাতিল'
  if (status === 'transferred') return 'স্থানান্তরিত'
  return status
}

function getStatusBadgeClass(status: string) {
  if (status === 'active' || status === 'approved' || status === 'enrolled') return 'badge-approved'
  if (status === 'pending') return 'badge-pending'
  if (status === 'rejected') return 'badge-rejected'
  return 'badge-pending'
}

onMounted(() => {
  loadStudent()
})
</script>

<style scoped lang="scss">
.top-nav-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: var(--font-bn);
  font-size: var(--text-sm);
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: var(--color-primary);
  text-decoration: none;
  font-weight: 600;
  padding: 0.35rem 0.75rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);

  &:hover {
    background: var(--color-primary-50);
    border-color: var(--color-primary);
  }
}

.sep {
  color: var(--color-text-muted);
}

.breadcrumb-current {
  color: var(--color-text-light);
  font-weight: 500;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-danger-outline {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.55rem 1rem;
  border-radius: var(--radius-sm);
  background: var(--color-bg-card);
  border: 1px solid #fca5a5;
  color: #dc2626;
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    background: #fef2f2;
    border-color: #ef4444;
  }
}

.alert-deleted-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 0.9rem 1.25rem;
  background: #fef2f2;
  border: 1px solid #f87171;
  border-radius: var(--radius-md);
  margin-bottom: 1.25rem;
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  color: #991b1b;
}

.alert-deleted-text {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.btn-outline-success {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.85rem;
  border-radius: var(--radius-sm);
  background: #ffffff;
  border: 1px solid #16a34a;
  color: #16a34a;
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    background: #f0fdf4;
  }
}

/* Hero Card */
.hero-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  box-shadow: var(--elevation-1);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #145032 0%, #186640 50%, #d4af37 100%);
  }
}

.hero-content {
  display: flex;
  align-items: center;
  gap: 1.75rem;
  flex-wrap: wrap;
}

.avatar-column {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.6rem;
}

.student-avatar-wrap {
  width: 100px;
  height: 100px;
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: var(--elevation-2);
  border: 3px solid #ffffff;
  outline: 2px solid var(--color-primary-100);
}

.student-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.student-avatar-initials {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #145032 0%, #186640 100%);
  color: #ffffff;
  font-size: 2.5rem;
  font-weight: 700;
  font-family: var(--font-bn);
}

.identity-column {
  flex: 1;
  min-width: 280px;
}

.student-name-bn {
  font-size: 1.85rem;
  font-weight: 800;
  color: var(--color-primary-dark);
  font-family: var(--font-bn);
  margin: 0;
  line-height: 1.2;
}

.student-name-en {
  font-size: 1rem;
  color: var(--color-text-light);
  margin: 0.2rem 0 0.75rem;
  font-weight: 500;
}

.meta-tags-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.meta-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  padding: 0.3rem 0.65rem;
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
  font-family: var(--font-bn);

  .tag-label {
    color: var(--color-text-muted);
  }

  .tag-value {
    color: var(--color-text);
  }
}

.hero-stats-column {
  display: flex;
  gap: 0.75rem;
  border-left: 1px solid var(--color-border-light);
  padding-left: 1.5rem;

  @media (max-width: 900px) {
    border-left: none;
    padding-left: 0;
    width: 100%;
    justify-content: space-between;
  }
}

.stat-pill-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: var(--color-bg);
  border: 1px solid var(--color-border-light);
  padding: 0.75rem 1rem;
  border-radius: var(--radius-sm);
  min-width: 100px;
  text-align: center;
}

.stat-box-label {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  font-family: var(--font-bn);
  margin-bottom: 0.25rem;
}

.stat-box-val {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--color-primary);
  font-family: var(--font-bn);
}

/* Tabs */
.tabs-nav {
  display: inline-flex;
  gap: 0.35rem;
  background: var(--color-bg-card);
  padding: 0.35rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
  box-shadow: var(--elevation-1);
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.6rem 1.15rem;
  border-radius: var(--radius-sm);
  border: 1px solid transparent;
  background: transparent;
  color: var(--color-text-light);
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  font-weight: var(--weight-medium);
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    color: var(--color-text);
    background: var(--color-bg-muted);
  }

  &.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: #ffffff;
    font-weight: var(--weight-bold);
    box-shadow: 0 2px 8px rgba(20, 80, 50, 0.25);
  }
}

.tab-badge {
  background: rgba(255, 255, 255, 0.25);
  color: inherit;
  font-size: 0.72rem;
  padding: 0.1rem 0.4rem;
  border-radius: 99px;
  font-weight: 700;
}

/* Info Cards Grid */
.profile-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;

  @media (max-width: 840px) {
    grid-template-columns: 1fr;
  }
}

.info-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: var(--elevation-1);

  &.full-width {
    grid-column: span 2;
    @media (max-width: 840px) {
      grid-column: span 1;
    }
  }
}

.card-header-clean {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
  background: var(--color-bg-muted);
}

.header-icon-title {
  display: flex;
  align-items: center;
  gap: 0.65rem;

  h3 {
    margin: 0;
    font-size: var(--text-md);
    font-weight: 700;
    color: var(--color-text);
    font-family: var(--font-bn);
  }
}

.section-icon {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-sm);
  display: inline-flex;
  align-items: center;
  justify-content: center;

  &.green { background: rgba(20, 80, 50, 0.12); color: var(--color-primary); }
  &.blue { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
  &.amber { background: rgba(245, 158, 11, 0.12); color: #d97706; }
  &.purple { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
}

.data-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--color-border-light);
  font-family: var(--font-bn);
  font-size: var(--text-sm);

  &:last-child {
    border-bottom: none;
  }
}

.data-label {
  color: var(--color-text-muted);
  font-weight: 500;
}

.data-val {
  color: var(--color-text);
  font-weight: 500;
}

.font-medium {
  font-weight: 600;
}

.mono-badge {
  font-family: monospace;
  font-size: 0.85rem;
  background: var(--color-bg-muted);
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  border: 1px solid var(--color-border-light);
  font-weight: 600;
  color: var(--color-primary);
}

.blood-badge {
  background: rgba(220, 38, 38, 0.1);
  color: #dc2626;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  font-size: 0.85rem;
}

.grid-contact {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;

  @media (max-width: 650px) {
    grid-template-columns: 1fr;
  }
}

.contact-item {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  background: var(--color-bg);
  padding: 0.85rem 1rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border-light);

  &.full {
    grid-column: span 2;
    @media (max-width: 650px) {
      grid-column: span 1;
    }
  }
}

.contact-label {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.contact-value {
  font-size: var(--text-sm);
  color: var(--color-text);
  font-family: var(--font-bn);
  font-weight: 600;
}

.contact-address {
  margin: 0;
  font-size: var(--text-sm);
  color: var(--color-text);
  font-family: var(--font-bn);
  line-height: 1.5;
}

/* Toolbar in Tables */
.toolbar-title h3 {
  margin: 0;
  color: var(--color-text);
  font-family: var(--font-bn);
}

/* ID Card Modal Styling */
.id-card-modal {
  max-width: 520px;
}

.id-card-preview {
  background: #ffffff;
  border: 2px solid #145032;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--elevation-2);
}

.id-card-top-strip {
  background: linear-gradient(135deg, #145032 0%, #186640 100%);
  color: #ffffff;
  padding: 0.85rem 1rem;
}

.id-madrasa-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.id-madrasa-logo {
  font-size: 1.75rem;
}

.id-madrasa-titles {
  h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    font-family: var(--font-bn);
  }
  small {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.75rem;
    font-family: var(--font-bn);
  }
}

.id-card-body {
  display: flex;
  padding: 1.25rem;
  gap: 1.25rem;
  align-items: center;
}

.id-avatar-box {
  width: 90px;
  height: 105px;
  border: 2px solid #145032;
  border-radius: 6px;
  overflow: hidden;
  flex-shrink: 0;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.id-avatar-placeholder {
  width: 100%;
  height: 100%;
  background: #e8f3ec;
  color: #145032;
  display: grid;
  place-items: center;
  font-size: 2.25rem;
  font-weight: 700;
  font-family: var(--font-bn);
}

.id-info-box {
  flex: 1;
}

.id-student-name {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: #145032;
  font-family: var(--font-bn);
}

.id-student-en {
  margin: 0.1rem 0 0.5rem;
  font-size: 0.8rem;
  color: #6b7280;
}

.id-meta-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.35rem;
  font-size: 0.8rem;
  font-family: var(--font-bn);

  span {
    color: #6b7280;
  }
}

.id-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.65rem 1rem;
  background: #f8fafc;
  border-top: 1px dashed #cbd5e1;
}

.id-barcode-mockup {
  font-family: monospace;
  font-size: 0.85rem;
  letter-spacing: 2px;
  color: #334155;
  font-weight: bold;
}

.id-signature-box {
  font-size: 0.72rem;
  color: #64748b;
  font-family: var(--font-bn);
  border-top: 1px solid #94a3b8;
  padding-top: 0.2rem;
}

.delete-modal-card {
  max-width: 440px;
}

@media print {
  body * {
    visibility: hidden;
  }
  #printable-id-card, #printable-id-card * {
    visibility: visible;
  }
  #printable-id-card {
    position: absolute;
    left: 0;
    top: 0;
    width: 320px;
  }
}
</style>
