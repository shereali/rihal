const fs = require('fs');

// Fix AppTopBar.vue
let vueContent = fs.readFileSync('c:/Users/shere/Desktop/Rihal/nuxt/app/components/layout/AppTopBar.vue', 'utf8');
let styleMatch = vueContent.match(/<style scoped>([\s\S]*?)<\/style>/);
if (styleMatch) {
  let styleContent = styleMatch[1];
  styleContent = styleContent.replace(/var\(--color-([a-zA-Z0-9-]+)\)/g, (match, p1) => {
    return `rgb(var(--color-${p1}))`;
  });
  // Since some might already be wrapped, let's fix any rgb(rgb(var(...))) just in case
  styleContent = styleContent.replace(/rgb\(rgb\(var\(--color-([a-zA-Z0-9-]+)\)\)\)/g, 'rgb(var(--color-$1))');
  styleContent = styleContent.replace(/rgba\(rgb\(var\(--color-([a-zA-Z0-9-]+)\)\)/g, 'rgba(var(--color-$1)');
  
  vueContent = vueContent.replace(styleMatch[1], styleContent);
  fs.writeFileSync('c:/Users/shere/Desktop/Rihal/nuxt/app/components/layout/AppTopBar.vue', vueContent);
}

// Fix main.css
let cssContent = fs.readFileSync('c:/Users/shere/Desktop/Rihal/nuxt/app/css/main.css', 'utf8');
const baseLayerReplacement = `@layer base {
  :root, [data-theme="light"] {
    --color-primary: 20 184 166;
    --color-primary-dark: 15 118 110;
    --color-primary-light: 45 212 191;
    --color-primary-50: 240 253 250;
    --color-primary-100: 204 251 241;
    --color-accent: 250 204 21;
    --color-surface: 255 255 255;
    --color-background: 248 250 252;
    --color-bg-muted: 241 245 249;
    --color-bg-card: 255 255 255;
    --color-text: 15 23 42;
    --color-text-muted: 100 116 139;
    --color-text-light: 148 163 184;
    --color-border: 226 232 240;
    --color-border-light: 241 245 249;
    --color-error: 239 68 68;
    --color-error-bg: 254 226 226;
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.4);
    --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
    --sidebar-width: 260px;
    --header-height: 70px;
    --transition-normal: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --color-bg: var(--color-background);
    --sidebar-bg: #0f172af2;
  }

  [data-theme="dark"] {
    --color-primary: 45 212 191;
    --color-primary-dark: 20 184 166;
    --color-primary-light: 94 234 212;
    --color-primary-50: 15 118 110;
    --color-primary-100: 17 94 89;
    --color-accent: 253 224 71;
    --color-surface: 30 41 59;
    --color-background: 15 23 42;
    --color-bg-muted: 30 41 59;
    --color-bg-card: 15 23 42;
    --color-text: 248 250 252;
    --color-text-muted: 148 163 184;
    --color-text-light: 100 116 139;
    --color-border: 51 65 85;
    --color-border-light: 30 41 59;
    --color-error: 248 113 113;
    --color-error-bg: 127 29 29;
    --glass-bg: rgba(30, 41, 59, 0.65);
    --glass-border: rgba(255, 255, 255, 0.05);
    --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
    --sidebar-bg: #0f172af2;
  }

  [data-theme="islamic"] {
    --color-primary: 20 80 50;
    --color-primary-dark: 15 60 38;
    --color-primary-light: 30 100 65;
    --color-primary-50: 236 245 238;
    --color-primary-100: 209 232 216;
    --color-accent: 218 165 32;
    --color-surface: 255 255 255;
    --color-background: 245 247 245;
    --color-bg-muted: 235 240 236;
    --color-bg-card: 255 255 255;
    --color-text: 20 40 30;
    --color-text-muted: 80 100 90;
    --color-text-light: 120 140 130;
    --color-border: 210 220 215;
    --color-border-light: 235 240 236;
    --color-error: 220 50 50;
    --color-error-bg: 250 230 230;
    --glass-bg: rgba(255, 255, 255, 0.75);
    --glass-border: rgba(20, 80, 50, 0.1);
    --glass-shadow: 0 8px 32px 0 rgba(20, 80, 50, 0.05);
    --sidebar-bg: #145032f2;
  }

  [data-theme="professional"] {
    --color-primary: 26 54 93;
    --color-primary-dark: 15 30 54;
    --color-primary-light: 40 80 130;
    --color-primary-50: 235 240 248;
    --color-primary-100: 210 225 240;
    --color-accent: 226 232 240;
    --color-surface: 255 255 255;
    --color-background: 248 250 252;
    --color-bg-muted: 241 245 249;
    --color-bg-card: 255 255 255;
    --color-text: 15 23 42;
    --color-text-muted: 71 85 105;
    --color-text-light: 100 116 139;
    --color-border: 203 213 225;
    --color-border-light: 226 232 240;
    --color-error: 185 28 28;
    --color-error-bg: 254 242 242;
    --glass-bg: rgba(255, 255, 255, 0.85);
    --glass-border: rgba(26, 54, 93, 0.1);
    --glass-shadow: 0 8px 32px 0 rgba(26, 54, 93, 0.05);
    --sidebar-bg: #1a365df2;
  }

  body {
    @apply bg-[rgb(var(--color-background))] text-[rgb(var(--color-text))] font-sans antialiased transition-colors duration-300;
    font-family: 'Outfit', 'Noto Sans Bengali', sans-serif;
    background-image: radial-gradient(at 0% 0%, rgb(var(--color-primary) / 0.05) 0px, transparent 50%),
                      radial-gradient(at 100% 100%, rgb(var(--color-accent) / 0.03) 0px, transparent 50%);
    background-attachment: fixed;
  }
}`;
cssContent = cssContent.replace(/@layer base \{[\s\S]*?body \{[\s\S]*?\}\n\}/, baseLayerReplacement);
fs.writeFileSync('c:/Users/shere/Desktop/Rihal/nuxt/app/css/main.css', cssContent);

console.log('Success');
