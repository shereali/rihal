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

    <!-- ID Card Modal -->
    <div v-if="showIdCardModal" class="modal-overlay" @click.self="showIdCardModal = false">
      <div class="modal-card id-card-modal-box">
        <div class="modal-header">
          <h3>ছাত্র পরিচয়পত্র (Student ID Card)</h3>
          <button class="action-btn" @click="showIdCardModal = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
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
                  <div><span>শ্রেণি:</span> <strong>{{ currentEnrollment?.class?.name_bn || student?.class?.name_bn || 'নূরানী প্রথম শ্রেণি' }}</strong></div>
                  <div><span>রোল নং:</span> <strong>{{ student?.roll_number || '১' }}</strong></div>
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

/* ID Card Modal Styling */
.id-card-modal-box {
  max-width: 480px;
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
    font-size: 0.95rem;
    font-weight: 700;
    font-family: var(--font-bn);
  }
  small {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.72rem;
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

.delete-modal-box {
  max-width: 420px;
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
