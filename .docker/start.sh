#!/bin/sh
set -e

# ─── Start PHP-FPM ───────────────────────────────────────────────────────────
php-fpm &
sleep 2

# ─── Buat direktori storage framework ────────────────────────────────────────
mkdir -p /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/cache \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/logs
chmod -R 777 /var/www/html/storage
chown -R www-data:www-data /var/www/html/storage

# ─── Tulis .env dari Environment Variables Railway ───────────────────────────
cat > /var/www/html/.env << 'ENVEOF'
APP_NAME="PLACEHOLDER_APP_NAME"
APP_ENV="PLACEHOLDER_APP_ENV"
APP_KEY="PLACEHOLDER_APP_KEY"
APP_DEBUG="PLACEHOLDER_APP_DEBUG"
APP_URL="PLACEHOLDER_APP_URL"

LOG_CHANNEL=stderr
LOG_LEVEL=PLACEHOLDER_LOG_LEVEL

DB_CONNECTION="PLACEHOLDER_DB_CONNECTION"
DB_HOST="PLACEHOLDER_DB_HOST"
DB_PORT="PLACEHOLDER_DB_PORT"
DB_DATABASE="PLACEHOLDER_DB_DATABASE"
DB_USERNAME="PLACEHOLDER_DB_USERNAME"
DB_PASSWORD="PLACEHOLDER_DB_PASSWORD"

CACHE_DRIVER=PLACEHOLDER_CACHE_DRIVER
FILESYSTEM_DISK=PLACEHOLDER_FILESYSTEM_DISK
QUEUE_CONNECTION=PLACEHOLDER_QUEUE_CONNECTION

SESSION_DRIVER=PLACEHOLDER_SESSION_DRIVER
SESSION_LIFETIME=PLACEHOLDER_SESSION_LIFETIME

# Cloudflare R2 (S3-compatible)
AWS_ACCESS_KEY_ID="PLACEHOLDER_AWS_ACCESS_KEY_ID"
AWS_SECRET_ACCESS_KEY="PLACEHOLDER_AWS_SECRET_ACCESS_KEY"
AWS_DEFAULT_REGION="PLACEHOLDER_AWS_DEFAULT_REGION"
AWS_BUCKET="PLACEHOLDER_AWS_BUCKET"
AWS_ENDPOINT="PLACEHOLDER_AWS_ENDPOINT"
AWS_URL="PLACEHOLDER_AWS_URL"
AWS_USE_PATH_STYLE_ENDPOINT="PLACEHOLDER_AWS_USE_PATH_STYLE_ENDPOINT"
STORAGE_PROXY_MEDIA=PLACEHOLDER_STORAGE_PROXY_MEDIA
ENVEOF

# Ganti semua placeholder dengan nilai env var yang sebenarnya
sed -i "s|PLACEHOLDER_APP_NAME|${APP_NAME:-Apipi Coffee}|g"     /var/www/html/.env
sed -i "s|PLACEHOLDER_APP_ENV|${APP_ENV:-production}|g"         /var/www/html/.env
sed -i "s|PLACEHOLDER_APP_KEY|${APP_KEY}|g"                     /var/www/html/.env
sed -i "s|PLACEHOLDER_APP_DEBUG|${APP_DEBUG:-false}|g"          /var/www/html/.env
sed -i "s|PLACEHOLDER_APP_URL|${APP_URL:-https://localhost}|g"  /var/www/html/.env
sed -i "s|PLACEHOLDER_LOG_LEVEL|${LOG_LEVEL:-error}|g"          /var/www/html/.env
sed -i "s|PLACEHOLDER_DB_CONNECTION|${DB_CONNECTION:-mysql}|g"  /var/www/html/.env
sed -i "s|PLACEHOLDER_DB_HOST|${DB_HOST:-127.0.0.1}|g"         /var/www/html/.env
sed -i "s|PLACEHOLDER_DB_PORT|${DB_PORT:-3306}|g"               /var/www/html/.env
sed -i "s|PLACEHOLDER_DB_DATABASE|${DB_DATABASE}|g"             /var/www/html/.env
sed -i "s|PLACEHOLDER_DB_USERNAME|${DB_USERNAME}|g"             /var/www/html/.env
sed -i "s|PLACEHOLDER_DB_PASSWORD|${DB_PASSWORD}|g"             /var/www/html/.env
sed -i "s|PLACEHOLDER_CACHE_DRIVER|${CACHE_DRIVER:-file}|g"     /var/www/html/.env
if [ -n "${AWS_BUCKET}" ]; then
  _FS_DISK="${FILESYSTEM_DISK:-s3}"
