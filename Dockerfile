# Dockerfile
FROM php:8.2-fpm

# Cài extension
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip curl git \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install && chmod -R 775 storage bootstrap/cache
RUN chmod +x start-queue.sh
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memlimit.ini