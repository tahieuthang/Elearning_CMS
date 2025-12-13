# 🔧 Fix Migration Error - Permission Tables

## ❌ Vấn đề

Migration `2025_07_04_105149_create_permission_tables.php` bị lỗi vì bảng `permissions` đã tồn tại từ migration cũ (`2025_02_14_084446_create_permissions_table.php`).

## ✅ Giải pháp

Đã sửa migration để **check nếu bảng đã tồn tại thì skip** thay vì tạo lại.

## 🚀 Chạy lại Migration

```bash
docker compose -f docker-compose.dev.yml exec app php artisan migrate
```

Migration sẽ:
- ✅ Chạy `add_rating_to_courses_table` (đã chạy thành công)
- ✅ Skip các bảng permission nếu đã tồn tại
- ✅ Tạo các bảng permission mới nếu chưa có

## ⚠️ Lưu ý

### Vấn đề tiềm ẩn về Schema

Bảng `permissions` cũ có schema:
- `permission_name` (string)
- `description` (text, nullable)
- `softDeletes`

Bảng `permissions` từ Spatie cần:
- `name` (string) - **khác với `permission_name`**
- Không có `description`
- Không có `softDeletes`

**Nếu bạn đang dùng Spatie Permission package**, có thể cần:
1. Migrate dữ liệu từ `permission_name` sang `name`
2. Hoặc tạo migration để rename column
3. Hoặc xóa bảng cũ và tạo lại (nếu không có dữ liệu quan trọng)

### Kiểm tra Schema hiện tại

```bash
docker compose -f docker-compose.dev.yml exec app php artisan tinker
```

```php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Kiểm tra columns của bảng permissions
DB::select('DESCRIBE permissions');
```

## 🔄 Nếu vẫn gặp lỗi

### Option 1: Mark migration đã chạy (nếu bảng đã đúng)

```bash
docker compose -f docker-compose.dev.yml exec app php artisan tinker
```

```php
use Illuminate\Support\Facades\DB;
DB::table('migrations')->insert([
    'migration' => '2025_07_04_105149_create_permission_tables',
    'batch' => DB::table('migrations')->max('batch') + 1
]);
```

### Option 2: Rollback và chạy lại (cẩn thận - mất dữ liệu)

```bash
# Rollback migration cuối
docker compose -f docker-compose.dev.yml exec app php artisan migrate:rollback

# Chạy lại
docker compose -f docker-compose.dev.yml exec app php artisan migrate
```

### Option 3: Xóa bảng cũ và tạo lại (nếu không có dữ liệu quan trọng)

```bash
docker compose -f docker-compose.dev.yml exec app php artisan tinker
```

```php
use Illuminate\Support\Facades\Schema;
Schema::dropIfExists('permissions');
Schema::dropIfExists('roles');
// ... drop other permission tables

// Sau đó chạy lại migration
```

---

**Sau khi sửa, chạy lại migration và kiểm tra kết quả!**

