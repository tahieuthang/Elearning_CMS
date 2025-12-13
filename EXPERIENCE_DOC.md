# 📚 EXPERIENCE_DOC.md - Tài liệu Kinh nghiệm và Kiến thức Dự án

## 🎯 Tổng quan Dự án

Dự án **E-Learning CMS** là một hệ thống quản lý nội dung học trực tuyến được xây dựng bằng **Laravel 11** (PHP 8.2) với kiến trúc **MVC** và **Service Layer Pattern**. Dự án bao gồm 2 phần chính:

1. **CMS Admin Panel** - Quản lý nội dung, khóa học, bài viết (Web interface)
2. **RESTful API** - Cung cấp API cho frontend/mobile app

---

## 📑 Mục lục

### [🏗️ Kiến trúc Dự án](#-kiến-trúc-dự-án)
- [MVC Pattern (Model-View-Controller)](#1-mvc-pattern-model-view-controller)
- [Service Layer Pattern](#2-service-layer-pattern)

### [🚀 Laravel Framework - Kiến thức Cốt lõi](#-laravel-framework---kiến-thức-cốt-lõi)
- [Eloquent ORM (Object-Relational Mapping)](#1-eloquent-orm-object-relational-mapping)
- [Migrations & Database Schema](#2-migrations--database-schema)
- [Routing & Middleware](#3-routing--middleware)
- [Authentication & Authorization](#4-authentication--authorization)
- [Request Validation](#5-request-validation)
- [Database Transactions](#6-database-transactions)
- [Queue Jobs (Background Processing)](#7-queue-jobs-background-processing)
- [Blade Templating Engine](#8-blade-templating-engine)

### [📦 Laravel Packages Quan trọng](#-laravel-packages-quan-trọng)
- [spatie/laravel-permission](#1-spatielaravel-permission---role--permission-system)
- [tymon/jwt-auth](#2-tymonjwt-auth---jwt-authentication)
- [yajra/laravel-datatables](#3-yajralaravel-datatables---server-side-datatables)
- [barryvdh/laravel-dompdf](#4-barryvdhlaravel-dompdf---pdf-generation)
- [vimeo/laravel](#5-vimelaravel---vimeo-api-integration)
- [laravel/sanctum](#6-laravelsanctum---api-token-authentication)

### [🎨 Frontend Technologies](#-frontend-technologies)
- [jQuery](#1-jquery---javascript-library)
- [jQuery Validation](#2-jquery-validation---form-validation)
- [Select2](#3-select2---advanced-select-box)
- [DataTables](#4-datatables---advanced-table)
- [Bootstrap FileInput](#5-bootstrap-fileinput-kartik-v---file-upload-widget)
- [SweetAlert2](#6-sweetalert2---beautiful-alert-dialogs)
- [CKEditor](#7-ckeditor---rich-text-editor)
- [jQuery UI](#8-jquery-ui---ui-interactions)

### [🗄️ Database & ORM](#️-database--orm)
- [Eloquent Relationships - Chi tiết](#eloquent-relationships---chi-tiết)
- [Query Optimization](#query-optimization)

### [🔐 Security Best Practices](#-security-best-practices)
- [CSRF Protection](#1-csrf-protection)
- [SQL Injection Prevention](#2-sql-injection-prevention)
- [XSS Prevention](#3-xss-prevention)
- [Authentication & Authorization](#4-authentication--authorization)

### [🚀 Performance Optimization](#-performance-optimization)
- [Caching](#1-caching)
- [Database Indexing](#2-database-indexing)
- [Lazy Loading vs Eager Loading](#3-lazy-loading-vs-eager-loading)

### [📡 API Design Patterns](#-api-design-patterns)
- [RESTful API Conventions](#1-restful-api-conventions)
- [Response Format](#2-response-format)
- [API Versioning](#3-api-versioning-nếu-cần)

### [🐳 Docker & Containerization](#-docker--containerization)

### [🔄 Payment Integration (VNPAY)](#-payment-integration-vnpay)

### [📝 Best Practices Đã Áp dụng](#-best-practices-đã-áp-dụng)

### [🎓 Kiến thức Quan trọng Cần Nắm](#-kiến-thức-quan-trọng-cần-nắm)

### [📚 Tài liệu Tham khảo](#-tài-liệu-tham-khảo)

---

## 🏗️ Kiến trúc Dự án

### 1. **MVC Pattern (Model-View-Controller)**

Laravel tuân theo kiến trúc MVC chuẩn:

```
app/
├── Models/          # Model - Tương tác với database
├── Views/           # View - Blade templates (HTML)
├── Controllers/     # Controller - Xử lý logic request/response
└── Services/        # Service Layer - Business logic
```

**Cách hoạt động:**
- **Route** → **Controller** → **Service** → **Model** → **Database**
- Controller nhận request, gọi Service xử lý logic, Service tương tác với Model
- Model sử dụng Eloquent ORM để query database
- Controller trả về response (JSON cho API hoặc View cho Web)

### 2. **Service Layer Pattern**

Dự án áp dụng **Service Layer** để tách biệt business logic khỏi Controller:

```php
// Controller chỉ xử lý HTTP request/response
public function updateCourse(Request $request) {
    $result = $this->courseServices->processUpdateCourse($id, $request->all());
    return response()->json($result);
}

// Service chứa toàn bộ business logic
public function processUpdateCourse($id, $formData) {
    // Validate, transform data, call model, etc.
}
```

**Lợi ích:**
- Code dễ maintain, test
- Business logic có thể tái sử dụng
- Controller gọn gàng, dễ đọc

---

## 🚀 Laravel Framework - Kiến thức Cốt lõi

### 1. **Eloquent ORM (Object-Relational Mapping)**

Eloquent là ORM của Laravel, cho phép tương tác với database bằng PHP objects thay vì SQL thuần.

#### **Model Relationships**

Dự án sử dụng nhiều loại relationships:

**One-to-Many:**
```php
// Course có nhiều CourseVideo
class Course extends Model {
    public function videos() {
        return $this->hasMany(CourseVideo::class);
    }
}
```

**Many-to-Many (Pivot Table):**
```php
// Course có nhiều Category, Category có nhiều Course
class Course extends Model {
    public function courseCategories() {
        return $this->belongsToMany(PostCategory::class, 'course_category_pivot');
    }
}
```

**BelongsTo:**
```php
// OrderItem thuộc về Order
class OrderItem extends Model {
    public function order() {
        return $this->belongsTo(Order::class);
    }
}
```

#### **Query Builder & Eloquent Methods**

```php
// Eager Loading (N+1 Problem Solution)
Course::with(['videos', 'courseCategories'])->get();

// Query với điều kiện
Cart::where('customer_id', $customerId)
    ->where('course_id', $courseId)
    ->first();

// Aggregates
Order::where('customer_id', $id)->sum('total_price');
```

### 2. **Migrations & Database Schema**

Migrations quản lý version control cho database schema:

```php
// Tạo migration
php artisan make:migration create_courses_table

// Migration file
Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->timestamps(); // created_at, updated_at
});
```

**Chạy migrations:**
```bash
php artisan migrate        # Chạy migrations mới
php artisan migrate:rollback # Rollback migration cuối
php artisan migrate:fresh   # Drop all tables và migrate lại
```

### 3. **Routing & Middleware**

#### **Web Routes** (CMS Admin)
```php
Route::group(['prefix' => 'courses', 'middleware' => 'auth'], function () {
    Route::get('/list', [CourseController::class, 'list']);
    Route::post('/createCourse', [CourseController::class, 'createCourse']);
});
```

#### **API Routes** (RESTful API)
```php
Route::group(['middleware' => [JWTVerifyCustomer::class]], function () {
    Route::get('/cart/content', [CartController::class, 'getCartContent']);
    Route::post('/cart/add', [CartController::class, 'addCartItem']);
});
```

#### **Middleware**

Middleware xử lý logic trước/sau request:

**Custom Middleware - JWT Authentication:**
```php
class JWTVerifyCustomer {
    public function handle(Request $request, Closure $next) {
        if (!auth('customer')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
```

**Middleware trong Kernel:**
```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        // ...
    ],
    'api' => [
        \App\Http\Middleware\JWTVerifyCustomer::class,
    ],
];
```

### 4. **Authentication & Authorization**

#### **Multi-Guard Authentication**

Dự án sử dụng 2 guards:
- `web` - Cho admin (session-based)
- `customer` - Cho customer (JWT-based)

```php
// Config auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'customer' => [
        'driver' => 'jwt',
        'provider' => 'customers',
    ],
],

// Sử dụng
auth('customer')->user(); // Lấy customer hiện tại
auth('web')->check();     // Kiểm tra admin đã login
```

#### **JWT Authentication (tymon/jwt-auth)**

JWT (JSON Web Token) dùng cho API authentication:

**Flow:**
1. Customer login → Server tạo JWT token
2. Client lưu token (localStorage/cookie)
3. Mỗi request gửi token trong header: `Authorization: Bearer {token}`
4. Middleware verify token → Cho phép/từ chối request

```php
// Login và tạo token
$token = JWTAuth::fromUser($customer);

// Verify token trong middleware
auth('customer')->check(); // Tự động verify từ header
```

### 5. **Request Validation**

Laravel có built-in validation:

```php
$request->validate([
    'title' => 'required|max:255',
    'email' => 'required|email|unique:customers',
    'quantity' => 'required|integer|min:1',
]);

// Custom validation rules
$validator = Validator::make($request->all(), [
    'originalPrice' => 'greaterThan:#saleOffPrice',
]);
```

### 6. **Database Transactions**

Đảm bảo tính toàn vẹn dữ liệu:

```php
DB::beginTransaction();
try {
    $order = Order::create($orderData);
    foreach ($items as $item) {
        OrderItem::create($itemData);
    }
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return response()->json(['error' => $e->getMessage()], 500);
}
```

### 7. **Queue Jobs (Background Processing)**

Xử lý tác vụ nặng ở background (upload video lên Vimeo):

```php
// Tạo Job
php artisan make:job UploadToVimeo

// Dispatch job
UploadToVimeo::dispatch($videoData);

// Chạy queue worker
php artisan queue:work
```

**Cấu hình Queue:**
- Driver: `database` hoặc `redis`
- Queue worker chạy liên tục, xử lý jobs trong queue

### 8. **Blade Templating Engine**

Blade là template engine của Laravel:

```blade
{{-- Variables --}}
{{ $course->title }}

{{-- Loops --}}
@foreach($courses as $course)
    <div>{{ $course->title }}</div>
@endforeach

{{-- Conditionals --}}
@if($course->status === 1)
    <span>Active</span>
@endif

{{-- Includes --}}
@include('course.form', ['course' => $course])
```

---

## 📦 Laravel Packages Quan trọng

### 1. **spatie/laravel-permission** - Role & Permission System

Quản lý quyền truy cập dựa trên Role và Permission:

```php
// Gán role cho user
$user->assignRole('admin');

// Kiểm tra permission
if ($user->can('course.create')) {
    // User có quyền tạo course
}

// Middleware
Route::middleware('permission:course.edit')->group(function () {
    // Chỉ user có permission course.edit mới vào được
});
```

**Database Structure:**
- `roles` - Vai trò (admin, editor, viewer)
- `permissions` - Quyền cụ thể (course.create, course.edit)
- `role_has_permissions` - Pivot table
- `model_has_roles` - User có roles nào

### 2. **tymon/jwt-auth** - JWT Authentication

Xác thực API bằng JWT tokens (đã giải thích ở trên).

### 3. **yajra/laravel-datatables** - Server-side DataTables

Tạo DataTables với server-side processing (phân trang, search, sort):

```php
// Controller
public function anyData() {
    return DataTables::of(Course::query())
        ->addColumn('action', function($course) {
            return '<button>Edit</button>';
        })
        ->make(true);
}
```

**Frontend:**
```javascript
$('#course-table').DataTable({
    serverSide: true,
    ajax: '/courses/anyData',
    columns: [
        { data: 'id' },
        { data: 'title' },
        { data: 'action' }
    ]
});
```

### 4. **barryvdh/laravel-dompdf** - PDF Generation

Tạo PDF từ HTML:

```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadView('invoice', $data);
return $pdf->download('invoice.pdf');
```

### 5. **vimeo/laravel** - Vimeo API Integration

Upload và quản lý video trên Vimeo:

```php
use Vimeo\Laravel\Facades\Vimeo;

// Upload video
$video = Vimeo::upload($filePath, [
    'name' => 'Video Title',
    'description' => 'Description'
]);
```

### 6. **laravel/sanctum** - API Token Authentication

Sanctum cung cấp authentication cho SPA và mobile apps (dự án có cài nhưng chưa sử dụng nhiều, chủ yếu dùng JWT).

---

## 🎨 Frontend Technologies

### 1. **jQuery** - JavaScript Library

jQuery được sử dụng rộng rãi trong CMS Admin Panel:

#### **DOM Manipulation**
```javascript
// Select elements
$('#course-title').val();
$('.btn-submit').click();

// Manipulate
$('#form-course').submit();
$('.alert').hide();
```

#### **AJAX Requests**
```javascript
$.ajax({
    url: '/api/cart/add',
    method: 'POST',
    data: { course_id: 1, quantity: 1 },
    headers: {
        'Authorization': 'Bearer ' + token,
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function(response) {
        console.log(response);
    }
});
```

#### **Event Handling**
```javascript
$('.btn-delete').on('click', function() {
    const id = $(this).data('id');
    // Handle delete
});
```

### 2. **jQuery Validation** - Form Validation

Validate form phía client:

```javascript
$('#form-course').validate({
    rules: {
        title: {
            required: true,
            maxlength: 255
        },
        originalPrice: {
            greaterThan: "#saleOffPrice"
        }
    },
    messages: {
        title: {
            required: "Title is required"
        }
    },
    submitHandler: function(form) {
        form.submit();
    }
});
```

### 3. **Select2** - Advanced Select Box

Select box với search, multiple selection:

```javascript
$('.select2').select2({
    placeholder: 'Select categories',
    multiple: true,
    allowClear: true
});
```

### 4. **DataTables** - Advanced Table

Table với search, pagination, sorting:

```javascript
$('#course-table').DataTable({
    serverSide: true,  // Server-side processing
    ajax: '/courses/anyData',
    columns: [
        { data: 'id' },
        { data: 'title' },
        { data: 'action', orderable: false }
    ],
    pageLength: 50
});
```

### 5. **Bootstrap FileInput (kartik-v)** - File Upload Widget

Upload file với preview:

```javascript
$("#input-pd").fileinput({
    maxFileSize: 5000,
    allowedFileExtensions: ['jpg', 'png'],
    showUpload: false,
    initialPreview: [thumbnailUrl],
    initialPreviewAsData: true
});
```

### 6. **SweetAlert2** - Beautiful Alert Dialogs

Thay thế `alert()` và `confirm()`:

```javascript
Swal.fire({
    title: 'Xác nhận xóa?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận'
}).then((result) => {
    if (result.isConfirmed) {
        // Delete
    }
});
```

### 7. **CKEditor** - Rich Text Editor

WYSIWYG editor cho content:

```javascript
const editor = CKEDITOR.replace('content', {
    filebrowserUploadUrl: '/courses/upload-img'
});
```

### 8. **jQuery UI** - UI Interactions

Sortable, draggable, datepicker:

```javascript
$('#video-body-list').sortable(); // Drag & drop để sắp xếp
```

---

## 🗄️ Database & ORM

### **Eloquent Relationships - Chi tiết**

#### **One-to-Many**
```php
// Course có nhiều CourseVideo
class Course extends Model {
    public function videos() {
        return $this->hasMany(CourseVideo::class);
    }
}

// Lấy course với videos
$course = Course::with('videos')->find(1);
```

#### **Many-to-Many với Pivot Table**
```php
// Course <-> Category (many-to-many)
class Course extends Model {
    public function courseCategories() {
        return $this->belongsToMany(
            PostCategory::class,
            'course_category_pivot',  // Pivot table
            'course_id',
            'category_id'
        );
    }
}

// Attach/Detach
$course->courseCategories()->attach([1, 2, 3]);
$course->courseCategories()->detach([1]);
```

#### **Polymorphic Relationships** (nếu có)
```php
// Một model có thể belong to nhiều model khác
class Image extends Model {
    public function imageable() {
        return $this->morphTo();
    }
}
```

### **Query Optimization**

**Eager Loading (N+1 Problem):**
```php
// ❌ Bad: N+1 queries
$courses = Course::all();
foreach ($courses as $course) {
    echo $course->videos->count(); // Query mỗi lần
}

// ✅ Good: 2 queries total
$courses = Course::with('videos')->get();
foreach ($courses as $course) {
    echo $course->videos->count(); // Đã load sẵn
}
```

**Lazy Eager Loading:**
```php
$courses = Course::all();
$courses->load('videos'); // Load sau
```

---

## 🔐 Security Best Practices

### 1. **CSRF Protection**

Laravel tự động bảo vệ CSRF cho web routes:

```blade
{{ csrf_field() }}  // Blade
```

```javascript
// AJAX requests
headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
```

### 2. **SQL Injection Prevention**

Eloquent tự động escape, nhưng cần cẩn thận với raw queries:

```php
// ✅ Safe
User::where('email', $email)->first();

// ⚠️ Cần bind parameters
DB::select('SELECT * FROM users WHERE email = ?', [$email]);
```

### 3. **XSS Prevention**

Blade tự động escape:

```blade
{{ $userInput }}  <!-- Auto escaped -->
{!! $trustedHtml !!}  <!-- Raw HTML (cẩn thận!) -->
```

### 4. **Authentication & Authorization**

- Sử dụng middleware để protect routes
- Kiểm tra permissions trước khi cho phép action
- Validate input từ user

---

## 🚀 Performance Optimization

### 1. **Caching**

```php
// Cache config, routes, views
php artisan config:cache
php artisan route:cache
php artisan view:cache

// Application cache
Cache::put('key', $value, 3600);
$value = Cache::get('key');
```

### 2. **Database Indexing**

Đảm bảo các cột thường query có index:

```php
$table->index('customer_id');
$table->index(['course_id', 'customer_id']); // Composite index
```

### 3. **Lazy Loading vs Eager Loading**

Luôn dùng Eager Loading khi cần relationships:

```php
// ✅ Good
Course::with('videos', 'categories')->get();

// ❌ Bad
Course::all(); // Sẽ lazy load khi access relationships
```

---

## 📡 API Design Patterns

### 1. **RESTful API Conventions**

```
GET    /api/course/list        # List resources
GET    /api/course/{id}        # Get single resource
POST   /api/course             # Create resource
PUT    /api/course/{id}        # Update resource
DELETE /api/course/{id}        # Delete resource
```

### 2. **Response Format**

```php
// Success response
return response()->json([
    'status' => true,
    'data' => $course,
    'message' => 'Success'
], 200);

// Error response
return response()->json([
    'status' => false,
    'errorCode' => ResponseCode::$NOT_FOUND,
    'message' => 'Course not found'
], 404);
```

### 3. **API Versioning** (nếu cần)

```php
Route::prefix('v1')->group(function () {
    Route::get('/courses', [CourseController::class, 'index']);
});
```

---

## 🐳 Docker & Containerization

### **Docker Compose Services**

- **app** - PHP-FPM application
- **nginx** - Web server
- **mysql** - Database
- **queue** - Laravel queue worker

**Cách hoạt động:**
- Mỗi service chạy trong container riêng
- Containers giao tiếp qua Docker network
- Volumes để persist data (database, storage)

---

## 🔄 Payment Integration (VNPAY)

### **Payment Flow**

1. Customer tạo order → Tạo payment URL từ VNPAY
2. Redirect customer đến VNPAY
3. Customer thanh toán
4. VNPAY callback về server (IPN URL)
5. Server verify payment → Update order status

```php
// Tạo payment URL
$vnpay = new VNPayService();
$paymentUrl = $vnpay->createPaymentUrl($orderData);

// Handle callback
public function resultPayment(Request $request) {
    $isValid = $vnpay->verifyPayment($request);
    if ($isValid) {
        // Update order status
    }
}
```

---

## 📝 Best Practices Đã Áp dụng

1. **Service Layer Pattern** - Tách business logic khỏi Controller
2. **Repository Pattern** (có thể mở rộng) - Abstract database access
3. **Form Request Validation** - Validate trong FormRequest class
4. **Resource Classes** - Transform API responses
5. **Event & Listeners** - Decouple logic (có thể dùng cho notifications)
6. **Helper Functions** - Tái sử dụng code (app/Helpers/Helper.php)
7. **Constants Config** - Centralize constants (config/constants.php)

---

## 🎓 Kiến thức Quan trọng Cần Nắm

### **Laravel Core Concepts:**
- ✅ Eloquent ORM & Relationships
- ✅ Routing & Middleware
- ✅ Authentication & Authorization
- ✅ Request Validation
- ✅ Database Transactions
- ✅ Queue Jobs
- ✅ Blade Templating

### **Frontend Essentials:**
- ✅ jQuery DOM manipulation & AJAX
- ✅ Form validation
- ✅ DataTables server-side processing
- ✅ File upload handling

### **Security:**
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ XSS prevention
- ✅ JWT authentication

### **Performance:**
- ✅ Eager Loading
- ✅ Caching
- ✅ Database indexing

---

## 📚 Tài liệu Tham khảo

- [Laravel Documentation](https://laravel.com/docs)
- [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [Laravel Queue](https://laravel.com/docs/queues)
- [jQuery Documentation](https://api.jquery.com/)
- [DataTables Documentation](https://datatables.net/)
- [JWT Auth Documentation](https://jwt-auth.readthedocs.io/)

---

**Tài liệu này được tạo để tổng hợp kiến thức và kinh nghiệm từ dự án E-Learning CMS. Cập nhật khi có thay đổi hoặc thêm tính năng mới.**

