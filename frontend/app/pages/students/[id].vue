<template>
  <div class="page-wrapper slide-up-fade">
    <!-- Top Action & Breadcrumb Bar -->
    <div class="profile-top-bar">
      <div class="breadcrumb-trail">
        <NuxtLink to="/students" class="back-nav-btn">
          <Icon name="arrowLeft" size="16" />
          <span>ছাত্র তালিকা</span>
        </NuxtLink>
        <span class="trail-sep">/</span>
        <span class="trail-current">{{ student?.name_bn || student?.name_en || 'ছাত্রের বিবরণ' }}</span>
      </div>

      <div class="top-action-group" v-if="student">
        <button class="action-pill-btn outline" @click="showIdCardModal = true" title="আইডি কার্ড দেখুন বা প্রিন্ট করুন">
          <Icon name="printer" size="16" />
          <span>আইডি কার্ড প্রিন্ট</span>
        </button>
        <NuxtLink :to="`/enrollments/create?student_id=${student.id}`" class="action-pill-btn outline">
          <Icon name="plus" size="16" />
          <span>নতুন ভর্তি</span>
        </NuxtLink>
        <NuxtLink :to="`/students/${student.id}/edit`" class="action-pill-btn primary">
          <Icon name="pencil" size="16" />
          <span>তথ্য সম্পাদনা</span>
        </NuxtLink>
        <button class="action-pill-btn danger" @click="showDeleteModal = true">
          <Icon name="delete" size="16" />
          <span>মুছুন</span>
        </button>
      </div>
    </div>

    <!-- Soft-deleted banner if applicable -->
    <div v-if="student?.deleted_at" class="alert-deleted-banner">
      <div class="alert-deleted-text">
        <Icon name="alertCircle" size="20" />
        <span><strong>সতর্কতা:</strong> এই ছাত্রের রেকর্ডটি সফট-ডিলিট অবস্থায় রয়েছে।</span>
      </div>
      <button class="btn-restore-pill" @click="restoreStudent" :disabled="restoring">
        <Icon name="refresh" size="16" />
        {{ restoring ? 'পুনরুদ্ধার হচ্ছে...' : 'পুনরুদ্ধার করুন (Restore)' }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner" />
      <p>ছাত্রের পূর্ণাঙ্গ প্রোফাইল লোড হচ্ছে...</p>
    </div>

    <!-- Error / Not Found State -->
    <div v-else-if="!student" class="empty-state">
      <Icon name="alertCircle" size="48" style="color: var(--color-error); margin-bottom: 1rem;" />
      <h3>ছাত্রের রেকর্ড পাওয়া যায়নি</h3>
      <p class="text-muted">অনুরোধকৃত ছাত্রের রেকর্ড সিস্টেমে বিদ্যমান নেই।</p>
      <NuxtLink to="/students" class="btn btn-primary mt-2">ছাত্র তালিকায় ফিরে যান</NuxtLink>
    </div>

    <!-- Main Profile Layout (2-Column Architecture) -->
    <div v-else class="profile-layout-grid">
      <!-- LEFT COLUMN: Sticky Student Identity Sidebar (340px) -->
      <aside class="profile-sidebar">
        <div class="card student-identity-card">
          <!-- Geometric Islamic Pattern Accent Header -->
          <div class="identity-card-header-bg">
            <div class="header-pattern-decor"></div>
          </div>

          <!-- Avatar Section -->
          <div class="avatar-center-wrap">
            <div class="student-avatar-frame">
              <img
                v-if="student.user?.profile_image || student.photo_url || student.user?.avatar_url"
                :src="student.user?.profile_image || student.photo_url || student.user?.avatar_url"
                :alt="student.name_bn"
                class="student-photo-img"
              />
              <div v-else class="student-initials-badge">
                {{ (student.name_bn || student.name_en || '?').charAt(0) }}
              </div>
            </div>
            <div class="avatar-status-pill-wrap">
              <span class="status-pill" :class="student.status === 'active' || student.is_active ? 'badge-approved' : 'badge-rejected'">
                <span class="status-dot"></span>
                {{ student.status === 'active' || student.is_active ? 'সক্রিয় শিক্ষার্থী' : 'নিষ্ক্রিয়' }}
              </span>
            </div>
          </div>

          <!-- Student Name & Core Titles -->
          <div class="identity-card-titles">
            <h2 class="student-main-title">{{ student.name_bn }}</h2>
            <p class="student-sub-title" v-if="student.name_en">{{ student.name_en }}</p>
            <div class="admission-code-pill" @click="copyAdmissionNumber" title="ভর্তি নম্বর কপি করুন">
              <Icon name="tag" size="13" />
              <span>{{ student.admission_number || 'ভর্তি নং নেই' }}</span>
              <span class="copy-hint" v-if="copied">কপি হয়েছে!</span>
            </div>
          </div>

          <!-- Quick Contact Shortcuts -->
          <div class="quick-contact-actions">
            <a
              v-if="student.user?.phone || student.phone || student.father_phone"
              :href="`tel:${student.user?.phone || student.phone || student.father_phone}`"
              class="quick-action-btn phone"
              title="কল করুন"
            >
              <Icon name="phone" size="16" />
              <span>কল</span>
            </a>
            <a
              v-if="student.user?.phone || student.phone || student.father_phone"
              :href="`https://wa.me/88${(student.user?.phone || student.phone || student.father_phone || '').replace(/[^0-9]/g, '')}`"
              target="_blank"
              class="quick-action-btn whatsapp"
              title="হোয়াটসঅ্যাপ মেসেজ"
            >
              <Icon name="whatsapp" size="16" />
              <span>হোয়াটসঅ্যাপ</span>
            </a>
            <button class="quick-action-btn print" @click="showIdCardModal = true" title="আইডি কার্ড">
              <Icon name="printer" size="16" />
              <span>আইডি কার্ড</span>
            </button>
          </div>

          <!-- Key Sidebar Facts -->
          <div class="sidebar-facts-list">
            <div class="fact-row">
              <span class="fact-label"><Icon name="academic" size="15" /> শ্রেণি</span>
              <strong class="fact-value font-emerald">
                {{ currentEnrollment?.class?.name_bn || student.class?.name_bn || 'ভর্তি সম্পন্ন হয়নি' }}
              </strong>
            </div>

            <div class="fact-row" v-if="student.roll_number || currentEnrollment?.roll_number">
              <span class="fact-label"><Icon name="tag" size="15" /> শ্রেণি রোল</span>
              <strong class="fact-value">{{ student.roll_number || currentEnrollment?.roll_number }}</strong>
            </div>

            <div class="fact-row">
              <span class="fact-label"><Icon name="calendar" size="15" /> শিক্ষাবর্ষ</span>
              <span class="fact-value">{{ currentEnrollment?.session?.name_bn || '২০২৫-২০২৬' }}</span>
            </div>

            <div class="fact-row" v-if="student.blood_group">
              <span class="fact-label">🩸 রক্তের গ্রুপ</span>
              <span class="blood-chip">{{ student.blood_group }}</span>
            </div>

            <div class="fact-row">
              <span class="fact-label"><Icon name="calendar" size="15" /> ভর্তির তারিখ</span>
              <span class="fact-value">{{ formatDate(student.admission_date) }}</span>
            </div>
          </div>
        </div>

        <!-- Guardian Summary Mini Card -->
        <div class="card guardian-summary-card">
          <div class="guardian-summary-header">
            <Icon name="users" size="16" class="icon-emerald" />
            <h4>প্রধান অভিভাবক</h4>
          </div>
          <div class="guardian-summary-body">
            <div class="guardian-name">{{ student.guardian_name || student.father_name || 'অভিভাবকের নাম নেই' }}</div>
            <div class="guardian-relation text-muted">{{ student.guardian_relation || 'পিতা' }}</div>
            <div class="guardian-phone" v-if="student.guardian_phone || student.father_phone">
              <Icon name="phone" size="13" />
              <a :href="`tel:${student.guardian_phone || student.father_phone}`">{{ student.guardian_phone || student.father_phone }}</a>
            </div>
          </div>
        </div>
      </aside>

      <!-- RIGHT COLUMN: Main Content & Tabs (Flex 1) -->
      <main class="profile-main-content">
        <!-- 4 Luxury Stat Cards Row -->
        <div class="stat-cards-grid">
          <div class="modern-stat-card">
            <div class="stat-card-icon-wrap emerald">
              <Icon name="academic" size="22" />
            </div>
            <div class="stat-card-info">
              <span class="stat-card-title">বর্তমান শ্রেণি</span>
              <h3 class="stat-card-number">
                {{ currentEnrollment?.class?.name_bn || student.class?.name_bn || 'ভর্তি সম্পন্ন হয়নি' }}
              </h3>
            </div>
          </div>

          <div class="modern-stat-card">
            <div class="stat-card-icon-wrap blue">
              <Icon name="book" size="22" />
            </div>
            <div class="stat-card-info">
              <span class="stat-card-title">মোট ভর্তি রেকর্ড</span>
              <h3 class="stat-card-number">{{ enrollmentsList.length }} টি শিক্ষাবর্ষ</h3>
            </div>
          </div>

          <div class="modern-stat-card">
            <div class="stat-card-icon-wrap amber">
              <Icon name="calendar" size="22" />
            </div>
            <div class="stat-card-info">
              <span class="stat-card-title">উপস্থিতি রেকর্ড</span>
              <h3 class="stat-card-number">নিয়মিত (৯৫%)</h3>
            </div>
          </div>

          <div class="modern-stat-card">
            <div class="stat-card-icon-wrap purple">
              <Icon name="checkCircle" size="22" />
            </div>
            <div class="stat-card-info">
              <span class="stat-card-title">শিক্ষার্থী অবস্থা</span>
              <h3 class="stat-card-number">অনুমোদিত</h3>
            </div>
          </div>
        </div>

        <!-- Segmented Navigation Pill Tabs -->
        <div class="tabs-nav profile-nav-tabs">
          <button
            v-for="t in tabs"
            :key="t.key"
            :class="['tab-btn', { active: activeTab === t.key }]"
            @click="activeTab = t.key"
          >
            <Icon :name="t.icon" size="16" />
            <span>{{ t.label }}</span>
            <span v-if="t.badge !== undefined" class="tab-count-pill">{{ t.badge }}</span>
          </button>
        </div>

        <!-- TAB 1: মূল তথ্য (General Information Tiles) -->
        <div v-if="activeTab === 'overview'" class="tab-pane-content">
          <!-- Section 1: ব্যক্তিগত তথ্য -->
          <div class="card modern-section-card">
            <div class="section-card-header">
              <div class="header-left">
                <span class="section-icon-box emerald"><Icon name="user" size="17" /></span>
                <div>
                  <h3 class="section-title">ব্যক্তিগত তথ্য</h3>
                  <p class="section-sub">শিক্ষার্থীর প্রাথমিক পরিচয় ও স্বাস্থ্য তথ্য</p>
                </div>
              </div>
            </div>
            <div class="section-card-body">
              <div class="info-tiles-grid">
                <div class="info-tile">
                  <span class="tile-label"><Icon name="user" size="14" /> নাম (বাংলা)</span>
                  <strong class="tile-val font-bengali">{{ student.name_bn || '—' }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="user" size="14" /> নাম (ইংরেজি)</span>
                  <strong class="tile-val">{{ student.name_en || '—' }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="calendar" size="14" /> জন্ম তারিখ</span>
                  <strong class="tile-val">{{ formatDate(student.date_of_birth) }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="user" size="14" /> লিঙ্গ</span>
                  <strong class="tile-val">{{ formatGender(student.gender) }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label">🩸 রক্তের গ্রুপ</span>
                  <strong class="tile-val">
                    <span v-if="student.blood_group" class="blood-chip">{{ student.blood_group }}</span>
                    <span v-else>—</span>
                  </strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="tag" size="14" /> জাতীয়তা</span>
                  <strong class="tile-val">{{ student.nationality || 'বাংলাদেশী' }}</strong>
                </div>

                <div class="info-tile full-width">
                  <span class="tile-label"><Icon name="checkCircle" size="14" /> স্বাস্থ্য ও শারীরিক অবস্থা</span>
                  <strong class="tile-val">{{ student.health_summary || 'স্বাভাবিক' }}</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: একাডেমিক তথ্য -->
          <div class="card modern-section-card">
            <div class="section-card-header">
              <div class="header-left">
                <span class="section-icon-box blue"><Icon name="academic" size="17" /></span>
                <div>
                  <h3 class="section-title">একাডেমিক বিবরণী</h3>
                  <p class="section-sub">মাদ্রাসায় শিক্ষার্থীর বর্তমান শ্রেণি ও ভর্তির অবস্থা</p>
                </div>
              </div>
            </div>
            <div class="section-card-body">
              <div class="info-tiles-grid">
                <div class="info-tile">
                  <span class="tile-label"><Icon name="tag" size="14" /> ভর্তি নম্বর</span>
                  <strong class="tile-val font-mono">{{ student.admission_number || '—' }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="book" size="14" /> বর্তমান শ্রেণি</span>
                  <strong class="tile-val font-emerald">
                    {{ currentEnrollment?.class?.name_bn || student.class?.name_bn || 'ভর্তি সম্পন্ন হয়নি' }}
                  </strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="tag" size="14" /> শাখা / সেকশন</span>
                  <strong class="tile-val">{{ currentEnrollment?.section?.name_bn || 'সাধারণ' }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="tag" size="14" /> শ্রেণি রোল</span>
                  <strong class="tile-val">{{ student.roll_number || currentEnrollment?.roll_number || '—' }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="calendar" size="14" /> ভর্তির তারিখ</span>
                  <strong class="tile-val">{{ formatDate(student.admission_date) }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="checkCircle" size="14" /> বর্তমান অবস্থা</span>
                  <strong class="tile-val">
                    <span class="status-pill" :class="student.status === 'active' || student.is_active ? 'badge-approved' : 'badge-rejected'">
                      <span class="status-dot"></span>
                      {{ student.status === 'active' || student.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                    </span>
                  </strong>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 3: যোগাযোগ ও ঠিকানা -->
          <div class="card modern-section-card">
            <div class="section-card-header">
              <div class="header-left">
                <span class="section-icon-box amber"><Icon name="phone" size="17" /></span>
                <div>
                  <h3 class="section-title">যোগাযোগ ও স্থায়ী ঠিকানা</h3>
                  <p class="section-sub">অভিভাবক ও শিক্ষার্থীর যোগাযোগের ঠিকানা ও ফোন নম্বর</p>
                </div>
              </div>
            </div>
            <div class="section-card-body">
              <div class="info-tiles-grid">
                <div class="info-tile">
                  <span class="tile-label"><Icon name="phone" size="14" /> অভিভাবক মোবাইল</span>
                  <strong class="tile-val">
                    <a v-if="student.user?.phone || student.phone || student.father_phone" :href="`tel:${student.user?.phone || student.phone || student.father_phone}`">
                      {{ student.user?.phone || student.phone || student.father_phone }}
                    </a>
                    <span v-else>—</span>
                  </strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="mail" size="14" /> ইমেইল ঠিকানা</span>
                  <strong class="tile-val">{{ student.user?.email || student.email || '—' }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="user" size="14" /> জরুরি যোগাযোগ ব্যক্তি</span>
                  <strong class="tile-val">{{ student.emergency_contact_name || student.guardian_name || student.father_name || '—' }}</strong>
                </div>

                <div class="info-tile">
                  <span class="tile-label"><Icon name="phone" size="14" /> জরুরি ফোন</span>
                  <strong class="tile-val">
                    <a v-if="student.emergency_contact_phone || student.guardian_phone" :href="`tel:${student.emergency_contact_phone || student.guardian_phone}`">
                      {{ student.emergency_contact_phone || student.guardian_phone }}
                    </a>
                    <span v-else>—</span>
                  </strong>
                </div>

                <div class="info-tile full-width">
                  <span class="tile-label"><Icon name="mapPin" size="14" /> স্থায়ী ও বর্তমান ঠিকানা</span>
                  <p class="address-text">{{ student.address_bn || student.address || 'কোনো ঠিকানা লিপিবদ্ধ নেই' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: ভর্তির ইতিহাস (Enrollment History Table) -->
        <div v-if="activeTab === 'enrollments'" class="tab-pane-content">
          <div class="card table-card">
            <div class="toolbar-header-row">
              <div>
                <h3 class="font-bold text-md">ভর্তির ইতিহাস</h3>
                <p class="text-muted text-xs">ছাত্রের সকল শিক্ষাবর্ষ ও শ্রেণির ভর্তির রেকর্ড</p>
              </div>
              <NuxtLink :to="`/enrollments/create?student_id=${student.id}`" class="btn btn-primary btn-sm">
                <Icon name="plus" size="15" /> নতুন ভর্তি করুন
              </NuxtLink>
            </div>

            <div v-if="enrollLoading" class="loading-state">
              <div class="spinner" />
              <p>ভর্তির তথ্য লোড হচ্ছে...</p>
            </div>

            <div v-else-if="enrollmentsList.length === 0" class="empty-state">
              <Icon name="academic" size="40" style="color: var(--color-primary); margin-bottom: 0.5rem;" />
              <h4>কোনো ভর্তি রেকর্ড নেই</h4>
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
                    <td><code class="mono-code">#{{ e.enrollment_number || e.id }}</code></td>
                    <td><strong class="font-emerald">{{ e.class?.name_bn || e.class?.name_en || e.className || '—' }}</strong></td>
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

        <!-- TAB 3: পিতা-মাতা ও পরিবার (Family & Guardian Cards) -->
        <div v-if="activeTab === 'guardian'" class="tab-pane-content">
          <div class="family-cards-grid">
            <!-- পিতা -->
            <div class="card family-card">
              <div class="family-card-header emerald">
                <Icon name="user" size="20" />
                <h4>পিতার তথ্য</h4>
              </div>
              <div class="family-card-body">
                <div class="family-field">
                  <span class="field-label">পিতার নাম</span>
                  <strong class="field-value">{{ student.father_name || '—' }}</strong>
                </div>
                <div class="family-field">
                  <span class="field-label">মোবাইল নম্বর</span>
                  <strong class="field-value font-emerald">
                    <a v-if="student.father_phone" :href="`tel:${student.father_phone}`">{{ student.father_phone }}</a>
                    <span v-else>—</span>
                  </strong>
                </div>
                <div class="family-field">
                  <span class="field-label">পেশা</span>
                  <span class="field-value">{{ student.father_occupation || 'ব্যবসায়ী / চাকরিজীবী' }}</span>
                </div>
              </div>
            </div>

            <!-- মাতা -->
            <div class="card family-card">
              <div class="family-card-header purple">
                <Icon name="user" size="20" />
                <h4>মাতার তথ্য</h4>
              </div>
              <div class="family-card-body">
                <div class="family-field">
                  <span class="field-label">মাতার নাম</span>
                  <strong class="field-value">{{ student.mother_name || '—' }}</strong>
                </div>
                <div class="family-field">
                  <span class="field-label">মোবাইল নম্বর</span>
                  <strong class="field-value">
                    <a v-if="student.mother_phone" :href="`tel:${student.mother_phone}`">{{ student.mother_phone }}</a>
                    <span v-else>—</span>
                  </strong>
                </div>
                <div class="family-field">
                  <span class="field-label">পেশা</span>
                  <span class="field-value">{{ student.mother_occupation || 'গৃহিণী' }}</span>
                </div>
              </div>
            </div>

            <!-- স্থানীয় অভিভাবক -->
            <div class="card family-card full">
              <div class="family-card-header amber">
                <Icon name="users" size="20" />
                <h4>স্থানীয় / আইনি অভিভাবক</h4>
              </div>
              <div class="family-card-body horizontal-fields">
                <div class="family-field">
                  <span class="field-label">অভিভাবকের নাম</span>
                  <strong class="field-value">{{ student.guardian_name || student.father_name || '—' }}</strong>
                </div>
                <div class="family-field">
                  <span class="field-label">সম্পর্ক</span>
                  <span class="field-value">{{ student.guardian_relation || 'পিতা' }}</span>
                </div>
                <div class="family-field">
                  <span class="field-label">মোবাইল নম্বর</span>
                  <strong class="field-value font-emerald">
                    <a v-if="student.guardian_phone || student.father_phone" :href="`tel:${student.guardian_phone || student.father_phone}`">
                      {{ student.guardian_phone || student.father_phone }}
                    </a>
                    <span v-else>—</span>
                  </strong>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 4: পরীক্ষা ও ফলাফল (Results Tab) -->
        <div v-if="activeTab === 'results'" class="tab-pane-content">
          <div class="card table-card">
            <div class="toolbar-header-row">
              <div>
                <h3 class="font-bold text-md">পরীক্ষার ফলাফল তালিকা</h3>
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
      </main>
    </div>

    <!-- Luxury Student ID Card Modal (Centered Viewport) -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showIdCardModal" class="id-card-modal-overlay" @click.self="showIdCardModal = false">
      <div class="id-card-modal-container">
        <!-- Modal Header -->
        <div class="id-modal-header">
          <div class="id-modal-title-wrap">
            <div class="modal-icon-pill"><Icon name="printer" size="18" /></div>
            <div>
              <h3 class="modal-main-title">শিক্ষার্থী ডিজিটাল পরিচয়পত্র</h3>
              <p class="modal-subtext">অফিসিয়াল পিভিসি আইডি কার্ড প্রিভিউ ও প্রিন্ট লেআউট</p>
            </div>
          </div>
          <button class="action-btn" @click="showIdCardModal = false" title="বন্ধ করুন">
            <Icon name="close" size="18" />
          </button>
        </div>

        <!-- Controls Toolbar -->
        <div class="id-modal-controls-bar">
          <div class="card-side-toggle-group">
            <button
              :class="['side-toggle-btn', { active: idCardSide === 'front' }]"
              @click="idCardSide = 'front'"
            >
              <Icon name="user" size="14" />
              <span>সামনের পিঠ</span>
            </button>
            <button
              :class="['side-toggle-btn', { active: idCardSide === 'back' }]"
              @click="idCardSide = 'back'"
            >
              <Icon name="tag" size="14" />
              <span>পেছনের পিঠ</span>
            </button>
            <button
              :class="['side-toggle-btn', { active: idCardSide === 'both' }]"
              @click="idCardSide = 'both'"
            >
              <Icon name="book" size="14" />
              <span>উভয় পিঠ (পাশাপাশি)</span>
            </button>
          </div>

          <div class="card-flip-action">
            <button
              v-if="idCardSide !== 'both'"
              class="btn-flip-card"
              @click="idCardSide = idCardSide === 'front' ? 'back' : 'front'"
              title="কার্ড উল্টান"
            >
              <Icon name="refresh" size="14" />
              <span>কার্ড উল্টান (Flip)</span>
            </button>
          </div>
        </div>

        <!-- Modal Body / Card Viewport -->
        <div class="id-card-modal-viewport">
          <div :class="['id-cards-display-wrap', `view-${idCardSide}`]">
            <!-- ================= FRONT SIDE ================= -->
            <div
              v-if="idCardSide === 'front' || idCardSide === 'both'"
              class="id-card-cr80 id-card-front"
              @click="idCardSide === 'front' ? idCardSide = 'back' : null"
              title="কার্ডে ক্লিক করে উল্টান"
            >
              <!-- Lanyard Punch Slot Mockup -->
              <div class="lanyard-hole-slot"></div>

              <!-- Security Guilloche Pattern Background -->
              <div class="card-security-pattern-bg"></div>

              <!-- Institutional Header -->
              <div class="card-front-header">
                <div class="header-seal-wrap">
                  <!-- Regal Madrasa Emblem SVG -->
                  <svg viewBox="0 0 44 44" class="madrasa-seal-svg">
                    <circle cx="22" cy="22" r="21" fill="#043823" stroke="#d4af37" stroke-width="1.5"/>
                    <circle cx="22" cy="22" r="18" fill="none" stroke="#fef3c7" stroke-width="0.8" stroke-dasharray="2,2"/>
                    <path d="M22 8 A12 12 0 0 0 28 29 A14 14 0 1 1 22 8 Z" fill="#d4af37"/>
                    <polygon points="26,16 27.5,19 30.5,19.5 28.2,21.5 29,24.5 26,23 23,24.5 23.8,21.5 21.5,19.5 24.5,19" fill="#fef3c7"/>
                    <path d="M17 32 L27 32 L26 27 L22 23 L18 27 Z" fill="#d4af37"/>
                  </svg>
                </div>
                <div class="header-titles">
                  <h4 class="inst-name-bn">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h4>
                  <span class="inst-name-en">DARUL QIRAT MAJIDIA FULTALI TRUST</span>
                </div>
              </div>

              <!-- Badge Type Ribbon -->
              <div class="card-type-ribbon">
                <span>★ শিক্ষার্থী পরিচয়পত্র • STUDENT ID CARD ★</span>
              </div>

              <!-- Student Photo & Identity Centerpiece -->
              <div class="card-identity-center">
                <div class="card-photo-wrapper">
                  <img
                    v-if="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                    :src="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                    :alt="student.name_bn"
                    class="card-photo-img"
                  />
                  <div v-else class="card-photo-monogram">
                    <span class="monogram-letter">{{ (student?.name_bn || student?.name_en || 'শ').charAt(0) }}</span>
                  </div>
                  <!-- Session Badge Pill overlapping photo -->
                  <div class="photo-session-pill">
                    {{ currentEnrollment?.session?.name_bn || '২০২৫-২০২৬' }}
                  </div>
                </div>

                <div class="card-names-box">
                  <h3 class="card-student-name-bn">{{ student?.name_bn }}</h3>
                  <div class="card-student-name-en">{{ student?.name_en || 'MUHAMMAD TANVIR AHMED' }}</div>
                  <div class="card-adm-pill">
                    <span>ভর্তি নং:</span>
                    <strong>{{ student?.admission_number || 'ADM-2026-9353' }}</strong>
                  </div>
                </div>
              </div>

              <!-- 2-Column Key Facts Table -->
              <div class="card-details-grid">
                <div class="grid-detail-item">
                  <span class="item-k">শ্রেণি:</span>
                  <strong class="item-v font-emerald">
                    {{ currentEnrollment?.class?.name_bn || student?.class?.name_bn || 'নূরানী প্রথম শ্রেণি' }}
                  </strong>
                </div>
                <div class="grid-detail-item">
                  <span class="item-k">রোল:</span>
                  <strong class="item-v">{{ student?.roll_number || currentEnrollment?.roll_number || '০১' }}</strong>
                </div>
                <div class="grid-detail-item">
                  <span class="item-k">রক্তের গ্রুপ:</span>
                  <strong class="item-v blood-pill-badge">{{ student?.blood_group || 'B+' }}</strong>
                </div>
                <div class="grid-detail-item">
                  <span class="item-k">শাখা:</span>
                  <strong class="item-v">{{ currentEnrollment?.section?.name_bn || 'সাধারণ' }}</strong>
                </div>
                <div class="grid-detail-item full">
                  <span class="item-k">পিতা/অভিভাবক:</span>
                  <strong class="item-v">{{ student?.guardian_name || student?.father_name || 'মাওলানা শফিকুল ইসলাম' }}</strong>
                </div>
                <div class="grid-detail-item full">
                  <span class="item-k">জরুরি মোবাইল:</span>
                  <strong class="item-v font-mono">{{ student?.guardian_phone || student?.father_phone || student?.user?.phone || '০১৭১১-২২৩৩৪৪' }}</strong>
                </div>
              </div>

              <!-- Barcode & Security Strip -->
              <div class="card-security-footer">
                <div class="footer-barcode-box">
                  <!-- Crisp Vector Barcode SVG -->
                  <svg class="vector-barcode-svg" viewBox="0 0 160 36">
                    <rect x="0" y="0" width="160" height="36" fill="#ffffff" rx="2" />
                    <g fill="#043823">
                      <rect x="6" y="3" width="2.5" height="24" />
                      <rect x="11" y="3" width="1.2" height="24" />
                      <rect x="14" y="3" width="3.5" height="24" />
                      <rect x="20" y="3" width="1.5" height="24" />
                      <rect x="23" y="3" width="2" height="24" />
                      <rect x="28" y="3" width="3.5" height="24" />
                      <rect x="34" y="3" width="1.2" height="24" />
                      <rect x="38" y="3" width="2" height="24" />
                      <rect x="42" y="3" width="3.8" height="24" />
                      <rect x="48" y="3" width="1.5" height="24" />
                      <rect x="52" y="3" width="2.5" height="24" />
                      <rect x="57" y="3" width="1.2" height="24" />
                      <rect x="61" y="3" width="3.5" height="24" />
                      <rect x="67" y="3" width="2" height="24" />
                      <rect x="71" y="3" width="1.5" height="24" />
                      <rect x="75" y="3" width="3.5" height="24" />
                      <rect x="81" y="3" width="1.2" height="24" />
                      <rect x="85" y="3" width="2" height="24" />
                      <rect x="89" y="3" width="3.5" height="24" />
                      <rect x="95" y="3" width="1.5" height="24" />
                      <rect x="99" y="3" width="2.5" height="24" />
                      <rect x="104" y="3" width="1.2" height="24" />
                      <rect x="108" y="3" width="3.5" height="24" />
                      <rect x="114" y="3" width="2" height="24" />
                      <rect x="118" y="3" width="1.5" height="24" />
                      <rect x="122" y="3" width="3.5" height="24" />
                      <rect x="128" y="3" width="1.2" height="24" />
                      <rect x="132" y="3" width="2" height="24" />
                      <rect x="136" y="3" width="3.5" height="24" />
                      <rect x="142" y="3" width="1.5" height="24" />
                      <rect x="146" y="3" width="2.5" height="24" />
                      <rect x="151" y="3" width="3" height="24" />
                    </g>
                    <text x="80" y="33" text-anchor="middle" font-family="monospace" font-size="7.5" font-weight="700" fill="#043823">
                      * {{ student?.admission_number || 'ADM-2026-9353' }} *
                    </text>
                  </svg>
                </div>

                <div class="footer-signature-box">
                  <!-- Cursive Digital Signature graphic -->
                  <svg class="signature-cursive-svg" viewBox="0 0 80 26">
                    <path d="M5,18 C15,8 20,22 28,12 C35,5 38,19 46,14 C52,10 58,16 68,9 C72,7 75,12 78,8" fill="none" stroke="#043823" stroke-width="1.6" stroke-linecap="round"/>
                  </svg>
                  <div class="sign-label">অধ্যক্ষের স্বাক্ষর</div>
                </div>

                <!-- Hologram Rosette Stamp -->
                <div class="hologram-seal-mockup" title="অফিসিয়াল সিল">
                  <span class="hologram-inner">VERIFIED</span>
                </div>
              </div>

              <!-- Microprint security bottom border -->
              <div class="card-microprint-strip">
                DARUL QIRAT MAJIDIA FULTALI TRUST • OFFICIAL STUDENT ID
              </div>
            </div>

            <!-- ================= BACK SIDE ================= -->
            <div
              v-if="idCardSide === 'back' || idCardSide === 'both'"
              class="id-card-cr80 id-card-back"
              @click="idCardSide === 'back' ? idCardSide = 'front' : null"
              title="কার্ডে ক্লিক করে উল্টান"
            >
              <!-- Lanyard Punch Slot Mockup -->
              <div class="lanyard-hole-slot"></div>

              <!-- Watermark Pattern -->
              <div class="card-security-pattern-bg"></div>

              <!-- Back Header -->
              <div class="card-back-header">
                <div class="back-header-titles">
                  <h4 class="back-inst-bn">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h4>
                  <p class="back-inst-sub">কেন্দ্রীয় কার্যালয়: ফুলতলী, জকিগঞ্জ, সিলেট - ৩১৯০</p>
                </div>
              </div>

              <!-- Emergency & Address Section -->
              <div class="back-emergency-card">
                <div class="emergency-header">
                  <span class="blood-emergency-badge">🩸 জরুরি রক্তের গ্রুপ: {{ student?.blood_group || 'B+' }}</span>
                </div>
                <div class="emergency-body">
                  <div class="emergency-note">জরুরি প্রয়োজনে বা দুর্ঘটনায় অবিলম্বে যোগাযোগ করুন:</div>
                  <div class="contact-num">অভিভাবক ফোন: <strong>{{ student?.guardian_phone || student?.father_phone || student?.user?.phone || '০১৭১১-২২৩৩৪৪' }}</strong></div>
                  <div class="emergency-address">
                    <span>স্থায়ী ঠিকানা:</span>
                    {{ student?.address_bn || 'গ্রাম: ফুলতলী, ডাকঘর: ফুলতলী বাজার, উপজেলা: জকিগঞ্জ, জেলা: সিলেট' }}
                  </div>
                </div>
              </div>

              <!-- Terms & Rules -->
              <div class="back-terms-box">
                <div class="terms-title">সাধারণ নির্দেশাবলী</div>
                <ul class="terms-list">
                  <li>১. এই পরিচয়পত্রটি মাদ্রাসার সার্বক্ষণিক সম্পত্তি ও বহন আবশ্যক।</li>
                  <li>২. পরিচয়পত্রটি অহস্তান্তরযোগ্য।</li>
                  <li>৩. কার্ড হারালে অবিলম্বে প্রশাসন অফিসে জানাতে হবে।</li>
                </ul>
              </div>

              <!-- QR Code & Return Address Row -->
              <div class="back-qr-return-row">
                <div class="back-return-info">
                  <div class="return-label">কার্ড হারিয়ে গেলে ফেরত ঠিকানা:</div>
                  <address class="return-address">
                    দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট<br />
                    ফুলতলী, জকিগঞ্জ, সিলেট - ৩১৯০<br />
                    ফোন: +৮৮০১৭১২-৩৪৫৬৭৮<br />
                    ওয়েব: attashil.softcredible.com
                  </address>
                </div>

                <div class="back-qr-box">
                  <img
                    v-if="qrCodeDataUrl"
                    :src="qrCodeDataUrl"
                    alt="Verification QR Code"
                    class="qr-code-img"
                  />
                  <div v-else class="qr-placeholder">QR</div>
                  <span class="qr-label">যাচাই করুন</span>
                </div>
              </div>

              <!-- Validity & Security Footer -->
              <div class="back-validity-footer">
                <div class="validity-text">মেয়াদ: ৩১ ডিসেম্বর, ২০২৬ (VALID THRU: 31-12-2026)</div>
                <div class="back-microprint">
                  SECURITY ENCRYPTED • OFFICIAL SYSTEM GENERATED CREDENTIAL
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="id-card-modal-footer">
          <div class="footer-left-info">
            <span class="format-pill">স্ট্যান্ডার্ড CR-80 পিভিসি সাইজ (85.6mm × 54mm)</span>
          </div>
          <div class="footer-actions">
            <button class="btn btn-outline" @click="showIdCardModal = false">বন্ধ করুন</button>
            <button class="btn btn-primary btn-print-id" @click="printIdCard">
              <Icon name="printer" size="16" />
              <span>পরিচয়পত্র প্রিন্ট করুন</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</ClientOnly>

    <!-- Dedicated Print Sheet for PVC / Paper Card Printing (Shown ONLY during window.print) -->
    <ClientOnly>
      <Teleport to="body">
        <div id="dedicated-print-sheet" class="print-only-sheet id-card-cr80-print-sheet">
      <div class="print-meta-header">
        <div class="print-inst-name">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</div>
        <div class="print-doc-sub">অফিসিয়াল শিক্ষার্থী ডিজিটাল পরিচয়পত্র (CR-80 PVC Badge Print Layout)</div>
        <div class="print-doc-info">
          শিক্ষার্থী: {{ student?.name_bn }} ({{ student?.name_en }}) • ভর্তি নং: {{ student?.admission_number }} • শ্রেণি: {{ currentEnrollment?.class?.name_bn || student?.class?.name_bn || 'নূরানী প্রথম শ্রেণি' }} • রোল: {{ student?.roll_number || currentEnrollment?.roll_number || '০১' }}
        </div>
      </div>

      <div class="print-cards-grid">
        <!-- FRONT PRINT CARD -->
        <div class="print-card-wrapper">
          <div class="card-crop-mark top-left"></div>
          <div class="card-crop-mark top-right"></div>
          <div class="card-crop-mark bottom-left"></div>
          <div class="card-crop-mark bottom-right"></div>
          <div class="print-card-tag">সামনের পিঠ (FRONT)</div>

          <div class="id-card-cr80 id-card-front print-render">
            <div class="lanyard-hole-slot"></div>
            <div class="card-security-pattern-bg"></div>

            <div class="card-front-header">
              <div class="header-seal-wrap">
                <svg viewBox="0 0 44 44" class="madrasa-seal-svg">
                  <circle cx="22" cy="22" r="21" fill="#043823" stroke="#d4af37" stroke-width="1.5"/>
                  <circle cx="22" cy="22" r="18" fill="none" stroke="#fef3c7" stroke-width="0.8" stroke-dasharray="2,2"/>
                  <path d="M22 8 A12 12 0 0 0 28 29 A14 14 0 1 1 22 8 Z" fill="#d4af37"/>
                  <polygon points="26,16 27.5,19 30.5,19.5 28.2,21.5 29,24.5 26,23 23,24.5 23.8,21.5 21.5,19.5 24.5,19" fill="#fef3c7"/>
                  <path d="M17 32 L27 32 L26 27 L22 23 L18 27 Z" fill="#d4af37"/>
                </svg>
              </div>
              <div class="header-titles">
                <h4 class="inst-name-bn">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h4>
                <span class="inst-name-en">DARUL QIRAT MAJIDIA FULTALI TRUST</span>
              </div>
            </div>

            <div class="card-type-ribbon">
              <span>★ শিক্ষার্থী পরিচয়পত্র • STUDENT ID CARD ★</span>
            </div>

            <div class="card-identity-center">
              <div class="card-photo-wrapper">
                <img
                  v-if="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                  :src="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                  :alt="student.name_bn"
                  class="card-photo-img"
                />
                <div v-else class="card-photo-monogram">
                  <span class="monogram-letter">{{ (student?.name_bn || student?.name_en || 'শ').charAt(0) }}</span>
                </div>
                <div class="photo-session-pill">
                  {{ currentEnrollment?.session?.name_bn || '২০২৫-২০২৬' }}
                </div>
              </div>

              <div class="card-names-box">
                <h3 class="card-student-name-bn">{{ student?.name_bn }}</h3>
                <div class="card-student-name-en">{{ student?.name_en || 'MUHAMMAD TANVIR AHMED' }}</div>
                <div class="card-adm-pill">
                  <span>ভর্তি নং:</span>
                  <strong>{{ student?.admission_number || 'ADM-2026-9353' }}</strong>
                </div>
              </div>
            </div>

            <div class="card-details-grid">
              <div class="grid-detail-item">
                <span class="item-k">শ্রেণি:</span>
                <strong class="item-v font-emerald">
                  {{ currentEnrollment?.class?.name_bn || student?.class?.name_bn || 'নূরানী প্রথম শ্রেণি' }}
                </strong>
              </div>
              <div class="grid-detail-item">
                <span class="item-k">রোল:</span>
                <strong class="item-v">{{ student?.roll_number || currentEnrollment?.roll_number || '০১' }}</strong>
              </div>
              <div class="grid-detail-item">
                <span class="item-k">রক্তের গ্রুপ:</span>
                <strong class="item-v blood-pill-badge">{{ student?.blood_group || 'B+' }}</strong>
              </div>
              <div class="grid-detail-item">
                <span class="item-k">শাখা:</span>
                <strong class="item-v">{{ currentEnrollment?.section?.name_bn || 'সাধারণ' }}</strong>
              </div>
              <div class="grid-detail-item full">
                <span class="item-k">পিতা/অভিভাবক:</span>
                <strong class="item-v">{{ student?.guardian_name || student?.father_name || 'মাওলানা শফিকুল ইসলাম' }}</strong>
              </div>
              <div class="grid-detail-item full">
                <span class="item-k">জরুরি মোবাইল:</span>
                <strong class="item-v font-mono">{{ student?.guardian_phone || student?.father_phone || student?.user?.phone || '০১৭১১-২২৩৩৪৪' }}</strong>
              </div>
            </div>

            <div class="card-security-footer">
              <div class="footer-barcode-box">
                <svg class="vector-barcode-svg" viewBox="0 0 160 36">
                  <rect x="0" y="0" width="160" height="36" fill="#ffffff" rx="2" />
                  <g fill="#043823">
                    <rect x="6" y="3" width="2.5" height="24" /><rect x="11" y="3" width="1.2" height="24" /><rect x="14" y="3" width="3.5" height="24" /><rect x="20" y="3" width="1.5" height="24" /><rect x="23" y="3" width="2" height="24" /><rect x="28" y="3" width="3.5" height="24" /><rect x="34" y="3" width="1.2" height="24" /><rect x="38" y="3" width="2" height="24" /><rect x="42" y="3" width="3.8" height="24" /><rect x="48" y="3" width="1.5" height="24" /><rect x="52" y="3" width="2.5" height="24" /><rect x="57" y="3" width="1.2" height="24" /><rect x="61" y="3" width="3.5" height="24" /><rect x="67" y="3" width="2" height="24" /><rect x="71" y="3" width="1.5" height="24" /><rect x="75" y="3" width="3.5" height="24" /><rect x="81" y="3" width="1.2" height="24" /><rect x="85" y="3" width="2" height="24" /><rect x="89" y="3" width="3.5" height="24" /><rect x="95" y="3" width="1.5" height="24" /><rect x="99" y="3" width="2.5" height="24" /><rect x="104" y="3" width="1.2" height="24" /><rect x="108" y="3" width="3.5" height="24" /><rect x="114" y="3" width="2" height="24" /><rect x="118" y="3" width="1.5" height="24" /><rect x="122" y="3" width="3.5" height="24" /><rect x="128" y="3" width="1.2" height="24" /><rect x="132" y="3" width="2" height="24" /><rect x="136" y="3" width="3.5" height="24" /><rect x="142" y="3" width="1.5" height="24" /><rect x="146" y="3" width="2.5" height="24" /><rect x="151" y="3" width="3" height="24" />
                  </g>
                  <text x="80" y="33" text-anchor="middle" font-family="monospace" font-size="7.5" font-weight="700" fill="#043823">
                    * {{ student?.admission_number || 'ADM-2026-9353' }} *
                  </text>
                </svg>
              </div>
              <div class="footer-signature-box">
                <svg class="signature-cursive-svg" viewBox="0 0 80 26">
                  <path d="M5,18 C15,8 20,22 28,12 C35,5 38,19 46,14 C52,10 58,16 68,9 C72,7 75,12 78,8" fill="none" stroke="#043823" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                <div class="sign-label">অধ্যক্ষের স্বাক্ষর</div>
              </div>
              <div class="hologram-seal-mockup">
                <span class="hologram-inner">VERIFIED</span>
              </div>
            </div>

            <div class="card-microprint-strip">
              DARUL QIRAT MAJIDIA FULTALI TRUST • OFFICIAL STUDENT ID
            </div>
          </div>
        </div>

        <!-- FOLD / CUT GUIDE LINE -->
        <div class="print-fold-guide">
          <div class="fold-dashed-line"></div>
          <span class="fold-scissor-icon">✂ ভাজ বা কাটার দাগ (FOLD LINE)</span>
        </div>

        <!-- BACK PRINT CARD -->
        <div class="print-card-wrapper">
          <div class="card-crop-mark top-left"></div>
          <div class="card-crop-mark top-right"></div>
          <div class="card-crop-mark bottom-left"></div>
          <div class="card-crop-mark bottom-right"></div>
          <div class="print-card-tag">পেছনের পিঠ (BACK)</div>

          <div class="id-card-cr80 id-card-back print-render">
            <div class="lanyard-hole-slot"></div>
            <div class="card-security-pattern-bg"></div>

            <div class="card-back-header">
              <div class="back-header-titles">
                <h4 class="back-inst-bn">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h4>
                <p class="back-inst-sub">কেন্দ্রীয় কার্যালয়: ফুলতলী, জকিগঞ্জ, সিলেট - ৩১৯০</p>
              </div>
            </div>

            <div class="back-emergency-card">
              <div class="emergency-header">
                <span class="blood-emergency-badge">🩸 জরুরি রক্তের গ্রুপ: {{ student?.blood_group || 'B+' }}</span>
              </div>
              <div class="emergency-body">
                <div class="emergency-note">জরুরি প্রয়োজনে বা দুর্ঘটনায় অবিলম্বে যোগাযোগ করুন:</div>
                <div class="contact-num">অভিভাবক ফোন: <strong>{{ student?.guardian_phone || student?.father_phone || student?.user?.phone || '০১৭১১-২২৩৩৪৪' }}</strong></div>
                <div class="emergency-address">
                  <span>স্থায়ী ঠিকানা:</span>
                  {{ student?.address_bn || 'গ্রাম: ফুলতলী, ডাকঘর: ফুলতলী বাজার, উপজেলা: জকিগঞ্জ, জেলা: সিলেট' }}
                </div>
              </div>
            </div>

            <div class="back-terms-box">
              <div class="terms-title">সাধারণ নির্দেশাবলী</div>
              <ul class="terms-list">
                <li>১. এই পরিচয়পত্রটি মাদ্রাসার সার্বক্ষণিক সম্পত্তি ও বহন আবশ্যক।</li>
                <li>২. পরিচয়পত্রটি অহস্তান্তরযোগ্য।</li>
                <li>৩. কার্ড হারালে অবিলম্বে প্রশাসন অফিসে জানাতে হবে।</li>
              </ul>
            </div>

            <div class="back-qr-return-row">
              <div class="back-return-info">
                <div class="return-label">কার্ড হারিয়ে গেলে ফেরত ঠিকানা:</div>
                <address class="return-address">
                  দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট<br />
                  ফুলতলী, জকিগঞ্জ, সিলেট - ৩১৯০<br />
                  ফোন: +৮৮০১৭১২-৩৪৫৬৭৮<br />
                  ওয়েব: attashil.softcredible.com
                </address>
              </div>

              <div class="back-qr-box">
                <img
                  v-if="qrCodeDataUrl"
                  :src="qrCodeDataUrl"
                  alt="Verification QR Code"
                  class="qr-code-img"
                />
                <div v-else class="qr-placeholder">QR</div>
                <span class="qr-label">যাচাই করুন</span>
              </div>
            </div>

            <div class="back-validity-footer">
              <div class="validity-text">মেয়াদ: ৩১ ডিসেম্বর, ২০২৬ (VALID THRU: 31-12-2026)</div>
              <div class="back-microprint">
                SECURITY ENCRYPTED • OFFICIAL SYSTEM GENERATED CREDENTIAL
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="print-footer-notice">
        <p>• নির্দেশনা: দাগ বরাবর কেটে পিভিসি পাউচে লেমিনেট করুন অথবা সরাসরি পিভিসি কার্ড প্রিন্টারে প্রিন্ট করুন।</p>
        <p>• কার্ড সাইজ: ISO/IEC 7810 ID-1 (CR-80: 85.60 × 53.98 মিমি) • সফটওয়্যার: রিহাল মাদরাসা ম্যানেজমেন্ট সিস্টেম (rihal.app)</p>
      </div>
    </div>
  </Teleport>
</ClientOnly>

    <!-- Delete Confirmation Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
          <div class="modal-card delete-modal-box">
            <div class="modal-header">
              <h3>আপনি কি নিশ্চিত?</h3>
              <button class="action-btn" @click="showDeleteModal = false">
                <Icon name="close" />
              </button>
            </div>
            <div class="modal-body">
              <p>
                আপনি "<strong>{{ student?.name_bn }}</strong>" ছাত্রটির সমস্ত রেকর্ড মুছে ফেলতে চাচ্ছেন। এই কাজটি নিশ্চিত করতে চান?
              </p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline" @click="showDeleteModal = false">বাতিল</button>
              <button class="btn btn-danger" @click="confirmDeleteStudent" :disabled="deleting">
                <Icon name="loader" v-if="deleting" />
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApiClient } from '~/utils/api'
import Icon from '~/components/Icon.vue'
import QRCode from 'qrcode'

const route = useRoute()
const router = useRouter()
const api = useApiClient()

const idCardSide = ref<'front' | 'back' | 'both'>('front')
const qrCodeDataUrl = ref('')

const loading = ref(true)
const student = ref<any>(null)
const enrollments = ref<any>(null)
const results = ref<any>(null)
const enrollLoading = ref(false)
const resultsLoading = ref(false)
const deleting = ref(false)
const restoring = ref(false)
const copied = ref(false)

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
    generateQRCode()
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

function copyAdmissionNumber() {
  if (!student.value?.admission_number) return
  navigator.clipboard?.writeText(student.value.admission_number)
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}

async function generateQRCode() {
  if (!student.value) return
  try {
    const host = typeof window !== 'undefined' ? window.location.origin : 'https://attashil.softcredible.com'
    const verifyUrl = `${host}/students/${student.value.id}`
    qrCodeDataUrl.value = await QRCode.toDataURL(verifyUrl, {
      width: 160,
      margin: 1,
      color: {
        dark: '#043823',
        light: '#ffffff',
      },
      errorCorrectionLevel: 'M',
    })
  } catch (err) {
    console.error('Failed to generate QR code:', err)
  }
}

watch(showIdCardModal, (open) => {
  if (open) {
    generateQRCode()
  }
})

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
/* Top Action Bar */
.profile-top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.breadcrumb-trail {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-family: var(--font-bn);
  font-size: var(--text-sm);
}

.back-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: var(--color-primary);
  text-decoration: none;
  font-weight: 600;
  padding: 0.45rem 0.9rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  box-shadow: var(--elevation-1);
  transition: all var(--transition-fast);

  &:hover {
    background: var(--color-primary-50);
    border-color: var(--color-primary);
    transform: translateX(-2px);
  }
}

.trail-sep {
  color: var(--color-text-muted);
}

.trail-current {
  color: var(--color-text-light);
  font-weight: 600;
}

.top-action-group {
  display: flex;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.action-pill-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1.1rem;
  border-radius: var(--radius-sm);
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: all var(--transition-fast);

  &.primary {
    background: var(--color-primary);
    color: #ffffff;
    border: 1px solid var(--color-primary);
    box-shadow: 0 2px 8px rgba(20, 80, 50, 0.25);

    &:hover {
      background: var(--color-primary-dark);
      transform: translateY(-1px);
    }
  }

  &.outline {
    background: var(--color-bg-card);
    color: var(--color-text);
    border: 1px solid var(--color-border);

    &:hover {
      border-color: var(--color-primary);
      color: var(--color-primary);
      background: var(--color-primary-50);
    }
  }

  &.danger {
    background: var(--color-bg-card);
    color: #dc2626;
    border: 1px solid #fca5a5;

    &:hover {
      background: #fef2f2;
      border-color: #ef4444;
    }
  }
}

/* Alert Deleted */
.alert-deleted-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 0.85rem 1.25rem;
  background: #fef2f2;
  border: 1px solid #f87171;
  border-radius: var(--radius-md);
  margin-bottom: 1.5rem;
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  color: #991b1b;
}

.alert-deleted-text {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.btn-restore-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.9rem;
  border-radius: var(--radius-sm);
  background: #ffffff;
  border: 1px solid #16a34a;
  color: #16a34a;
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  font-weight: 600;
  cursor: pointer;

  &:hover {
    background: #f0fdf4;
  }
}

/* 2-Column Profile Layout Grid */
.profile-layout-grid {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 1.5rem;
  align-items: start;

  @media (max-width: 1040px) {
    grid-template-columns: 1fr;
  }
}

/* Left Sidebar Identity Card */
.profile-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.student-identity-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  box-shadow: var(--elevation-1);
  overflow: hidden;
  position: relative;
}

.identity-card-header-bg {
  height: 85px;
  background: linear-gradient(135deg, #145032 0%, #186640 50%, #0d3b24 100%);
  position: relative;
}

.header-pattern-decor {
  position: absolute;
  inset: 0;
  opacity: 0.12;
  background-image: radial-gradient(#ffffff 1px, transparent 1px);
  background-size: 12px 12px;
}

.avatar-center-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: -50px;
  position: relative;
  z-index: 2;
}

.student-avatar-frame {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid #ffffff;
  box-shadow: var(--elevation-2);
  background: #ffffff;
}

.student-photo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.student-initials-badge {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #145032 0%, #186640 100%);
  color: #ffffff;
  display: grid;
  place-items: center;
  font-size: 2.5rem;
  font-weight: 700;
  font-family: var(--font-bn);
}

.avatar-status-pill-wrap {
  margin-top: 0.5rem;
}

.identity-card-titles {
  text-align: center;
  padding: 0.75rem 1.25rem;
}

.student-main-title {
  font-size: 1.45rem;
  font-weight: 800;
  color: var(--color-text);
  font-family: var(--font-bn);
  margin: 0;
  line-height: 1.3;
}

.student-sub-title {
  font-size: 0.9rem;
  color: var(--color-text-light);
  margin: 0.2rem 0 0.6rem;
  font-weight: 500;
}

.admission-code-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.3rem 0.75rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 99px;
  font-family: monospace;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-primary);
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-50);
  }

  .copy-hint {
    font-size: 0.7rem;
    color: #16a34a;
    font-family: var(--font-bn);
  }
}

.quick-contact-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border-top: 1px solid var(--color-border-light);
  border-bottom: 1px solid var(--color-border-light);
  background: var(--color-bg);
}

.quick-action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 0.25rem;
  border-radius: var(--radius-sm);
  text-decoration: none;
  border: 1px solid var(--color-border-light);
  background: #ffffff;
  font-family: var(--font-bn);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-light);
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    transform: translateY(-2px);
    box-shadow: var(--elevation-1);
  }

  &.phone:hover { color: #0284c7; border-color: #0284c7; }
  &.whatsapp:hover { color: #16a34a; border-color: #16a34a; }
  &.print:hover { color: var(--color-primary); border-color: var(--color-primary); }
}

.sidebar-facts-list {
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.fact-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: var(--text-sm);
  font-family: var(--font-bn);
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--color-border-light);

  &:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
}

.fact-label {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: var(--color-text-muted);
}

.fact-value {
  color: var(--color-text);
  font-weight: 600;

  &.font-emerald {
    color: var(--color-primary);
  }
}

.blood-chip {
  background: rgba(220, 38, 38, 0.1);
  color: #dc2626;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
}

/* Guardian Summary Card */
.guardian-summary-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  padding: 1.15rem;
  box-shadow: var(--elevation-1);
}

.guardian-summary-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;

  h4 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--color-text);
    font-family: var(--font-bn);
  }
}

