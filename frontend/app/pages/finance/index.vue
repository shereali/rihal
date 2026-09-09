<template>
  <div class="page-wrapper">
    <div class="page-header-row no-print">
      <div class="header-title-block">
        <span class="eyebrow">হিসাব ও অর্থায়ন</span>
        <h1>আর্থিক ও ফান্ড ব্যবস্থাপনা</h1>
        <p class="page-subtitle">ফান্ড, দান-অনুদান, প্রাতিষ্ঠানিক ব্যয়, রিসিপ্ট ও আর্থিক লেনদেন নিয়ন্ত্রণ</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/finance/audit" class="btn btn-outline">
          <icon name="shield-check" /> অডিট ট্রেইল
        </NuxtLink>
        <button class="btn btn-primary" @click="openCreateModal">
          <icon name="plus" /> {{ createButtonLabel }}
        </button>
        <button class="btn btn-outline" @click="loadFinance">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Stats KPI Grid -->
    <div class="stats-grid no-print">
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="cash-multiple" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(summary?.total_donations) }} ৳</span>
          <span class="stat-label">মোট সংগৃহীত দান</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap red"><icon name="cash-minus" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(summary?.total_expenses) }} ৳</span>
          <span class="stat-label">মোট প্রাতিষ্ঠানিক ব্যয়</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="heart-multiple" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(summary?.total_fee_collected) }} ৳</span>
          <span class="stat-label">শিক্ষার্থী ফি আয়</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="cash-plus" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(summary?.net_balance) }} ৳</span>
          <span class="stat-label">নিট তহবিল স্থিতি</span>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="report-tabs no-print">
      <button v-for="tab in tabs" :key="tab.id" class="report-tab-btn" :class="{ active: activeTab === tab.id }" @click="activeTab = tab.id">
        <icon :name="tab.icon" /> {{ tab.label }}
      </button>
    </div>

    <!-- ================= FUNDS TAB ================= -->
    <div v-show="activeTab === 'funds'" class="tab-pane">
      <div class="toolbar card no-print">
        <div class="search-box">
          <icon name="search" class="search-icon" />
          <input v-model="fundSearch" placeholder="ফান্ডের নাম বা বিবরণ খুঁজুন..." />
          <button v-if="fundSearch" class="clear-search-btn" @click="fundSearch = ''">×</button>
        </div>
        <select v-model="fundTypeFilter" class="form-select">
          <option value="">সব ধরনের ফান্ড</option>
          <option value="সাধারণ">সাধারণ ফান্ড</option>
          <option value="যাকাত">যাকাত ফান্ড</option>
          <option value="নির্মাণ">নির্মাণ / অবকাঠামো</option>
          <option value="এতিম">এতিম সহায়তা</option>
          <option value="বিশেষ">বিশেষ ফান্ড</option>
        </select>
        <div class="pagination-info" v-if="filteredFunds.length">
          মোট <span class="highlight">{{ filteredFunds.length.toLocaleString('bn-BD') }}</span> টি ফান্ড
        </div>
      </div>

      <div v-if="loading" class="loading-state card"><div class="spinner" /><p>ফান্ড লোড হচ্ছে...</p></div>
      <div v-else-if="!filteredFunds.length" class="empty-state card">
        <div class="empty-icon-wrap"><icon name="folder-plus" /></div>
        <h3>কোনো ফান্ড পাওয়া যায়নি</h3>
        <p>মাদ্রাসার কার্যক্রম পরিচালনার জন্য নতুন ফান্ড তৈরি করুন</p>
        <button class="btn btn-primary" @click="openFundModal()"><icon name="plus" /> নতুন ফান্ড তৈরি করুন</button>
      </div>
      <div v-else class="funds-grid">
        <div v-for="fund in filteredFunds" :key="fund.id" class="fund-card card">
          <div class="fund-card-header">
            <div class="fund-icon-box"><icon name="cash-multiple" /></div>
            <div class="fund-title-block">
              <h3>{{ fund.name_bn }}</h3>
              <span class="fund-type-tag">{{ fund.type || 'নির্দিষ্ট ফান্ড' }}</span>
            </div>
            <span class="status-pill" :class="fund.is_active ? 'badge-approved' : 'badge-rejected'">
              <span class="status-dot" />
              {{ fund.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
            </span>
          </div>

          <div class="fund-amounts-row">
            <div class="amount-item">
              <span class="amount-label">সংগৃহীত তহবিল</span>
              <strong class="amount-val success">{{ formatCurrency(fund.collected_amount) }} ৳</strong>
            </div>
            <div class="amount-item" v-if="fund.target_amount">
              <span class="amount-label">লক্ষ্যমাত্রা</span>
              <strong class="amount-val">{{ formatCurrency(fund.target_amount) }} ৳</strong>
            </div>
          </div>

          <div v-if="fund.target_amount" class="fund-progress-wrap">
            <div class="fund-progress-bar" :style="{ width: getFundPercent(fund) + '%' }" />
            <span class="progress-label">{{ getFundPercent(fund) }}% অর্জিত</span>
          </div>

          <div class="card-footer-actions">
            <NuxtLink :to="`/finance/funds/${fund.id}`" class="view-link">
              বিস্তারিত বিবরণ <icon name="arrow-right" />
            </NuxtLink>
            <div class="action-buttons">
              <button class="action-btn" @click="openFundModal(fund)" title="সম্পাদনা">
                <icon name="pencil" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ================= DONATIONS TAB ================= -->
    <div v-show="activeTab === 'donations'" class="tab-pane">
      <div class="toolbar card no-print">
        <div class="search-box">
          <icon name="search" class="search-icon" />
          <input v-model="donationSearch" placeholder="দাতার নাম, রশিদ নং বা মন্তব্য খুঁজুন..." />
          <button v-if="donationSearch" class="clear-search-btn" @click="donationSearch = ''">×</button>
        </div>
        <select v-model="donationFundFilter" class="form-select">
          <option value="">সব ফান্ড (All Funds)</option>
          <option v-for="f in fundsList" :key="f.id" :value="f.id">{{ f.name_bn }}</option>
        </select>
        <div class="pagination-info" v-if="filteredDonations.length">
          মোট <span class="highlight">{{ filteredDonations.length.toLocaleString('bn-BD') }}</span> টি দান
        </div>
      </div>

      <div v-if="loading" class="loading-state card"><div class="spinner" /><p>দান তালিকা লোড হচ্ছে...</p></div>
      <div v-else-if="!filteredDonations.length" class="empty-state card">
        <div class="empty-icon-wrap"><icon name="heart" /></div>
        <h3>কোনো দানের রেকর্ড নেই</h3>
        <p>নতুন দান বা অনুদান গ্রহণ করে রিসিপ্ট তৈরি করুন</p>
        <button class="btn btn-primary" @click="openDonationModal()"><icon name="plus" /> নতুন দান এন্ট্রি করুন</button>
      </div>
      <div v-else class="card table-card">
        <div class="table-responsive">
          <table class="premium-table">
            <thead>
              <tr>
                <th>দাতা</th>
                <th>ফান্ড</th>
                <th>পরিমাণ</th>
                <th>পদ্ধতি</th>
                <th>তারিখ</th>
                <th>মন্তব্য / রশিদ</th>
                <th class="text-right">অ্যাকশন</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in filteredDonations" :key="d.id">
                <td>
                  <div class="user-cell">
                    <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(d.donor?.name_bn || d.donor?.name_en || 'দ') }">
                      {{ (d.donor?.name_bn || d.donor?.name_en || 'দ').charAt(0) }}
                    </div>
                    <div>
                      <strong>{{ d.donor?.name_bn || d.donor?.name_en || 'অজ্ঞাত দাতা' }}</strong>
                      <div class="sub-text" v-if="d.donor?.phone">{{ d.donor.phone }}</div>
                    </div>
                  </div>
                </td>
                <td><span class="fund-tag">{{ d.fund?.name_bn || 'সাধারণ ফান্ড' }}</span></td>
                <td><strong class="text-success">{{ formatCurrency(d.amount) }} ৳</strong></td>
                <td><span class="type-tag">{{ d.method || 'নগদ' }}</span></td>
                <td>{{ formatDate(d.donation_date) }}</td>
                <td><span class="notes-text">{{ d.notes || d.receipt_number || '—' }}</span></td>
                <td class="text-right">
                  <NuxtLink :to="`/finance/donations/${d.id}`" class="action-btn" title="রশিদ দেখুন">
                    <icon name="eye" />
                  </NuxtLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================= EXPENSES TAB ================= -->
    <div v-show="activeTab === 'expenses'" class="tab-pane">
      <div class="toolbar card no-print">
        <div class="search-box">
          <icon name="search" class="search-icon" />
          <input v-model="expenseSearch" placeholder="ব্যয়ের বিষয়, ভেন্ডর বা নোট খুঁজুন..." />
          <button v-if="expenseSearch" class="clear-search-btn" @click="expenseSearch = ''">×</button>
        </div>
        <select v-model="expenseCategoryFilter" class="form-select">
          <option value="">সব খাত (All Categories)</option>
          <option value="বেতন">শিক্ষক/কর্মী বেতন</option>
          <option value="ইউটিলিটি">বিদ্যুৎ / গ্যাস / পানি</option>
          <option value="মেরামত">মেরামত ও রক্ষণাবেক্ষণ</option>
          <option value="খাদ্য">হোস্টেল খাদ্য সামগ্রী</option>
          <option value="স্টেশনারি">বই ও স্টেশনারি</option>
          <option value="অন্যান্য">অন্যান্য প্রশাসনিক</option>
        </select>
        <div class="pagination-info" v-if="filteredExpenses.length">
          মোট <span class="highlight">{{ filteredExpenses.length.toLocaleString('bn-BD') }}</span> টি ব্যয়
        </div>
      </div>

      <div v-if="loading" class="loading-state card"><div class="spinner" /><p>ব্যয় তালিকা লোড হচ্ছে...</p></div>
      <div v-else-if="!filteredExpenses.length" class="empty-state card">
        <div class="empty-icon-wrap"><icon name="cash-minus" /></div>
        <h3>কোনো ব্যয়ের রেকর্ড নেই</h3>
        <p>নতুন ভাউচার ও ব্যয়ের হিসাব যুক্ত করুন</p>
        <button class="btn btn-primary" @click="openExpenseModal()"><icon name="plus" /> নতুন ব্যয় যুক্ত করুন</button>
      </div>
      <div v-else class="card table-card">
        <div class="table-responsive">
          <table class="premium-table">
            <thead>
              <tr>
                <th>ব্যয়ের বিষয়</th>
                <th>খাত / ক্যাটাগরি</th>
                <th>পরিমাণ</th>
                <th>প্রাপক / ভেন্ডর</th>
                <th>পদ্ধতি</th>
                <th>তারিখ</th>
                <th class="text-right">অ্যাকশন</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="exp in filteredExpenses" :key="exp.id">
                <td>
                  <strong>{{ exp.description_bn || exp.description }}</strong>
                  <div class="sub-text" v-if="exp.notes">{{ exp.notes }}</div>
                </td>
                <td><span class="type-tag">{{ exp.category || 'সাধারণ' }}</span></td>
                <td><strong class="text-danger">{{ formatCurrency(exp.amount) }} ৳</strong></td>
                <td>{{ exp.vendor?.name_bn || exp.payee_name || '—' }}</td>
                <td><span class="badge-outline">{{ exp.method || 'নগদ' }}</span></td>
                <td>{{ formatDate(exp.transaction_date) }}</td>
                <td class="text-right">
                  <NuxtLink :to="`/finance/expenses/${exp.id}`" class="action-btn" title="ভাউচার দেখুন">
                    <icon name="eye" />
                  </NuxtLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================= DONORS TAB ================= -->
    <div v-show="activeTab === 'donors'" class="tab-pane">
      <div class="toolbar card no-print">
        <div class="search-box">
          <icon name="search" class="search-icon" />
          <input v-model="donorSearch" placeholder="দাতার নাম, মোবাইল বা প্রতিষ্ঠান খুঁজুন..." />
          <button v-if="donorSearch" class="clear-search-btn" @click="donorSearch = ''">×</button>
        </div>
        <div class="pagination-info" v-if="filteredDonors.length">
          মোট <span class="highlight">{{ filteredDonors.length.toLocaleString('bn-BD') }}</span> জন দাতা
        </div>
      </div>

      <div v-if="loading" class="loading-state card"><div class="spinner" /><p>দাতা তালিকা লোড হচ্ছে...</p></div>
      <div v-else-if="!filteredDonors.length" class="empty-state card">
        <div class="empty-icon-wrap"><icon name="users" /></div>
        <h3>কোনো দাতা নিবন্ধিত নেই</h3>
        <p>মাদ্রাসার দাতা ও পৃষ্ঠপোষকদের তথ্য যোগ করুন</p>
        <button class="btn btn-primary" @click="openDonorModal()"><icon name="plus" /> নতুন দাতা যোগ করুন</button>
      </div>
      <div v-else class="card table-card">
        <div class="table-responsive">
          <table class="premium-table">
            <thead>
              <tr>
                <th>দাতার নাম</th>
                <th>মোবাইল নম্বর</th>
                <th>ইমেইল</th>
                <th>প্রতিষ্ঠান</th>
                <th>রক্তের গ্রুপ</th>
                <th>ঠিকানা</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in filteredDonors" :key="d.id">
                <td>
                  <div class="user-cell">
                    <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(d.name_bn || d.name_en) }">
                      {{ (d.name_bn || d.name_en || 'দ').charAt(0) }}
                    </div>
                    <strong>{{ d.name_bn }}</strong>
                  </div>
                </td>
                <td class="mono-font">{{ d.phone || '—' }}</td>
                <td>{{ d.email || '—' }}</td>
                <td>{{ d.organization || '—' }}</td>
                <td><span class="type-tag" v-if="d.blood_group">{{ d.blood_group }}</span><span v-else>—</span></td>
                <td>{{ d.address_bn || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================= STOCKS TAB ================= -->
    <div v-show="activeTab === 'stocks'" class="tab-pane">
      <div v-if="loading" class="loading-state card"><div class="spinner" /><p>রিসিপ্ট স্টক লোড হচ্ছে...</p></div>
      <div v-else-if="!stocksList.length" class="empty-state card">
        <div class="empty-icon-wrap"><icon name="document-text" /></div>
        <h3>কোনো রিসিপ্ট স্টক রেকর্ড নেই</h3>
        <p>রিসিপ্ট ও ভাউচার বই ব্যবস্থাপনা</p>
      </div>
      <div v-else class="card table-card">
        <div class="table-responsive">
          <table class="premium-table">
            <thead>
              <tr>
                <th>রিসিপ্ট / বই নং</th>
                <th>তারিখ</th>
                <th>ধরণ</th>
                <th>পরিমাণ</th>
                <th>বন্টন / দায়িত্বশীল</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in stocksList" :key="s.id">
                <td><strong>#{{ s.receipt_number || s.number || s.id }}</strong></td>
                <td>{{ formatDate(s.receipt_date || s.date) }}</td>
                <td><span class="type-tag">{{ s.type || s.category || 'আয়' }}</span></td>
                <td><strong>{{ formatCurrency(s.amount) }} ৳</strong></td>
                <td>{{ s.allocation || s.distributed_to || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================= MODALS ================= -->

    <!-- Create/Edit Fund Modal -->
    <div v-if="showFundModal" class="modal-overlay" @click.self="showFundModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>{{ fundEditingId ? 'ফান্ড সম্পাদনা' : 'নতুন ফান্ড তৈরি করুন' }}</h3>
            <p>ফান্ডের নাম, ধরন, লক্ষ্যমাত্রা ও বিবরণ লিখুন</p>
          </div>
          <button class="modal-close-btn" @click="showFundModal = false">×</button>
        </div>
        <form @submit.prevent="saveFund" class="modal-form">
          <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">ফান্ডের নাম (বাংলা) *</label>
              <input v-model="fundForm.name_bn" class="form-input" required placeholder="যেমন: নতুন মসজিদ নির্মাণ ফান্ড" />
            </div>
            <div class="form-group">
              <label class="form-label">ফান্ডের নাম (ইংরেজি)</label>
              <input v-model="fundForm.name_en" class="form-input" placeholder="e.g. Mosque Construction Fund" />
            </div>
            <div class="form-group">
              <label class="form-label">ফান্ডের ধরন *</label>
              <select v-model="fundForm.type" class="form-select" required>
                <option value="সাধারণ">সাধারণ ফান্ড</option>
                <option value="যাকাত">যাকাত ফান্ড</option>
                <option value="নির্মাণ">নির্মাণ / অবকাঠামো</option>
                <option value="এতিম">এতিম সহায়তা</option>
                <option value="বিশেষ">বিশেষ ফান্ড</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">লক্ষ্যমাত্রা (টাকা)</label>
              <input v-model.number="fundForm.target_amount" type="number" min="0" class="form-input" placeholder="১০০০০০" />
            </div>
            <div class="form-group wide">
              <label class="form-label">বিবরণ</label>
              <textarea v-model="fundForm.description_bn" class="form-textarea" rows="2" placeholder="ফান্ডের উদ্দেশ্য ও বিবরণ..."></textarea>
            </div>
            <div class="form-group wide">
              <label class="custom-checkbox">
                <input type="checkbox" v-model="fundForm.is_active" />
                <span class="checkbox-text">ফান্ডটি সক্রিয় রাখুন</span>
              </label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showFundModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (fundEditingId ? 'আপডেট করুন' : 'ফান্ড সংরক্ষণ করুন') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Donation Modal -->
    <div v-if="showDonationModal" class="modal-overlay" @click.self="showDonationModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন দান / অনুদান এন্ট্রি</h3>
            <p>দাতার নাম, ফান্ড, পরিমাণ ও রশিদ বিবরণী সংরক্ষণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showDonationModal = false">×</button>
        </div>
        <form @submit.prevent="saveDonation" class="modal-form">
          <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">দাতা নির্বাচন করুন *</label>
              <select v-model="donationForm.donor_id" class="form-select" required>
                <option value="">দাতা নির্বাচন করুন</option>
                <option v-for="d in donorsList" :key="d.id" :value="d.id">
                  {{ d.name_bn }} ({{ d.phone || 'ফোন নেই' }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">ফান্ড নির্বাচন করুন *</label>
              <select v-model="donationForm.fund_id" class="form-select" required>
                <option value="">ফান্ড নির্বাচন করুন</option>
                <option v-for="f in fundsList" :key="f.id" :value="f.id">{{ f.name_bn }}</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">দানের পরিমাণ (টাকা) *</label>
              <input v-model.number="donationForm.amount" type="number" min="1" class="form-input" required placeholder="৫০০০" />
            </div>
            <div class="form-group">
              <label class="form-label">পরিশোধ পদ্ধতি *</label>
              <select v-model="donationForm.method" class="form-select" required>
                <option value="নগদ">নগদ (Cash)</option>
                <option value="ব্যাংক">ব্যাংক ট্রান্সফার (Bank)</option>
                <option value="বিকাশ">বিকাশ (bKash)</option>
                <option value="নগদ/রকেট">নগদ / রকেট</option>
                <option value="চেক">চেক (Cheque)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">দানের তারিখ *</label>
              <input v-model="donationForm.donation_date" type="date" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">রশিদ নম্বর</label>
              <input v-model="donationForm.receipt_number" class="form-input" placeholder="যেমন: REC-2026-001" />
            </div>
            <div class="form-group wide">
              <label class="form-label">মন্তব্য / নোট</label>
              <input v-model="donationForm.notes" class="form-input" placeholder="যেমন: বিশেষ দোয়া বা উদ্দেশ্য" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showDonationModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'দান সংরক্ষণ করুন' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Expense Modal -->
    <div v-if="showExpenseModal" class="modal-overlay" @click.self="showExpenseModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন ব্যয় / ভাউচার এন্ট্রি</h3>
            <p>ব্যয়ের বিবরণ, খাত, ভেন্ডর ও পরিশোধের তথ্য নির্ধারণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showExpenseModal = false">×</button>
        </div>
        <form @submit.prevent="saveExpense" class="modal-form">
          <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">ব্যয়ের বিবরণ (বাংলা) *</label>
              <input v-model="expenseForm.description_bn" class="form-input" required placeholder="যেমন: মে মাসের বিদ্যুৎ বিল পরিশোধ" />
            </div>
            <div class="form-group">
              <label class="form-label">ব্যয়ের খাত / ক্যাটাগরি *</label>
              <select v-model="expenseForm.category" class="form-select" required>
                <option value="বেতন">শিক্ষক/কর্মী বেতন</option>
                <option value="ইউটিলিটি">বিদ্যুৎ / গ্যাস / পানি</option>
                <option value="মেরামত">মেরামত ও রক্ষণাবেক্ষণ</option>
                <option value="খাদ্য">হোস্টেল খাদ্য সামগ্রী</option>
                <option value="স্টেশনারি">বই ও স্টেশনারি</option>
                <option value="অন্যান্য">অন্যান্য প্রশাসনিক</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">পরিমাণ (টাকা) *</label>
              <input v-model.number="expenseForm.amount" type="number" min="1" class="form-input" required placeholder="৩০০০" />
            </div>
            <div class="form-group">
              <label class="form-label">প্রাপক / ভেন্ডরের নাম</label>
              <input v-model="expenseForm.payee_name" class="form-input" placeholder="যেমন: ডেসকো / স্থানীয় বিক্রেতা" />
            </div>
            <div class="form-group">
              <label class="form-label">পরিশোধ পদ্ধতি *</label>
              <select v-model="expenseForm.method" class="form-select" required>
                <option value="নগদ">নগদ (Cash)</option>
                <option value="ব্যাংক">ব্যাংক ট্রান্সফার</option>
                <option value="বিকাশ">বিকাশ / নগদ</option>
                <option value="চেক">চেক</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">লেনদেনের তারিখ *</label>
              <input v-model="expenseForm.transaction_date" type="date" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">ভাউচার নম্বর</label>
              <input v-model="expenseForm.voucher_number" class="form-input" placeholder="VOUCH-01" />
            </div>
            <div class="form-group wide">
              <label class="form-label">অতিরিক্ত নোট</label>
              <input v-model="expenseForm.notes" class="form-input" placeholder="অনুমোদনের রেফারেন্স বা নোট..." />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showExpenseModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'ব্যয় সংরক্ষণ করুন' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Donor Modal -->
    <div v-if="showDonorModal" class="modal-overlay" @click.self="showDonorModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>নতুন দাতা নিবন্ধন</h3>
            <p>দাতার নাম, মোবাইল নম্বর, প্রতিষ্ঠান ও যোগাযোগের ঠিকানা যোগ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showDonorModal = false">×</button>
        </div>
        <form @submit.prevent="saveDonor" class="modal-form">
          <div v-if="modalError" class="alert alert-error">{{ modalError }}</div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">দাতার নাম (বাংলা) *</label>
              <input v-model="donorForm.name_bn" class="form-input" required placeholder="দাতার পূর্ণ নাম" />
            </div>
            <div class="form-group">
              <label class="form-label">দাতার নাম (ইংরেজি)</label>
              <input v-model="donorForm.name_en" class="form-input" placeholder="Donor's English Name" />
            </div>
            <div class="form-group">
              <label class="form-label">মোবাইল নম্বর *</label>
              <input v-model="donorForm.phone" class="form-input" required placeholder="০১৭১১..." />
            </div>
            <div class="form-group">
              <label class="form-label">ইমেইল</label>
              <input v-model="donorForm.email" type="email" class="form-input" placeholder="donor@example.com" />
            </div>
            <div class="form-group">
              <label class="form-label">প্রতিষ্ঠান / পদবী</label>
              <input v-model="donorForm.organization" class="form-input" placeholder="কোম্পানি বা ব্যবসার নাম" />
            </div>
            <div class="form-group">
              <label class="form-label">রক্তের গ্রুপ</label>
              <select v-model="donorForm.blood_group" class="form-select">
                <option value="">নির্বাচন করুন</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
              </select>
            </div>
            <div class="form-group wide">
              <label class="form-label">ঠিকানা</label>
              <input v-model="donorForm.address_bn" class="form-input" placeholder="বাসা/অফিসের ঠিকানা..." />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showDonorModal = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'দাতা সংরক্ষণ করুন' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()

const activeTab = ref('funds')
const loading = ref(true)
const saving = ref(false)
const modalError = ref('')

const funds = ref<any>(null)
const donations = ref<any>(null)
const expenses = ref<any>(null)
const donors = ref<any>(null)
const stocks = ref<any>(null)
const summary = ref<any>(null)

// Filters
const fundSearch = ref('')
const fundTypeFilter = ref('')
const donationSearch = ref('')
const donationFundFilter = ref('')
const expenseSearch = ref('')
const expenseCategoryFilter = ref('')
const donorSearch = ref('')

// Tabs
const tabs = [
  { id: 'funds', label: 'ফান্ড সমূহ', icon: 'folder' },
  { id: 'donations', label: 'দান ও অনুদান', icon: 'heart' },
  { id: 'expenses', label: 'ব্যয় ও ভাউচার', icon: 'cash-minus' },
  { id: 'donors', label: 'দাতাবৃন্দ', icon: 'users' },
  { id: 'stocks', label: 'রিসিপ্ট স্টক', icon: 'document-text' },
]

const createButtonLabel = computed(() => {
  if (activeTab.value === 'funds') return 'নতুন ফান্ড'
  if (activeTab.value === 'donations') return 'নতুন দান'
  if (activeTab.value === 'expenses') return 'নতুন ব্যয়'
  if (activeTab.value === 'donors') return 'নতুন দাতা'
  return 'নতুন এন্ট্রি'
})

// Modals state
const showFundModal = ref(false)
const fundEditingId = ref<number | null>(null)
const showDonationModal = ref(false)
const showExpenseModal = ref(false)
const showDonorModal = ref(false)

const fundForm = reactive({
  name_bn: '',
  name_en: '',
  type: 'সাধারণ',
  target_amount: 0,
  description_bn: '',
  is_active: true,
})

const donationForm = reactive({
  donor_id: '',
  fund_id: '',
  amount: 0,
  method: 'নগদ',
  donation_date: new Date().toISOString().slice(0, 10),
  receipt_number: '',
  notes: '',
})

const expenseForm = reactive({
  description_bn: '',
  category: 'অন্যান্য',
  amount: 0,
  payee_name: '',
  method: 'নগদ',
  transaction_date: new Date().toISOString().slice(0, 10),
  voucher_number: '',
  notes: '',
})

const donorForm = reactive({
  name_bn: '',
  name_en: '',
  phone: '',
  email: '',
  organization: '',
  blood_group: '',
  address_bn: '',
})

// Loaders
async function loadFinance() {
  loading.value = true
  try {
    const [fundsRes, donationsRes, expensesRes, donorsRes, stocksRes, summaryRes] = await Promise.all([
      api.get('/finance/funds?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/finance/donations?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/finance/expenses?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/finance/donors?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/finance/stocks?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/finance/summary').catch(() => ({ data: { data: {} } })),
    ])

    funds.value = fundsRes.data
    donations.value = donationsRes.data
    expenses.value = expensesRes.data
    donors.value = donorsRes.data
    stocks.value = stocksRes.data
    summary.value = summaryRes.data?.data || {}
  } catch (error) {
    console.error('Failed to load finance data:', error)
  } finally {
    loading.value = false
  }
}

// Filtered Computed Lists
const fundsList = computed(() => funds.value?.data?.data || funds.value?.data || [])
const donationsList = computed(() => donations.value?.data?.data || donations.value?.data || [])
const expensesList = computed(() => expenses.value?.data?.data || expenses.value?.data || [])
const donorsList = computed(() => donors.value?.data?.data || donors.value?.data || [])
const stocksList = computed(() => stocks.value?.data?.data || stocks.value?.data || [])

const filteredFunds = computed(() => {
  return fundsList.value.filter((f: any) => {
    const matchesSearch = !fundSearch.value || (f.name_bn || '').toLowerCase().includes(fundSearch.value.toLowerCase()) || (f.name_en || '').toLowerCase().includes(fundSearch.value.toLowerCase())
    const matchesType = !fundTypeFilter.value || f.type === fundTypeFilter.value
    return matchesSearch && matchesType
  })
})

const filteredDonations = computed(() => {
  return donationsList.value.filter((d: any) => {
    const donorName = d.donor?.name_bn || d.donor?.name_en || ''
    const matchesSearch = !donationSearch.value || donorName.toLowerCase().includes(donationSearch.value.toLowerCase()) || (d.receipt_number || '').includes(donationSearch.value)
    const matchesFund = !donationFundFilter.value || String(d.fund_id) === String(donationFundFilter.value)
    return matchesSearch && matchesFund
  })
})

const filteredExpenses = computed(() => {
  return expensesList.value.filter((e: any) => {
    const desc = (e.description_bn || e.description || '') + ' ' + (e.payee_name || e.vendor?.name_bn || '')
    const matchesSearch = !expenseSearch.value || desc.toLowerCase().includes(expenseSearch.value.toLowerCase())
    const matchesCat = !expenseCategoryFilter.value || e.category === expenseCategoryFilter.value
    return matchesSearch && matchesCat
  })
})

const filteredDonors = computed(() => {
  return donorsList.value.filter((d: any) => {
    const term = (d.name_bn || '') + ' ' + (d.name_en || '') + ' ' + (d.phone || '') + ' ' + (d.organization || '')
    return !donorSearch.value || term.toLowerCase().includes(donorSearch.value.toLowerCase())
  })
})

// Modal Open Handlers
function openCreateModal() {
  if (activeTab.value === 'funds') openFundModal()
  else if (activeTab.value === 'donations') openDonationModal()
  else if (activeTab.value === 'expenses') openExpenseModal()
  else if (activeTab.value === 'donors') openDonorModal()
}

function openFundModal(fund: any = null) {
  modalError.value = ''
  if (fund) {
    fundEditingId.value = fund.id
    fundForm.name_bn = fund.name_bn || ''
    fundForm.name_en = fund.name_en || ''
    fundForm.type = fund.type || 'সাধারণ'
    fundForm.target_amount = Number(fund.target_amount) || 0
    fundForm.description_bn = fund.description_bn || ''
    fundForm.is_active = fund.is_active !== false
  } else {
    fundEditingId.value = null
    fundForm.name_bn = ''
    fundForm.name_en = ''
    fundForm.type = 'সাধারণ'
    fundForm.target_amount = 0
    fundForm.description_bn = ''
    fundForm.is_active = true
  }
  showFundModal.value = true
}

function openDonationModal() {
  modalError.value = ''
  donationForm.donor_id = donorsList.value[0]?.id || ''
  donationForm.fund_id = fundsList.value[0]?.id || ''
  donationForm.amount = 0
  donationForm.method = 'নগদ'
  donationForm.donation_date = new Date().toISOString().slice(0, 10)
  donationForm.receipt_number = ''
  donationForm.notes = ''
  showDonationModal.value = true
}

function openExpenseModal() {
  modalError.value = ''
  expenseForm.description_bn = ''
  expenseForm.category = 'অন্যান্য'
  expenseForm.amount = 0
  expenseForm.payee_name = ''
  expenseForm.method = 'নগদ'
  expenseForm.transaction_date = new Date().toISOString().slice(0, 10)
  expenseForm.voucher_number = ''
  expenseForm.notes = ''
  showExpenseModal.value = true
}

function openDonorModal() {
  modalError.value = ''
  donorForm.name_bn = ''
  donorForm.name_en = ''
  donorForm.phone = ''
  donorForm.email = ''
  donorForm.organization = ''
  donorForm.blood_group = ''
  donorForm.address_bn = ''
  showDonorModal.value = true
}

// Modal Save Handlers
async function saveFund() {
  saving.value = true
  modalError.value = ''
  try {
    if (fundEditingId.value) {
      await api.put(`/finance/funds/${fundEditingId.value}`, fundForm)
    } else {
      await api.post('/finance/funds', fundForm)
    }
    showFundModal.value = false
    await loadFinance()
  } catch (e: any) {
    modalError.value = e?.response?.data?.message || 'ফান্ড সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

async function saveDonation() {
  saving.value = true
  modalError.value = ''
  try {
    await api.post('/finance/donations', donationForm)
    showDonationModal.value = false
    await loadFinance()
  } catch (e: any) {
    modalError.value = e?.response?.data?.message || 'দান সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

async function saveExpense() {
  saving.value = true
  modalError.value = ''
  try {
    await api.post('/finance/expenses', expenseForm)
    showExpenseModal.value = false
    await loadFinance()
  } catch (e: any) {
    modalError.value = e?.response?.data?.message || 'ব্যয় সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

async function saveDonor() {
  saving.value = true
  modalError.value = ''
  try {
    await api.post('/finance/donors', donorForm)
    showDonorModal.value = false
    await loadFinance()
  } catch (e: any) {
    modalError.value = e?.response?.data?.message || 'দাতা সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

// Helpers
function getFundPercent(fund: any) {
  const target = Number(fund.target_amount) || 1
  const col = Number(fund.collected_amount) || 0
  return Math.min(100, Math.round((col / target) * 100))
}

function formatCurrency(val: any) {
  if (!val) return '০'
  const num = Number(val) || 0
  return num.toLocaleString('bn-BD')
}

function formatDate(dateStr: string) {
  if (!dateStr) return '—'
  try {
    return new Date(dateStr).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
  } catch {
    return dateStr
  }
}

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}

onMounted(loadFinance)
</script>

<style scoped>
.page-wrapper {
  max-width: 1320px;
  margin: 0 auto;
  padding: 1.75rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.75rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.eyebrow {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--color-primary);
  letter-spacing: 0.08em;
}

.header-title-block h1 {
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0.2rem 0 0.35rem;
  color: var(--color-text);
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
}

/* Tabs */
.report-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
  padding-bottom: 0.5rem;
  overflow-x: auto;
}

.report-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.65rem 1.15rem;
  border-radius: 10px;
  border: 1px solid transparent;
  background: transparent;
  color: var(--color-text-light);
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.report-tab-btn:hover {
  background: rgba(20, 80, 50, 0.04);
  color: var(--color-text);
}

.report-tab-btn.active {
  background: rgba(20, 80, 50, 0.09);
  color: var(--color-primary);
  border-color: rgba(20, 80, 50, 0.18);
  font-weight: 700;
}

/* Clear search */
.clear-search-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: var(--color-text-light);
  cursor: pointer;
  padding: 0 0.2rem;
}

.pagination-info {
  margin-left: auto;
  font-size: 0.85rem;
  color: var(--color-text-light);
}

.pagination-info .highlight {
  font-weight: 700;
  color: var(--color-primary);
}

/* Funds Grid */
.funds-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
  gap: 1.25rem;
}

.fund-card {
  padding: 1.35rem;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 14px;
}

.fund-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.fund-card-header {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin-bottom: 1.1rem;
  padding-bottom: 0.85rem;
  border-bottom: 1px solid var(--color-border-light);
}

.fund-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.fund-title-block {
  flex: 1;
  min-width: 0;
}

.fund-title-block h3 {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 0.2rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.fund-type-tag {
  display: inline-block;
  font-size: 0.75rem;
  color: var(--color-text-light);
}

.fund-amounts-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.amount-item {
  display: flex;
  flex-direction: column;
}

.amount-label {
  font-size: 0.76rem;
  color: var(--color-text-light);
}

.amount-val {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--color-text);
}

.amount-val.success {
  color: #15803d;
}

.fund-progress-wrap {
  position: relative;
  height: 8px;
  background: rgba(0, 0, 0, 0.06);
  border-radius: 4px;
  margin-bottom: 1.25rem;
}

.fund-progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #10b981 0%, #145032 100%);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-label {
  position: absolute;
  right: 0;
  top: 12px;
  font-size: 0.74rem;
  color: var(--color-text-light);
  font-weight: 600;
}

.card-footer-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 0.85rem;
  border-top: 1px solid var(--color-border-light);
}

.view-link {
  font-size: 0.84rem;
  font-weight: 600;
  color: var(--color-primary);
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  text-decoration: none;
}

.view-link:hover {
  text-decoration: underline;
}

/* Table Cards */
.table-card {
  border-radius: 14px;
  overflow: hidden;
}

.table-responsive {
  overflow-x: auto;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.user-avatar-initials {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #fff;
  font-size: 0.84rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sub-text {
  font-size: 0.76rem;
  color: var(--color-text-light);
}

.notes-text {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: inline-block;
  font-size: 0.82rem;
  color: var(--color-text-light);
}

.fund-tag {
  display: inline-block;
  padding: 0.15rem 0.55rem;
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
}

.type-tag {
  display: inline-block;
  padding: 0.15rem 0.55rem;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-outline {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  font-size: 0.75rem;
}

.text-success { color: #15803d; }
.text-danger { color: #dc2626; }
.mono-font { font-family: monospace; font-size: 0.84rem; }

.action-buttons {
  display: flex;
  gap: 0.35rem;
}

.action-btn {
  width: 30px;
  height: 30px;
  border-radius: 6px;
  border: 1px solid var(--color-border-light);
  background: var(--color-bg);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--color-text-light);
  transition: all 0.15s ease;
  text-decoration: none;
}

.action-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  color: var(--color-text);
  transform: translateY(-1px);
}

.btn {
  padding: 0.6rem 1.15rem;
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

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35);
}

.btn-outline {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-outline:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.btn-ghost {
  background: transparent;
  color: var(--color-text);
}

.btn-ghost:hover {
  background: rgba(0, 0, 0, 0.05);
}

/* Modals */
.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.custom-checkbox { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 500; cursor: pointer; }
.custom-checkbox input { accent-color: var(--color-primary); width: 16px; height: 16px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

.empty-icon-wrap {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 1rem;
}
.empty-state h3 { font-size: 1.2rem; margin: 0 0 0.35rem; color: var(--color-text); }
.empty-state p { font-size: 0.88rem; margin: 0 0 1.25rem; }
</style>
