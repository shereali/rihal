const fs = require('fs');
const path = 'c:/Users/shere/Desktop/Rihal/nuxt/app/pages/dashboard.vue';
let content = fs.readFileSync(path, 'utf8');

// 1. Replace KPIs
const kpiBlock = `<section class="kpi-grid">
      <article v-for="stat in kpis" :key="stat.label" class="kpi-card" :class="\`tone-\${stat.tone}\`" @click="stat.onClick">
        <div class="kpi-topline">
          <span class="kpi-icon"><icon :name="stat.icon" /></span>
          <span class="kpi-trend" v-if="stat.trend">{{ stat.trend }}</span>
        </div>
        <div class="kpi-value">{{ stat.value }}</div>
        <div class="kpi-label">{{ stat.label }}</div>
        <div class="kpi-shine" />
      </article>
    </section>`;
content = content.replace(kpiBlock, '<DashboardKPIs :kpis="kpis" />');

// 2. Replace License Modal (using regex for flexibility)
content = content.replace(/<!-- License Modal -->[\s\S]*?<\/Teleport>/, '<DashboardLicenseModal :isOpen="licenseOpen" :license="license" @close="licenseOpen = false" />');

// 3. Replace duplicate class-panel with Activity Feed
content = content.replace(/<article class="panel class-panel">.*?শ্রেণিভিত্তিক শিক্ষার্থী.*?<\/article>\s*<\/section>/, '<DashboardActivityFeed />\n    </section>');

// 4. Add imports
content = content.replace(`import { computed, onMounted, ref } from 'vue'`, `import { computed, onMounted, ref } from 'vue'\nimport DashboardKPIs from '~/components/dashboard/DashboardKPIs.vue'\nimport DashboardLicenseModal from '~/components/dashboard/DashboardLicenseModal.vue'\nimport DashboardActivityFeed from '~/components/dashboard/DashboardActivityFeed.vue'`);

// 5. Remove extracted computed properties related to license (since they are now in DashboardLicenseModal)
const licenseRegex = /const licenseStatusClass = computed\(\(\) => \{[\s\S]*?const licenseWhatsapp = computed\(\(\) => 'https:\/\/wa\.me\/8801XXXXXXXXXX'\)/;
content = content.replace(licenseRegex, '');

// 6. Clean up KPI styles
content = content.replace(/\.kpi-grid \{.*?\}/, '');
content = content.replace(/\.kpi-card \{.*?\}/, '');
content = content.replace(/\.tone-green.*?\.tone-gold \{.*?\}/, '');
content = content.replace(/\.kpi-topline.*?\.kpi-shine \{.*?\}/, '');

// 7. Add stagger animations to other panels
content = content.replace(/class="panel/g, 'class="panel slide-up-fade');

fs.writeFileSync(path, content, 'utf8');
console.log('dashboard.vue updated successfully.');