.icon-emerald {
  color: var(--color-primary);
}

.guardian-name {
  font-size: var(--text-sm);
  font-weight: 700;
  color: var(--color-text);
  font-family: var(--font-bn);
}

.guardian-relation {
  font-size: var(--text-xs);
  margin: 0.1rem 0 0.4rem;
}

.guardian-phone {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: var(--text-xs);
  font-weight: 600;
  color: var(--color-primary);

  a {
    color: inherit;
    text-decoration: none;
    &:hover { text-decoration: underline; }
  }
}

/* RIGHT COLUMN: Stat Cards Grid */
.stat-cards-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.25rem;

  @media (max-width: 1200px) {
    grid-template-columns: repeat(2, 1fr);
  }
  @media (max-width: 600px) {
    grid-template-columns: 1fr;
  }
}

.modern-stat-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  padding: 1.15rem;
  display: flex;
  align-items: center;
  gap: 0.9rem;
  box-shadow: var(--elevation-1);
  transition: all var(--transition-fast);

  &:hover {
    transform: translateY(-2px);
    box-shadow: var(--elevation-2);
  }
}

.stat-card-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;

  &.emerald { background: rgba(20, 80, 50, 0.1); color: var(--color-primary); }
  &.blue { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
  &.amber { background: rgba(245, 158, 11, 0.1); color: #d97706; }
  &.purple { background: rgba(139, 92, 246, 0.1); color: #7c3aed; }
}

.stat-card-info {
  display: flex;
  flex-direction: column;
}

.stat-card-title {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.stat-card-number {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--color-text);
  font-family: var(--font-bn);
  margin: 0.2rem 0 0;
  line-height: 1.2;
}

/* Tabs Navigation */
.profile-nav-tabs {
  margin-bottom: 1.25rem;
  width: fit-content;
}

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
  padding: 0.55rem 1.15rem;
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

.tab-count-pill {
  background: rgba(255, 255, 255, 0.25);
  padding: 0.1rem 0.45rem;
  border-radius: 99px;
  font-size: 0.72rem;
  font-weight: 700;
}

/* Modern Section Cards with Info Tiles Grid */
.modern-section-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  box-shadow: var(--elevation-1);
  margin-bottom: 1.25rem;
  overflow: hidden;
}

.section-card-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
  background: var(--color-bg);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-icon-box {
  width: 34px;
  height: 34px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;

  &.emerald { background: rgba(20, 80, 50, 0.12); color: var(--color-primary); }
  &.blue { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
  &.amber { background: rgba(245, 158, 11, 0.12); color: #d97706; }
}

.section-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-text);
  font-family: var(--font-bn);
}

.section-sub {
  margin: 0.1rem 0 0;
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.section-card-body {
  padding: 1.25rem;
}

.info-tiles-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.85rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

.info-tile {
  background: var(--color-bg);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-sm);
  padding: 0.75rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;

  &.full-width {
    grid-column: span 2;
    @media (max-width: 768px) {
      grid-column: span 1;
    }
  }
}

.tile-label {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.tile-val {
  font-size: var(--text-sm);
  color: var(--color-text);
  font-family: var(--font-bn);
  font-weight: 600;

  &.font-emerald {
    color: var(--color-primary);
  }
}

.address-text {
  margin: 0;
  font-size: var(--text-sm);
  color: var(--color-text);
  font-family: var(--font-bn);
  line-height: 1.5;
  font-weight: 500;
}

/* Family Cards Grid */
.family-cards-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

.family-card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  box-shadow: var(--elevation-1);
  overflow: hidden;

  &.full {
    grid-column: span 2;
    @media (max-width: 768px) {
      grid-column: span 1;
    }
  }
}

.family-card-header {
  padding: 0.85rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;

  &.emerald { background: rgba(20, 80, 50, 0.08); color: var(--color-primary); }
  &.purple { background: rgba(139, 92, 246, 0.08); color: #7c3aed; }
  &.amber { background: rgba(245, 158, 11, 0.08); color: #d97706; }

  h4 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    font-family: var(--font-bn);
    color: var(--color-text);
  }
}

.family-card-body {
  padding: 1.15rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;

  &.horizontal-fields {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }
  }
}

.family-field {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.field-label {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.field-value {
  font-size: var(--text-sm);
  color: var(--color-text);
  font-family: var(--font-bn);

  a {
    color: inherit;
    text-decoration: none;
    &:hover { text-decoration: underline; }
  }
}

/* Tables Styling */
.toolbar-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
  background: var(--color-bg);

  h3 { margin: 0; }
  p { margin: 0.1rem 0 0; }
}

.mono-code {
  font-family: monospace;
  font-size: 0.85rem;
  background: var(--color-bg-muted);
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-weight: 600;
  color: var(--color-primary);
}

/* =========================================================
   LUXURY ID CARD MODAL STYLING (PERFECTLY CENTERED ON SCREEN)
   ========================================================= */
.id-card-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(15, 23, 42, 0.72);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  overflow-y: auto;
}

.id-card-modal-container {
  width: 100%;
  max-width: 820px;
  max-height: 94vh;
  margin: auto;
  display: flex;
  flex-direction: column;
  background: var(--color-bg-card, #ffffff);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.15);
  border: 1px solid var(--color-border-light, #e2e8f0);
  animation: idModalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes idModalPop {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.id-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.15rem 1.5rem;
  border-bottom: 1px solid var(--color-border-light);
  background: var(--color-bg);
}

.id-modal-title-wrap {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.modal-icon-pill {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  background: rgba(20, 80, 50, 0.12);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-main-title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--color-text);
  font-family: var(--font-bn);
}

.modal-subtext {
  margin: 0.15rem 0 0;
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

/* Controls Toolbar */
.id-modal-controls-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: var(--color-bg-muted);
  border-bottom: 1px solid var(--color-border-light);
  flex-wrap: wrap;
  gap: 0.75rem;
}

.card-side-toggle-group {
  display: inline-flex;
  gap: 0.35rem;
  background: #ffffff;
  padding: 0.25rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border-light);
}

.side-toggle-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.85rem;
  border-radius: 4px;
  border: none;
  background: transparent;
  color: var(--color-text-light);
  font-family: var(--font-bn);
  font-size: var(--text-xs);
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    color: var(--color-text);
  }

  &.active {
    background: var(--color-primary);
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(20, 80, 50, 0.25);
  }
}

.btn-flip-card {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.9rem;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  color: var(--color-primary);
  font-family: var(--font-bn);
  font-size: var(--text-xs);
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);

  &:hover {
    background: var(--color-primary-50);
    border-color: var(--color-primary);
  }
}

