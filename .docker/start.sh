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
# Jika pakai Cloud SQL Unix Socket, DB_HOST diisi dengan path socket:
# /cloudsql/PROJECT_ID:REGION:INSTANCE_NAME
# Jika pakai Cloud SQL Public IP, isi dengan IP langsung
DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
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

# Jalankan migrate otomatis dengan retry
echo "⏳ Menjalankan database migration..."
for i in 1 2 3 4 5; do
    php artisan migrate --force && break
    echo "⚠️  Migrate gagal (percobaan $i/5), coba lagi dalam 5 detik..."
    sleep 5
done

# ─── Start Nginx ─────────────────────────────────────────────────────────────
echo "✅ Aplikasi siap. Menjalankan Nginx..."
nginx -g "daemon off;" 2>&1