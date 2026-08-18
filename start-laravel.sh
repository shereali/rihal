#!/bin/sh
##########################################################################
#  Laravel startscript by 'laravel.com' 
#  This script is a wrapper for 'php artisan serve --host=0.0.0.0 --port=8000'
##########################################################################

# Ensure PHP is available
if ! command -v php > /dev/null 2>&1; then
    echo "Error: PHP is not installed or not on PATH."
    echo "Install PHP 8.2+ and composer from https://php.net and https://getcomposer.org"
    exit 1
fi

# Change to Laravel directory
cd "$(dirname "$0")/laravel" || exit 1

# Install dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    composer install --no-interaction --no-dev --optimize-autoloader 2>/dev/null || \
    composer install --no-interaction --optimize-autoloader
fi

# Generate app key if missing
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

if [ -z "$(grep APP_KEY .env)" ] || [ "$(grep APP_KEY .env | cut -d= -f2)" = "" ]; then
    php artisan key:generate --force
fi

# Run migrations if needed
php artisan migrate --force 2>/dev/null || true

# Start Laravel server
echo "Starting Sabaaq Next Laravel API on http://localhost:8000"
php artisan serve --host=0.0.0.0 --port=8000
