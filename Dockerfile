FROM php:8.2-fpm

# system deps
RUN apt-get update && apt-get install -y \
    git unzip zip curl \
    libpng-dev libjpeg-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# copy source
COPY . .

# 🚨 CỰC KỲ QUAN TRỌNG
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

RUN chmod -R 775 storage bootstrap/cache
