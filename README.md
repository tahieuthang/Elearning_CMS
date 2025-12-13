# 🎓 E-Learning CMS - Backend System

> Hệ thống quản lý nội dung học trực tuyến (CMS) được xây dựng bằng Laravel 11, cung cấp RESTful API và Admin Panel để quản lý khóa học, bài học, người dùng và thanh toán.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-20.10+-2496ED?style=flat&logo=docker&logoColor=white)](https://www.docker.com/)

---

## 📋 Mục lục

- [Giới thiệu](#-giới-thiệu)
- [Tính năng](#-tính-năng)
- [Công nghệ sử dụng](#-công-nghệ-sử-dụng)
- [Cấu trúc dự án](#-cấu-trúc-dự-án)
- [Cài đặt và Chạy](#-cài-đặt-và-chạy)
- [API Documentation](#-api-documentation)
- [Kiến trúc](#-kiến-trúc)
- [Dự định cải tiến](#-dự-định-cải-tiến)
- [Tài liệu tham khảo](#-tài-liệu-tham-khảo)

---

## 🎯 Giới thiệu

**E-Learning CMS** là một hệ thống backend hoàn chỉnh cho nền tảng học trực tuyến, bao gồm:

- **CMS Admin Panel**: Giao diện web quản trị để quản lý nội dung, khóa học, người dùng
- **RESTful API**: API đầy đủ cho frontend/mobile app với JWT authentication
- **Payment Integration**: Tích hợp VNPAY cho thanh toán trực tuyến
- **Video Management**: Upload và quản lý video qua Vimeo API với Queue Jobs

Dự án được xây dựng theo kiến trúc **MVC** và **Service Layer Pattern**, đảm bảo code dễ maintain và mở rộng.

---

## ✨ Tính năng

### 🔐 Authentication & Authorization
- ✅ Multi-guard authentication (Admin session-based, Customer JWT-based)
- ✅ Role-based access control (RBAC) với Spatie Permission
- ✅ Email verification cho customer
- ✅ Password reset functionality
- ✅ JWT token authentication cho API

### 📚 Course Management
- ✅ CRUD operations cho khóa học
- ✅ Quản lý categories và tags
- ✅ Upload thumbnail và banner
- ✅ Rich text editor (CKEditor) cho nội dung
- ✅ Quản lý video episodes (drag & drop sắp xếp)
- ✅ Upload video lên Vimeo qua Queue Jobs
- ✅ Course status management (active/private)

### 👥 User Management
- ✅ Quản lý admin users với permissions
- ✅ Quản lý customers
- ✅ Customer profile management
- ✅ Customer achievements và statistics

### 🛒 E-Commerce Features
- ✅ Shopping cart functionality
- ✅ Order management
- ✅ Payment integration (VNPAY)
- ✅ Transaction history
- ✅ Order status tracking

### 📝 Content Management
- ✅ Blog/Post management
- ✅ Category và tag system
- ✅ Hot content management
- ✅ Review và rating system

### 📊 Admin Dashboard
- ✅ Statistics và analytics
- ✅ DataTables với server-side processing
- ✅ Advanced search và filtering
- ✅ Export data (PDF generation)

### 🎥 Video Management
- ✅ Vimeo API integration
- ✅ Background video upload (Queue Jobs)
- ✅ Video metadata management
- ✅ Video preview và thumbnail

---

## 🚀 Công nghệ sử dụng

### Backend Framework & Core
- **Laravel 11** - PHP Framework
- **PHP 8.2** - Programming Language
- **MySQL 8.0** - Database
- **Nginx** - Web Server

### Laravel Packages
- **tymon/jwt-auth** - JWT Authentication cho API
- **spatie/laravel-permission** - Role & Permission management
- **yajra/laravel-datatables** - Server-side DataTables
- **barryvdh/laravel-dompdf** - PDF generation
- **vimeo/laravel** - Vimeo API integration
- **laravel/sanctum** - API token authentication

### Frontend Technologies (Admin Panel)
- **jQuery 3.7** - JavaScript library
- **jQuery Validation** - Form validation
- **DataTables** - Advanced tables
- **Select2** - Advanced select boxes
- **Bootstrap FileInput** - File upload widget
- **CKEditor** - Rich text editor
- **SweetAlert2** - Beautiful alerts
- **jQuery UI** - UI interactions
- **AdminLTE 3** - Admin dashboard template

### DevOps & Infrastructure
- **Docker & Docker Compose** - Containerization
- **Vite** - Frontend build tool
- **Git** - Version control

### Third-party APIs
- **Vimeo API** - Video hosting và streaming
- **VNPAY** - Payment gateway

---

## 📁 Cấu trúc dự án

```
Elearning_CMS/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controllers (Web & API)
│   │   │   ├── API/           # API Controllers
│   │   │   └── Auth/          # Authentication Controllers
│   │   └── Middleware/        # Custom Middleware
│   ├── Models/                 # Eloquent Models
│   ├── Services/               # Business Logic Layer
│   ├── Jobs/                   # Queue Jobs (Vimeo upload)
│   ├── Notifications/          # Email Notifications
│   └── Helpers/                # Helper Functions
├── config/                     # Configuration files
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── routes/
│   ├── web.php                 # Web routes (CMS)
│   └── api.php                 # API routes
├── resources/
│   └── views/                  # Blade templates
├── public/                      # Public assets
├── docker-compose.dev.yml      # Docker Compose (Dev)
├── docker-compose.yml          # Docker Compose (Production)
└── Dockerfile                  # Docker image definition
```

**Kiến trúc:**
- **MVC Pattern**: Model-View-Controller separation
- **Service Layer**: Business logic tách biệt khỏi Controllers
- **Repository Pattern**: (Có thể mở rộng) Abstract database access

---

## 🛠️ Cài đặt và Chạy

### Yêu cầu hệ thống
- Docker Engine 20.10+
- Docker Compose 2.0+
- Git
- Node.js 18+ (cho build assets)

### Quick Start với Docker

```bash
# 1. Clone repository
git clone https://github.com/Kamadee/Elearning_CMS
cd Elearning_CMS

# 2. Tạo file .env
cp .env.example .env

# 3. Cấu hình database trong .env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=vfl-academy
DB_USERNAME=root
DB_PASSWORD=root

# 4. Build và khởi động containers
docker compose -f docker-compose.dev.yml up -d --build

# 5. Cấu hình Laravel
docker compose -f docker-compose.dev.yml exec app php artisan key:generate
docker compose -f docker-compose.dev.yml exec app php artisan migrate --force
docker compose -f docker-compose.dev.yml exec app php artisan db:seed

# 6. Build frontend assets
npm install
npm run build

# 7. Truy cập ứng dụng
# CMS: http://localhost:8081
# API: http://localhost:8081/api
```

### Cài đặt local (không dùng Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install && npm run build
php artisan serve
```

**Chi tiết hơn:** Xem [BUILD_PROJECT.md](./BUILD_PROJECT.md)

---

## 📡 API Documentation

### Base URL
```
http://localhost:8081/api
```

### Authentication
API sử dụng JWT authentication. Gửi token trong header:
```
Authorization: Bearer {token}
```

### Endpoints chính

#### Customer Authentication
- `POST /api/customer/login` - Đăng nhập
- `POST /api/customer/register` - Đăng ký
- `POST /api/customer/verify` - Xác thực email
- `POST /api/customer/logout` - Đăng xuất

#### Course Management
- `GET /api/course/list` - Danh sách khóa học
- `GET /api/course/detail/{id}` - Chi tiết khóa học
- `GET /api/course/top` - Khóa học nổi bật

#### Cart Management
- `GET /api/cart/content` - Nội dung giỏ hàng
- `POST /api/cart/add` - Thêm vào giỏ hàng
- `DELETE /api/cart/delete/{id}` - Xóa khỏi giỏ hàng

#### Payment
- `POST /api/payment/create` - Tạo payment URL
- `GET /api/payment/result` - Kết quả thanh toán

**Chi tiết API:** Xem [EXPERIENCE_DOC.md](./EXPERIENCE_DOC.md)

---

## 🏗️ Kiến trúc

### Request Flow
```
Route → Middleware → Controller → Service → Model → Database
```

### Design Patterns
- **MVC Pattern**: Separation of concerns
- **Service Layer Pattern**: Business logic abstraction
- **Repository Pattern**: (Có thể mở rộng) Data access abstraction
- **Factory Pattern**: Model factories cho testing

### Database Design
- **Eloquent ORM**: Object-relational mapping
- **Relationships**: One-to-Many, Many-to-Many với pivot tables
- **Migrations**: Version control cho database schema
- **Seeders**: Dữ liệu mẫu cho development

### Security
- ✅ CSRF Protection
- ✅ SQL Injection Prevention (Eloquent ORM)
- ✅ XSS Prevention (Blade auto-escaping)
- ✅ JWT Authentication
- ✅ Role-based Access Control
- ✅ Input Validation

### Performance Optimization
- ✅ Eager Loading (N+1 problem solution)
- ✅ Database Indexing
- ✅ Caching (Config, Routes, Views)
- ✅ Queue Jobs cho heavy tasks
- ✅ Server-side DataTables processing

---

## 🔮 Dự định cải tiến

### Short-term (1-2 tháng)
- [ ] Unit tests và Integration tests
- [ ] API documentation với Swagger/OpenAPI
- [ ] Real-time notifications với WebSockets
- [ ] Advanced search với Elasticsearch
- [ ] Image optimization và CDN integration

### Medium-term (3-6 tháng)
- [ ] Microservices architecture (tách services)
- [ ] Redis caching layer
- [ ] Message queue với RabbitMQ
- [ ] GraphQL API (bên cạnh REST)
- [ ] Mobile app API optimization

### Long-term (6+ tháng)
- [ ] Kubernetes deployment
- [ ] CI/CD pipeline hoàn chỉnh
- [ ] Monitoring và logging (ELK Stack)
- [ ] Auto-scaling
- [ ] Multi-tenant support

---

## 📚 Tài liệu tham khảo

### Project Documentation
- [BUILD_PROJECT.md](./BUILD_PROJECT.md) - Hướng dẫn build và deploy chi tiết
- [EXPERIENCE_DOC.md](./EXPERIENCE_DOC.md) - Kiến thức và kinh nghiệm dự án
- [BUG_DOC.md](./BUG_DOC.md) - Troubleshooting và bug fixes
- [CACHE_CLEAR_GUIDE.md](./CACHE_CLEAR_GUIDE.md) - Cache management

### External Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [JWT Auth Documentation](https://jwt-auth.readthedocs.io/)
- [Docker Documentation](https://docs.docker.com/)

---

