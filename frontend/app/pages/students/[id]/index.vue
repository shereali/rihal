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
        <button class="action-pill-btn primary" @click="switchToEditTab" title="শিক্ষার্থীর তথ্য সরাসরি সম্পাদনা করুন">
          <Icon name="pencil" size="16" />
          <span>তথ্য সম্পাদনা</span>
        </button>
        <NuxtLink :to="`/students/${student.id}/edit`" class="action-pill-btn outline" title="সম্পূর্ণ এডিট ফর্মে যান">
          <Icon name="pencil" size="16" />
          <span>আলাদা এডিট পেজ</span>
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

        <!-- TAB 0: তথ্য সরাসরি সম্পাদনা (Inline Edit Tab) -->
        <div v-if="activeTab === 'edit'" class="tab-pane-content">
          <div class="card modern-section-card">
            <div class="section-card-header">
              <div class="header-left">
                <span class="section-icon-box blue"><Icon name="pencil" size="17" /></span>
                <div>
                  <h3 class="section-title">শিক্ষার্থীর তথ্য সম্পাদনা</h3>
                  <p class="section-sub">প্রয়োজনীয় তথ্য পরিবর্তন করে নিচে 'পরিবর্তন সংরক্ষণ করুন' বাটনে ক্লিক করুন</p>
                </div>
              </div>
              <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-sm btn-ghost" @click="activeTab = 'overview'">
                  <Icon name="close" size="14" /> বাতিল
                </button>
                <button type="button" class="btn btn-sm btn-primary" :disabled="editSaving || !editForm.name_bn" @click="saveInlineEdit">
                  <Icon v-if="editSaving" name="loader" size="14" class="animate-spin" />
                  <Icon v-else name="save" size="14" />
                  {{ editSaving ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ' }}
                </button>
              </div>
            </div>

            <!-- Alerts -->
            <div v-if="editError" class="alert alert-error" style="margin: 1rem 1.5rem 0; background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 0.5rem;">
              <Icon name="alertCircle" size="16" />
              <span>{{ editError }}</span>
            </div>
            <div v-if="editSuccess" class="alert alert-success" style="margin: 1rem 1.5rem 0; background: #dcfce7; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 0.5rem;">
              <Icon name="checkCircle" size="16" />
              <span>{{ editSuccess }}</span>
            </div>

            <div class="section-card-body" style="padding: 1.5rem;">
              <form @submit.prevent="saveInlineEdit">
                <!-- Group 1: Basic & Personal Info -->
                <div class="edit-form-section" style="margin-bottom: 1.75rem;">
                  <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-primary, #1e3a8a); margin-bottom: 0.75rem; border-bottom: 1px solid var(--color-border-light, #e5e7eb); padding-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                    <Icon name="user" size="15" /> ব্যক্তিগত ও পরিচিতি তথ্য
                  </h4>
                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                      <label class="form-label font-bold">নাম (বাংলায়) <span class="text-danger" style="color: #ef4444;">*</span></label>
                      <input v-model="editForm.name_bn" type="text" class="form-control" placeholder="যেমন: মুহাম্মদ তানভীর আহমেদ" required />
                    </div>
                    <div class="form-group">
                      <label class="form-label">নাম (ইংরেজিতে)</label>
                      <input v-model="editForm.name_en" type="text" class="form-control" placeholder="e.g. Muhammad Tanvir Ahmed" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">ভর্তি নম্বর (Admission No)</label>
                      <input v-model="editForm.admission_number" type="text" class="form-control" placeholder="যেমন: ADM-2026-001" />
                    </div>
                  </div>

                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                      <label class="form-label">রোল নম্বর</label>
                      <input v-model="editForm.roll_number" type="text" class="form-control" placeholder="যেমন: ০১" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">মোবাইল ফোন নম্বর</label>
                      <input v-model="editForm.phone" type="tel" class="form-control" placeholder="017XXXXXXXX" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">জন্ম তারিখ</label>
                      <input v-model="editForm.date_of_birth" type="date" class="form-control" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">লিঙ্গ</label>
                      <select v-model="editForm.gender" class="form-control form-select">
                        <option value="">নির্বাচন করুন</option>
                        <option value="male">ছাত্র / ছেলে</option>
                        <option value="female">ছাত্রী / মেয়ে</option>
                        <option value="other">অন্যান্য</option>
                      </select>
                    </div>
                  </div>

                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                      <label class="form-label">রক্তের গ্রুপ</label>
                      <select v-model="editForm.blood_group" class="form-control form-select">
                        <option value="">নির্বাচন করুন</option>
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label">ইমেইল ঠিকানা</label>
                      <input v-model="editForm.email" type="email" class="form-control" placeholder="student@example.com" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">শিক্ষার্থী অবস্থা</label>
                      <select v-model="editForm.is_active" class="form-control form-select">
                        <option :value="true">সক্রিয় ও নিয়মিত (Active)</option>
                        <option :value="false">নিষ্ক্রিয় / স্থগিত (Inactive)</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Group 2: Academic Class & Section -->
                <div class="edit-form-section" style="margin-bottom: 1.75rem;">
                  <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-primary, #1e3a8a); margin-bottom: 0.75rem; border-bottom: 1px solid var(--color-border-light, #e5e7eb); padding-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                    <Icon name="academic" size="15" /> জামাত / শ্রেণি ও ভর্তি তথ্য
                  </h4>
                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                      <label class="form-label font-bold">শ্রেণি / জামাত</label>
                      <select v-model="editForm.class_id" class="form-control form-select" @change="onEditClassChange">
                        <option value="">শ্রেণি নির্বাচন করুন</option>
                        <option v-for="cls in editClassOptions" :key="cls.id" :value="cls.id">
                          {{ cls.name_bn || cls.name }}
                        </option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label">শাখা / সেকশন</label>
                      <select v-model="editForm.section_id" class="form-control form-select">
                        <option value="">শাখা নেই / সাধারণ</option>
                        <option v-for="sec in editSectionOptions" :key="sec.id" :value="sec.id">
                          {{ sec.name_bn || sec.name }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Group 3: Parents & Guardian -->
                <div class="edit-form-section" style="margin-bottom: 1.75rem;">
                  <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-primary, #1e3a8a); margin-bottom: 0.75rem; border-bottom: 1px solid var(--color-border-light, #e5e7eb); padding-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                    <Icon name="users" size="15" /> পিতা-মাতা ও অভিভাবকের তথ্য
                  </h4>
                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                      <label class="form-label">পিতার নাম</label>
                      <input v-model="editForm.father_name" type="text" class="form-control" placeholder="পিতার নাম" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">পিতার মোবাইল নম্বর</label>
                      <input v-model="editForm.father_phone" type="tel" class="form-control" placeholder="017XXXXXXXX" />
                    </div>
                  </div>

                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                      <label class="form-label">মাতার নাম</label>
                      <input v-model="editForm.mother_name" type="text" class="form-control" placeholder="মাতার নাম" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">মাতার মোবাইল নম্বর</label>
                      <input v-model="editForm.mother_phone" type="tel" class="form-control" placeholder="017XXXXXXXX" />
                    </div>
                  </div>

                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                      <label class="form-label">স্থানীয় অভিভাবকের নাম</label>
                      <input v-model="editForm.guardian_name" type="text" class="form-control" placeholder="অভিভাবকের নাম" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">অভিভাবকের মোবাইল</label>
                      <input v-model="editForm.guardian_phone" type="tel" class="form-control" placeholder="018XXXXXXXX" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">সম্পর্ক</label>
                      <input v-model="editForm.guardian_relation" type="text" class="form-control" placeholder="যেমন: চাচা / মামা / ভাই" />
                    </div>
                  </div>
                </div>

                <!-- Group 4: Address & Health -->
                <div class="edit-form-section" style="margin-bottom: 1.75rem;">
                  <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-primary, #1e3a8a); margin-bottom: 0.75rem; border-bottom: 1px solid var(--color-border-light, #e5e7eb); padding-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                    <Icon name="pin" size="15" /> ঠিকানা ও অন্যান্য বিবরণ
                  </h4>
                  <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">স্থায়ী ও বর্তমান ঠিকানা</label>
                    <textarea v-model="editForm.address_bn" class="form-control" rows="2" placeholder="গ্রাম/মহল্লা, ডাকঘর, থানা/উপজেলা, জেলা..."></textarea>
                  </div>
                  <div class="form-group">
                    <label class="form-label">স্বাস্থ্য বিবরণ / বিশেষ মন্তব্য</label>
                    <input v-model="editForm.health_summary" type="text" class="form-control" placeholder="শারীরিক বা স্বাস্থ্যগত কোনো বিশেষ বিষয় থাকলে লিখুন" />
                  </div>
                </div>

                <!-- Form Action Buttons Footer -->
                <div style="display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid var(--color-border-light, #e5e7eb); padding-top: 1.25rem;">
                  <button type="button" class="btn btn-ghost" @click="activeTab = 'overview'">
                    বাতিল
                  </button>
                  <button type="submit" class="btn btn-primary btn-lg" :disabled="editSaving || !editForm.name_bn">
                    <Icon v-if="editSaving" name="loader" class="animate-spin" />
                    <Icon v-else name="save" />
                    {{ editSaving ? 'সংরক্ষণ হচ্ছে...' : 'আপডেট সম্পন্ন করুন' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
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
              <button class="btn btn-sm btn-outline" @click="openQuickEditModal" title="ব্যক্তিগত তথ্য সম্পাদনা">
                <Icon name="pencil" size="14" /> সম্পাদনা
              </button>
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
              <button class="btn btn-sm btn-outline" @click="openQuickEditModal" title="একাডেমিক তথ্য সম্পাদনা">
                <Icon name="pencil" size="14" /> জামাত পরিবর্তন
              </button>
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
              <button class="btn btn-sm btn-outline" @click="openQuickEditModal" title="ঠিকানা ও যোগাযোগ সম্পাদনা">
                <Icon name="pencil" size="14" /> সম্পাদনা
              </button>
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
          <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
            <button class="btn btn-sm btn-outline" @click="openQuickEditModal" title="পিতা-মাতা ও অভিভাবকের তথ্য সম্পাদনা">
              <Icon name="pencil" size="14" /> অভিভাবক তথ্য সম্পাদনা
            </button>
          </div>
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
                  <p class="modal-subtext">অফিসিয়াল পিভিসি স্মার্ট আইডি কার্ড প্রিভিউ ও প্রিন্ট লেআউট</p>
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
                  <!-- Lanyard Hole Slot Mockup -->
                  <div class="lanyard-hole-slot"></div>

                  <!-- Guilloche / Security Pattern Background -->
                  <div class="card-security-pattern-bg"></div>

                  <!-- Institutional Header -->
                  <div class="card-front-header">
                    <div class="header-seal-wrap">
                      <svg viewBox="0 0 50 50" class="madrasa-seal-svg">
                        <defs>
                          <radialGradient id="sealGoldGradM" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#fef08a" />
                            <stop offset="60%" stop-color="#d4af37" />
                            <stop offset="100%" stop-color="#92400e" />
                          </radialGradient>
                        </defs>
                        <circle cx="25" cy="25" r="23.5" fill="#012215" stroke="url(#sealGoldGradM)" stroke-width="1.8"/>
                        <circle cx="25" cy="25" r="21" fill="none" stroke="#fef08a" stroke-width="0.7" stroke-dasharray="1.5,1.5"/>
                        <path d="M25,7 L28,14 L35,11 L32,18 L39,21 L33,25 L39,29 L32,32 L35,39 L28,36 L25,43 L22,36 L15,39 L18,32 L11,29 L17,25 L11,21 L18,18 L15,11 L22,14 Z" fill="none" stroke="#d4af37" stroke-width="0.6" opacity="0.45"/>
                        <circle cx="25" cy="25" r="14" fill="#043823" stroke="#fef08a" stroke-width="0.9"/>
                        <path d="M25,14 A8,8 0 0,0 29,29 A9.5,9.5 0 1,1 25,14 Z" fill="url(#sealGoldGradM)"/>
                        <polygon points="28,19 29,21.5 31.5,21.5 29.5,23 30.5,25.5 28,24 25.5,25.5 26.5,23 24.5,21.5 27,21.5" fill="#fef08a"/>
                        <path d="M20,29 L25,26.5 L30,29 L30,30 L25,27.5 L20,30 Z" fill="#fef08a"/>
                        <path d="M22,30 L25,32.5 L28,30" fill="none" stroke="#d4af37" stroke-width="0.8"/>
                      </svg>
                    </div>
                    <div class="header-titles">
                      <h4 class="inst-name-bn">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h4>
                      <span class="inst-name-en">DARUL QIRAT MAJIDIA FULTALI TRUST</span>
                      <div class="inst-tagline">ফুলতলী, জকিগঞ্জ, সিলেট • স্থাপিত: ১৯৫০</div>
                    </div>
                  </div>

                  <!-- Top Smart Tech Row: EMV Chip + NFC Waves + Regular Status -->
                  <div class="card-smart-bar">
                    <div class="chip-and-nfc">
                      <svg class="emv-chip-svg" viewBox="0 0 46 34" width="34" height="25">
                        <defs>
                          <linearGradient id="chipGoldGradM" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#fef08a" />
                            <stop offset="25%" stop-color="#eab308" />
                            <stop offset="50%" stop-color="#fef9c3" />
                            <stop offset="80%" stop-color="#ca8a04" />
                            <stop offset="100%" stop-color="#854d0e" />
                          </linearGradient>
                        </defs>
                        <rect x="1" y="1" width="44" height="32" rx="4" fill="url(#chipGoldGradM)" stroke="#78350f" stroke-width="0.9"/>
                        <path d="M1,17 L15,17 M31,17 L45,17 M15,1 L15,33 M31,1 L31,33 M15,11 L31,11 M15,23 L31,23" stroke="#713f12" stroke-width="0.75" fill="none"/>
                        <rect x="20" y="13.5" width="6" height="7" rx="1.5" fill="#fef08a" stroke="#854d0e" stroke-width="0.6"/>
                      </svg>

                      <svg class="nfc-waves-svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#047857" stroke-width="2.2" stroke-linecap="round">
                        <path d="M5 8c3.5-3.5 10.5-3.5 14 0" />
                        <path d="M8 11c2-2 6-2 8 0" />
                        <path d="M11 14c.5-.5 1.5-.5 2 0" />
                      </svg>
                    </div>

                    <span class="card-status-badge">
                      <span class="status-dot"></span>
                      <span>নিয়মিত শিক্ষার্থী</span>
                    </span>
                  </div>

                  <!-- Hero Centered Student Photo -->
                  <div class="card-hero-photo-wrap">
                    <div class="card-photo-wrapper hero-center">
                      <img
                        v-if="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                        :src="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                        :alt="student.name_bn"
                        class="card-photo-img"
                      />
                      <div v-else class="card-photo-monogram">
                        <svg class="student-avatar-svg" viewBox="0 0 64 64" fill="none">
                          <circle cx="32" cy="32" r="30" fill="#f0fdf4" stroke="#a7f3d0" stroke-width="1.5"/>
                          <path d="M32 14 C24 14 20 18 20 23 C20 27 24 30 32 30 C40 30 44 27 44 23 C44 18 40 14 32 14 Z" fill="#064e3b" />
                          <path d="M32 20 C27 20 24 23 24 26 C24 30 27 33 32 33 C37 33 40 30 40 26 C40 23 37 20 32 20 Z" fill="#fde68a" />
                          <path d="M18 52 C18 43 24 38 32 38 C40 38 46 43 46 52 Z" fill="#064e3b" />
                          <path d="M28 38 L32 44 L36 38" fill="none" stroke="#d4af37" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="monogram-name">{{ student?.name_bn ? student.name_bn.split(' ')[0] : 'শিক্ষার্থী' }}</span>
                      </div>
                      <div class="photo-session-pill">
                        শিক্ষাবর্ষ: {{ currentEnrollment?.session?.name_bn || '২০২৫-২০২৬' }}
                      </div>
                    </div>
                  </div>

                  <!-- Student Names & Official Role Pill -->
                  <div class="card-hero-names-wrap">
                    <h3 class="card-student-name-bn">{{ student?.name_bn }}</h3>
                    <div class="card-student-name-en">{{ student?.name_en || 'MUHAMMAD TANVIR AHMED' }}</div>
                    <div class="student-role-pill">
                      <span>শিক্ষার্থী • STUDENT</span>
                    </div>
                  </div>

                  <!-- 4-Cell Standard Academic Credentials Matrix -->
                  <div class="card-cred-matrix">
                    <div class="cred-cell">
                      <span class="cred-k">আইডি নং:</span>
                      <strong class="cred-v font-mono">{{ student?.admission_number || 'ADM-2026-9353' }}</strong>
                    </div>
                    <div class="cred-cell">
                      <span class="cred-k">শ্রেণি:</span>
                      <span class="class-pill-badge">{{ currentEnrollment?.class?.name_bn || student?.class?.name_bn || 'নূরানী প্রথম শ্রেণি' }}</span>
                    </div>
                    <div class="cred-cell">
                      <span class="cred-k">রোল নং:</span>
                      <span class="roll-pill-badge">{{ student?.roll_number || currentEnrollment?.roll_number || '০১' }}</span>
                    </div>
                    <div class="cred-cell">
                      <span class="cred-k">শাখা:</span>
                      <strong class="cred-v">{{ currentEnrollment?.section?.name_bn || 'সাধারণ / ক-শাখা' }}</strong>
                    </div>
                  </div>

                  <!-- Barcode, Signature & Hologram Security Strip -->
                  <div class="card-security-footer">
                    <div class="footer-barcode-box">
                      <svg class="vector-barcode-svg" viewBox="0 0 160 38">
                        <rect x="0" y="0" width="160" height="38" fill="#ffffff" rx="2" />
                        <g fill="#022c22">
                          <rect x="6" y="3" width="2.5" height="22" /><rect x="11" y="3" width="1.2" height="22" /><rect x="14" y="3" width="3.5" height="22" /><rect x="20" y="3" width="1.5" height="22" /><rect x="23" y="3" width="2" height="22" /><rect x="28" y="3" width="3.5" height="22" /><rect x="34" y="3" width="1.2" height="22" /><rect x="38" y="3" width="2" height="22" /><rect x="42" y="3" width="3.8" height="22" /><rect x="48" y="3" width="1.5" height="22" /><rect x="52" y="3" width="2.5" height="22" /><rect x="57" y="3" width="1.2" height="22" /><rect x="61" y="3" width="3.5" height="22" /><rect x="67" y="3" width="2" height="22" /><rect x="71" y="3" width="1.5" height="22" /><rect x="75" y="3" width="3.5" height="22" /><rect x="81" y="3" width="1.2" height="22" /><rect x="85" y="3" width="2" height="22" /><rect x="89" y="3" width="3.5" height="22" /><rect x="95" y="3" width="1.5" height="22" /><rect x="99" y="3" width="2.5" height="22" /><rect x="104" y="3" width="1.2" height="22" /><rect x="108" y="3" width="3.5" height="22" /><rect x="114" y="3" width="2" height="22" /><rect x="118" y="3" width="1.5" height="22" /><rect x="122" y="3" width="3.5" height="22" /><rect x="128" y="3" width="1.2" height="22" /><rect x="132" y="3" width="2" height="22" /><rect x="136" y="3" width="3.5" height="22" /><rect x="142" y="3" width="1.5" height="22" /><rect x="146" y="3" width="2.5" height="22" /><rect x="151" y="3" width="3" height="22" />
                        </g>
                        <text x="80" y="33" text-anchor="middle" font-family="monospace" font-size="8.5" font-weight="800" fill="#022c22">
                          * {{ student?.admission_number || 'ADM-2026-9353' }} *
                        </text>
                      </svg>
                    </div>

                    <div class="footer-signature-box">
                      <div class="signature-wrap">
                        <svg class="signature-cursive-svg" viewBox="0 0 90 28">
                          <path d="M6,22 C14,10 18,24 26,12 C32,5 36,21 44,14 C52,9 58,19 68,9 C72,7 78,14 84,9" fill="none" stroke="#043823" stroke-width="1.8" stroke-linecap="round"/>
                          <path d="M12,24 C35,27 65,24 82,18" fill="none" stroke="#043823" stroke-width="0.9" stroke-linecap="round" opacity="0.6"/>
                        </svg>
                        <div class="signature-stamp-circle">
                          <span>ختم</span>
                        </div>
                      </div>
                      <div class="sign-label">মুহতামিম / অধ্যক্ষের স্বাক্ষর</div>
                    </div>

                    <div class="hologram-seal-mockup" title="অফিসিয়াল নিরাপত্তা হলোগ্রাম">
                      <div class="hologram-inner">
                        <svg viewBox="0 0 20 20" width="11" height="11" fill="#ffffff">
                          <path d="M10 1 L12.5 6.5 L18.5 7 L14 11 L15.5 17 L10 14 L4.5 17 L6 11 L1.5 7 L7.5 6.5 Z"/>
                        </svg>
                        <span class="holo-line1">SECURE</span>
                        <span class="holo-line2">VERIFIED</span>
                      </div>
                    </div>
                  </div>

                  <!-- Microprint security bottom border -->
                  <div class="card-microprint-strip">
                    DARUL QIRAT MAJIDIA FULTALI TRUST • OFFICIAL STUDENT SMART ID • ISO/IEC 7810 ID-1
                  </div>
                </div>

                <!-- ================= BACK SIDE ================= -->
                <div
                  v-if="idCardSide === 'back' || idCardSide === 'both'"
                  class="id-card-cr80 id-card-back"
                  @click="idCardSide === 'back' ? idCardSide = 'front' : null"
                  title="কার্ডে ক্লিক করে উল্টান"
                >
                  <!-- Lanyard Slot Punch Mockup -->
                  <div class="lanyard-hole-slot"></div>

                  <!-- Magnetic Stripe Mockup -->
                  <div class="magnetic-stripe-bar">
                    <div class="mag-stripe-shimmer"></div>
                    <span class="mag-stripe-text">DARUL QIRAT MAJIDIA FULTALI TRUST • ENCRYPTED MAGNETIC ENCODE</span>
                  </div>

                  <!-- Watermark Pattern -->
                  <div class="card-security-pattern-bg"></div>

                  <!-- Back Header -->
                  <div class="card-back-header">
                    <div class="back-header-titles">
                      <h4 class="back-inst-bn">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h4>
                      <p class="back-inst-sub">কেন্দ্রীয় কার্যালয়: ফুলতলী, জকিগঞ্জ, সিলেট - ৩১৯০ • প্রশাসন ও পরীক্ষা নিয়ন্ত্রণ বিভাগ</p>
                    </div>
                  </div>

                  <!-- Emergency & Medical Section -->
                  <div class="back-emergency-card">
                    <div class="em-top-row">
                      <span class="blood-emergency-badge">
                        <span>🩸 রক্তের গ্রুপ: <strong>{{ student?.blood_group || 'B+' }} (পজিটিভ)</strong></span>
                      </span>
                      <span class="dob-badge">
                        <span>জন্ম: <strong>{{ student?.date_of_birth ? formatDate(student.date_of_birth) : '১৫ মার্চ, ২০১৬' }}</strong></span>
                      </span>
                    </div>
                    <div class="em-guardian-row">
                      <div class="em-field">
                        <span class="em-k">অভিভাবক:</span>
                        <strong class="em-v text-truncate">{{ student?.guardian_name || student?.father_name || 'মাওলানা শফিকুল ইসলাম' }}</strong>
                      </div>
                      <div class="em-field hotline-highlight">
                        <span class="em-k">জরুরি:</span>
                        <strong class="em-v font-mono">{{ student?.guardian_phone || student?.father_phone || student?.user?.phone || '০১৭১১-২২৩৩৪৪' }}</strong>
                      </div>
                    </div>
                    <div class="em-address-row">
                      <span class="em-k">স্থায়ী ঠিকানা:</span>
                      <span class="em-v">{{ student?.address_bn || 'গ্রাম: ফুলতলী, ডাকঘর: ফুলতলী বাজার, উপজেলা: জকিগঞ্জ, জেলা: সিলেট' }}</span>
                    </div>
                  </div>

                  <!-- Terms & Rules -->
                  <div class="back-terms-box">
                    <div class="terms-title">
                      <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="#043823" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                      <span>সাধারণ নির্দেশাবলী ও নিয়মাবলী:</span>
                    </div>
                    <ul class="terms-list">
                      <li><span class="term-bullet">◆</span><span>এই পরিচয়পত্রটি মাদ্রাসার সার্বক্ষণিক সম্পত্তি ও ক্যাম্পাসে বহন বাধ্যতামূলক।</span></li>
                      <li><span class="term-bullet">◆</span><span>কার্ডটি অহস্তান্তরযোগ্য; কেবল উল্লেখিত শিক্ষার্থীর ব্যবহারের জন্য প্রযোজ্য।</span></li>
                      <li><span class="term-bullet">◆</span><span>স্মার্ট ডিজিটাল হাজিরা, লাইব্রেরি ও পরীক্ষার হলে কার্ড প্রদর্শন আবশ্যক।</span></li>
                      <li><span class="term-bullet">◆</span><span>কার্ড হারালে বা ক্ষতিগ্রস্ত হলে অবিলম্বে প্রশাসন কার্যালয়ে রিপোর্ট করতে হবে।</span></li>
                    </ul>
                  </div>

                  <!-- QR Code & Return Address Row -->
                  <div class="back-qr-return-row">
                    <div class="back-return-info">
                      <div class="return-label">কার্ড হারিয়ে গেলে ফেরত ঠিকানা:</div>
                      <address class="return-address">
                        <strong>দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</strong><br />
                        ফুলতলী, জকিগঞ্জ, সিলেট - ৩১৯০<br />
                        হেল্পলাইন: +৮৮০১৭১২-৩৪৫৬৭৮ • ওয়েব: attashil.softcredible.com
                      </address>
                    </div>

                    <div class="back-qr-box">
                      <div class="qr-frame-reticle">
                        <img
                          v-if="qrCodeDataUrl"
                          :src="qrCodeDataUrl"
                          alt="Verification QR Code"
                          class="qr-code-img"
                        />
                        <div v-else class="qr-placeholder">QR</div>
                        <div class="reticle-corner top-left"></div>
                        <div class="reticle-corner top-right"></div>
                        <div class="reticle-corner bottom-left"></div>
                        <div class="reticle-corner bottom-right"></div>
                      </div>
                      <span class="qr-label">অনলাইনে যাচাই করুন</span>
                    </div>
                  </div>

                  <!-- Validity & Security Footer -->
                  <div class="back-validity-footer">
                    <div class="validity-text">
                      <span class="val-k">মেয়াদ উত্তীর্ণ:</span>
                      <strong class="val-v">৩১ ডিসেম্বর, ২০২৬</strong>
                      <span class="val-en">(VALID THRU: 12/2026)</span>
                    </div>
                    <div class="back-microprint">
                      ENCRYPTED STUDENT CREDENTIAL • DARUL QIRAT MAJIDIA FULTALI TRUST • SECURE RFID
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

    <!-- Dedicated Print Sheet for PVC / Paper Card Printing (Teleported to body, shown ONLY during print) -->
    <ClientOnly>
      <Teleport to="body">
        <div id="dedicated-print-sheet" class="print-only-sheet id-card-cr80-print-sheet">
          <div class="print-meta-header">
            <div class="print-inst-name">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</div>
            <div class="print-doc-sub">অফিসিয়াল শিক্ষার্থী ডিজিটাল পরিচয়পত্র (CR-80 PVC Badge Print Layout)</div>
            <div class="print-doc-info">
              শিক্ষার্থী: {{ student?.name_bn }} ({{ student?.name_en }}) • ভর্তি নং: {{ student?.admission_number }} • শ্রেণি: {{ currentEnrollment?.class?.name_bn || student?.class?.name_bn || 'নূরানী প্রথম শ্রেণি' }} • রোল: {{ student?.roll_number || currentEnrollment?.roll_number || '০১' }} • সেশন: {{ currentEnrollment?.session?.name_bn || '২০২৫-২০২৬' }}
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
                <!-- Guilloche / Watermark Security Background -->
                <div class="card-security-pattern-bg"></div>

                <!-- Institutional Header -->
                <div class="card-front-header">
                  <div class="header-seal-wrap">
                    <svg viewBox="0 0 50 50" class="madrasa-seal-svg">
                      <defs>
                        <radialGradient id="sealGoldGradP" cx="50%" cy="50%" r="50%">
                          <stop offset="0%" stop-color="#fef08a" />
                          <stop offset="60%" stop-color="#d4af37" />
                          <stop offset="100%" stop-color="#92400e" />
                        </radialGradient>
                      </defs>
                      <circle cx="25" cy="25" r="23.5" fill="#012215" stroke="url(#sealGoldGradP)" stroke-width="1.8"/>
                      <circle cx="25" cy="25" r="21" fill="none" stroke="#fef08a" stroke-width="0.7" stroke-dasharray="1.5,1.5"/>
                      <path d="M25,7 L28,14 L35,11 L32,18 L39,21 L33,25 L39,29 L32,32 L35,39 L28,36 L25,43 L22,36 L15,39 L18,32 L11,29 L17,25 L11,21 L18,18 L15,11 L22,14 Z" fill="none" stroke="#d4af37" stroke-width="0.6" opacity="0.45"/>
                      <circle cx="25" cy="25" r="14" fill="#043823" stroke="#fef08a" stroke-width="0.9"/>
                      <path d="M25,14 A8,8 0 0,0 29,29 A9.5,9.5 0 1,1 25,14 Z" fill="url(#sealGoldGradP)"/>
                      <polygon points="28,19 29,21.5 31.5,21.5 29.5,23 30.5,25.5 28,24 25.5,25.5 26.5,23 24.5,21.5 27,21.5" fill="#fef08a"/>
                      <path d="M20,29 L25,26.5 L30,29 L30,30 L25,27.5 L20,30 Z" fill="#fef08a"/>
                      <path d="M22,30 L25,32.5 L28,30" fill="none" stroke="#d4af37" stroke-width="0.8"/>
                    </svg>
                  </div>
                  <div class="header-titles">
                    <h4 class="inst-name-bn">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h4>
                    <span class="inst-name-en">DARUL QIRAT MAJIDIA FULTALI TRUST</span>
                    <div class="inst-tagline">ফুলতলী, জকিগঞ্জ, সিলেট • স্থাপিত: ১৯৫০</div>
                  </div>
                </div>

                <!-- Top Smart Tech Row: EMV Chip + Status -->
                <div class="card-smart-bar">
                  <div class="chip-and-nfc">
                    <svg class="emv-chip-svg" viewBox="0 0 46 34" width="36" height="26">
                      <defs>
                        <linearGradient id="chipGoldGradP" x1="0%" y1="0%" x2="100%" y2="100%">
                          <stop offset="0%" stop-color="#fef08a" />
                          <stop offset="25%" stop-color="#eab308" />
                          <stop offset="50%" stop-color="#fef9c3" />
                          <stop offset="80%" stop-color="#ca8a04" />
                          <stop offset="100%" stop-color="#854d0e" />
                        </linearGradient>
                      </defs>
                      <rect x="1" y="1" width="44" height="32" rx="4" fill="url(#chipGoldGradP)" stroke="#78350f" stroke-width="0.9"/>
                      <path d="M1,17 L15,17 M31,17 L45,17 M15,1 L15,33 M31,1 L31,33 M15,11 L31,11 M15,23 L31,23" stroke="#713f12" stroke-width="0.75" fill="none"/>
                      <rect x="20" y="13.5" width="6" height="7" rx="1.5" fill="#fef08a" stroke="#854d0e" stroke-width="0.6"/>
                    </svg>
                  </div>

                  <span class="card-status-badge">
                    <span class="status-dot"></span>
                    <span>নিয়মিত শিক্ষার্থী</span>
                  </span>
                </div>

                <!-- Hero Centered Student Photo -->
                <div class="card-hero-photo-wrap">
                  <div class="card-photo-wrapper hero-center">
                    <img
                      v-if="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                      :src="student?.user?.profile_image || student?.photo_url || student?.user?.avatar_url"
                      :alt="student.name_bn"
                      class="card-photo-img"
                    />
                    <div v-else class="card-photo-monogram">
                      <svg class="student-avatar-svg" viewBox="0 0 64 64" fill="none">
                        <circle cx="32" cy="32" r="30" fill="#f0fdf4" stroke="#a7f3d0" stroke-width="1.5"/>
                        <path d="M32 14 C24 14 20 18 20 23 C20 27 24 30 32 30 C40 30 44 27 44 23 C44 18 40 14 32 14 Z" fill="#064e3b" />
                        <path d="M32 20 C27 20 24 23 24 26 C24 30 27 33 32 33 C37 33 40 30 40 26 C40 23 37 20 32 20 Z" fill="#fde68a" />
                        <path d="M18 52 C18 43 24 38 32 38 C40 38 46 43 46 52 Z" fill="#064e3b" />
                        <path d="M28 38 L32 44 L36 38" fill="none" stroke="#d4af37" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span class="monogram-name">{{ student?.name_bn ? student.name_bn.split(' ')[0] : 'শিক্ষার্থী' }}</span>
                    </div>
                    <div class="photo-session-pill">
                      শিক্ষাবর্ষ: {{ currentEnrollment?.session?.name_bn || '২০২৫-২০২৬' }}
                    </div>
                  </div>
                </div>

                <!-- Student Names & Official Role Pill -->
                <div class="card-hero-names-wrap">
                  <h3 class="card-student-name-bn">{{ student?.name_bn }}</h3>
                  <div class="card-student-name-en">{{ student?.name_en || 'MUHAMMAD TANVIR AHMED' }}</div>
                  <div class="student-role-pill">
                    <span>শিক্ষার্থী • STUDENT</span>
                  </div>
                </div>

                <!-- 4-Cell Standard Academic Credentials Matrix -->
                <div class="card-cred-matrix">
                  <div class="cred-cell">
                    <span class="cred-k">আইডি নং:</span>
                    <strong class="cred-v font-mono">{{ student?.admission_number || 'ADM-2026-9353' }}</strong>
                  </div>
                  <div class="cred-cell">
                    <span class="cred-k">শ্রেণি:</span>
                    <span class="class-pill-badge">{{ currentEnrollment?.class?.name_bn || student?.class?.name_bn || 'নূরানী প্রথম শ্রেণি' }}</span>
                  </div>
                  <div class="cred-cell">
                    <span class="cred-k">রোল নং:</span>
                    <span class="roll-pill-badge">{{ student?.roll_number || currentEnrollment?.roll_number || '০১' }}</span>
                  </div>
                  <div class="cred-cell">
                    <span class="cred-k">শাখা:</span>
                    <strong class="cred-v">{{ currentEnrollment?.section?.name_bn || 'সাধারণ / ক-শাখা' }}</strong>
                  </div>
                </div>

                <!-- Barcode, Signature & Hologram Security Strip -->
                <div class="card-security-footer">
                  <div class="footer-barcode-box">
                    <svg class="vector-barcode-svg" viewBox="0 0 160 38">
                      <rect x="0" y="0" width="160" height="38" fill="#ffffff" rx="2" />
                      <g fill="#022c22">
                        <rect x="6" y="3" width="2.5" height="22" /><rect x="11" y="3" width="1.2" height="22" /><rect x="14" y="3" width="3.5" height="22" /><rect x="20" y="3" width="1.5" height="22" /><rect x="23" y="3" width="2" height="22" /><rect x="28" y="3" width="3.5" height="22" /><rect x="34" y="3" width="1.2" height="22" /><rect x="38" y="3" width="2" height="22" /><rect x="42" y="3" width="3.8" height="22" /><rect x="48" y="3" width="1.5" height="22" /><rect x="52" y="3" width="2.5" height="22" /><rect x="57" y="3" width="1.2" height="22" /><rect x="61" y="3" width="3.5" height="22" /><rect x="67" y="3" width="2" height="22" /><rect x="71" y="3" width="1.5" height="22" /><rect x="75" y="3" width="3.5" height="22" /><rect x="81" y="3" width="1.2" height="22" /><rect x="85" y="3" width="2" height="22" /><rect x="89" y="3" width="3.5" height="22" /><rect x="95" y="3" width="1.5" height="22" /><rect x="99" y="3" width="2.5" height="22" /><rect x="104" y="3" width="1.2" height="22" /><rect x="108" y="3" width="3.5" height="22" /><rect x="114" y="3" width="2" height="22" /><rect x="118" y="3" width="1.5" height="22" /><rect x="122" y="3" width="3.5" height="22" /><rect x="128" y="3" width="1.2" height="22" /><rect x="132" y="3" width="2" height="22" /><rect x="136" y="3" width="3.5" height="22" /><rect x="142" y="3" width="1.5" height="22" /><rect x="146" y="3" width="2.5" height="22" /><rect x="151" y="3" width="3" height="22" />
                      </g>
                      <text x="80" y="33" text-anchor="middle" font-family="monospace" font-size="8.5" font-weight="800" fill="#022c22">
                        * {{ student?.admission_number || 'ADM-2026-9353' }} *
                      </text>
                    </svg>
                  </div>

                  <div class="footer-signature-box">
                    <div class="signature-wrap">
                      <svg class="signature-cursive-svg" viewBox="0 0 90 28">
                        <path d="M6,22 C14,10 18,24 26,12 C32,5 36,21 44,14 C52,9 58,19 68,9 C72,7 78,14 84,9" fill="none" stroke="#043823" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M12,24 C35,27 65,24 82,18" fill="none" stroke="#043823" stroke-width="0.9" stroke-linecap="round" opacity="0.6"/>
                      </svg>
                      <div class="signature-stamp-circle">
                        <span>ختম</span>
                      </div>
                    </div>
                    <div class="sign-label">মুহতামিম / অধ্যক্ষের স্বাক্ষর</div>
                  </div>

                  <div class="hologram-seal-mockup">
                    <div class="hologram-inner">
                      <svg viewBox="0 0 20 20" width="11" height="11" fill="#ffffff">
                        <path d="M10 1 L12.5 6.5 L18.5 7 L14 11 L15.5 17 L10 14 L4.5 17 L6 11 L1.5 7 L7.5 6.5 Z"/>
                      </svg>
                      <span class="holo-line1">SECURE</span>
                      <span class="holo-line2">VERIFIED</span>
                    </div>
                  </div>
                </div>

                <div class="card-microprint-strip">
                  DARUL QIRAT MAJIDIA FULTALI TRUST • OFFICIAL STUDENT SMART ID • ISO/IEC 7810 ID-1
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
                <div class="magnetic-stripe-bar">
                  <div class="mag-stripe-shimmer"></div>
                  <span class="mag-stripe-text">DARUL QIRAT MAJIDIA FULTALI TRUST • ENCRYPTED MAGNETIC ENCODE</span>
                </div>

                <div class="card-security-pattern-bg"></div>

                <div class="card-back-header">
                  <div class="back-header-titles">
                    <h4 class="back-inst-bn">দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h4>
                    <p class="back-inst-sub">কেন্দ্রীয় কার্যালয়: ফুলতলী, জকিগঞ্জ, সিলেট - ৩১৯০ • প্রশাসন ও পরীক্ষা নিয়ন্ত্রণ বিভাগ</p>
                  </div>
                </div>

                <!-- Emergency & Medical Section -->
                <div class="back-emergency-card">
                  <div class="em-top-row">
                    <span class="blood-emergency-badge">
                      <span>🩸 রক্তের গ্রুপ: <strong>{{ student?.blood_group || 'B+' }} (পজিটিভ)</strong></span>
                    </span>
                    <span class="dob-badge">
                      <span>জন্ম: <strong>{{ student?.date_of_birth ? formatDate(student.date_of_birth) : '১৫ মার্চ, ২০১৬' }}</strong></span>
                    </span>
                  </div>
                  <div class="em-guardian-row">
                    <div class="em-field">
                      <span class="em-k">অভিভাবক:</span>
                      <strong class="em-v text-truncate">{{ student?.guardian_name || student?.father_name || 'মাওলানা শফিকুল ইসলাম' }}</strong>
                    </div>
                    <div class="em-field hotline-highlight">
                      <span class="em-k">জরুরি:</span>
                      <strong class="em-v font-mono">{{ student?.guardian_phone || student?.father_phone || student?.user?.phone || '০১৭১১-২২৩৩৪৪' }}</strong>
                    </div>
                  </div>
                  <div class="em-address-row">
                    <span class="em-k">স্থায়ী ঠিকানা:</span>
                    <span class="em-v">{{ student?.address_bn || 'গ্রাম: ফুলতলী, ডাকঘর: ফুলতলী বাজার, উপজেলা: জকিগঞ্জ, জেলা: সিলেট' }}</span>
                  </div>
                </div>

                <!-- Terms & Rules -->
                <div class="back-terms-box">
                  <div class="terms-title">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="#043823" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>সাধারণ নির্দেশাবলী ও নিয়মাবলী:</span>
                  </div>
                  <ul class="terms-list">
                    <li><span class="term-bullet">◆</span><span>এই পরিচয়পত্রটি মাদ্রাসার সার্বক্ষণিক সম্পত্তি ও ক্যাম্পাসে বহন বাধ্যতামূলক।</span></li>
                    <li><span class="term-bullet">◆</span><span>কার্ডটি অহস্তান্তরযোগ্য; কেবল উল্লেখিত শিক্ষার্থীর ব্যবহারের জন্য প্রযোজ্য।</span></li>
                    <li><span class="term-bullet">◆</span><span>স্মার্ট ডিজিটাল হাজিরা, লাইব্রেরি ও পরীক্ষার হলে কার্ড প্রদর্শন আবশ্যক।</span></li>
                    <li><span class="term-bullet">◆</span><span>কার্ড হারালে বা ক্ষতিগ্রস্ত হলে অবিলম্বে প্রশাসন কার্যালয়ে রিপোর্ট করতে হবে।</span></li>
                  </ul>
                </div>

                <!-- QR Code & Return Address Row -->
                <div class="back-qr-return-row">
                  <div class="back-return-info">
                    <div class="return-label">কার্ড হারিয়ে গেলে ফেরত ঠিকানা:</div>
                    <address class="return-address">
                      <strong>দারুল ক্বিরাত মজিদিয়া ফুলতলী ট্রাস্ট</strong><br />
                      ফুলতলী, জকিগঞ্জ, সিলেট - ৩১৯০<br />
                      হেল্পলাইন: +৮৮০১৭১২-৩৪৫৬৭৮ • ওয়েব: attashil.softcredible.com
                    </address>
                  </div>

                  <div class="back-qr-box">
                    <div class="qr-frame-reticle">
                      <img
                        v-if="qrCodeDataUrl"
                        :src="qrCodeDataUrl"
                        alt="Verification QR Code"
                        class="qr-code-img"
                      />
                      <div v-else class="qr-placeholder">QR</div>
                      <div class="reticle-corner top-left"></div>
                      <div class="reticle-corner top-right"></div>
                      <div class="reticle-corner bottom-left"></div>
                      <div class="reticle-corner bottom-right"></div>
                    </div>
                    <span class="qr-label">অনলাইনে যাচাই করুন</span>
                  </div>
                </div>

                <!-- Validity & Security Footer -->
                <div class="back-validity-footer">
                  <div class="validity-text">
                    <span class="val-k">মেয়াদ উত্তীর্ণ:</span>
                    <strong class="val-v">৩১ ডিসেম্বর, ২০২৬</strong>
                    <span class="val-en">(VALID THRU: 12/2026)</span>
                  </div>
                  <div class="back-microprint">
                    ENCRYPTED STUDENT CREDENTIAL • DARUL QIRAT MAJIDIA FULTALI TRUST • SECURE RFID
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="print-footer-notice">
            <p>• নির্দেশনা: সরাসরি পিভিসি কার্ড প্রিন্টারে প্রিন্ট করুন অথবা এ-ফোর (A4) ফটো পেপারে প্রিন্ট করে দাগ বরাবর কেটে পিভিসি পাউচে লেমিনেট করুন।</p>
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

        <!-- In-App Quick Edit Modal -->
        <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
          <div class="modal-card modal-lg animate-fade-in" style="max-height: 90vh; overflow-y: auto; max-width: 800px; width: 95%;">
            <div class="modal-header">
              <div class="modal-title-group">
                <h3>শিক্ষার্থীর তথ্য সরাসরি সম্পাদনা</h3>
                <p>শিক্ষার্থীর ব্যক্তিগত, জামাত ও অভিভাবক সংক্রান্ত তথ্য পরিবর্তন করুন</p>
              </div>
              <button class="action-btn" @click="showEditModal = false">
                <Icon name="close" />
              </button>
            </div>

            <div v-if="editError" class="alert alert-error" style="margin: 1rem 1.5rem 0; background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px;">
              <span>{{ editError }}</span>
            </div>

            <form @submit.prevent="saveQuickEdit" style="padding: 1.25rem 1.5rem;">
              <!-- Personal -->
              <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-primary, #1e3a8a); margin-bottom: 0.75rem; border-bottom: 1px solid var(--color-border-light, #e5e7eb); padding-bottom: 0.35rem;">
                ব্যক্তিগত তথ্য
              </h4>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.85rem; margin-bottom: 1.25rem;">
                <div class="form-group">
                  <label class="form-label">নাম (বাংলায়) *</label>
                  <input v-model="editForm.name_bn" class="form-control" placeholder="বাংলায় নাম" required />
                </div>
                <div class="form-group">
                  <label class="form-label">নাম (ইংরেজিতে)</label>
                  <input v-model="editForm.name_en" class="form-control" placeholder="English Name" />
                </div>
                <div class="form-group">
                  <label class="form-label">ভর্তি নম্বর</label>
                  <input v-model="editForm.admission_number" class="form-control" placeholder="ADM-2026-001" />
                </div>
                <div class="form-group">
                  <label class="form-label">রোল নম্বর</label>
                  <input v-model="editForm.roll_number" class="form-control" placeholder="যেমন: ০৫" />
                </div>
                <div class="form-group">
                  <label class="form-label">মোবাইল নম্বর</label>
                  <input v-model="editForm.phone" class="form-control" placeholder="017XXXXXXXX" />
                </div>
                <div class="form-group">
                  <label class="form-label">ইমেইল (ঐচ্ছিক)</label>
                  <input v-model="editForm.email" class="form-control" placeholder="email@domain.com" />
                </div>
                <div class="form-group">
                  <label class="form-label">জন্ম তারিখ</label>
                  <input v-model="editForm.date_of_birth" type="date" class="form-control" />
                </div>
                <div class="form-group">
                  <label class="form-label">লিঙ্গ</label>
                  <select v-model="editForm.gender" class="form-control form-select">
                    <option value="">নির্বাচন করুন</option>
                    <option value="ছেলে">ছেলে</option>
                    <option value="মেয়ে">মেয়ে</option>
                    <option value="অন্যান্য">অন্যান্য</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">রক্তের গ্রুপ</label>
                  <select v-model="editForm.blood_group" class="form-control form-select">
                    <option value="">নির্বাচন করুন</option>
                    <option value="A+">A+</option><option value="A-">A-</option>
                    <option value="B+">B+</option><option value="B-">B-</option>
                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                    <option value="O+">O+</option><option value="O-">O-</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">অবস্থা (Status)</label>
                  <select v-model="editForm.is_active" class="form-control form-select">
                    <option :value="true">সক্রিয় (Active)</option>
                    <option :value="false">নিষ্ক্রিয় (Inactive)</option>
                  </select>
                </div>
              </div>

              <!-- Academic -->
              <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-primary, #1e3a8a); margin: 1.25rem 0 0.75rem 0; border-bottom: 1px solid var(--color-border-light, #e5e7eb); padding-bottom: 0.35rem;">
                জামাত ও শ্রেণি
              </h4>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.85rem; margin-bottom: 1.25rem;">
                <div class="form-group">
                  <label class="form-label">শ্রেণি / জামাত</label>
                  <select v-model="editForm.class_id" class="form-control form-select" @change="onEditClassChange">
                    <option value="">শ্রেণি নির্বাচন করুন</option>
                    <option v-for="cls in editClassOptions" :key="cls.id" :value="cls.id">
                      {{ cls.name_bn || cls.name }}
                    </option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">শাখা / সেকশন</label>
                  <select v-model="editForm.section_id" class="form-control form-select">
                    <option value="">শাখা নেই / সাধারণ</option>
                    <option v-for="sec in editSectionOptions" :key="sec.id" :value="sec.id">
                      {{ sec.name_bn || sec.name }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Parents & Guardian -->
              <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-primary, #1e3a8a); margin: 1.25rem 0 0.75rem 0; border-bottom: 1px solid var(--color-border-light, #e5e7eb); padding-bottom: 0.35rem;">
                পিতা-মাতা ও অভিভাবকের তথ্য
              </h4>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.85rem; margin-bottom: 1.25rem;">
                <div class="form-group">
                  <label class="form-label">পিতার নাম</label>
                  <input v-model="editForm.father_name" class="form-control" placeholder="পিতার নাম" />
                </div>
                <div class="form-group">
                  <label class="form-label">পিতার মোবাইল</label>
                  <input v-model="editForm.father_phone" class="form-control" placeholder="017XXXXXXXX" />
                </div>
                <div class="form-group">
                  <label class="form-label">মাতার নাম</label>
                  <input v-model="editForm.mother_name" class="form-control" placeholder="মাতার নাম" />
                </div>
                <div class="form-group">
                  <label class="form-label">মাতার মোবাইল</label>
                  <input v-model="editForm.mother_phone" class="form-control" placeholder="017XXXXXXXX" />
                </div>
                <div class="form-group">
                  <label class="form-label">অভিভাবকের নাম</label>
                  <input v-model="editForm.guardian_name" class="form-control" placeholder="অভিভাবকের নাম" />
                </div>
                <div class="form-group">
                  <label class="form-label">অভিভাবকের মোবাইল</label>
                  <input v-model="editForm.guardian_phone" class="form-control" placeholder="018XXXXXXXX" />
                </div>
              </div>

              <!-- Address -->
              <div class="form-group" style="margin-top: 1rem;">
                <label class="form-label">স্থায়ী ও বর্তমান ঠিকানা</label>
                <textarea v-model="editForm.address_bn" class="form-control" rows="2" placeholder="গ্রাম, ডাকঘর, থানা, জেলা..."></textarea>
              </div>

              <div class="modal-footer" style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--color-border-light, #e5e7eb); padding-top: 1rem;">
                <button type="button" class="btn btn-ghost" @click="showEditModal = false">বাতিল</button>
                <button type="submit" class="btn btn-primary" :disabled="editSaving || !editForm.name_bn">
                  {{ editSaving ? 'সংরক্ষণ হচ্ছে...' : 'আপডেট সম্পন্ন করুন' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </ClientOnly>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
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
const showEditModal = ref(false)
const editSaving = ref(false)
const editError = ref('')
const editClassOptions = ref<any[]>([])
const editSectionOptions = ref<any[]>([])

const editForm = reactive({
  name_bn: '',
  name_en: '',
  admission_number: '',
  roll_number: '',
  phone: '',
  email: '',
  date_of_birth: '',
  gender: '',
  blood_group: '',
  nationality: 'বাংলাদেশী',
  class_id: '' as string | number,
  section_id: '' as string | number,
  is_active: true,
  father_name: '',
  father_phone: '',
  mother_name: '',
  mother_phone: '',
  guardian_name: '',
  guardian_phone: '',
  guardian_relation: '',
  address_bn: '',
  health_summary: '',
})

async function loadEditClasses() {
  try {
    const res = await api.get('/academic/classes').catch(() => null)
    if (res?.data?.data) {
      editClassOptions.value = res.data.data
    } else {
      const alt = await api.get('/settings/classes').catch(() => ({ data: { data: [] } }))
      editClassOptions.value = alt.data?.data || []
    }
  } catch (e) {
    console.error(e)
  }
}

async function loadEditSections(classId: string | number) {
  if (!classId) {
    editSectionOptions.value = []
    return
  }
  try {
    const res = await api.get(`/academic/sections?class_id=${classId}`).catch(() => null)
    editSectionOptions.value = res?.data?.data || []
  } catch (e) {
    console.error(e)
  }
}

function onEditClassChange() {
  editForm.section_id = ''
  if (editForm.class_id) {
    loadEditSections(editForm.class_id)
  }
}

const editSuccess = ref('')

function populateEditForm() {
  if (!student.value) return
  const s = student.value
  editForm.name_bn = s.name_bn || s.user?.name_bn || s.user?.name || ''
  editForm.name_en = s.name_en || s.user?.name_en || ''
  editForm.admission_number = s.admission_number || ''
  editForm.roll_number = s.roll_number || currentEnrollment.value?.roll_number || s.enrollments?.[0]?.roll_number || ''
  editForm.phone = s.phone || s.user?.phone || s.guardian?.phone || s.father_phone || ''
  editForm.email = s.email || s.user?.email || ''
  editForm.date_of_birth = s.date_of_birth ? String(s.date_of_birth).slice(0, 10) : ''
  editForm.gender = s.gender || ''
  editForm.blood_group = s.blood_group || ''
  editForm.nationality = s.nationality || 'বাংলাদেশী'
  editForm.is_active = s.status ? s.status === 'active' : (s.is_active !== undefined ? !!s.is_active : true)

  const activeClassId = s.class_id || currentEnrollment.value?.class_id || s.enrollments?.[0]?.class_id || s.class?.id || ''
  const activeSectionId = s.section_id || currentEnrollment.value?.section_id || s.enrollments?.[0]?.section_id || s.section?.id || ''
  editForm.class_id = activeClassId
  if (activeClassId) {
    loadEditSections(activeClassId)
  }
  editForm.section_id = activeSectionId

  editForm.father_name = s.father_name || s.guardian?.father_name || ''
  editForm.father_phone = s.father_phone || s.guardian?.father_phone || ''
  editForm.mother_name = s.mother_name || s.guardian?.mother_name || ''
  editForm.mother_phone = s.mother_phone || s.guardian?.mother_phone || ''
  editForm.guardian_name = s.guardian_name || s.guardian?.guardian_name || ''
  editForm.guardian_phone = s.guardian_phone || s.guardian?.guardian_phone || ''
  editForm.guardian_relation = s.guardian_relation || s.guardian?.relation || s.guardian?.relationship || ''
  editForm.address_bn = s.address_bn || ''
  editForm.health_summary = typeof s.health_summary === 'string' ? s.health_summary : (s.health_summary?.notes || '')
}

function switchToEditTab() {
  populateEditForm()
  activeTab.value = 'edit'
}

function openQuickEditModal() {
  populateEditForm()
  editError.value = ''
  editSuccess.value = ''
  showEditModal.value = true
}

async function saveQuickEdit() {
  if (!editForm.name_bn.trim() || !student.value) return
  editSaving.value = true
  editError.value = ''

  try {
    const payload: Record<string, any> = {
      name_bn: editForm.name_bn?.trim(),
      name_en: editForm.name_en?.trim() || null,
      admission_number: editForm.admission_number?.trim() || null,
      roll_number: editForm.roll_number?.trim() || null,
      phone: editForm.phone?.trim() || null,
      email: editForm.email?.trim() || null,
      date_of_birth: editForm.date_of_birth || null,
      gender: editForm.gender || null,
      blood_group: editForm.blood_group || null,
      nationality: editForm.nationality || 'বাংলাদেশী',
      class_id: editForm.class_id ? Number(editForm.class_id) : null,
      section_id: editForm.section_id ? Number(editForm.section_id) : null,
      is_active: !!editForm.is_active,
      father_name: editForm.father_name?.trim() || null,
      father_phone: editForm.father_phone?.trim() || null,
      mother_name: editForm.mother_name?.trim() || null,
      mother_phone: editForm.mother_phone?.trim() || null,
      guardian_name: editForm.guardian_name?.trim() || null,
      guardian_phone: editForm.guardian_phone?.trim() || null,
      guardian_relation: editForm.guardian_relation?.trim() || null,
      address_bn: editForm.address_bn?.trim() || null,
      health_summary: editForm.health_summary?.trim() || null,
    }

    await api.put(`/students/${student.value.id}`, payload)
    showEditModal.value = false
    await loadStudent()
    alert('শিক্ষার্থীর তথ্য সফলভাবে আপডেট করা হয়েছে!')
  } catch (err: any) {
    console.error('Update failed:', err)
    editError.value = err?.response?.data?.message || 'সংরক্ষণে ত্রুটি হয়েছে। তথ্যাবলী যাচাই করুন।'
  } finally {
    editSaving.value = false
  }
}

async function saveInlineEdit() {
  if (!editForm.name_bn.trim() || !student.value) return
  editSaving.value = true
  editError.value = ''
  editSuccess.value = ''

  try {
    const payload: Record<string, any> = {
      name_bn: editForm.name_bn?.trim(),
      name_en: editForm.name_en?.trim() || null,
      admission_number: editForm.admission_number?.trim() || null,
      roll_number: editForm.roll_number?.trim() || null,
      phone: editForm.phone?.trim() || null,
      email: editForm.email?.trim() || null,
      date_of_birth: editForm.date_of_birth || null,
      gender: editForm.gender || null,
      blood_group: editForm.blood_group || null,
      nationality: editForm.nationality || 'বাংলাদেশী',
      class_id: editForm.class_id ? Number(editForm.class_id) : null,
      section_id: editForm.section_id ? Number(editForm.section_id) : null,
      is_active: !!editForm.is_active,
      father_name: editForm.father_name?.trim() || null,
      father_phone: editForm.father_phone?.trim() || null,
      mother_name: editForm.mother_name?.trim() || null,
      mother_phone: editForm.mother_phone?.trim() || null,
      guardian_name: editForm.guardian_name?.trim() || null,
      guardian_phone: editForm.guardian_phone?.trim() || null,
      guardian_relation: editForm.guardian_relation?.trim() || null,
      address_bn: editForm.address_bn?.trim() || null,
      health_summary: editForm.health_summary?.trim() || null,
    }

    const res = await api.put(`/students/${student.value.id}`, payload)
    editSuccess.value = res.data?.message || 'শিক্ষার্থীর তথ্য সফলভাবে আপডেট করা হয়েছে!'
    await loadStudent()
    setTimeout(() => {
      activeTab.value = 'overview'
    }, 1000)
  } catch (err: any) {
    console.error('Update failed:', err)
    editError.value = err?.response?.data?.message || 'সংরক্ষণে ত্রুটি হয়েছে। তথ্যাবলী যাচাই করুন।'
  } finally {
    editSaving.value = false
  }
}

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
  { key: 'edit', label: 'তথ্য সম্পাদনা', icon: 'pencil' },
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
    populateEditForm()
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
  window.scrollTo(0, 0)
  setTimeout(() => {
    window.print()
  }, 60)
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

onMounted(async () => {
  await loadEditClasses()
  await loadStudent()
  if (route.query.edit === 'true' || route.hash === '#edit') {
    switchToEditTab()
  }
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
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  overflow-y: auto;
}

.id-card-modal-container {
  width: 100%;
  max-width: 940px;
  max-height: 94vh;
  margin: auto;
  display: flex;
  flex-direction: column;
  background: var(--color-bg-card, #ffffff);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 30px 65px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.15);
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
  padding: 2rem 1.5rem;
  background: radial-gradient(circle at 50% 30%, #f1f5f9 0%, #cbd5e1 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 560px;
  max-height: calc(94vh - 150px);
  overflow-y: auto;
  overflow-x: auto;
  flex: 1;
}

.id-cards-display-wrap {
  display: flex;
  gap: 2rem;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  padding: 0.5rem 0;
}

/* =========================================================
   STANDARD CR-80 EXECUTIVE PVC SMART CARD (326px × 516px)
   ========================================================= */
.id-card-cr80 {
  width: 326px;
  height: 516px;
  background: #ffffff;
  border-radius: 16px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.35), 0 4px 14px rgba(0, 0, 0, 0.12), inset 0 0 0 1px rgba(212, 175, 55, 0.35);
  display: flex;
  flex-direction: column;
  user-select: none;
  border: 1px solid #cbd5e1;
  transition: transform 0.25s ease, box-shadow 0.25s ease;

  &:hover {
    transform: translateY(-3px);
    box-shadow: 0 25px 52px -8px rgba(0, 0, 0, 0.45), 0 6px 18px rgba(0, 0, 0, 0.16);
  }
}

/* Lanyard Slot Punch Mockup */
.lanyard-hole-slot {
  width: 48px;
  height: 8px;
  background: rgba(0, 0, 0, 0.28);
  border-radius: 99px;
  position: absolute;
  top: 7px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  border: 1px solid rgba(255, 255, 255, 0.45);
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.5);
}

/* Guilloche Security Pattern Background */
.card-security-pattern-bg {
  position: absolute;
  inset: 0;
  opacity: 0.045;
  pointer-events: none;
  background-image: radial-gradient(#043823 1px, transparent 1px), radial-gradient(#043823 1px, transparent 1px);
  background-size: 14px 14px;
  background-position: 0 0, 7px 7px;
  z-index: 1;
}

/* ==================== FRONT SIDE ==================== */
.id-card-front {
  background: #ffffff;
}

.card-front-header {
  background: linear-gradient(145deg, #012215 0%, #064e3b 55%, #022a1b 100%);
  color: #ffffff;
  padding: 1.45rem 0.85rem 0.65rem;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  position: relative;
  z-index: 2;
  border-bottom: 1px solid #d4af37;
}

.header-seal-wrap {
  flex-shrink: 0;
}

.madrasa-seal-svg {
  width: 44px;
  height: 44px;
  filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.45));
}

.header-titles {
  flex: 1;
  min-width: 0;
}

.inst-name-bn {
  margin: 0;
  font-size: 13.5px;
  font-weight: 800;
  color: #ffffff;
  font-family: var(--font-bn);
  line-height: 1.25;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.inst-name-en {
  display: block;
  font-size: 7.2px;
  font-weight: 700;
  color: #fef08a;
  letter-spacing: 0.8px;
  margin-top: 1px;
}

.inst-tagline {
  font-size: 7.5px;
  color: #d1fae5;
  font-family: var(--font-bn);
  margin-top: 1px;
}

/* Top Smart Tech Row */
.card-smart-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.35rem 0.85rem 0.25rem;
  background: linear-gradient(90deg, #f8fafc 0%, #ffffff 50%, #f8fafc 100%);
  border-bottom: 1px solid #e2e8f0;
  position: relative;
  z-index: 2;

  .chip-and-nfc {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .emv-chip-svg {
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.25));
  }

  .nfc-waves-svg {
    opacity: 0.85;
  }

  .card-status-badge {
    font-size: 8px;
    font-weight: 700;
    color: #065f46;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 99px;
    padding: 1.5px 7px;
    font-family: var(--font-bn);
    display: inline-flex;
    align-items: center;
    gap: 4px;

    .status-dot {
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background: #10b981;
      box-shadow: 0 0 0 2px #d1fae5;
    }
  }
}

/* Hero Centered Student Photo */
.card-hero-photo-wrap {
  display: flex;
  justify-content: center;
  padding-top: 0.45rem;
  position: relative;
  z-index: 2;
}

.card-photo-wrapper.hero-center {
  position: relative;
  width: 104px;
  height: 122px;
  border-radius: 9px;
  border: 2px solid #d4af37;
  outline: 2px solid #ffffff;
  box-shadow: 0 6px 14px -3px rgba(1, 34, 21, 0.3);
  background: #f8fafc;
  overflow: hidden;
  flex-shrink: 0;
}

.card-photo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-photo-monogram {
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, #f0fdf4 0%, #dcfce7 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding-bottom: 12px;

  .student-avatar-svg {
    width: 54px;
    height: 54px;
    filter: drop-shadow(0 2px 4px rgba(6, 78, 59, 0.2));
  }

  .monogram-name {
    font-size: 8.5px;
    font-weight: 700;
    color: #064e3b;
    font-family: var(--font-bn);
    margin-top: 2px;
    max-width: 80px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}

.photo-session-pill {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(90deg, #012215 0%, #064e3b 100%);
  color: #fef08a;
  font-size: 7.5px;
  font-weight: 700;
  text-align: center;
  padding: 2px 0;
  font-family: var(--font-bn);
  letter-spacing: 0.5px;
  border-top: 1px solid #d4af37;
}

/* Hero Names & Official Role Pill */
.card-hero-names-wrap {
  text-align: center;
  padding: 0.4rem 0.85rem 0.2rem;
  position: relative;
  z-index: 2;

  .card-student-name-bn {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #012215;
    font-family: var(--font-bn);
    line-height: 1.2;
  }

  .card-student-name-en {
    font-size: 8.5px;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.8px;
    margin: 1.5px 0 3.5px;
    text-transform: uppercase;
  }

  .student-role-pill {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(90deg, #012215 0%, #064e3b 100%);
    color: #fef08a;
    font-size: 8px;
    font-weight: 800;
    padding: 2px 10px;
    border-radius: 99px;
    letter-spacing: 0.6px;
    font-family: var(--font-bn);
    border: 1px solid #d4af37;
    box-shadow: 0 2px 5px rgba(1, 34, 21, 0.2);
  }
}

/* 4-Cell Standard Academic Credentials Matrix */
.card-cred-matrix {
  margin: 0.35rem 0.85rem 0.3rem;
  background: #fbfdfc;
  border: 1px solid #e2e8f0;
  border-top: 2px solid #d4af37;
  border-radius: 7px;
  padding: 0.4rem 0.6rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.35rem 0.5rem;
  font-family: var(--font-bn);
  position: relative;
  z-index: 2;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);

  .cred-cell {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.25rem;
    font-size: 9px;
  }

  .cred-k {
    color: #64748b;
    font-size: 8.5px;
    white-space: nowrap;
  }

  .cred-v {
    color: #0f172a;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}

.class-pill-badge {
  background: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
  padding: 1px 6px;
  border-radius: 3px;
  font-weight: 700;
  font-size: 8.5px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.roll-pill-badge {
  background: #fffbeb;
  color: #92400e;
  border: 1px solid #fde68a;
  padding: 1px 7px;
  border-radius: 3px;
  font-weight: 800;
  font-family: monospace;
  font-size: 9px;
}

/* Barcode, Signature & Hologram Security Strip */
.card-security-footer {
  margin-top: auto;
  padding: 0.45rem 0.85rem 0.35rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px dashed #cbd5e1;
  background: #ffffff;
  position: relative;
  z-index: 2;
}

.footer-barcode-box {
  width: 130px;
}

.vector-barcode-svg {
  width: 100%;
  height: 32px;
  display: block;
}

.footer-signature-box {
  text-align: center;
}

.signature-wrap {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 22px;
}

.signature-cursive-svg {
  width: 68px;
  height: 22px;
  display: block;
}

.signature-stamp-circle {
  position: absolute;
  right: -4px;
  top: -2px;
  width: 22px;
  height: 22px;
  border: 1.5px solid #dc2626;
  border-radius: 50%;
  color: #dc2626;
  font-size: 8px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.75;
  transform: rotate(-12deg);
  background: rgba(254, 242, 242, 0.4);
}

.sign-label {
  font-size: 7.5px;
  color: #64748b;
  font-family: var(--font-bn);
  border-top: 1px solid #cbd5e1;
  padding-top: 1px;
}

.hologram-seal-mockup {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: radial-gradient(circle at 35% 35%, #fffbeb 0%, #fef08a 25%, #d4af37 55%, #b45309 85%, #78350f 100%);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25), inset 0 0 4px rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #fef08a;
  transform: rotate(-10deg);
}

.hologram-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  line-height: 1;

  .holo-line1 {
    font-size: 4.8px;
    font-weight: 900;
    color: #78350f;
    letter-spacing: 0.5px;
  }

  .holo-line2 {
    font-size: 4.2px;
    font-weight: 800;
    color: #012215;
    letter-spacing: 0.4px;
  }
}

.card-microprint-strip {
  background: #012215;
  color: #a7f3d0;
  font-size: 6px;
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

.magnetic-stripe-bar {
  height: 34px;
  background: linear-gradient(180deg, #111827 0%, #1f2937 45%, #111827 70%, #030712 100%);
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  padding: 0 12px;
  margin-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  border-bottom: 1px solid rgba(0, 0, 0, 0.6);
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.6);

  .mag-stripe-shimmer {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.07) 50%, transparent 100%);
    pointer-events: none;
  }

  .mag-stripe-text {
    color: rgba(255, 255, 255, 0.22);
    font-size: 5.5px;
    font-family: monospace;
    letter-spacing: 1.2px;
    white-space: nowrap;
    overflow: hidden;
  }
}

.card-back-header {
  background: linear-gradient(145deg, #012215 0%, #064e3b 100%);
  color: #ffffff;
  padding: 0.55rem 0.85rem 0.45rem;
  text-align: center;
  border-bottom: 2px solid #d4af37;
  position: relative;
  z-index: 2;
}

.back-inst-bn {
  margin: 0;
  font-size: 11px;
  font-weight: 800;
  color: #fef08a;
  font-family: var(--font-bn);
}

.back-inst-sub {
  margin: 2px 0 0;
  font-size: 7px;
  color: #d1fae5;
  font-family: var(--font-bn);
}

/* Emergency & Medical Alert Card Box */
.back-emergency-card {
  margin: 0.45rem 0.85rem 0.3rem;
  background: #fff1f2;
  border: 1px solid #fecdd3;
  border-radius: 6px;
  padding: 0.4rem 0.65rem;
  position: relative;
  z-index: 2;

  .em-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3px;
  }

  .blood-emergency-badge {
    font-size: 8.5px;
    font-weight: 800;
    color: #b91c1c;
    font-family: var(--font-bn);
  }

  .dob-badge {
    font-size: 7.8px;
    color: #475569;
    font-family: var(--font-bn);

    strong {
      color: #0f172a;
    }
  }

  .em-guardian-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.35rem;
    margin-bottom: 2px;
    font-size: 8px;
    font-family: var(--font-bn);
  }

  .em-field {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

    &.hotline-highlight {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 3px;
      padding: 1px 5px;

      .em-v {
        color: #166534;
        font-weight: 800;
      }
    }
  }

  .em-k {
    color: #64748b;
  }

  .em-v {
    color: #0f172a;
    font-weight: 700;
  }

  .em-address-row {
    font-size: 7.2px;
    color: #334155;
    font-family: var(--font-bn);
    line-height: 1.25;
    display: flex;
    gap: 0.25rem;
    margin-top: 2px;
    border-top: 1px dashed #fecaca;
    padding-top: 2px;
  }
}

/* Terms Box */
.back-terms-box {
  margin: 0.3rem 0.85rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 0.4rem 0.65rem;
  position: relative;
  z-index: 2;
}

.terms-title {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 8.5px;
  font-weight: 800;
  color: #012215;
  font-family: var(--font-bn);
  margin-bottom: 3px;
}

.terms-list {
  margin: 0;
  padding: 0;
  list-style: none;
  font-size: 7.5px;
  color: #475569;
  font-family: var(--font-bn);
  line-height: 1.35;

  li {
    display: flex;
    align-items: flex-start;
    gap: 4px;
    margin-bottom: 2px;
  }

  .term-bullet {
    color: #d4af37;
    font-size: 6px;
    margin-top: 1px;
    flex-shrink: 0;
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
  font-size: 7.5px;
  font-weight: 700;
  color: #012215;
  font-family: var(--font-bn);
  margin-bottom: 1px;
}

.return-address {
  font-style: normal;
  font-size: 7px;
  color: #64748b;
  font-family: var(--font-bn);
  line-height: 1.35;

  strong {
    color: #043823;
  }
}

.back-qr-box {
  width: 68px;
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
}

.qr-frame-reticle {
  position: relative;
  padding: 3px;
  background: #ffffff;
  border-radius: 4px;
  border: 1px solid #e2e8f0;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;

  .reticle-corner {
    position: absolute;
    width: 6px;
    height: 6px;
    pointer-events: none;
    border-color: #d4af37;
    border-style: solid;

    &.top-left {
      top: 0;
      left: 0;
      border-width: 1.5px 0 0 1.5px;
    }
    &.top-right {
      top: 0;
      right: 0;
      border-width: 1.5px 1.5px 0 0;
    }
    &.bottom-left {
      bottom: 0;
      left: 0;
      border-width: 0 0 1.5px 1.5px;
    }
    &.bottom-right {
      bottom: 0;
      right: 0;
      border-width: 0 1.5px 1.5px 0;
    }
  }
}

.qr-code-img {
  width: 52px;
  height: 52px;
  display: block;
}

.qr-placeholder {
  width: 52px;
  height: 52px;
  background: #f1f5f9;
  border: 1px dashed #cbd5e1;
  display: grid;
  place-items: center;
  font-size: 10px;
  font-weight: bold;
  color: #64748b;
}

.qr-label {
  font-size: 6.8px;
  font-weight: 700;
  color: #043823;
  font-family: var(--font-bn);
  margin-top: 2px;
  text-align: center;
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
  color: #012215;
  font-family: var(--font-bn);
  background: #f8fafc;
  padding: 3px 0;
  border-top: 1px dashed #cbd5e1;

  .val-k {
    color: #64748b;
    margin-right: 4px;
  }

  .val-v {
    color: #012215;
    font-weight: 800;
    margin-right: 4px;
  }

  .val-en {
    color: #64748b;
    font-size: 7px;
  }
}

.back-microprint {
  background: #012215;
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
</style>

<!-- Dedicated unscoped print stylesheet so global tags like #__nuxt and body are properly manipulated in print -->
<style lang="scss">
/* Hidden on screen across the entire application */
@media screen {
  .print-only-sheet,
  #dedicated-print-sheet {
    display: none !important;
  }
}

/* Print styles for A4 sheet with 2 CR-80 badges */
@media print {
  @page {
    size: A4 portrait;
    margin: 8mm 10mm;
  }

  /* Reset root body and html */
  html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: #ffffff !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    overflow: visible !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  /* Completely collapse and remove the entire Nuxt application from the print tree */
  #__nuxt,
  #app,
  .default-layout,
  .layout-main,
  .layout-content,
  .page-wrapper,
  .sidebar,
  .topbar,
  .sidebar-backdrop,
  .ai-float-btn,
  .ai-float-overlay,
  .id-card-modal-overlay,
  .modal-overlay,
  .no-print {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    max-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
    opacity: 0 !important;
  }

  /* Dedicated print sheet takes top of Page 1 */
  #dedicated-print-sheet {
    display: block !important;
    visibility: visible !important;
    position: static !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    max-width: 195mm !important;
    margin: 0 auto !important;
    padding: 3mm 0 !important;
    background: #ffffff !important;
    opacity: 1 !important;
    box-sizing: border-box !important;
  }

  #dedicated-print-sheet,
  #dedicated-print-sheet * {
    visibility: visible !important;
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

    .magnetic-stripe-bar {
      height: 5.5mm !important;
      margin-top: 0 !important;
      padding: 0 2mm !important;
      .mag-stripe-text {
        font-size: 3pt !important;
      }
    }

    .card-front-header {
      padding: 1.8mm 2mm 1.2mm !important;
      gap: 1.5mm !important;
      border-bottom: 1px solid #d4af37 !important;
    }

    .madrasa-seal-svg {
      width: 7.5mm !important;
      height: 7.5mm !important;
    }

    .inst-name-bn {
      font-size: 6.2pt !important;
      line-height: 1.2 !important;
    }

    .inst-name-en {
      font-size: 3.5pt !important;
      letter-spacing: 0.2px !important;
    }

    .inst-tagline {
      font-size: 3.4pt !important;
    }

    .card-smart-bar {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      padding: 0.8mm 2mm 0.5mm !important;
      border-bottom: 0.5px solid #cbd5e1 !important;

      .chip-and-nfc {
        display: flex !important;
        align-items: center !important;
        gap: 1mm !important;
      }

      .emv-chip-svg {
        width: 6mm !important;
        height: 4.5mm !important;
      }

      .nfc-waves-svg {
        display: none !important;
      }

      .card-status-badge {
        font-size: 3.6pt !important;
        padding: 0.2mm 0.8mm !important;
        .status-dot {
          width: 1mm !important;
          height: 1mm !important;
        }
      }
    }

    .card-hero-photo-wrap {
      display: flex !important;
      justify-content: center !important;
      padding-top: 0.8mm !important;
    }

    .card-photo-wrapper.hero-center {
      width: 17mm !important;
      height: 21mm !important;
      border-radius: 1.5mm !important;
      border-width: 1px !important;
    }

    .photo-session-pill {
      font-size: 3.6pt !important;
      padding: 0.3mm 0 !important;
    }

    .card-photo-monogram {
      .student-avatar-svg {
        width: 9mm !important;
        height: 9mm !important;
      }
      .monogram-name {
        font-size: 3.8pt !important;
      }
    }

    .card-hero-names-wrap {
      text-align: center !important;
      padding: 0.6mm 1mm 0.3mm !important;

      .card-student-name-bn {
        font-size: 7.2pt !important;
        line-height: 1.15 !important;
      }

      .card-student-name-en {
        font-size: 4pt !important;
        margin: 0.2mm 0 0.5mm !important;
      }

      .student-role-pill {
        font-size: 3.8pt !important;
        padding: 0.2mm 1.5mm !important;
      }
    }

    .card-cred-matrix {
      margin: 0.6mm 1.8mm 0.5mm !important;
      padding: 0.6mm 1mm !important;
      gap: 0.5mm 1mm !important;
      font-size: 4.5pt !important;
      border-radius: 1.5mm !important;

      .cred-cell {
        font-size: 4.5pt !important;
      }

      .cred-k {
        font-size: 4.2pt !important;
      }

      .cred-v {
        font-size: 4.5pt !important;
      }

      .class-pill-badge,
      .roll-pill-badge {
        font-size: 4.2pt !important;
        padding: 0.2mm 0.8mm !important;
      }
    }

    .card-security-footer {
      padding: 1mm 1.8mm 0.6mm !important;
    }

    .footer-barcode-box {
      width: 25mm !important;
    }

    .vector-barcode-svg {
      height: 6mm !important;
    }

    .footer-signature-box {
      .signature-wrap {
        height: 3.5mm !important;
      }
      .signature-cursive-svg {
        width: 11mm !important;
        height: 3.5mm !important;
      }
      .signature-stamp-circle {
        width: 3.5mm !important;
        height: 3.5mm !important;
        font-size: 2.8pt !important;
        right: -2px !important;
      }
      .sign-label {
        font-size: 3.8pt !important;
      }
    }

    .hologram-seal-mockup {
      width: 5.5mm !important;
      height: 5.5mm !important;
      .hologram-inner {
        .holo-line1,
        .holo-line2 {
          font-size: 2.2pt !important;
        }
      }
    }

    .card-microprint-strip {
      font-size: 3.2pt !important;
      padding: 0.4mm 0 !important;
    }

    /* Back side print scaling */
    .card-back-header {
      padding: 1.5mm 1.8mm 0.8mm !important;
      .back-inst-bn {
        font-size: 5.8pt !important;
      }
      .back-inst-sub {
        font-size: 3.6pt !important;
      }
    }

    .back-emergency-card {
      margin: 0.6mm 1.8mm 0.4mm !important;
      padding: 0.6mm 1mm !important;
      border-radius: 1.5mm !important;

      .em-top-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-bottom: 0.3mm !important;
      }

      .blood-emergency-badge {
        font-size: 4.5pt !important;
      }

      .dob-badge {
        font-size: 4pt !important;
      }

      .em-guardian-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 1mm !important;
        margin-bottom: 0.3mm !important;
        font-size: 4pt !important;
      }

      .em-field {
        font-size: 4pt !important;
        &.hotline-highlight {
          padding: 0.2mm 0.8mm !important;
          .em-v {
            font-size: 4.2pt !important;
          }
        }
      }

      .em-k {
        font-size: 3.8pt !important;
      }

      .em-v {
        font-size: 4pt !important;
      }

      .em-address-row {
        font-size: 3.8pt !important;
        line-height: 1.2 !important;
        margin-top: 0.3mm !important;
        padding-top: 0.3mm !important;
      }
    }

    .back-terms-box {
      margin: 0.5mm 1.8mm !important;
      padding: 0.8mm 1mm !important;
      border-radius: 1.5mm !important;
      .terms-title {
        font-size: 4.6pt !important;
        margin-bottom: 0.4mm !important;
      }
      .terms-list {
        font-size: 3.8pt !important;
        line-height: 1.2 !important;
        li {
          gap: 0.8mm !important;
          margin-bottom: 0.3mm !important;
        }
        .term-bullet {
          font-size: 3.2pt !important;
        }
      }
    }

    .back-qr-return-row {
      margin: 0.6mm 1.8mm !important;
      gap: 1.2mm !important;
    }

    .return-label {
      font-size: 4.2pt !important;
    }

    .return-address {
      font-size: 3.6pt !important;
      line-height: 1.2 !important;
    }

    .back-qr-box {
      width: 13mm !important;
    }

    .qr-frame-reticle {
      width: 12mm !important;
      height: 12mm !important;
      padding: 0.3mm !important;
      .reticle-corner {
        width: 1.2mm !important;
        height: 1.2mm !important;
      }
    }

    .qr-code-img,
    .qr-placeholder {
      width: 11mm !important;
      height: 11mm !important;
    }

    .qr-label {
      font-size: 3.4pt !important;
    }

    .validity-text {
      font-size: 4.2pt !important;
      padding: 0.5mm 0 !important;
    }

    .back-microprint {
      font-size: 3.2pt !important;
      padding: 0.4mm 0 !important;
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
