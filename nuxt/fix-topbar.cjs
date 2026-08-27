const fs = require('fs');
const path = 'c:/Users/shere/Desktop/Rihal/nuxt/app/components/layout/AppTopBar.vue';
let content = fs.readFileSync(path, 'utf8');

// The regex matches rgb(var(--some-color-var)) and captures the var(...) part.
content = content.replace(/rgb\((var\(--color-[a-zA-Z0-9-]+\))\)/g, '$1');

fs.writeFileSync(path, content, 'utf8');
console.log('AppTopBar.vue updated successfully.');
