# ===== Stage 1: build vendor =====
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ===== Stage 2: php runtime =====
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libonig-dev libxml2-dev \
    zip unzip curl git \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd

WORKDIR /var/www

# Copy source
COPY . .

# Copy vendor từ stage 1
COPY --from=vendor /app/vendor /var/www/vendor

RUN chmod -R 775 storage bootstrap/cache \
 && chmod +x start-queue.sh

CMD ["php-fpm"]
