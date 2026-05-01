FROM php:8.3-fpm

# Install dependensi sistem + ekstensi PHP
RUN apt-get update && apt-get install -y \
    nginx \
    gettext-base \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy semua file project
COPY . .

# Install dependencies PHP (tanpa dev, optimized untuk production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Set permission storage
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy konfigurasi nginx dan startup script
COPY .docker/nginx.conf /etc/nginx/sites-available/default
COPY .docker/start.sh /start.sh
RUN chmod +x /start.sh

# Cloud Run membutuhkan port 8080
EXPOSE 8080

CMD ["/start.sh"]