/* Modal Viewport */
.id-card-modal-viewport {
  padding: 1.5rem 1.25rem;
  background: radial-gradient(circle at center, #f8fafc 0%, #e2e8f0 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 480px;
  max-height: calc(94vh - 150px);
  overflow-y: auto;
  overflow-x: auto;
  flex: 1;
}

.id-cards-display-wrap {
  display: flex;
  gap: 1.75rem;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  padding: 0.5rem 0;
}

/* =========================================================
   STANDARD CR-80 LUXURY ID CARD (320px × 508px, Ratio 1:1.58)
   ========================================================= */
.id-card-cr80 {
  width: 320px;
  height: 508px;
  background: #ffffff;
  border-radius: 16px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.35), 0 4px 14px rgba(0, 0, 0, 0.12), inset 0 0 0 1px rgba(255, 255, 255, 0.6);
  display: flex;
  flex-direction: column;
  user-select: none;
  border: 1px solid #cbd5e1;
  transition: transform 0.25s ease, box-shadow 0.25s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 25px 50px -8px rgba(0, 0, 0, 0.42), 0 6px 18px rgba(0, 0, 0, 0.15);
  }
}

/* Lanyard Slot Punch Mockup */
.lanyard-hole-slot {
  width: 44px;
  height: 8px;
  background: rgba(0, 0, 0, 0.25);
  border-radius: 99px;
  position: absolute;
  top: 7px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  border: 1px solid rgba(255, 255, 255, 0.4);
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4);
}

