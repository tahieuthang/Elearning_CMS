# 🔧 CORS Troubleshooting Guide

## ❌ Vấn đề: CORS không hoạt động với ngrok URL

Ngay cả sau khi clear cache, CORS vẫn không hoạt động với ngrok URL.

## ✅ Đã sửa

### 1. **Sửa Kernel.php** - Đặt HandleCors đúng vị trí

**Trước (SAI):**
```php
protected $middlewareGroups = [
  'api' => [...],
  \Illuminate\Http\Middleware\HandleCors::class, // ❌ Sai vị trí
];
```

**Sau (ĐÚNG):**
```php
protected $middleware = [
  \Illuminate\Http\Middleware\HandleCors::class, // ✅ Global middleware
];

protected $middlewareGroups = [
  'api' => [...],
];
```

### 2. **Sửa cors.php** - Dùng pattern cho ngrok URL

**Vấn đề:** Ngrok URL thay đổi mỗi lần restart, không thể hardcode.

**Giải pháp:** Dùng `allowed_origins_patterns` với regex:

```php
'allowed_origins' => [
  'https://elearning-landing.netlify.app',
  'http://localhost:5173'
],

// Pattern cho ngrok URLs
'allowed_origins_patterns' => [
  '/^https:\/\/.*\.ngrok-free\.dev$/',      // *.ngrok-free.dev
  '/^https:\/\/.*\.ngrok\.io$/',            // *.ngrok.io
  '/^https:\/\/.*\.ngrok-app\.com$/',       // *.ngrok-app.com
],
```

## 🚀 Các bước khắc phục

### Bước 1: Clear tất cả cache

```bash
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
docker compose -f docker-compose.dev.yml exec app php artisan cache:clear
docker compose -f docker-compose.dev.yml exec app php artisan route:clear
docker compose -f docker-compose.dev.yml exec app php artisan view:clear
```

### Bước 2: Restart containers

```bash
docker compose -f docker-compose.dev.yml restart app nginx
```

Hoặc rebuild nếu cần:

```bash
docker compose -f docker-compose.dev.yml up -d --build app nginx
```

### Bước 3: Verify CORS config

```bash
docker compose -f docker-compose.dev.yml exec app php artisan tinker
```

```php
// Kiểm tra CORS config
config('cors.allowed_origins');
config('cors.allowed_origins_patterns');

// Test pattern matching
$url = 'https://marth-venerative-ferally.ngrok-free.dev';
$patterns = config('cors.allowed_origins_patterns');
foreach ($patterns as $pattern) {
    if (preg_match($pattern, $url)) {
        echo "Match: $pattern\n";
    }
}
```

## 🔍 Debug CORS

### Kiểm tra Response Headers

```bash
# Test API với curl
curl -I -X OPTIONS \
  -H "Origin: https://marth-venerative-ferally.ngrok-free.dev" \
  -H "Access-Control-Request-Method: GET" \
  http://localhost:8081/api/course/list

# Phải thấy headers:
# Access-Control-Allow-Origin: https://marth-venerative-ferally.ngrok-free.dev
# Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE
# Access-Control-Allow-Headers: Content-Type, Authorization, ...
```

### Kiểm tra Browser Console

Mở Browser DevTools → Network tab → Xem request → Check Response Headers

**Phải có:**
- `Access-Control-Allow-Origin: https://marth-venerative-ferally.ngrok-free.dev`
- `Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE`
- `Access-Control-Allow-Headers: Content-Type, Authorization, ...`

## ⚠️ Lưu ý quan trọng

### 1. **Ngrok URL thay đổi**

Ngrok URL thay đổi mỗi lần restart ngrok. Vì vậy:
- ✅ Dùng **pattern** thay vì hardcode URL
- ✅ Hoặc update URL mỗi lần restart ngrok

### 2. **Nginx cũng có CORS headers**

Nginx trong `nginx.dev.conf` đang set CORS headers với `*`. Điều này có thể conflict với Laravel CORS.

**Nếu vẫn lỗi, có thể tạm thời comment CORS headers trong nginx:**

```nginx
# Comment các dòng này trong nginx.dev.conf
# add_header 'Access-Control-Allow-Origin' '*' always;
# add_header 'Access-Control-Allow-Methods' '...' always;
# add_header 'Access-Control-Allow-Headers' '...' always;
```

Để Laravel xử lý CORS hoàn toàn.

### 3. **Preflight OPTIONS request**

Browser gửi OPTIONS request trước khi gửi request thật. Đảm bảo:
- ✅ Laravel xử lý OPTIONS request
- ✅ Nginx không block OPTIONS request

### 4. **Config Cache**

Sau khi sửa `config/cors.php`, **LUÔN clear config cache**:

```bash
php artisan config:clear
```

## 🔄 Nếu vẫn không hoạt động

### Option 1: Tạm thời dùng wildcard (chỉ cho dev)

```php
// config/cors.php
'allowed_origins' => ['*'], // ⚠️ Chỉ dùng cho dev, không dùng production
```

### Option 2: Disable CORS trong nginx, để Laravel xử lý

Sửa `nginx.dev.conf` - comment các CORS headers, để Laravel xử lý hoàn toàn.

### Option 3: Kiểm tra middleware order

Đảm bảo `HandleCors` chạy trước các middleware khác (đã sửa trong Kernel.php).

## 📋 Checklist

- [x] Sửa Kernel.php - HandleCors trong $middleware (global)
- [x] Sửa cors.php - Dùng pattern cho ngrok URLs
- [ ] Clear config cache
- [ ] Restart containers
- [ ] Test với ngrok URL mới
- [ ] Verify response headers có CORS headers

---

**Sau khi sửa, clear cache và restart containers, sau đó test lại!**

