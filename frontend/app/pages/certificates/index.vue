<template>
  <div class="page-wrapper slide-up-fade">
    <!-- In-App Toast Notification Banner -->
    <div v-if="toastMessage" class="toast-banner no-print" :class="toastType">
      <div class="toast-content">
        <Icon :name="toastType === 'success' ? 'checkCircle' : 'alertCircle'" />
        <span>{{ toastMessage }}</span>
      </div>
      <button class="toast-close" @click="toastMessage = ''">×</button>
    </div>

    <!-- Sub-navigation Links to Certificate Generators -->
    <div class="subnav-row no-print">
      <NuxtLink to="/certificates" class="subnav-pill active">
        <Icon name="tag" /> সনদ রেজিস্টার ও টেমপলেট
      </NuxtLink>
      <NuxtLink to="/certificates/sonad" class="subnav-pill">
        <Icon name="school" /> সনদপত্র মুদ্রণ (Sanad)
      </NuxtLink>
      <NuxtLink to="/certificates/transfer" class="subnav-pill">
        <Icon name="document" /> ছাড়পত্র মুদ্রণ (Transfer Certificate)
      </NuxtLink>
    </div>

    <!-- Page Header -->
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <span class="eyebrow">সার্টিফিকেশন ও পাঠ্যক্রম</span>
        <h1>সার্টিফিকেট ও পাঠ্যক্রম ব্যবস্থাপনা</h1>
        <p class="page-subtitle">সার্টিফিকেট টেমপলেট তৈরি, শিক্ষার্থী সনদপত্র প্রদান, পাঠ্যক্রম ও বিষয় পরিচালনা করুন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="openIssueModal">
          <Icon name="tag" /> সনদ প্রকাশ করুন
        </button>
        <button class="btn btn-primary" @click="openCreateTemplate">
          <Icon name="plus" /> নতুন টেমপলেট
        </button>
        <NuxtLink to="/certificates/sonad" class="btn btn-secondary">
          <Icon name="printer" /> সনদপত্র প্রিন্ট (Sanad)
        </NuxtLink>
      </div>
    </div>

    <!-- Metric KPI Stats Bar -->
    <div class="stats-overview-grid no-print">
      <div class="stat-card">
        <div class="stat-icon-wrapper primary">
          <Icon name="book" />
        </div>
        <div class="stat-content">
          <span class="stat-label">মোট টেমপলেট</span>
          <h3 class="stat-value">{{ templates.total || 0 }}</h3>
          <span class="stat-sub">অনুমোদিত ফরম্যাট</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrapper success">
          <Icon name="tag" />
        </div>
        <div class="stat-content">
          <span class="stat-label">মোট সনদ প্রকাশনা</span>
          <h3 class="stat-value">{{ issuedCerts.total || 0 }}</h3>
          <span class="stat-sub">শিক্ষার্থীদের প্রদত্ত</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrapper info">
          <Icon name="school" />
        </div>
        <div class="stat-content">
          <span class="stat-label">পাঠ্যক্রম ও বিষয়</span>
          <h3 class="stat-value">{{ subjects.length || 0 }}</h3>
          <span class="stat-sub">নিবন্ধিত বিষয় তালিকা</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrapper warning">
          <Icon name="checkCircle" />
        </div>
        <div class="stat-content">
          <span class="stat-label">সক্রিয় টেমপলেট</span>
          <h3 class="stat-value">{{ activeTemplatesCount }}</h3>
          <span class="stat-sub">ব্যবহারযোগ্য টেমপলেট</span>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs-nav mb-3 no-print">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="['tab-btn', { active: activeTab === tab.key }]"
        @click="activeTab = tab.key"
      >
        <Icon :name="tab.icon" />
        {{ tab.label }}
        <span class="tab-count-badge" v-if="tab.key === 'templates'">{{ templates.total || 0 }}</span>
        <span class="tab-count-badge" v-else-if="tab.key === 'issue'">{{ issuedCerts.total || 0 }}</span>
        <span class="tab-count-badge" v-else-if="tab.key === 'syllabus'">{{ subjects.length || 0 }}</span>
      </button>
    </div>

    <!-- ==================== TAB 1: TEMPLATES ==================== -->
    <div v-if="activeTab === 'templates'" class="table-card no-print">
      <div class="toolbar">
        <div class="search-box">
          <Icon name="search" class="search-icon" />
          <input
            v-model="templateSearch"
            type="text"
            placeholder="টেমপলেট শিরোনাম দিয়ে খুঁজুন..."
            @input="debounceTemplateSearch"
          />
          <button v-if="templateSearch" @click="templateSearch = ''; fetchTemplates(1)" class="clear-search-btn" title="মুছে ফেলুন">
            <Icon name="close" />
          </button>
        </div>
        <div class="select-wrapper">
          <select v-model="templateTypeFilter" class="form-select" @change="fetchTemplates(1)">
            <option value="">সব ধরন</option>
            <option value="annual">বার্ষিক সনদ (Annual)</option>
            <option value="transfer">হস্তান্তর / ছাড়পত্র (Transfer)</option>
            <option value="sanction">অনুমোদন সনদ (Sanction)</option>
            <option value="conduct">আচরণ ও প্রশংসাপত্র (Conduct)</option>
            <option value="others">অন্যান্য (Others)</option>
          </select>
        </div>
        <div class="select-wrapper">
          <select v-model="templateClassFilter" class="form-select" @change="fetchTemplates(1)">
            <option value="">সব শ্রেণি</option>
            <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="pagination-info text-muted">
          মোট <strong style="color: var(--color-primary);">{{ templates.total || 0 }}</strong> টি টেমপলেট
        </div>
      </div>

      <div v-if="templateLoading" class="loading-state">
        <div class="spinner"></div>
        <p>টেমপলেট লোড হচ্ছে...</p>
      </div>

      <div v-else-if="templates.data?.length" class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>শিরোনাম</th>
              <th>ধরন</th>
              <th>শ্রেণি</th>
              <th>বিষয়</th>
              <th>অবস্থা</th>
              <th class="text-right">কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in templates.data" :key="t.id">
              <td>
                <div class="template-title-cell">
                  <strong>{{ t.title }}</strong>
                  <small class="text-muted" v-if="t.template_data?.subtitle">{{ t.template_data.subtitle }}</small>
                </div>
              </td>
              <td>
                <span class="badge-pill" :class="getTemplateTypeClass(t.template_type)">
                  {{ formatTemplateType(t.template_type) }}
                </span>
              </td>
              <td>{{ t.classRelation?.name_bn || t.classRelation?.name_en || t.classRelation?.name || 'সকল শ্রেণি' }}</td>
              <td>{{ t.subjectRelation?.name_bn || t.subjectRelation?.name_en || t.subjectRelation?.name || '—' }}</td>
              <td>
                <span class="status-pill" :class="t.is_active ? 'badge-approved' : 'badge-rejected'">
                  <span class="status-dot"></span> {{ t.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                </span>
              </td>
              <td class="text-right">
                <div class="flex gap-1" style="justify-content: flex-end;">
                  <button class="action-btn edit" title="সম্পাদনা করুন" @click="editTemplate(t)">
                    <Icon name="pencil" />
                  </button>
                  <button class="action-btn delete" title="মুছে ফেলুন" @click="deleteTemplate(t)">
                    <Icon name="delete" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="empty-state">
        <div class="empty-icon-circle">
          <Icon name="book" />
        </div>
        <h3>কোনো টেমপলেট পাওয়া যায়নি</h3>
        <p class="text-muted">এখনও কোনো সার্টিফিকেট টেমপলেট তৈরি করা হয়নি অথবা ফিল্টারের সাথে মিলছে না।</p>
        <button class="btn btn-primary mt-2" @click="openCreateTemplate">
          <Icon name="plus" /> প্রথম টেমপলেট তৈরি করুন
        </button>
      </div>

      <div v-if="templates.last_page > 1" class="pagination-wrapper">
        <div class="pagination-info">{{ templates.from }}–{{ templates.to }} / মোট {{ templates.total }} টেমপলেট</div>
        <div class="pagination-numbers">
          <button class="pagination-btn" :disabled="!templates.prev_page_url" @click="goTemplatePage(templates.current_page - 1)">
            <Icon name="chevron-left" /> পূর্ববর্তী
          </button>
          <span class="page-info">পৃষ্ঠা {{ templates.current_page }} / {{ templates.last_page }}</span>
          <button class="pagination-btn" :disabled="!templates.next_page_url" @click="goTemplatePage(templates.current_page + 1)">
            পরবর্তী <Icon name="chevron-right" />
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== TAB 2: ISSUED CERTIFICATES ==================== -->
    <div v-if="activeTab === 'issue'" class="table-card no-print">
      <div class="toolbar">
        <div class="search-box">
          <Icon name="search" class="search-icon" />
          <input
            v-model="issueSearch"
            type="text"
            placeholder="শিক্ষার্থী নাম বা সার্টিফিকেট নম্বর দিয়ে খুঁজুন..."
            @input="debounceIssueSearch"
          />
          <button v-if="issueSearch" @click="issueSearch = ''; fetchIssueCerts(1)" class="clear-search-btn" title="মুছে ফেলুন">
            <Icon name="close" />
          </button>
        </div>
        <div class="select-wrapper">
          <select v-model="issueClassFilter" class="form-select" @change="fetchIssueCerts(1)">
            <option value="">সব শ্রেণি</option>
            <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="pagination-info text-muted">
          মোট <strong style="color: var(--color-primary);">{{ issuedCerts.total || 0 }}</strong> টি সনদ
        </div>
      </div>

      <div v-if="issueLoading" class="loading-state">
        <div class="spinner"></div>
        <p>সার্টিফিকেট প্রকাশনা লোড হচ্ছে...</p>
      </div>

      <div v-else-if="issuedCerts.data?.length" class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>সার্টিফিকেট নং</th>
              <th>শিক্ষার্থী</th>
              <th>শ্রেণি</th>
              <th>টেমপলেট</th>
              <th>প্রকাশের তারিখ</th>
              <th>অনুমোদনকারী</th>
              <th class="text-right">কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in issuedCerts.data" :key="c.id">
              <td><code class="mono cert-badge">{{ c.certificate_number }}</code></td>
              <td>
                <div class="student-profile-cell">
                  <div class="student-avatar-badge">
                    {{ getStudentInitials(c) }}
                  </div>
                  <div class="student-info-meta">
                    <strong>{{ getStudentName(c) }}</strong>
                    <span class="roll-sub">রোল: {{ c.studentRelation?.roll_number || c.student_id }}</span>
                  </div>
                </div>
              </td>
              <td>{{ getClassName(c) }}</td>
              <td>
                <span class="template-pill-tag">
                  {{ c.templateRelation?.title || 'সাধারণ সনদ' }}
                </span>
              </td>
              <td>{{ formatDate(c.issue_date) }}</td>
              <td><span class="auth-person">{{ c.authorized_by || 'মুহতামিম / অধ্যক্ষ' }}</span></td>
              <td class="text-right">
                <div class="flex gap-1" style="justify-content: flex-end;">
                  <button class="action-btn view" title="বিবরণ ও প্রিভিউ" @click="viewIssue(c)">
                    <Icon name="eye" />
                  </button>
                  <button class="action-btn print" title="সনদ প্রিন্ট করুন" @click="quickPrint(c)">
                    <Icon name="printer" />
                  </button>
                  <button class="action-btn delete" title="মুছে ফেলুন" @click="deleteIssue(c)">
                    <Icon name="delete" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="empty-state">
        <div class="empty-icon-circle">
          <Icon name="tag" />
        </div>
        <h3>কোনো প্রকাশনা নেই</h3>
        <p class="text-muted">এখনও কোনো শিক্ষার্থীকে সার্টিফিকেট প্রদান করা হয়নি অথবা কোনো রেকর্ড মেলেনি।</p>
        <button class="btn btn-primary mt-2" @click="openIssueModal">
          <Icon name="tag" /> প্রথম সার্টিফিকেট প্রকাশ করুন
        </button>
      </div>

      <div v-if="issuedCerts.last_page > 1" class="pagination-wrapper">
        <div class="pagination-info">{{ issuedCerts.from }}–{{ issuedCerts.to }} / মোট {{ issuedCerts.total }} রেকর্ড</div>
        <div class="pagination-numbers">
          <button class="pagination-btn" :disabled="!issuedCerts.prev_page_url" @click="goIssuePage(issuedCerts.current_page - 1)">
            <Icon name="chevron-left" /> পূর্ববর্তী
          </button>
          <span class="page-info">পৃষ্ঠা {{ issuedCerts.current_page }} / {{ issuedCerts.last_page }}</span>
          <button class="pagination-btn" :disabled="!issuedCerts.next_page_url" @click="goIssuePage(issuedCerts.current_page + 1)">
            পরবর্তী <Icon name="chevron-right" />
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== TAB 3: SYLLABUS & BOOKS ==================== -->
    <div v-if="activeTab === 'syllabus'" class="table-card p-4 no-print" style="padding: 1.5rem;">
      <div class="flex-between mb-3">
        <div>
          <h3 style="font-weight: 700; font-size: 1.15rem; color: var(--color-text);">পাঠ্যক্রম ও বই তালিকা</h3>
          <p class="text-muted text-sm">বিভিন্ন শ্রেণির জন্য অনুমোদিত বিষয় ও পাঠ্যসূচি</p>
        </div>
      </div>
      <div class="syllabus-grid">
        <div v-for="sub in subjects" :key="sub.id" class="syllabus-card">
          <div class="syllabus-card-header">
            <h4>{{ sub.name_bn || sub.name_en || sub.name }}</h4>
            <span class="code-badge">{{ sub.code || 'SUB' }}</span>
          </div>
          <div class="syllabus-card-body">
            <div class="syllabus-field">
              <span class="label">শ্রেণি:</span>
              <span class="value">{{ formatClassesList(sub.classes) }}</span>
            </div>
            <div class="syllabus-field" v-if="sub.marks">
              <span class="label">পূর্ণমান:</span>
              <span class="value">{{ sub.marks }} নম্বর</span>
            </div>
          </div>
        </div>
        <div v-if="subjects.length === 0" class="empty-state">
          <div class="empty-icon-circle">
            <Icon name="book" />
          </div>
          <h3>কোনো পাঠ্যক্রম পাওয়া যায়নি</h3>
          <p class="text-muted">পাঠ্যক্রম ও বিষয় তালিকা লোড হতে ব্যর্থ হয়েছে অথবা কোনো বিষয় যুক্ত করা নেই।</p>
        </div>
      </div>
    </div>

    <!-- ==================== CREATE / EDIT TEMPLATE MODAL ==================== -->
    <div v-if="showCreate" class="modal-overlay no-print" @click.self="showCreate = false">
      <div class="modal-card modal-lg">
        <div class="modal-header">
          <div class="modal-title-wrap">
            <span class="modal-eyebrow">টেমপলেট কনফিগারেশন</span>
            <h3>{{ editingTemplate ? 'সার্টিফিকেট টেমপলেট সম্পাদনা' : 'নতুন সার্টিফিকেট টেমপলেট' }}</h3>
          </div>
          <button class="action-btn close" @click="showCreate = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveTemplate">
            <div class="form-row mb-3">
              <div class="form-group flex-2">
                <label class="form-label">টেমপলেট শিরোনাম <span class="required">*</span></label>
                <input
                  v-model="templateForm.title"
                  type="text"
                  class="form-control"
                  placeholder="যেমন: হিফজুল কুরআন সমাপনী সনদপত্র বা বার্ষিক ফলাফল প্রশংসাপত্র"
                  required
                />
              </div>
              <div class="form-group flex-1">
                <label class="form-label">সার্টিফিকেটের ধরন <span class="required">*</span></label>
                <select v-model="templateForm.template_type" class="form-select" required>
                  <option value="annual">বার্ষিক সনদ (Annual)</option>
                  <option value="transfer">ছাড়পত্র / বদলি (Transfer)</option>
                  <option value="sanction">অনুমোদন সনদ (Sanction)</option>
                  <option value="conduct">আচরণ ও প্রশংসাপত্র (Conduct)</option>
                  <option value="others">অন্যান্য (Others)</option>
                </select>
              </div>
            </div>

            <div class="form-row mb-3">
              <div class="form-group">
                <label class="form-label">নির্দিষ্ট শ্রেণি (ঐচ্ছিক)</label>
                <select v-model="templateForm.class_id" class="form-select">
                  <option value="">সকল শ্রেণির জন্য প্রযোজ্য</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">নির্দিষ্ট বিষয় (ঐচ্ছিক)</label>
                <select v-model="templateForm.subject_id" class="form-select">
                  <option value="">সকল বিষয়ের জন্য</option>
                  <option v-for="s in subjectOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
            </div>

            <!-- Structured Template Customization Section -->
            <div class="structured-card-section mb-3">
              <h4 class="section-title">
                <Icon name="pencil" /> সনদপত্রের নকশা ও বয়ান (Template Content)
              </h4>

              <div class="form-row mb-2">
                <div class="form-group flex-1">
                  <label class="form-label">প্রতিষ্ঠানের নাম (Header Title)</label>
                  <input
                    v-model="templateForm.institute_name"
                    type="text"
                    class="form-control"
                    placeholder="মারকাযুল উলুম মাদ্রাসা"
                  />
                </div>
                <div class="form-group flex-1">
                  <label class="form-label">সাব-শিরোনাম / পরিচিতি</label>
                  <input
                    v-model="templateForm.subtitle"
                    type="text"
                    class="form-control"
                    placeholder="স্থাপিত: ১৯৯৫ খ্রিঃ · গোপালগঞ্জ, বাংলাদেশ"
                  />
                </div>
              </div>

              <div class="form-group mb-2">
                <label class="form-label">
                  প্রত্যয়ন বক্তব্য / মূল বয়ান
                  <span class="field-hint text-muted">(ব্যবহারযোগ্য টোকেন: {student_name}, {father_name}, {class_name}, {roll_no}, {issue_date})</span>
                </label>
                <textarea
                  v-model="templateForm.body_text"
                  class="form-control"
                  rows="3"
                  placeholder="এই মর্মে প্রত্যয়ন করা যাইতেছে যে, {student_name}, পিতা: {father_name}, অত্র প্রতিষ্ঠানের {class_name} জামাতের একজন নিয়মিত শিক্ষার্থী..."
                ></textarea>
              </div>

              <div class="form-row mb-2">
                <div class="form-group">
                  <label class="form-label">প্রধান স্বাক্ষরকারী পদবি</label>
                  <input
                    v-model="templateForm.authority_title"
                    type="text"
                    class="form-control"
                    placeholder="মুহতামিম / প্রিন্সিপাল"
                  />
                </div>
                <div class="form-group">
                  <label class="form-label">সহকারী স্বাক্ষরকারী পদবি</label>
                  <input
                    v-model="templateForm.second_signatory"
                    type="text"
                    class="form-control"
                    placeholder="পরীক্ষা নিয়ন্ত্রক"
                  />
                </div>
              </div>

              <div class="form-check-group mt-2">
                <label class="checkbox-label">
                  <input type="checkbox" v-model="templateForm.show_bismillah" />
                  <span>উপরে বিসমিল্লাহির রাহমানির রাহীম ক্যালিগ্রাফি প্রদর্শন করুন</span>
                </label>
              </div>
            </div>

            <!-- Advanced Raw JSON Collapsible Toggle -->
            <div class="advanced-json-toggle mb-3">
              <button
                type="button"
                class="btn-text"
                @click="templateForm.show_raw_json = !templateForm.show_raw_json"
              >
                <Icon :name="templateForm.show_raw_json ? 'chevron-up' : 'chevron-down'" />
                {{ templateForm.show_raw_json ? 'উন্নত JSON মোড বন্ধ করুন' : 'উন্নত JSON মোড দেখুন' }}
              </button>
              <div v-if="templateForm.show_raw_json" class="mt-2">
                <textarea
                  v-model="templateForm.template_data_json"
                  class="form-control font-mono"
                  rows="4"
                  placeholder='{"custom_note": "বিশেষ দ্রষ্টব্য..."}'
                ></textarea>
                <small class="text-muted">উন্নত কাস্টম ফিল্ড যোগ করতে চাইলে সরাসরি JSON ডেটা লিখুন।</small>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">সক্রিয় অবস্থা</label>
              <div class="toggle-row">
                <label class="toggle">
                  <input type="checkbox" v-model="templateForm.is_active" />
                  <span class="toggle-slider"></span>
                </label>
                <span class="ml-2" style="margin-left: 0.6rem; font-weight: 500;">
                  {{ templateForm.is_active ? 'সক্রিয় (ব্যবহারের জন্য প্রস্তুত)' : 'নিষ্ক্রিয়' }}
                </span>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showCreate = false">বাতিল</button>
          <button class="btn btn-primary" @click="saveTemplate" :disabled="templateSaving">
            <Icon name="loader" v-if="templateSaving" />
            <Icon name="save" v-else />
            {{ editingTemplate ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== ISSUE CERTIFICATE MODAL ==================== -->
    <div v-if="showIssue" class="modal-overlay no-print" @click.self="showIssue = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-wrap">
            <span class="modal-eyebrow">সনদ প্রকাশনা</span>
            <h3>শিক্ষার্থীকে সার্টিফিকেট প্রদান করুন</h3>
          </div>
          <button class="action-btn close" @click="showIssue = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="issueCertificate">
            <div class="form-group mb-3">
              <label class="form-label">শিক্ষার্থী নির্বাচন করুন <span class="required">*</span></label>
              <select
                v-model="issueForm.student_id"
                class="form-select"
                required
                @change="onStudentSelectChange"
              >
                <option value="">তালিকায় থাকা শিক্ষার্থী নির্বাচন করুন</option>
                <option v-for="s in studentOptions" :key="s.id" :value="s.id">
                  {{ s.name }} (রোল: {{ s.roll_no }}) — {{ s.class_name || 'সাধারণ' }}
                </option>
              </select>
            </div>

            <div class="form-group mb-3">
              <label class="form-label">সার্টিফিকেট টেমপলেট <span class="required">*</span></label>
              <select v-model="issueForm.template_id" class="form-select" required>
                <option value="">টেমপলেট নির্বাচন করুন</option>
                <option v-for="t in templateOptions" :key="t.id" :value="t.id">
                  {{ t.title }} ({{ formatTemplateType(t.type) }})
                </option>
              </select>
            </div>

            <div class="form-row mb-3">
              <div class="form-group">
                <label class="form-label">অধ্যয়নরত শ্রেণি</label>
                <select v-model="issueForm.class_id" class="form-select">
                  <option value="">স্বয়ংক্রিয় / নির্বাচন করুন</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">পাঠ্য বিষয় (ঐচ্ছিক)</label>
                <select v-model="issueForm.subject_id" class="form-select">
                  <option value="">সার্বিক বিষয়</option>
                  <option v-for="s in subjectOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
            </div>

            <div class="form-row mb-3">
              <div class="form-group">
                <label class="form-label">প্রকাশের তারিখ <span class="required">*</span></label>
                <input v-model="issueForm.issue_date" type="date" class="form-control" required />
              </div>
              <div class="form-group">
                <label class="form-label">অনুমোদনকারী স্বাক্ষরকর্তা</label>
                <input
                  v-model="issueForm.authorized_by"
                  type="text"
                  class="form-control"
                  placeholder="মুহতামিম / প্রিন্সিপাল"
                />
              </div>
            </div>

            <div class="form-group mb-2">
              <label class="form-label">মন্তব্য বা বিশেষ প্রাপ্তি (ঐচ্ছিক)</label>
              <textarea
                v-model="issueForm.remarks"
                class="form-control"
                rows="2"
                placeholder="যেমন: মুমতাজ বিভাগে উত্তীর্ণ, বা অন্যান্য বিশেষ মন্তব্য"
              ></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showIssue = false">বাতিল</button>
          <button class="btn btn-primary" @click="issueCertificate" :disabled="issueSaving">
            <Icon name="loader" v-if="issueSaving" />
            <Icon name="tag" v-else />
            প্রকাশ করুন
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== VIEW & PRINT CERTIFICATE MODAL ==================== -->
    <div v-if="showViewIssue" class="modal-overlay" @click.self="showViewIssue = false">
      <div class="modal-card modal-cert-preview">
        <div class="modal-header no-print">
          <div class="modal-title-wrap">
            <span class="modal-eyebrow">সনদপত্র প্রিভিউ ও মুদ্রণ</span>
            <h3>সার্টিফিকেটের বিবরণ ও প্রিন্ট</h3>
          </div>
          <div class="flex gap-2 align-center">
            <button class="btn btn-primary" @click="printCurrentCertificate">
              <Icon name="printer" /> প্রিন্ট করুন
            </button>
            <button class="action-btn close" @click="showViewIssue = false">
              <Icon name="close" />
            </button>
          </div>
        </div>

        <div class="modal-body cert-preview-modal-body">
          <!-- Authentic Printable Certificate Paper -->
          <div id="printable-certificate" class="sanad-container-outer">
            <div class="sanad-paper">
              <div class="sanad-border">
                <div class="sanad-inner-border">
                  <!-- Header Bismillah -->
                  <div
                    v-if="certTemplateData?.show_bismillah !== false"
                    class="sanad-bismillah"
                  >
                    بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم
                  </div>

                  <!-- Madrasha Header -->
                  <div class="sanad-madrasha-header">
                    <h1 class="bn-madrasha-title">
                      {{ certTemplateData?.institute_name || 'মারকাযুল উলুম মাদ্রাসা' }}
                    </h1>
                    <p class="madrasha-est">
                      {{ certTemplateData?.subtitle || 'দ্বীনি ও আধুনিক শিক্ষার সমন্বিত প্রতিষ্ঠান · বাংলাদেশ' }}
                    </p>
                  </div>

                  <!-- Certificate Title Badge -->
                  <div class="sanad-badge-title">
                    <span class="bn-text">
                      {{ viewIssueData?.templateRelation?.title || 'প্রত্যয়ন পত্র / সনদপত্র' }}
                    </span>
                    <span class="type-subtext">
                      ({{ formatTemplateType(viewIssueData?.templateRelation?.template_type || 'annual') }})
                    </span>
                  </div>

                  <!-- Certificate Statement -->
                  <div class="sanad-body-text">
                    <p class="body-para">
                      {{ formatCertBody(viewIssueData) }}
                    </p>
                    <p class="body-para-dua">
                      আমরা তাহার উজ্জ্বল ভবিষ্যৎ, ইলমে দ্বীনের প্রচার-প্রসার এবং জীবনে পূর্ণ সফলতা ও বরকত কামনা করি।
                    </p>
                  </div>

                  <!-- Meta Details Grid -->
                  <div class="sanad-meta-row">
                    <div><strong>সনদ নম্বর:</strong> <span class="mono">{{ viewIssueData?.certificate_number }}</span></div>
                    <div><strong>শিক্ষার্থী রোল:</strong> {{ viewIssueData?.studentRelation?.roll_number || viewIssueData?.student_id }}</div>
                    <div><strong>শ্রেণি:</strong> {{ getClassName(viewIssueData) }}</div>
                    <div><strong>ইস্যুর তারিখ:</strong> {{ formatDate(viewIssueData?.issue_date) }}</div>
                  </div>

                  <!-- Signatures Section -->
                  <div class="sanad-signatures">
                    <div class="sig-block">
                      <div class="sig-line"></div>
                      <span class="sig-role">{{ certTemplateData?.second_signatory || 'পরীক্ষা নিয়ন্ত্রক' }}</span>
                    </div>
                    <div class="seal-block">
                      <div class="official-seal">
                        <span>মারকাযুল উলুম</span>
                        <small>অফিসিয়াল সিল</small>
                      </div>
                    </div>
                    <div class="sig-block">
                      <div class="sig-line"></div>
                      <span class="sig-role">{{ viewIssueData?.authorized_by || certTemplateData?.authority_title || 'মুহতামিম / প্রিন্সিপাল' }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer no-print">
          <button class="btn btn-outline" @click="showViewIssue = false">বন্ধ করুন</button>
          <button class="btn btn-primary" @click="printCurrentCertificate">
            <Icon name="printer" /> প্রিন্ট করুন
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== DELETE CONFIRMATION MODAL ==================== -->
    <div v-if="showDeleteConfirm && deleteConfirmItem" class="modal-overlay no-print" @click.self="showDeleteConfirm = false">
      <div class="modal-card" style="max-width: 440px;">
        <div class="modal-header">
          <h3>{{ deleteConfirmType === 'template' ? 'টেমপলেট মুছে ফেলা' : 'সার্টিফিকেট মুছে ফেলা' }}</h3>
          <button class="action-btn close" @click="showDeleteConfirm = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body" style="padding: 1.25rem;">
          <p v-if="deleteConfirmType === 'template'">
            আপনি কি নিশ্চিত যে <strong>"{{ deleteConfirmItem.title }}"</strong> টেমপলেটটি মুছে ফেলতে চান?
          </p>
          <p v-else>
            আপনি কি নিশ্চিত যে <strong>{{ deleteConfirmItem.certificate_number }}</strong> সনদটি রেজিস্টার থেকে মুছে ফেলতে চান?
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDeleteConfirm = false" :disabled="deleteConfirming">বাতিল</button>
          <button class="btn btn-danger" @click="executeConfirmedDelete" :disabled="deleteConfirming">
            <Icon name="loader" v-if="deleteConfirming" />
            মুছে ফেলুন
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import Icon from '~/components/Icon.vue'

const api = useApiClient()

// In-app Feedback Toast
const toastMessage = ref('')
const toastType = ref<'success' | 'error'>('success')
let toastTimer: any = null

function showToast(msg: string, type: 'success' | 'error' = 'success') {
  toastMessage.value = msg
  toastType.value = type
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toastMessage.value = ''
  }, 4000)
}

// Tabs
const activeTab = ref('templates')
const tabs = [
  { key: 'templates', label: 'টেমপলেট তালিকা', icon: 'book' },
  { key: 'issue', label: 'প্রকাশিত সনদ রেজিস্টার', icon: 'tag' },
  { key: 'syllabus', label: 'পাঠ্যক্রম ও বিষয় তালিকা', icon: 'school' },
]

// State & Options
const templateLoading = ref(true)
const templates = ref<any>({ data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })
const templateSearch = ref('')
const templateTypeFilter = ref('')
const templateClassFilter = ref('')

const classOptions = ref<any[]>([])
const subjectOptions = ref<any[]>([])
const templateOptions = ref<any[]>([])
const studentOptions = ref<any[]>([])
const subjects = ref<any[]>([])

// Template Form
const showCreate = ref(false)
const editingTemplate = ref<any>(null)
const templateSaving = ref(false)

const templateForm = reactive({
  title: '',
  template_type: 'annual',
  class_id: '',
  subject_id: '',
  institute_name: 'মারকাযুল উলুম মাদ্রাসা',
  subtitle: 'দ্বীনি ও আধুনিক শিক্ষার সমন্বিত প্রতিষ্ঠান · বাংলাদেশ',
  body_text: 'এই মর্মে প্রত্যয়ন করা যাইতেছে যে, {student_name}, পিতা: {father_name}, অত্র প্রতিষ্ঠানের {class_name} জামাতের একজন নিয়মিত শিক্ষার্থী। সে পরীক্ষায় কৃতিত্বের সহিত উত্তীর্ণ হইয়াছে।',
  authority_title: 'মুহতামিম / প্রিন্সিপাল',
  second_signatory: 'পরীক্ষা নিয়ন্ত্রক',
  show_bismillah: true,
  template_data_json: '',
  show_raw_json: false,
  is_active: true
})

// Issue Form
const issueLoading = ref(true)
const issuedCerts = ref<any>({ data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })
const issueSearch = ref('')
const issueClassFilter = ref('')
const showIssue = ref(false)
const issueSaving = ref(false)

const issueForm = reactive({
  student_id: '',
  template_id: '',
  class_id: '',
  subject_id: '',
  issue_date: new Date().toISOString().split('T')[0],
  authorized_by: 'মুহতামিম / প্রিন্সিপাল',
  remarks: ''
})

// View / Print
const showViewIssue = ref(false)
const viewIssueData = ref<any>(null)

// Delete Confirm
const showDeleteConfirm = ref(false)
const deleteConfirmItem = ref<any>(null)
const deleteConfirmType = ref<'template' | 'issue'>('template')
const deleteConfirming = ref(false)

let templateTimeout: any = null
let issueTimeout: any = null

// Computed
const activeTemplatesCount = computed(() => {
  return (templates.value.data || []).filter((t: any) => t.is_active).length
})

const certTemplateData = computed(() => {
  if (!viewIssueData.value?.templateRelation) return {}
  const td = viewIssueData.value.templateRelation.template_data
  if (typeof td === 'string') {
    try { return JSON.parse(td) } catch { return {} }
  }
  return td || {}
})

// Data Fetchers
async function fetchClasses() {
  try {
    const res = await api.get('/academic/classes?per_page=100').catch(() => null)
    const list = res?.data?.data?.data || res?.data?.data || []
    classOptions.value = list.map((c: any) => ({
      id: c.id,
      name: c.name_bn || c.name_en || c.name
    }))
  } catch (err) {
    console.error(err)
  }
}

async function fetchSubjects() {
  try {
    const res = await api.get('/certificates/syllabus').catch(() => null)
    const list = res?.data?.data || res?.data || []
    subjectOptions.value = list.map((s: any) => ({
      id: s.id,
      name: s.name_bn || s.name_en || s.name,
      code: s.code
    }))
    subjects.value = list
  } catch (err) {
    console.error(err)
  }
}

async function fetchTemplates(page = 1) {
  templateLoading.value = true
  try {
    const params = new URLSearchParams({
      page: String(page),
      per_page: '15',
      ...(templateSearch.value ? { search: templateSearch.value } : {}),
      ...(templateTypeFilter.value ? { type: templateTypeFilter.value } : {}),
      ...(templateClassFilter.value ? { class_id: templateClassFilter.value } : {})
    })
    const res = await api.get(`/certificate-templates?${params}`).catch(() => null)
    templates.value = res?.data?.data || res?.data || { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }
    templateOptions.value = (templates.value.data || []).map((t: any) => ({
      id: t.id,
      title: t.title,
      type: t.template_type
    }))
  } catch (err) {
    console.error(err)
  } finally {
    templateLoading.value = false
  }
}

async function fetchIssueCerts(page = 1) {
  issueLoading.value = true
  try {
    const params = new URLSearchParams({
      page: String(page),
      per_page: '15',
      ...(issueSearch.value ? { search: issueSearch.value } : {}),
      ...(issueClassFilter.value ? { class_id: issueClassFilter.value } : {})
    })
    const res = await api.get(`/certificates?${params}`).catch(() => null)
    issuedCerts.value = res?.data?.data || res?.data || { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }
  } catch (err) {
    console.error(err)
  } finally {
    issueLoading.value = false
  }
}

async function fetchStudents() {
  try {
    const res = await api.get('/students?per_page=100').catch(() => null)
    const list = res?.data?.data?.data || res?.data?.data || []
    studentOptions.value = list.map((s: any) => ({
      id: s.id,
      name: s.name_bn || s.name_en || s.name,
      roll_no: s.roll_number || s.id,
      class_id: s.academic_class_id || s.class_id || s.academic_class?.id,
      class_name: s.academic_class?.name_bn || s.academic_class?.name_en || s.academic_class?.name || s.class || '',
      father_name: s.father_name_bn || s.father_name || '',
      present_address: s.present_address || ''
    }))
  } catch (err) {
    console.error(err)
  }
}

function debounceTemplateSearch() {
  clearTimeout(templateTimeout)
  templateTimeout = setTimeout(() => fetchTemplates(1), 300)
}

function debounceIssueSearch() {
  clearTimeout(issueTimeout)
  issueTimeout = setTimeout(() => fetchIssueCerts(1), 300)
}

function goTemplatePage(page: number) {
  if (page < 1 || page > templates.value.last_page) return
  fetchTemplates(page)
}

function goIssuePage(page: number) {
  if (page < 1 || page > issuedCerts.value.last_page) return
  fetchIssueCerts(page)
}

// Student selection handler to auto-fill class
function onStudentSelectChange() {
  const selected = studentOptions.value.find(s => s.id === Number(issueForm.student_id))
  if (selected && selected.class_id) {
    issueForm.class_id = String(selected.class_id)
  }
}

// Open Template Modals
function openCreateTemplate() {
  editingTemplate.value = null
  templateForm.title = ''
  templateForm.template_type = 'annual'
  templateForm.class_id = ''
  templateForm.subject_id = ''
  templateForm.institute_name = 'মারকাযুল উলুম মাদ্রাসা'
  templateForm.subtitle = 'দ্বীনি ও আধুনিক শিক্ষার সমন্বিত প্রতিষ্ঠান · বাংলাদেশ'
  templateForm.body_text = 'এই মর্মে প্রত্যয়ন করা যাইতেছে যে, {student_name}, পিতা: {father_name}, অত্র প্রতিষ্ঠানের {class_name} জামাতের একজন নিয়মিত শিক্ষার্থী। সে পরীক্ষায় কৃতিত্বের সহিত উত্তীর্ণ হইয়াছে।'
  templateForm.authority_title = 'মুহতামিম / প্রিন্সিপাল'
  templateForm.second_signatory = 'পরীক্ষা নিয়ন্ত্রক'
  templateForm.show_bismillah = true
  templateForm.template_data_json = ''
  templateForm.show_raw_json = false
  templateForm.is_active = true
  showCreate.value = true
}

function editTemplate(t: any) {
  editingTemplate.value = t
  templateForm.title = t.title || ''
  templateForm.template_type = t.template_type || 'annual'
  templateForm.class_id = t.class_id ? String(t.class_id) : ''
  templateForm.subject_id = t.subject_id ? String(t.subject_id) : ''
  
  const td = t.template_data || {}
  templateForm.institute_name = td.institute_name || 'মারকাযুল উলুম মাদ্রাসা'
  templateForm.subtitle = td.subtitle || 'দ্বীনি ও আধুনিক শিক্ষার সমন্বিত প্রতিষ্ঠান · বাংলাদেশ'
  templateForm.body_text = td.body_text || 'এই মর্মে প্রত্যয়ন করা যাইতেছে যে, {student_name}, পিতা: {father_name}, অত্র প্রতিষ্ঠানের {class_name} জামাতের একজন নিয়মিত শিক্ষার্থী। সে পরীক্ষায় কৃতিত্বের সহিত উত্তীর্ণ হইয়াছে।'
  templateForm.authority_title = td.authority_title || 'মুহতামিম / প্রিন্সিপাল'
  templateForm.second_signatory = td.second_signatory || 'পরীক্ষা নিয়ন্ত্রক'
  templateForm.show_bismillah = td.show_bismillah !== false
  templateForm.template_data_json = JSON.stringify(td, null, 2)
  templateForm.show_raw_json = false
  templateForm.is_active = t.is_active ?? true
  showCreate.value = true
}

async function saveTemplate() {
  if (!templateForm.title.trim()) {
    showToast('অনুগ্রহ করে টেমপলেটের শিরোনাম লিখুন', 'error')
    return
  }
  templateSaving.value = true
  try {
    const url = editingTemplate.value ? `/certificate-templates/${editingTemplate.value.id}` : '/certificate-templates'
    
    let templateData: any = {}
    if (templateForm.show_raw_json && templateForm.template_data_json.trim()) {
      try {
        templateData = JSON.parse(templateForm.template_data_json)
      } catch (e) {
        showToast('JSON সিনট্যাক্স সঠিক নয়, অনুগ্রহ করে পরীক্ষা করুন', 'error')
        templateSaving.value = false
        return
      }
    } else {
      templateData = {
        institute_name: templateForm.institute_name,
        subtitle: templateForm.subtitle,
        body_text: templateForm.body_text,
        authority_title: templateForm.authority_title,
        second_signatory: templateForm.second_signatory,
        show_bismillah: templateForm.show_bismillah
      }
    }

    const body = {
      title: templateForm.title,
      template_type: templateForm.template_type,
      class_id: Number(templateForm.class_id) || null,
      subject_id: Number(templateForm.subject_id) || null,
      template_data: templateData,
      is_active: templateForm.is_active
    }

    const res = await (editingTemplate.value ? api.put(url, body) : api.post(url, body))
    if (res?.data?.status === 200 || res?.data?.status === 201 || res?.status === 200 || res?.status === 201) {
      showToast(editingTemplate.value ? 'টেমপলেট সফলভাবে আপডেট করা হয়েছে' : 'নতুন টেমপলেট সফলভাবে তৈরি করা হয়েছে', 'success')
      showCreate.value = false
      editingTemplate.value = null
      fetchTemplates(templates.value.current_page || 1)
    } else {
      showToast(res?.data?.message || 'টেমপলেট সংরক্ষণ করা সম্ভব হয়নি', 'error')
    }
  } catch (err: any) {
    console.error('Template save failed:', err)
    showToast(err?.response?.data?.message || 'টেমপলেট সংরক্ষণ করতে সমস্যা হয়েছে', 'error')
  } finally {
    templateSaving.value = false
  }
}

// Issue Modal
function openIssueModal() {
  issueForm.student_id = ''
  issueForm.template_id = templateOptions.value[0]?.id ? String(templateOptions.value[0].id) : ''
  issueForm.class_id = ''
  issueForm.subject_id = ''
  issueForm.issue_date = new Date().toISOString().split('T')[0]
  issueForm.authorized_by = 'মুহতামিম / প্রিন্সিপাল'
  issueForm.remarks = ''
  showIssue.value = true
}

async function issueCertificate() {
  if (!issueForm.student_id) {
    showToast('শিক্ষার্থী নির্বাচন করুন', 'error')
    return
  }
  if (!issueForm.template_id) {
    showToast('টেমপলেট নির্বাচন করুন', 'error')
    return
  }
  issueSaving.value = true
  try {
    const res = await api.post('/certificates', {
      student_id: Number(issueForm.student_id),
      template_id: Number(issueForm.template_id),
      class_id: Number(issueForm.class_id) || null,
      subject_id: Number(issueForm.subject_id) || null,
      issue_date: issueForm.issue_date,
      authorized_by: issueForm.authorized_by || null,
      remarks: issueForm.remarks || null,
    })

    if (res?.data?.status === 201 || res?.status === 201 || res?.data?.status === 200) {
      showToast('সার্টিফিকেট সফলভাবে প্রকাশ করা হয়েছে', 'success')
      showIssue.value = false
      fetchIssueCerts(1)
      activeTab.value = 'issue'
    } else {
      showToast(res?.data?.message || 'সার্টিফিকেট প্রকাশ করতে সমস্যা হয়েছে', 'error')
    }
  } catch (err: any) {
    console.error('Issue failed:', err)
    showToast(err?.response?.data?.message || 'সার্টিফিকেট প্রকাশ ব্যর্থ হয়েছে', 'error')
  } finally {
    issueSaving.value = false
  }
}

// View & Print Certificate
function viewIssue(c: any) {
  viewIssueData.value = c
  showViewIssue.value = true
}

function quickPrint(c: any) {
  viewIssueData.value = c
  showViewIssue.value = true
  setTimeout(() => {
    window.print()
  }, 300)
}

function printCurrentCertificate() {
  window.print()
}

// Delete Handlers
function deleteTemplate(t: any) {
  deleteConfirmItem.value = t
  deleteConfirmType.value = 'template'
  showDeleteConfirm.value = true
}

function deleteIssue(c: any) {
  deleteConfirmItem.value = c
  deleteConfirmType.value = 'issue'
  showDeleteConfirm.value = true
}

async function executeConfirmedDelete() {
  if (!deleteConfirmItem.value) return
  deleteConfirming.value = true
  try {
    if (deleteConfirmType.value === 'template') {
      await api.delete(`/certificate-templates/${deleteConfirmItem.value.id}`)
      showToast('টেমপলেট সফলভাবে মুছে ফেলা হয়েছে', 'success')
      showDeleteConfirm.value = false
      deleteConfirmItem.value = null
      fetchTemplates(templates.value.current_page)
    } else {
      await api.delete(`/certificates/${deleteConfirmItem.value.id}`)
      showToast('সনদ সফলভাবে মুছে ফেলা হয়েছে', 'success')
      showDeleteConfirm.value = false
      deleteConfirmItem.value = null
      fetchIssueCerts(issuedCerts.value.current_page)
    }
  } catch (err: any) {
    console.error('Delete failed:', err)
    showToast(err?.response?.data?.message || 'মুছে ফেলতে সমস্যা হয়েছে', 'error')
  } finally {
    deleteConfirming.value = false
  }
}

// Helper Formatters
function formatTemplateType(type: string) {
  const map: Record<string, string> = {
    annual: 'বার্ষিক সনদ',
    transfer: 'ছাড়পত্র / বদলি',
    sanction: 'অনুমোদন সনদ',
    conduct: 'আচরণ ও প্রশংসাপত্র',
    others: 'অন্যান্য'
  }
  return map[type] || type || 'সাধারণ সনদ'
}

function getTemplateTypeClass(type: string) {
  const map: Record<string, string> = {
    annual: 'type-annual',
    transfer: 'type-transfer',
    sanction: 'type-sanction',
    conduct: 'type-conduct',
    others: 'type-others'
  }
  return map[type] || 'type-annual'
}

function formatDate(date: string) {
  if (!date) return '—'
  try {
    return new Date(date).toLocaleDateString('bn-BD', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    })
  } catch {
    return date
  }
}

function getStudentName(cert: any) {
  if (!cert) return '—'
  return cert.studentRelation?.name_bn || cert.studentRelation?.name_en || cert.studentRelation?.name || 'শিক্ষার্থী'
}

function getStudentInitials(cert: any) {
  const name = getStudentName(cert)
  return name.charAt(0) || 'শ'
}

function getClassName(cert: any) {
  if (!cert) return '—'
  return cert.classRelation?.name_bn || cert.classRelation?.name_en || cert.classRelation?.name || cert.studentRelation?.academic_class?.name_bn || '—'
}

function formatClassesList(classes: any) {
  if (!classes) return '—'
  if (Array.isArray(classes)) {
    return classes.map(c => c.name_bn || c.name_en || c.name).join(', ') || '—'
  }
  return classes.name_bn || classes.name_en || classes.name || '—'
}

function formatCertBody(cert: any) {
  if (!cert) return ''
  const td = cert.templateRelation?.template_data || {}
  const rawBody = td.body_text ||
    'এই মর্মে সনদপত্র ও প্রত্যয়ন প্রদান করা যাইতেছে যে, {student_name}, পিতা: {father_name}, অত্র প্রতিষ্ঠানের {class_name} জামাতের একজন নিয়মিত ও মনোযোগী শিক্ষার্থী। সে পরীক্ষায় কৃতিত্বপূর্ণ ফলাফল অর্জন করিয়াছে।'
  
  const studentName = getStudentName(cert)
  const fatherName = cert.studentRelation?.father_name_bn || cert.studentRelation?.father_name || 'মুহাম্মদ রফিকুল ইসলাম'
  const className = getClassName(cert)
  const rollNo = cert.studentRelation?.roll_number || cert.student_id || '—'
  const certNo = cert.certificate_number || '—'
  const issueDate = formatDate(cert.issue_date)

  return rawBody
    .replace(/\{student_name\}/g, studentName)
    .replace(/\{father_name\}/g, fatherName)
    .replace(/\{class_name\}/g, className)
    .replace(/\{roll_no\}/g, String(rollNo))
    .replace(/\{certificate_number\}/g, certNo)
    .replace(/\{issue_date\}/g, issueDate)
}

onMounted(() => {
  fetchClasses()
  fetchSubjects()
  fetchTemplates()
  fetchIssueCerts()
  fetchStudents()
})
</script>

<style scoped lang="scss">
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Anek+Bangla:wght@400;500;600;700;800&display=swap');

.page-wrapper {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem;
}

/* Toast Banner */
.toast-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.25rem;
  border-radius: var(--radius-md, 8px);
  margin-bottom: 1.25rem;
  font-family: var(--font-bn);
  font-size: var(--text-sm);
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  animation: slideDown 0.3s ease;

  &.success {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
  }

  &.error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
  }

  .toast-content {
    display: flex;
    align-items: center;
    gap: 0.65rem;
  }

  .toast-close {
    background: transparent;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: inherit;
    opacity: 0.7;
    &:hover { opacity: 1; }
  }
}

