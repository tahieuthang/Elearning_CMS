# 🎓 E-Learning CMS Admin – Laravel 11

Đây là mã nguồn back-end quản lý nội dung (CMS) cho một nền tảng học trực tuyến. Dự án sử dụng Laravel 11, cho phép quản trị viên thêm/sửa/xoá khóa học, bài học và thực hiện upload video lên Vimeo thông qua hàng đợi (`Queue Job`).

## 🚀 Công nghệ sử dụng

- Laravel 11 (PHP 8.2)
- MySQL
- Vimeo API
- VNPAY
- Laravel Queue Job
  
## ⚙️ Cài đặt local (máy cá nhân)

```bash
# Clone repo
git clone https://github.com/Kamadee/Elearning_CMS
cd Elearning_CMS

# Cài đặt các package PHP
composer install

# Tạo file .env
cp .env.example .env

# Tạo APP_KEY
php artisan key:generate

# Tạo database + chạy migration
php artisan migrate

# Khởi chạy project
php artisan serve
