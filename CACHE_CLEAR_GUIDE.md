# 📋 Hướng dẫn Clear Cache trong Laravel với Docker

## 🎯 Khi nào cần clear cache?

### ✅ **CẦN CLEAR CACHE** trong các trường hợp sau:

#### 1. **Thay đổi file `.env`**
```bash
# Sau khi sửa .env, LUÔN clear config cache
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
```

**Lý do:** Laravel cache config từ `.env` vào `bootstrap/cache/config.php`. Nếu không clear, thay đổi trong `.env` sẽ không có hiệu lực.

**Ví dụ:**
- Thay đổi `DB_HOST`, `DB_PASSWORD`
- Thay đổi `VNPAY_PAYMENT_TMNCODE`, `VNPAY_PAYMENT_HASHSECRET`
- Thay đổi `APP_DEBUG`, `APP_ENV`
- Thay đổi bất kỳ biến môi trường nào

#### 2. **Thay đổi file config (`config/*.php`)**
```bash
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
```

**Lý do:** Nếu đã chạy `config:cache`, Laravel sẽ cache toàn bộ config. Cần clear để load lại.

#### 3. **Thay đổi routes (`routes/*.php`)**
```bash
docker compose -f docker-compose.dev.yml exec app php artisan route:clear
```

**Lý do:** Laravel cache routes để tăng performance. Khi thêm/sửa/xóa routes, cần clear.

**Lưu ý:** Trong môi trường dev, thường không cần cache routes. Chỉ cache trong production.

#### 4. **Thay đổi views (`resources/views/*.blade.php`)**
```bash
docker compose -f docker-compose.dev.yml exec app php artisan view:clear
```

**Lý do:** Laravel compile và cache Blade templates. Khi sửa views, cần clear để thấy thay đổi.

**Lưu ý:** Trong môi trường dev, thường không cần cache views. Chỉ cache trong production.

#### 5. **Thay đổi application cache (Cache facade)**
```bash
docker compose -f docker-compose.dev.yml exec app php artisan cache:clear
```

**Lý do:** Clear tất cả cache được lưu bằng `Cache::put()`, `cache()` helper, Redis, etc.

**Khi nào cần:**
- Thay đổi logic cache trong code
- Cache bị lỗi hoặc không đúng
- Muốn force refresh cache

#### 6. **Sau khi deploy code mới**
```bash
# Clear tất cả cache sau khi deploy
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
docker compose -f docker-compose.dev.yml exec app php artisan cache:clear
docker compose -f docker-compose.dev.yml exec app php artisan route:clear
docker compose -f docker-compose.dev.yml exec app php artisan view:clear
```

**Lý do:** Đảm bảo code mới được load, không bị cache cũ ảnh hưởng.

#### 7. **Khi gặp lỗi "Config not found" hoặc "Route not found"**
```bash
# Clear config và route cache
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
docker compose -f docker-compose.dev.yml exec app php artisan route:clear
```

**Lý do:** Cache có thể bị lỗi hoặc không đồng bộ với code hiện tại.

### ❌ **KHÔNG CẦN CLEAR CACHE** trong các trường hợp:

#### 1. **Chỉ sửa code logic (Controllers, Services, Models)**
- Không cần clear cache
- Laravel tự động load lại code mới

#### 2. **Chỉ sửa database (migrations, seeders)**
- Không cần clear cache
- Chỉ cần chạy migrations

#### 3. **Chỉ sửa CSS/JS (public assets)**
- Không cần clear cache
- Chỉ cần hard refresh browser (Ctrl+Shift+R)

#### 4. **Trong môi trường dev với volume mount**
- Code được mount trực tiếp, thay đổi có hiệu lực ngay
- Chỉ cần clear khi thay đổi `.env` hoặc config files

## 🔧 Các lệnh Clear Cache phổ biến

### Clear từng loại cache

```bash
# Clear config cache
docker compose -f docker-compose.dev.yml exec app php artisan config:clear

# Clear application cache
docker compose -f docker-compose.dev.yml exec app php artisan cache:clear

# Clear route cache
docker compose -f docker-compose.dev.yml exec app php artisan route:clear

# Clear view cache
docker compose -f docker-compose.dev.yml exec app php artisan view:clear

# Clear compiled class cache
docker compose -f docker-compose.dev.yml exec app php artisan clear-compiled

# Clear event cache
docker compose -f docker-compose.dev.yml exec app php artisan event:clear
```

### Clear tất cả cache (one-liner)

```bash
docker compose -f docker-compose.dev.yml exec app bash -c "php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear"
```

### Clear cache và optimize (Production)

```bash
# Clear cache
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
docker compose -f docker-compose.dev.yml exec app php artisan cache:clear
docker compose -f docker-compose.dev.yml exec app php artisan route:clear
docker compose -f docker-compose.dev.yml exec app php artisan view:clear

# Rebuild cache (chỉ trong production)
docker compose -f docker-compose.dev.yml exec app php artisan config:cache
docker compose -f docker-compose.dev.yml exec app php artisan route:cache
docker compose -f docker-compose.dev.yml exec app php artisan view:cache
```

## 📝 Best Practices

### Development Environment

1. **Không cache trong dev:**
   - Không chạy `config:cache`, `route:cache`, `view:cache`
   - Chỉ clear khi cần thiết (sau khi sửa `.env`)

2. **Workflow thông thường:**
   ```bash
   # Sửa .env
   docker compose -f docker-compose.dev.yml exec app php artisan config:clear
   
   # Sửa routes
   docker compose -f docker-compose.dev.yml exec app php artisan route:clear
   
   # Sửa views
   docker compose -f docker-compose.dev.yml exec app php artisan view:clear
   ```

### Production Environment

1. **Luôn cache trong production:**
   ```bash
   # Sau khi deploy
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Khi cần update:**
   ```bash
   # Clear trước
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   
   # Rebuild cache
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## 🚨 Troubleshooting

### Vấn đề: Thay đổi `.env` không có hiệu lực

**Giải pháp:**
```bash
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
docker compose -f docker-compose.dev.yml restart app
```

### Vấn đề: Route mới không hoạt động

**Giải pháp:**
```bash
docker compose -f docker-compose.dev.yml exec app php artisan route:clear
docker compose -f docker-compose.dev.yml exec app php artisan route:list  # Kiểm tra routes
```

### Vấn đề: View không cập nhật

**Giải pháp:**
```bash
docker compose -f docker-compose.dev.yml exec app php artisan view:clear
# Và hard refresh browser (Ctrl+Shift+R)
```

### Vấn đề: Cache vẫn còn sau khi clear

**Giải pháp:**
```bash
# Clear tất cả và restart container
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
docker compose -f docker-compose.dev.yml exec app php artisan cache:clear
docker compose -f docker-compose.dev.yml exec app php artisan route:clear
docker compose -f docker-compose.dev.yml exec app php artisan view:clear
docker compose -f docker-compose.dev.yml restart app
```

## 📚 Tài liệu tham khảo

- [Laravel Configuration Caching](https://laravel.com/docs/configuration#configuration-caching)
- [Laravel Route Caching](https://laravel.com/docs/routing#route-caching)
- [Laravel View Caching](https://laravel.com/docs/views#view-caching)

