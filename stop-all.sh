#!/bin/sh
# Sabaaq Next — Stop All Services

echo "Stopping Sabaaq Next services..."

# Kill Laravel
pkill -f "artisan serve" 2>/dev/null || true
echo "  Laravel stopped."

# Kill Nuxt
pkill -f "nuxt dev" 2>/dev/null || true
echo "  Nuxt stopped."

echo "All services stopped."
