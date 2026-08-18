# syntax=docker/dockerfile:1.7
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git zip unzip curl nodejs npm \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./

RUN --mount=type=secret,id=composer_auth \
    COMPOSER_AUTH="$(cat /run/secrets/composer_auth)" \
    composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader

COPY package.json pnpm-lock.yaml ./

RUN npm install --global pnpm@9 \
 && pnpm install --frozen-lockfile

COPY . .

RUN composer dump-autoload --no-dev --optimize --no-interaction \
 && pnpm run build \
 && mkdir -p public/uploads \
 && chown -R www-data:www-data storage bootstrap/cache public/uploads \
 && chmod -R 775 storage bootstrap/cache public/uploads

USER www-data