/* Sub-nav Navigation Pills */
.subnav-row {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
  overflow-x: auto;
  padding-bottom: 0.25rem;
}

.subnav-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 99px;
  background: var(--color-bg-card, #ffffff);
  border: 1px solid var(--color-border-light, #e2e8f0);
  color: var(--color-text-light, #64748b);
  font-family: var(--font-bn);
  font-size: 0.88rem;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s ease;

  &:hover {
    color: var(--color-primary, #145032);
    border-color: var(--color-primary, #145032);
    background: rgba(20, 80, 50, 0.04);
  }

  &.active {
    background: var(--color-primary, #145032);
    border-color: var(--color-primary, #145032);
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(20, 80, 50, 0.2);
  }
}

/* Page Header */
.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-title-block {
  .eyebrow {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--color-primary, #145032);
    letter-spacing: 0.05em;
  }
  h1 {
    font-size: 1.6rem;
    font-weight: 800;
    margin: 0.2rem 0;
    color: var(--color-text, #1e293b);
    font-family: var(--font-bn);
  }
  .page-subtitle {
    color: var(--color-text-light, #64748b);
    font-size: 0.9rem;
    margin: 0;
    font-family: var(--font-bn);
  }
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

/* KPI Summary Cards */
.stats-overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: var(--color-bg-card, #ffffff);
  border: 1px solid var(--color-border-light, #e2e8f0);
  border-radius: var(--radius-lg, 12px);
  padding: 1rem 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .stat-icon-wrapper {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;

    &.primary {
      background: rgba(20, 80, 50, 0.1);
      color: #145032;
    }
    &.success {
      background: rgba(16, 185, 129, 0.12);
      color: #059669;
    }
    &.info {
      background: rgba(59, 130, 246, 0.12);
      color: #2563eb;
    }
    &.warning {
      background: rgba(245, 158, 11, 0.12);
      color: #d97706;
    }
  }

  .stat-content {
    display: flex;
    flex-direction: column;

    .stat-label {
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--color-text-light, #64748b);
      font-family: var(--font-bn);
    }
    .stat-value {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--color-text, #1e293b);
      margin: 0.1rem 0;
      line-height: 1.2;
    }
    .stat-sub {
      font-size: 0.72rem;
      color: var(--color-text-light, #94a3b8);
      font-family: var(--font-bn);
    }
  }
}

/* Tabs */
.tabs-nav {
  display: inline-flex;
  gap: 0.35rem;
  background: var(--color-bg-card, #ffffff);
  padding: 0.35rem;
  border-radius: var(--radius-md, 8px);
  border: 1px solid var(--color-border-light, #e2e8f0);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1.1rem;
  border-radius: var(--radius-sm, 6px);
  border: 1px solid transparent;
  background: transparent;
  color: var(--color-text-light, #64748b);
  font-family: var(--font-bn);
  font-size: 0.88rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;

  &:hover {
    color: var(--color-text, #1e293b);
    background: rgba(0, 0, 0, 0.03);
  }

  &.active {
    background: var(--color-primary, #145032);
    border-color: var(--color-primary, #145032);
    color: #ffffff;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(20, 80, 50, 0.25);

    .tab-count-badge {
      background: rgba(255, 255, 255, 0.25);
      color: #ffffff;
    }
  }

  .tab-count-badge {
    display: inline-block;
    padding: 0.1rem 0.45rem;
    border-radius: 99px;
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(0, 0, 0, 0.06);
    color: var(--color-text, #1e293b);
  }
}

/* Table Card & Toolbar */
.table-card {
  background: var(--color-bg-card, #ffffff);
  border: 1px solid var(--color-border-light, #e2e8f0);
  border-radius: var(--radius-lg, 12px);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.toolbar {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border-light, #e2e8f0);
  background: var(--color-bg-muted, #f8fafc);
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 220px;

  .search-icon {
    position: absolute;
    left: 0.75rem;
    color: var(--color-text-light, #94a3b8);
  }

  input {
    width: 100%;
    padding: 0.5rem 2rem 0.5rem 2.25rem;
    border: 1px solid var(--color-border, #cbd5e1);
    border-radius: var(--radius-sm, 6px);
    font-family: var(--font-bn);
    font-size: 0.88rem;
    background: #ffffff;

    &:focus {
      outline: none;
      border-color: var(--color-primary, #145032);
      box-shadow: 0 0 0 3px rgba(20, 80, 50, 0.1);
    }
  }

  .clear-search-btn {
    position: absolute;
    right: 0.6rem;
    background: transparent;
    border: none;
    cursor: pointer;
    color: var(--color-text-light, #94a3b8);
    display: flex;
    align-items: center;
    justify-content: center;
    &:hover { color: var(--color-text, #1e293b); }
  }
}

.select-wrapper {
  min-width: 160px;
}

.form-select, .form-control {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--color-border, #cbd5e1);
  border-radius: var(--radius-sm, 6px);
  font-family: var(--font-bn);
  font-size: 0.88rem;
  background: #ffffff;

  &:focus {
    outline: none;
    border-color: var(--color-primary, #145032);
    box-shadow: 0 0 0 3px rgba(20, 80, 50, 0.1);
  }
}

/* Premium Table */
.table-responsive {
  overflow-x: auto;
}

.premium-table {
  width: 100%;
  border-collapse: collapse;
  font-family: var(--font-bn);

  th {
    padding: 0.85rem 1.25rem;
    text-align: left;
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--color-text-light, #64748b);
    border-bottom: 1px solid var(--color-border-light, #e2e8f0);
    background: #f8fafc;
  }

  td {
    padding: 0.95rem 1.25rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--color-border-light, #e2e8f0);
    font-size: 0.9rem;
    color: var(--color-text, #1e293b);
  }

  tr:hover td {
    background: rgba(20, 80, 50, 0.02);
  }
}

.template-title-cell {
  display: flex;
  flex-direction: column;
  strong { font-size: 0.92rem; color: var(--color-text, #1e293b); }
  small { font-size: 0.75rem; margin-top: 0.1rem; }
}

.student-profile-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;

  .student-avatar-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(20, 80, 50, 0.12);
    color: #145032;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
  }

  .student-info-meta {
    display: flex;
    flex-direction: column;
    strong { font-size: 0.9rem; }
    .roll-sub { font-size: 0.75rem; color: var(--color-text-light, #64748b); }
  }
}

.template-pill-tag {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  background: #f1f5f9;
  color: #334155;
  font-size: 0.8rem;
  font-weight: 600;
}

.auth-person {
  font-size: 0.85rem;
  color: #475569;
}

/* Badges */
.badge-pill {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 99px;
  font-size: 0.75rem;
  font-weight: 600;
  font-family: var(--font-bn);

  &.type-annual { background: #dbeafe; color: #1e40af; }
  &.type-transfer { background: #fef3c7; color: #92400e; }
  &.type-sanction { background: #e0e7ff; color: #3730a3; }
  &.type-conduct { background: #d1fae5; color: #065f46; }
  &.type-others { background: #f3f4f6; color: #4b5563; }
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.2rem 0.65rem;
  border-radius: 99px;
  font-size: 0.75rem;
  font-weight: 600;

  &.badge-approved { background: #ecfdf5; color: #065f46; }
  &.badge-rejected { background: #fef2f2; color: #991b1b; }

  .status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
  }
}

.cert-badge {
  background: #f1f5f9;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  border: 1px solid #e2e8f0;
  font-family: monospace;
  font-size: 0.82rem;
  font-weight: 600;
  color: #145032;
}

/* Action Buttons */
.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: var(--radius-sm, 6px);
  border: 1px solid var(--color-border-light, #e2e8f0);
  background: #ffffff;
  color: var(--color-text-light, #64748b);
  cursor: pointer;
  transition: all 0.15s ease;

  &:hover {
    border-color: var(--color-primary, #145032);
    color: var(--color-primary, #145032);
    background: rgba(20, 80, 50, 0.05);
  }

  &.print:hover {
    border-color: #2563eb;
    color: #2563eb;
    background: rgba(37, 99, 235, 0.08);
  }

  &.delete:hover {
    border-color: #ef4444;
    color: #ef4444;
    background: rgba(239, 68, 68, 0.08);
  }

  &.close {
    border: none;
    background: transparent;
    font-size: 1.1rem;
    &:hover { background: rgba(0, 0, 0, 0.05); }
  }
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1rem;
  border-radius: var(--radius-sm, 6px);
  font-family: var(--font-bn);
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  text-decoration: none;

  &.btn-primary {
    background: var(--color-primary, #145032);
    color: #ffffff;
    &:hover:not(:disabled) {
      background: #0f3d26;
      box-shadow: 0 2px 8px rgba(20, 80, 50, 0.3);
    }
  }

  &.btn-outline {
    background: #ffffff;
    border-color: var(--color-border, #cbd5e1);
    color: var(--color-text, #1e293b);
    &:hover:not(:disabled) {
      border-color: var(--color-primary, #145032);
      color: var(--color-primary, #145032);
      background: rgba(20, 80, 50, 0.04);
    }
  }

  &.btn-secondary {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #334155;
    &:hover:not(:disabled) {
      background: #e2e8f0;
    }
  }

  &.btn-danger {
    background: #ef4444;
    color: #ffffff;
    &:hover:not(:disabled) { background: #dc2626; }
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}

.btn-text {
  background: transparent;
  border: none;
  color: var(--color-primary, #145032);
  font-family: var(--font-bn);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0;

  &:hover { text-decoration: underline; }
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
  backdrop-filter: blur(2px);
}

.modal-card {
  background: #ffffff;
  border-radius: var(--radius-lg, 12px);
  width: 100%;
  max-width: 540px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
  animation: scaleIn 0.2s ease;

  &.modal-lg { max-width: 760px; }
  &.modal-cert-preview { max-width: 900px; }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.15rem 1.5rem;
  border-bottom: 1px solid var(--color-border-light, #e2e8f0);

  .modal-title-wrap {
    .modal-eyebrow {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--color-primary, #145032);
      text-transform: uppercase;
    }
    h3 {
      margin: 0.1rem 0 0;
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--color-text, #1e293b);
      font-family: var(--font-bn);
    }
  }
}

.modal-body {
  padding: 1.25rem 1.5rem;
  overflow-y: auto;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border-light, #e2e8f0);
  background: #f8fafc;
  border-bottom-left-radius: var(--radius-lg, 12px);
  border-bottom-right-radius: var(--radius-lg, 12px);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;

  .form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--color-text, #1e293b);
    font-family: var(--font-bn);

    .required { color: #ef4444; }
    .field-hint { font-size: 0.75rem; font-weight: 400; margin-left: 0.3rem; }
  }
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;

  &.mb-2 { margin-bottom: 0.5rem; }
  &.mb-3 { margin-bottom: 0.85rem; }
}

.structured-card-section {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 1rem;

  .section-title {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.92rem;
    font-weight: 700;
    color: #145032;
    margin: 0 0 0.85rem;
    font-family: var(--font-bn);
  }
}

.form-check-group {
  .checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #334155;
    cursor: pointer;
    font-family: var(--font-bn);
  }
}

/* Toggle Switch */
.toggle-row { display: flex; align-items: center; }
.toggle { position: relative; display: inline-block; width: 40px; height: 22px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: 0.3s; border-radius: 22px; }
.toggle-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; }
.toggle input:checked + .toggle-slider { background-color: #145032; }
.toggle input:checked + .toggle-slider:before { transform: translateX(18px); }

/* Syllabus Grid */
.syllabus-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.syllabus-card {
  background: #ffffff;
  border: 1px solid var(--color-border-light, #e2e8f0);
  border-radius: var(--radius-md, 8px);
  padding: 1.15rem;
  transition: all 0.2s ease;

  &:hover {
    border-color: #145032;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
  }
}

.syllabus-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.65rem;

  h4 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--color-text, #1e293b);
  }
}

.code-badge {
  font-size: 0.75rem;
  background: rgba(20, 80, 50, 0.1);
  color: #145032;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  font-weight: 700;
  font-family: monospace;
}

.syllabus-field {
  display: flex;
  gap: 0.5rem;
  font-size: 0.85rem;
  margin-bottom: 0.3rem;

  .label { color: var(--color-text-light, #64748b); min-width: 48px; }
  .value { color: var(--color-text, #1e293b); font-weight: 600; }
}

/* Pagination */
.pagination-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-top: 1px solid var(--color-border-light, #e2e8f0);
  background: #ffffff;
}

.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.45rem 0.85rem;
  border-radius: var(--radius-sm, 6px);
  background: #ffffff;
  border: 1px solid var(--color-border, #cbd5e1);
  color: var(--color-text, #1e293b);
  font-family: var(--font-bn);
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease;

  &:hover:not(:disabled) {
    border-color: #145032;
    color: #145032;
    background: rgba(20, 80, 50, 0.05);
  }

  &:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    background: #f1f5f9;
  }
}

.pagination-numbers {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.page-info {
  font-size: 0.85rem;
  color: var(--color-text-light, #64748b);
  font-family: var(--font-bn);
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3.5rem 1.5rem;
  text-align: center;
  font-family: var(--font-bn);

  .empty-icon-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    margin-bottom: 1rem;
  }

  h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--color-text, #1e293b);
  }

  p {
    margin: 0.35rem 0 1rem;
    font-size: 0.9rem;
    color: var(--color-text-light, #64748b);
    max-width: 440px;
  }
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  font-family: var(--font-bn);
  color: var(--color-text-light, #64748b);

  .spinner {
    width: 36px;
    height: 36px;
    border: 3px solid #e2e8f0;
    border-top-color: #145032;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 0.75rem;
  }
}

/* =======================================================
   PRINTABLE ISLAMIC SANAD / CERTIFICATE STYLES
   ======================================================= */
.cert-preview-modal-body {
  background: #f1f5f9;
  padding: 1.5rem;
}

.sanad-container-outer {
  max-width: 820px;
  margin: 0 auto;
}

.sanad-paper {
  background: #fffdf7;
  padding: 1.25rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
  border-radius: 4px;
}

.sanad-border {
  border: 4px double #b45309;
  padding: 6px;
}

.sanad-inner-border {
  border: 1.5px solid #145032;
  padding: 1.75rem 2rem;
  text-align: center;
  position: relative;
  background-image: radial-gradient(circle at 50% 50%, rgba(180, 83, 9, 0.02) 0%, transparent 60%);
}

.sanad-bismillah {
  font-family: 'Amiri', serif;
  font-size: 1.6rem;
  color: #145032;
  margin-bottom: 0.75rem;
}

.sanad-madrasha-header {
  margin-bottom: 1rem;

  .bn-madrasha-title {
    font-family: 'Anek Bangla', sans-serif;
    font-size: 1.75rem;
    font-weight: 800;
    color: #145032;
    margin: 0.2rem 0;
  }

  .madrasha-est {
    font-family: 'Anek Bangla', sans-serif;
    font-size: 0.85rem;
    color: #b45309;
    margin: 0;
    font-weight: 600;
  }
}

.sanad-badge-title {
  display: inline-block;
  margin: 0.75rem auto 1.25rem;
  border-bottom: 2px solid #b45309;
  padding-bottom: 0.35rem;

  .bn-text {
    font-family: 'Anek Bangla', sans-serif;
    font-size: 1.3rem;
    font-weight: 800;
    color: #1e293b;
    margin-right: 0.5rem;
  }

  .type-subtext {
    font-size: 0.85rem;
    color: #b45309;
    font-weight: 600;
  }
}

.sanad-body-text {
  text-align: justify;
  margin: 1.25rem 0;
  line-height: 1.85;
  font-family: 'Anek Bangla', sans-serif;

  .body-para {
    font-size: 1.05rem;
    color: #1e293b;
    margin-bottom: 0.85rem;
  }

  .body-para-dua {
    font-size: 0.95rem;
    color: #145032;
    font-weight: 600;
    text-align: center;
    font-style: italic;
  }
}

.sanad-meta-row {
  display: flex;
  justify-content: space-between;
  border-top: 1px dashed #cbd5e1;
  border-bottom: 1px dashed #cbd5e1;
  padding: 0.75rem 0;
  margin: 1.25rem 0;
  font-family: 'Anek Bangla', sans-serif;
  font-size: 0.88rem;
  color: #334155;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.sanad-signatures {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: 2.25rem;
  padding: 0 1rem;

  .sig-block {
    text-align: center;
    width: 170px;

    .sig-line {
      border-top: 1.5px solid #475569;
      margin-bottom: 0.4rem;
    }

    .sig-role {
      font-family: 'Anek Bangla', sans-serif;
      font-size: 0.88rem;
      font-weight: 700;
      color: #1e293b;
    }
  }

  .seal-block {
    .official-seal {
      width: 80px;
      height: 80px;
      border: 2px dashed #b45309;
      border-radius: 50%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #b45309;
      font-family: 'Anek Bangla', sans-serif;
      font-size: 0.7rem;
      font-weight: 700;
      transform: rotate(-10deg);
      opacity: 0.85;

      small { font-size: 0.55rem; letter-spacing: 1px; }
    }
  }
}

/* Keyframe Animations */
@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}

/* =======================================================
   PRINT MEDIA QUERY FOR A4 SANAD
   ======================================================= */
@media print {
  body * {
    visibility: hidden !important;
  }

  .no-print,
  .modal-header,
  .modal-footer,
  .sidebar,
  .topbar,
  .subnav-row,
  .page-header-row,
  .stats-overview-grid,
  .tabs-nav,
  .table-card {
    display: none !important;
  }

  .modal-overlay {
    position: static !important;
    background: transparent !important;
    padding: 0 !important;
    display: block !important;
    backdrop-filter: none !important;
  }

  .modal-card,
  .modal-cert-preview {
    border: none !important;
    box-shadow: none !important;
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    background: transparent !important;
    max-height: none !important;
  }

  .cert-preview-modal-body {
    background: transparent !important;
    padding: 0 !important;
  }

  #printable-certificate,
  #printable-certificate * {
    visibility: visible !important;
  }

  #printable-certificate {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    box-shadow: none !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .sanad-paper {
    box-shadow: none !important;
    padding: 0.5cm !important;
  }
}
</style>
