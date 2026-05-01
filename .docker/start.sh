#!/bin/sh

echo "=== START CONTAINER ==="

# ─── 1. Setup storage (cepat, tidak butuh DB) ─────────────────────────────────
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chmod -R 777 /var/www/html/storage
chown -R www-data:www-data /var/www/html/storage

# ─── 2. Tulis .env dari env vars Cloud Run ────────────────────────────────────
cat > /var/www/html/.env << 'ENVEOF'
APP_NAME=apipi_pos
APP_ENV=production
APP_DEBUG=false
ENVEOF

# Append nilai dari environment variable (hindari masalah dengan karakter khusus)
echo "APP_KEY=${APP_KEY}" >> /var/www/html/.env
echo "APP_URL=${APP_URL:-https://localhost}" >> /var/www/html/.env
echo "LOG_CHANNEL=stderr" >> /var/www/html/.env
echo "LOG_LEVEL=${LOG_LEVEL:-error}" >> /var/www/html/.env
echo "DB_CONNECTION=${DB_CONNECTION:-mysql}" >> /var/www/html/.env
echo "DB_HOST=${DB_HOST:-127.0.0.1}" >> /var/www/html/.env
echo "DB_PORT=${DB_PORT:-3306}" >> /var/www/html/.env
echo "DB_DATABASE=${DB_DATABASE}" >> /var/www/html/.env
echo "DB_USERNAME=${DB_USERNAME}" >> /var/www/html/.env
echo "DB_PASSWORD=${DB_PASSWORD}" >> /var/www/html/.env
echo "CACHE_DRIVER=${CACHE_DRIVER:-file}" >> /var/www/html/.env
echo "FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}" >> /var/www/html/.env
echo "QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}" >> /var/www/html/.env
echo "SESSION_DRIVER=${SESSION_DRIVER:-cookie}" >> /var/www/html/.env
echo "SESSION_LIFETIME=${SESSION_LIFETIME:-120}" >> /var/www/html/.env
echo "SESSION_SECURE_COOKIE=true" >> /var/www/html/.env
echo "SESSION_SAME_SITE=none" >> /var/www/html/.env

# ─── 3. Start PHP-FPM ─────────────────────────────────────────────────────────
echo "Starting PHP-FPM..."
php-fpm &
sleep 3

# ─── 4. Clear cache Laravel ───────────────────────────────────────────────────
cd /var/www/html
php artisan config:clear 2>&1 || true
php artisan cache:clear 2>&1 || true

# ─── 5. Migrate di background ─────────────────────────────────────────────────
(
  for i in 1 2 3 4 5; do
    php artisan migrate --force 2>&1 && echo "Migration OK" && break
    echo "Migrate gagal $i/5, retry..."
    sleep 5
  done
) &

# ─── 6. Test nginx config sebelum start ───────────────────────────────────────
echo "Testing nginx config..."
nginx -t 2>&1

# ─── 7. Start Nginx ────────────────────────────────────────────────────────────
echo "Starting nginx on port 8080..."
nginx -g "daemon off;"