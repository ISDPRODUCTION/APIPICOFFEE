#!/bin/sh

# ─── Start PHP-FPM ───────────────────────────────────────────────────────────
php-fpm &
sleep 2

# ─── Buat direktori storage ──────────────────────────────────────────────────
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chmod -R 777 /var/www/html/storage
chown -R www-data:www-data /var/www/html/storage

# ─── Tulis .env dari Environment Variables Google Cloud Run ──────────────────
cat > /var/www/html/.env << EOF
APP_NAME="${APP_NAME:-apipi_pos}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-https://localhost}"

LOG_CHANNEL=stderr
LOG_LEVEL=${LOG_LEVEL:-error}

# ── Database ──
# Jika DB_SOCKET di-set (Cloud SQL Unix Socket), gunakan localhost sebagai host
DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_SOCKET:+localhost}${DB_SOCKET:-${DB_HOST:-127.0.0.1}}"
DB_SOCKET="${DB_SOCKET}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE}"
DB_USERNAME="${DB_USERNAME}"
DB_PASSWORD="${DB_PASSWORD}"

CACHE_DRIVER=${CACHE_DRIVER:-file}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}

SESSION_DRIVER=${SESSION_DRIVER:-cookie}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none

AWS_ACCESS_KEY_ID="${AWS_ACCESS_KEY_ID}"
AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY}"
AWS_DEFAULT_REGION="${AWS_DEFAULT_REGION:-us-east-1}"
AWS_BUCKET="${AWS_BUCKET}"
AWS_ENDPOINT="${AWS_ENDPOINT}"
AWS_URL="${AWS_URL}"
AWS_USE_PATH_STYLE_ENDPOINT="${AWS_USE_PATH_STYLE_ENDPOINT:-false}"
EOF

# ─── Laravel bootstrap ───────────────────────────────────────────────────────
cd /var/www/html

php artisan config:clear
php artisan cache:clear

# ─── Start Nginx DULU agar Cloud Run bisa detect port 8080 ───────────────────
echo "🚀 Menjalankan Nginx di port 8080..."
nginx -g "daemon off;" 2>&1 &
NGINX_PID=$!

# Tunggu Nginx siap (max 5 detik)
sleep 3

# ─── Jalankan migrate di background (non-blocking) ───────────────────────────
echo "🔄 Menjalankan database migration..."
php artisan migrate --force --no-interaction 2>&1 || echo "⚠️  Migration gagal, tapi server tetap jalan."

echo "✅ Aplikasi siap."

# Tunggu Nginx (keep container hidup)
wait $NGINX_PID