# ═══════════════════════════════════════════════════════════════
#  STAGE 1 — Builder (Laravel saja, tanpa PhpSpreadsheet)
#  Menggunakan composer:2.7 untuk download Laravel 12
# ═══════════════════════════════════════════════════════════════
FROM composer:2.7 AS builder

WORKDIR /app

# Download Laravel 12 fresh
RUN composer create-project laravel/laravel:^12.0 . --prefer-dist --no-interaction

# Copy file GoTiket — timpa file default Laravel
COPY app/       app/
COPY bootstrap/ bootstrap/
COPY database/  database/
COPY resources/ resources/
COPY routes/    routes/

# Hapus migration default Laravel yang bentrok dengan migration GoTiket
RUN rm -f database/migrations/0001_01_01_000000_create_users_table.php \
          database/migrations/0001_01_01_000001_create_cache_table.php \
          database/migrations/0001_01_01_000002_create_jobs_table.php

# Optimasi autoloader
RUN composer dump-autoload --optimize


# ═══════════════════════════════════════════════════════════════
#  STAGE 2 — Runtime
#  PHP 8.2 sudah punya ext-gd, baru install PhpSpreadsheet
# ═══════════════════════════════════════════════════════════════
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev

# Install PHP extensions (termasuk gd yang dibutuhkan PhpSpreadsheet)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        exif \
        opcache \
        intl \
        bcmath

# Copy Composer binary dari image resminya
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy hasil Stage 1 (Laravel + file GoTiket + vendor awal)
COPY --from=builder /app /var/www/html

# Install PhpSpreadsheet di sini — ext-gd sudah tersedia
RUN composer require phpoffice/phpspreadsheet --no-interaction --no-progress \
    && composer dump-autoload --optimize \
    && rm /usr/bin/composer

# Copy konfigurasi Docker
COPY docker/nginx/default.conf          /etc/nginx/http.d/default.conf
COPY docker/php/php.ini                 /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf                /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh               /entrypoint.sh

RUN chmod +x /entrypoint.sh

# Buat direktori storage
RUN mkdir -p storage/app/attachments \
             storage/app/public \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
    && chown -R www-data:www-data storage/ bootstrap/cache/ \
    && chmod -R 775 storage/ bootstrap/cache/

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