/* Guilloche Security Pattern Background */
.card-security-pattern-bg {
  position: absolute;
  inset: 0;
  opacity: 0.04;
  pointer-events: none;
  background-image: radial-gradient(#043823 1px, transparent 1px), radial-gradient(#043823 1px, transparent 1px);
  background-size: 16px 16px;
  background-position: 0 0, 8px 8px;
  z-index: 1;
}

/* ==================== FRONT SIDE ==================== */
.id-card-front {
  background: #ffffff;
}

.card-front-header {
  background: linear-gradient(145deg, #022c1b 0%, #064e3b 55%, #065f46 100%);
  color: #ffffff;
  padding: 1.45rem 0.85rem 0.65rem;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  border-bottom: 2.5px solid #d4af37;
  position: relative;
  z-index: 2;
}

.header-seal-wrap {
  flex-shrink: 0;
}

.madrasa-seal-svg {
  width: 42px;
  height: 42px;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.header-titles {
  flex: 1;
  text-align: left;
}

.inst-name-bn {
  margin: 0;
  font-size: 12.5px;
  font-weight: 800;
  color: #fef3c7;
  font-family: var(--font-bn);
  line-height: 1.25;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.inst-name-en {
  display: block;
  font-size: 7.2px;
  font-weight: 700;
  color: #a7f3d0;
  letter-spacing: 0.8px;
  margin-top: 1px;
}

.card-type-ribbon {
  background: linear-gradient(90deg, #b45309 0%, #d97706 50%, #b45309 100%);
  color: #ffffff;
  text-align: center;
  font-size: 8.5px;
  font-weight: 800;
  letter-spacing: 0.8px;
  padding: 2.5px 0;
  font-family: var(--font-bn);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
  position: relative;
  z-index: 2;
}

/* Center Identity & Photo */
.card-identity-center {
  display: flex;
  padding: 0.75rem 0.85rem 0.4rem;
  gap: 0.85rem;
  align-items: center;
  position: relative;
  z-index: 2;
}

.card-photo-wrapper {
  position: relative;
  width: 90px;
  height: 106px;
  border-radius: 8px;
  border: 2px solid #d4af37;
  outline: 2px solid #ffffff;
  box-shadow: 0 6px 14px -3px rgba(6, 78, 59, 0.35);
  flex-shrink: 0;
  background: #f8fafc;
  overflow: hidden;
}

.card-photo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-photo-monogram {
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, #065f46 0%, #043823 100%);
  color: #fef3c7;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-bn);
  font-size: 2.75rem;
  font-weight: 800;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
}

.photo-session-pill {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(4, 56, 35, 0.9);
  color: #fef3c7;
  font-size: 7.5px;
  font-weight: 700;
  text-align: center;
  padding: 1.5px 0;
  font-family: var(--font-bn);
  backdrop-filter: blur(2px);
}

.card-names-box {
  flex: 1;
  text-align: left;
}

.card-student-name-bn {
  margin: 0;
  font-size: 15.5px;
  font-weight: 800;
  color: #043823;
  font-family: var(--font-bn);
  line-height: 1.25;
}

.card-student-name-en {
  font-size: 9px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.6px;
  margin: 2px 0 6px;
  text-transform: uppercase;
}

.card-adm-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 2px 7px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 4px;
  font-size: 9px;
  font-family: monospace;
  color: #166534;

  span {
    color: #64748b;
    font-size: 8px;
    font-family: var(--font-bn);
  }
}

/* 2-Column Attributes Table */
.card-details-grid {
  margin: 0.2rem 0.85rem 0.5rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.45rem 0.65rem;
  display: grid;
  grid-template-columns: 1.3fr 1fr;
  gap: 0.35rem 0.5rem;
  font-size: 9.5px;
  font-family: var(--font-bn);
  position: relative;
  z-index: 2;
}

.grid-detail-item {
  display: flex;
  align-items: center;
  gap: 0.35rem;

  &.full {
    grid-column: span 2;
  }
}

.item-k {
  color: #64748b;
  font-size: 9px;
  flex-shrink: 0;
}

.item-v {
  color: #0f172a;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;

  &.font-emerald {
    color: #047857;
  }
}

.blood-pill-badge {
  background: #fee2e2;
  color: #dc2626;
  padding: 1px 6px;
  border-radius: 3px;
  border: 1px solid #fca5a5;
  font-weight: 800;
  font-size: 9.5px;
}

/* Barcode & Security Strip */
.card-security-footer {
  margin-top: auto;
  padding: 0.5rem 0.85rem 0.35rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px dashed #cbd5e1;
  background: #ffffff;
  position: relative;
  z-index: 2;
}

.footer-barcode-box {
  width: 135px;
}

.vector-barcode-svg {
  width: 100%;
  height: 32px;
  display: block;
}

.footer-signature-box {
  text-align: center;
}

.signature-cursive-svg {
  width: 65px;
  height: 20px;
  display: block;
  margin: 0 auto;
}

.sign-label {
  font-size: 7.5px;
  color: #64748b;
  font-family: var(--font-bn);
  border-top: 1px solid #94a3b8;
  padding-top: 1px;
}

.hologram-seal-mockup {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: radial-gradient(circle, #fde68a 0%, #d97706 60%, #92400e 100%);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #ffffff;
  transform: rotate(-15deg);
}

.hologram-inner {
  font-size: 5.5px;
  font-weight: 900;
  letter-spacing: 0.5px;
  color: #ffffff;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.4);
}

.card-microprint-strip {
  background: #022c1b;
  color: #a7f3d0;
  font-size: 6.5px;
  font-weight: 700;
  letter-spacing: 0.8px;
  text-align: center;
  padding: 2.5px 0;
  position: relative;
  z-index: 2;
}

/* ==================== BACK SIDE ==================== */
.id-card-back {
  background: #ffffff;
  display: flex;
  flex-direction: column;
}

.card-back-header {
  background: linear-gradient(145deg, #022c1b 0%, #064e3b 100%);
  color: #ffffff;
  padding: 1.4rem 0.85rem 0.55rem;
  text-align: center;
  border-bottom: 2px solid #d4af37;
  position: relative;
  z-index: 2;
}

.back-inst-bn {
  margin: 0;
  font-size: 11.5px;
  font-weight: 800;
  color: #fef3c7;
  font-family: var(--font-bn);
}

.back-inst-sub {
  margin: 2px 0 0;
  font-size: 7.5px;
  color: #a7f3d0;
  font-family: var(--font-bn);
}

/* Emergency Card Box */
.back-emergency-card {
  margin: 0.65rem 0.85rem 0.35rem;
  background: #fff1f2;
  border: 1px solid #fecdd3;
  border-radius: 8px;
  padding: 0.55rem 0.75rem;
  position: relative;
  z-index: 2;
}

.emergency-header {
  margin-bottom: 0.25rem;
}

.blood-emergency-badge {
  font-size: 10px;
  font-weight: 800;
  color: #be123c;
  font-family: var(--font-bn);
}

.emergency-body {
  font-size: 8.5px;
  font-family: var(--font-bn);
  color: #334155;
  line-height: 1.35;
}

.emergency-note {
  font-size: 8px;
  color: #64748b;
  margin-bottom: 2px;
}

.contact-num {
  margin-bottom: 3px;
  strong {
    color: #be123c;
  }
}

.emergency-address {
  span {
    color: #64748b;
  }
}

/* Terms Box */
.back-terms-box {
  margin: 0.35rem 0.85rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
  position: relative;
  z-index: 2;
}

.terms-title {
  font-size: 9px;
  font-weight: 800;
  color: #043823;
  font-family: var(--font-bn);
  margin-bottom: 0.25rem;
}

.terms-list {
  margin: 0;
  padding: 0;
  list-style: none;
  font-size: 8px;
  color: #475569;
  font-family: var(--font-bn);
  line-height: 1.4;

  li {
    margin-bottom: 2px;
  }
}

/* QR Code & Return Row */
.back-qr-return-row {
  margin: 0.35rem 0.85rem;
  display: flex;
  gap: 0.75rem;
  align-items: center;
  position: relative;
  z-index: 2;
}

.back-return-info {
  flex: 1;
}

.return-label {
  font-size: 8px;
  font-weight: 700;
  color: #043823;
  font-family: var(--font-bn);
  margin-bottom: 2px;
}

.return-address {
  font-style: normal;
  font-size: 7.5px;
  color: #64748b;
  font-family: var(--font-bn);
  line-height: 1.35;
}

.back-qr-box {
  width: 72px;
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
}

.qr-code-img {
  width: 64px;
  height: 64px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  display: block;
}

.qr-placeholder {
  width: 64px;
  height: 64px;
  background: #f1f5f9;
  border: 1px dashed #cbd5e1;
  display: grid;
  place-items: center;
  font-size: 10px;
  font-weight: bold;
  color: #64748b;
}

.qr-label {
  font-size: 7px;
  font-weight: 700;
  color: #043823;
  font-family: var(--font-bn);
  margin-top: 2px;
}

/* Validity Footer */
.back-validity-footer {
  margin-top: auto;
  position: relative;
  z-index: 2;
}

.validity-text {
  text-align: center;
  font-size: 8px;
  font-weight: 700;
  color: #043823;
  font-family: var(--font-bn);
  background: #f8fafc;
  padding: 4px 0;
  border-top: 1px dashed #cbd5e1;
}

.back-microprint {
  background: #022c1b;
  color: #a7f3d0;
  font-size: 6px;
  font-weight: 700;
  letter-spacing: 0.6px;
  text-align: center;
  padding: 2.5px 0;
}

/* Modal Footer */
.id-card-modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border-light);
  background: var(--color-bg);
}

.format-pill {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.footer-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-print-id {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1.25rem;
  font-weight: 700;
}

.delete-modal-box {
  max-width: 420px;
}

/* =========================================================
   PRINT SHEET: Hidden on screen
   ========================================================= */
.print-only-sheet,
#dedicated-print-sheet {
  display: none !important;
}

/* =========================================================
   PRINT STYLES FOR DEDICATED ID CARD PRINT SHEET (A4 / CR-80)
   ========================================================= */
@media print {
  @page {
    size: A4 portrait;
    margin: 10mm 15mm;
  }

  /* Reset body and html */
  html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: #ffffff !important;
    width: 100% !important;
    height: auto !important;
    overflow: visible !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  /* Hide screen elements */
  body * {
    visibility: hidden !important;
  }

  /* Hide all screen elements and the entire Nuxt application */
  #__nuxt,
  .default-layout,
  .sidebar,
  .topbar,
  .layout-main,
  .layout-content,
  .page-wrapper,
  .id-card-modal-overlay,
  .modal-overlay,
  .no-print {
    display: none !important;
    visibility: hidden !important;
  }

  /* Show only the dedicated print sheet */
  #dedicated-print-sheet,
  #dedicated-print-sheet * {
    visibility: visible !important;
  }

  #dedicated-print-sheet {
    display: block !important;
    position: relative !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    max-width: 190mm !important;
    margin: 0 auto !important;
    padding: 0 !important;
    background: #ffffff !important;
    z-index: 9999999 !important;
  }

  .print-meta-header {
    text-align: center;
    margin-bottom: 6mm;
    padding-bottom: 3mm;
    border-bottom: 1.5px solid #043823;
  }

  .print-inst-name {
    font-size: 15pt;
    font-weight: 800;
    color: #043823 !important;
    font-family: var(--font-bn);
  }

  .print-doc-sub {
    font-size: 9.5pt;
    font-weight: 700;
    color: #b45309 !important;
    margin: 1mm 0;
    font-family: var(--font-bn);
  }

  .print-doc-info {
    font-size: 8.5pt;
    color: #334155 !important;
    font-family: var(--font-bn);
  }

  .print-cards-grid {
    display: flex !important;
    flex-direction: row !important;
    justify-content: center !important;
    align-items: flex-start !important;
    gap: 8mm !important;
    margin: 4mm 0 !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .print-card-wrapper {
    position: relative;
    padding: 3.5mm;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .print-card-tag {
    font-size: 7.5pt;
    font-weight: 700;
    color: #043823 !important;
    margin-bottom: 2mm;
    font-family: var(--font-bn);
  }

  /* 4 Corner Crop marks for blade trimming */
  .card-crop-mark {
    position: absolute;
    width: 5mm;
    height: 5mm;
    pointer-events: none;

    &.top-left {
      top: 1mm;
      left: 1mm;
      border-top: 1.5px solid #043823;
      border-left: 1.5px solid #043823;
    }
    &.top-right {
      top: 1mm;
      right: 1mm;
      border-top: 1.5px solid #043823;
      border-right: 1.5px solid #043823;
    }
    &.bottom-left {
      bottom: 1mm;
      left: 1mm;
      border-bottom: 1.5px solid #043823;
      border-left: 1.5px solid #043823;
    }
    &.bottom-right {
      bottom: 1mm;
      right: 1mm;
      border-bottom: 1.5px solid #043823;
      border-right: 1.5px solid #043823;
    }
  }

  .print-fold-guide {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0 2mm;
    align-self: stretch;
  }

  .fold-dashed-line {
    width: 1px;
    height: 70mm;
    border-left: 1.5px dashed #94a3b8;
  }

  .fold-scissor-icon {
    font-size: 6.5pt;
    color: #64748b !important;
    writing-mode: vertical-rl;
    margin-top: 2mm;
    letter-spacing: 1px;
    font-family: var(--font-bn);
  }

  /* Exact CR-80 physical dimensions on paper */
  .id-card-cr80.print-render {
    width: 54mm !important;
    height: 85.6mm !important;
    min-width: 54mm !important;
    min-height: 85.6mm !important;
    max-width: 54mm !important;
    max-height: 85.6mm !important;
    border-radius: 3.18mm !important;
    border: 1px solid #94a3b8 !important;
    box-shadow: none !important;
    overflow: hidden !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
    transform: none !important;
  }

  /* Internal elements proportional scaling for 54mm x 85.6mm print */
  .print-render {
    .lanyard-hole-slot {
      display: none !important;
    }

    .card-front-header {
      padding: 2.2mm 2mm 1.5mm !important;
      gap: 1.5mm !important;
      border-bottom: 1px solid #d4af37 !important;
    }

    .madrasa-seal-svg {
      width: 8mm !important;
      height: 8mm !important;
    }

    .inst-name-bn {
      font-size: 6.5pt !important;
      line-height: 1.2 !important;
    }

    .inst-name-en {
      font-size: 3.8pt !important;
      letter-spacing: 0.2px !important;
    }

    .card-type-ribbon {
      font-size: 4.8pt !important;
      padding: 0.8mm 0 !important;
    }

    .card-identity-center {
      padding: 1.8mm 2mm 1mm !important;
      gap: 1.8mm !important;
    }

    .card-photo-wrapper {
      width: 17mm !important;
      height: 21mm !important;
      border-radius: 1.5mm !important;
      border-width: 1px !important;
    }

    .card-student-name-bn {
      font-size: 7.5pt !important;
      line-height: 1.15 !important;
    }

    .card-student-name-en {
      font-size: 4.5pt !important;
      margin: 0.5mm 0 !important;
    }

    .card-adm-pill {
      font-size: 4.8pt !important;
      padding: 0.4mm 1.2mm !important;
    }

    .photo-session-pill {
      font-size: 4.2pt !important;
      padding: 0.4mm 0 !important;
    }

    .card-details-grid {
      margin: 1mm 2mm !important;
      padding: 1mm 1.2mm !important;
      gap: 0.8mm 1.2mm !important;
      font-size: 5pt !important;
      border-radius: 1.5mm !important;
    }

    .item-k {
      font-size: 4.8pt !important;
    }

    .blood-pill-badge {
      font-size: 4.8pt !important;
      padding: 0.2mm 1mm !important;
    }

    .card-security-footer {
      padding: 1.2mm 2mm 0.8mm !important;
    }

    .footer-barcode-box {
      width: 26mm !important;
    }

    .vector-barcode-svg {
      height: 6.5mm !important;
    }

    .footer-signature-box {
      .signature-cursive-svg {
        width: 12mm !important;
        height: 4mm !important;
      }
      .sign-label {
        font-size: 4pt !important;
      }
    }

    .hologram-seal-mockup {
      width: 6mm !important;
      height: 6mm !important;
      .hologram-inner {
        font-size: 3pt !important;
      }
    }

    .card-microprint-strip {
      font-size: 3.5pt !important;
      padding: 0.6mm 0 !important;
    }

    /* Back side print scaling */
    .card-back-header {
      padding: 2.2mm 2mm 1.2mm !important;
      .back-inst-bn {
        font-size: 6pt !important;
      }
      .back-inst-sub {
        font-size: 4pt !important;
      }
    }

    .back-emergency-card {
      margin: 1.2mm 2mm 0.8mm !important;
      padding: 1mm 1.2mm !important;
      border-radius: 1.5mm !important;
      .blood-emergency-badge {
        font-size: 5pt !important;
      }
      .emergency-body {
        font-size: 4.5pt !important;
        line-height: 1.25 !important;
      }
      .emergency-note {
        font-size: 4pt !important;
      }
    }

    .back-terms-box {
      margin: 0.8mm 2mm !important;
      padding: 1mm 1.2mm !important;
      border-radius: 1.5mm !important;
      .terms-title {
        font-size: 5pt !important;
      }
      .terms-list {
        font-size: 4.2pt !important;
        line-height: 1.25 !important;
      }
    }

    .back-qr-return-row {
      margin: 0.8mm 2mm !important;
      gap: 1.5mm !important;
    }

    .return-label {
      font-size: 4.5pt !important;
    }

    .return-address {
      font-size: 4pt !important;
      line-height: 1.25 !important;
    }

    .back-qr-box {
      width: 14mm !important;
    }

    .qr-code-img,
    .qr-placeholder {
      width: 13mm !important;
      height: 13mm !important;
    }

    .qr-label {
      font-size: 3.8pt !important;
    }

    .validity-text {
      font-size: 4.5pt !important;
      padding: 0.8mm 0 !important;
    }

    .back-microprint {
      font-size: 3.5pt !important;
      padding: 0.5mm 0 !important;
    }
  }

  .print-footer-notice {
    margin-top: 6mm;
    text-align: center;
    border-top: 1px dashed #cbd5e1;
    padding-top: 3mm;

    p {
      margin: 0.8mm 0;
      font-size: 7pt;
      color: #64748b !important;
      font-family: var(--font-bn);
    }
  }
}
</style>
