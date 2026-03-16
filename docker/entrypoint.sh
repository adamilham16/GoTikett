#!/bin/sh
set -e

echo "========================================="
echo "  GoTiket — Laravel Docker Setup"
echo "========================================="

cd /var/www/html

# ── 1. Generate APP_KEY jika belum ada ────────────────────────────────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo ">>> Generating APP_KEY..."
    php artisan key:generate --force
else
    echo ">>> APP_KEY sudah ada, skip."
fi

# ── 2. Tunggu MySQL siap ──────────────────────────────────────────────────
echo ">>> Menunggu koneksi MySQL..."
MAX_TRIES=30
COUNT=0
until php -r "
    \$pdo = new PDO(
        'mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-gotiket}',
        '${DB_USERNAME:-gotiket_user}',
        '${DB_PASSWORD:-gotiket_pass}'
    );
    echo 'OK';
" 2>/dev/null; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo "ERROR: MySQL tidak bisa diakses setelah $MAX_TRIES percobaan."
        exit 1
    fi
    echo "  MySQL belum siap, coba lagi dalam 3 detik... ($COUNT/$MAX_TRIES)"
    sleep 3
done
echo ">>> MySQL siap!"

# ── 3. Jalankan migration ─────────────────────────────────────────────────
echo ">>> Menjalankan migration..."
php artisan migrate --force

# ── 4. Jalankan seeder (hanya jika tabel users kosong) ────────────────────
USER_COUNT=$(php -r "
    \$pdo = new PDO(
        'mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-gotiket}',
        '${DB_USERNAME:-gotiket_user}',
        '${DB_PASSWORD:-gotiket_pass}'
    );
    echo \$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
" 2>/dev/null || echo "0")

if [ "$USER_COUNT" = "0" ]; then
    echo ">>> Menjalankan seeder (data awal)..."
    php artisan db:seed --force
    echo ">>> Seeder selesai! Akun demo sudah dibuat."
else
    echo ">>> Data sudah ada ($USER_COUNT users), skip seeder."
fi

# ── 5. Cache config & routes untuk production ─────────────────────────────
if [ "$APP_ENV" = "production" ]; then
    echo ">>> Production mode: caching config, routes, views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo ">>> Development mode: clear cache..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

# ── 6. Storage link ───────────────────────────────────────────────────────
echo ">>> Membuat storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# ── 7. Fix permissions ────────────────────────────────────────────────────
chown -R www-data:www-data storage/ bootstrap/cache/
chmod -R 775 storage/ bootstrap/cache/

echo "========================================="
echo "  Setup selesai! GoTiket siap diakses."
echo "  URL: ${APP_URL:-http://localhost:8000}"
echo "========================================="

# ── Jalankan supervisord (Nginx + PHP-FPM) ────────────────────────────────
exec /usr/bin/supervisord -c /etc/supervisord.conf