else
  _FS_DISK="${FILESYSTEM_DISK:-local}"
  echo "FILESYSTEM_DISK: local (AWS_BUCKET kosong, gunakan local sampai R2 dikonfigurasi)"
fi
sed -i "s|PLACEHOLDER_FILESYSTEM_DISK|${_FS_DISK}|g" /var/www/html/.env
sed -i "s|PLACEHOLDER_QUEUE_CONNECTION|${QUEUE_CONNECTION:-sync}|g" /var/www/html/.env
sed -i "s|PLACEHOLDER_SESSION_DRIVER|${SESSION_DRIVER:-file}|g" /var/www/html/.env
sed -i "s|PLACEHOLDER_SESSION_LIFETIME|${SESSION_LIFETIME:-120}|g" /var/www/html/.env
sed -i "s|PLACEHOLDER_AWS_ACCESS_KEY_ID|${AWS_ACCESS_KEY_ID}|g"                     /var/www/html/.env
sed -i "s|PLACEHOLDER_AWS_SECRET_ACCESS_KEY|${AWS_SECRET_ACCESS_KEY}|g"             /var/www/html/.env
sed -i "s|PLACEHOLDER_AWS_DEFAULT_REGION|${AWS_DEFAULT_REGION:-auto}|g"             /var/www/html/.env
sed -i "s|PLACEHOLDER_AWS_BUCKET|${AWS_BUCKET}|g"                                   /var/www/html/.env
sed -i "s|PLACEHOLDER_AWS_ENDPOINT|${AWS_ENDPOINT}|g"                               /var/www/html/.env
sed -i "s|PLACEHOLDER_AWS_URL|${AWS_URL}|g"                                         /var/www/html/.env
sed -i "s|PLACEHOLDER_AWS_USE_PATH_STYLE_ENDPOINT|${AWS_USE_PATH_STYLE_ENDPOINT:-true}|g" /var/www/html/.env
sed -i "s|PLACEHOLDER_STORAGE_PROXY_MEDIA|${STORAGE_PROXY_MEDIA:-true}|g" /var/www/html/.env

echo "✅ .env berhasil ditulis"
cat /var/www/html/.env | grep DB_HOST

if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = '""' ] || [ "${APP_KEY}" = "base64:" ]; then
  echo "FATAL: APP_KEY tidak diset di Railway Variables." >&2
  echo "Generate: php artisan key:generate --show" >&2
  echo "Lalu tambahkan APP_KEY di Railway → service → Variables → redeploy." >&2
  exit 1
fi
echo "APP_KEY set: yes"

if [ -n "${AWS_BUCKET}" ] && [ -z "${AWS_URL}" ]; then
  echo "WARNING: AWS_BUCKET diset tapi AWS_URL kosong — gambar tidak bisa dimuat di browser." >&2
  echo "         Set AWS_URL ke URL publik R2 (pub-xxx.r2.dev atau custom domain)." >&2
fi

# ─── Laravel bootstrap ────────────────────────────────────────────────────────
cd /var/www/html
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true
php artisan filament:cache-components 2>&1 || true

# ─── Port: Railway inject $PORT, default 8080 ─────────────────────────────────
APP_PORT="${PORT:-8080}"
echo "🚀 Menjalankan Nginx di port $APP_PORT..."

# Update nginx config dengan port yang benar
sed -i "s|listen 8080;|listen ${APP_PORT};|g" /etc/nginx/conf.d/default.conf

nginx -g "daemon off;" 2>&1 &
NGINX_PID=$!
sleep 3

# ─── Migrate ──────────────────────────────────────────────────────────────────
echo "🔄 Menjalankan migrate..."
php artisan migrate --force --no-interaction 2>&1 \
  && echo "✅ Migrate sukses." \
  || echo "⚠️  Migrate gagal — server tetap jalan."

echo "✅ Aplikasi siap di port ${APP_PORT}."
wait $NGINX_PID