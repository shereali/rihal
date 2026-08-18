#!/bin/sh
#
# Sabaaq Next — Start Frontend
#

cd "$(dirname "$0")/nuxt" || exit 1

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ] || [ ! -f "package-lock.json" ]; then
    echo "Installing npm dependencies..."
    npm install --legacy-peer-deps
fi

# Start Nuxt dev server
echo "Starting Sabaaq Next Nuxt frontend on http://localhost:3000"
npm run dev
