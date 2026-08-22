#!/bin/bash
cd /c/Users/shere/Desktop/SabaaqNext/nuxt
export NUXT_IGNORE_LOCK=1
npx nuxt dev --port 3000 > /tmp/nuxt-3000-final.log 2>&1
echo "Nuxt dev server exited with code $?"
