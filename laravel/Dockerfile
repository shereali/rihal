# Laravel 11 API — PHP 8.3 FPM + Composer
FROM php:8.3-fpm

# System deps
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
        libfreetype6-dev default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions required by Laravel 11
RUN docker-php-ext-configure gd --with-freetype \
    && docker-php-ext-install pdo_mysql mbstring bcmath xml zip gd pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies first (better layer caching)
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-dev --optimize-autoloader || true

# Copy application source
COPY . .

# Generate key + permissions at build (overridden at runtime if .env present)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
