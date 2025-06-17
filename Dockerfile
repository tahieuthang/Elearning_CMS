# Sử dụng PHP 8.2 image chính thức
FROM php:8.2-fpm

# Cài đặt các tiện ích hệ thống
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    libpq-dev \
    libmcrypt-dev \
    libssl-dev \
    nano \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Tạo thư mục làm việc
WORKDIR /var/www

# Sao chép mã nguồn
COPY . .

# Cài đặt thư viện Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Phân quyền cho thư mục storage và bootstrap
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Cổng mặc định Laravel
EXPOSE 8000

# Lệnh chạy app
CMD php artisan serve --host=0.0.0.0 --port=8000
