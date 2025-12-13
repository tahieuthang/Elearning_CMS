# 🔧 Fix Migration Error - Permission Tables (V2)

## ❌ Vấn đề

Lỗi: `Undefined array key "model_has_permissions"` khi chạy migration.

**Nguyên nhân:** Config cache chưa được clear sau khi thêm key `model_has_permissions` vào `config/permission.php`.

## ✅ Giải pháp

### Bước 1: Clear Config Cache

```bash
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
```

### Bước 2: Verify Config

Kiểm tra xem config đã có key `model_has_permissions` chưa:

```bash
docker compose -f docker-compose.dev.yml exec app php artisan tinker
```

```php
config('permission.table_names');
// Phải có key 'model_has_permissions'
```

### Bước 3: Chạy Migration

```bash
docker compose -f docker-compose.dev.yml exec app php artisan migrate
```

## 📋 Checklist

- [x] Key `model_has_permissions` đã được thêm vào `config/permission.php`
- [x] Migration đã được sửa để check bảng tồn tại trước khi tạo
- [x] Migration đã có validation cho các table names cần thiết
- [ ] **Clear config cache** (quan trọng!)
- [ ] Chạy migration

## 🔍 Debug

Nếu vẫn lỗi, kiểm tra:

```bash
# 1. Xem config hiện tại
docker compose -f docker-compose.dev.yml exec app php artisan tinker
```

```php
// Kiểm tra config
$tableNames = config('permission.table_names');
var_dump($tableNames);

// Phải có các keys:
// - permissions
// - roles
// - model_has_permissions  ← Quan trọng!
// - model_has_roles
// - role_has_permissions
```

```bash
# 2. Clear tất cả cache
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
docker compose -f docker-compose.dev.yml exec app php artisan cache:clear
docker compose -f docker-compose.dev.yml exec app php artisan route:clear
```

## ⚠️ Lưu ý

**QUAN TRỌNG:** Luôn clear config cache sau khi sửa file config:

```bash
php artisan config:clear
```

Hoặc trong Docker:
```bash
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
```

---

**Sau khi clear config cache, chạy lại migration!**

