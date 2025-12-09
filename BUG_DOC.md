# 🐛 Bug Documentation - Container elearning-queue Restarting

## 📋 Tóm tắt

Container `elearning-queue` bị restart liên tục khi khởi động Docker containers, không thể chạy queue worker để xử lý background jobs.

## 🔍 Mô tả lỗi

### Triệu chứng

```bash
$ docker ps -a | grep elearning-queue
af93b15197cd   elearning_cms-queue   "docker-php-entrypoi…"   X minutes ago   Restarting (126) X seconds ago   elearning-queue
```

Container có status `Restarting` với exit code `126` hoặc `1`.

### Logs lỗi

```bash
$ docker logs elearning-queue
/usr/local/bin/docker-php-entrypoint: 9: exec: ./start-queue.sh: Permission denied
/usr/local/bin/docker-php-entrypoint: 9: exec: ./start-queue.sh: Permission denied
...
```

Hoặc:

```bash
⏳ Đợi database khởi động...
⏳ Đợi database khởi động...
⏳ Đợi database khởi động...
# Loop vô hạn, không bao giờ chạy queue worker
```

## 🔎 Nguyên nhân

### Nguyên nhân 1: Permission Denied (Exit code 126)

**Vấn đề:** File `start-queue.sh` không có quyền thực thi trong container.

**Nguyên nhân:**
- File `start-queue.sh` được copy vào image nhưng không có quyền thực thi
- Dockerfile không set quyền thực thi cho file này

**Kiểm tra:**
```bash
$ ls -la start-queue.sh
-rw-rw-r-- 1 user user 298 Dec  8 16:48 start-queue.sh
# Thiếu quyền 'x' (execute)
```

### Nguyên nhân 2: Database Connection Failed (Exit code 1)

**Vấn đề:** Script `start-queue.sh` không thể kết nối database, loop vô hạn.

**Nguyên nhân:**
1. **Cấu hình `.env` sai:**
   - `DB_HOST=127.0.0.1` thay vì `DB_HOST=mysql` (tên service trong Docker)
   - `DB_PORT=3307` thay vì `DB_PORT=3306` (port trong container)
   - `DB_PASSWORD=` (trống) thay vì `DB_PASSWORD=root`

2. **Script kiểm tra database sai:**
   - Script dùng `php artisan migrate:status` để check database
   - Command này trả về exit code 1 khi chưa có migration table
   - Script loop vô hạn vì luôn nhận exit code 1

**Kiểm tra:**
```bash
# Kiểm tra cấu hình .env
$ grep "^DB_" .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1    # ❌ SAI - phải là 'mysql'
DB_PORT=3307         # ❌ SAI - phải là '3306'
DB_PASSWORD=         # ❌ SAI - phải là 'root'
```

## ✅ Giải pháp

### Fix 1: Thêm quyền thực thi cho start-queue.sh

**Cách 1: Sửa trong Dockerfile (Khuyến nghị)**

Thêm dòng sau vào `Dockerfile`:

```dockerfile
RUN composer install && chmod -R 775 storage bootstrap/cache
RUN chmod +x start-queue.sh  # ← Thêm dòng này
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memlimit.ini
```

**Cách 2: Set quyền trên host**

```bash
chmod +x start-queue.sh
```

Sau đó rebuild container:

```bash
docker compose -f docker-compose.dev.yml build queue
docker compose -f docker-compose.dev.yml up -d queue
```

### Fix 2: Sửa cấu hình database trong .env

**Bước 1: Sửa file `.env`**

```bash
# Sửa DB_HOST
sed -i 's/^DB_HOST=127.0.0.1/DB_HOST=mysql/' .env

# Sửa DB_PORT
sed -i 's/^DB_PORT=3307/DB_PORT=3306/' .env

# Sửa DB_PASSWORD
sed -i 's/^DB_PASSWORD=$/DB_PASSWORD=root/' .env
```

**Hoặc sửa thủ công:**

```env
DB_CONNECTION=mysql
DB_HOST=mysql          # ✅ Đúng - tên service trong docker-compose
DB_PORT=3306           # ✅ Đúng - port trong container
DB_DATABASE=vfl-academy
DB_USERNAME=root
DB_PASSWORD=root       # ✅ Đúng - password từ docker-compose.yml
```

**Bước 2: Clear config cache**

```bash
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
docker compose -f docker-compose.dev.yml exec queue php artisan config:clear
```

**Bước 3: Restart containers**

```bash
docker compose -f docker-compose.dev.yml restart queue
```

### Fix 3: Sửa script start-queue.sh

**Vấn đề:** Script dùng `migrate:status` để check database, nhưng command này fail khi chưa có migration table.

**Giải pháp:** Thay bằng kiểm tra kết nối PDO trực tiếp.

**File `start-queue.sh` cũ:**

```bash
#!/bin/sh
echo "⏳ Đợi database khởi động..."
until php artisan migrate:status > /dev/null 2>&1; do
  sleep 2
done
echo "✅ Database đã sẵn sàng, chạy queue worker..."
php artisan queue:work --tries=3 --timeout=90
```

**File `start-queue.sh` mới:**

```bash
#!/bin/sh

# Chờ đến khi database sẵn sàng (khoảng 60s tối đa)
echo "⏳ Đợi database khởi động..."
counter=0
max_attempts=30

# Kiểm tra kết nối database bằng cách test query đơn giản
until php -r "try { \$pdo = new PDO('mysql:host=mysql;port=3306;dbname=vfl-academy', 'root', 'root'); \$pdo->query('SELECT 1'); exit(0); } catch (Exception \$e) { exit(1); }" > /dev/null 2>&1 || [ $counter -ge $max_attempts ]; do
  sleep 2
  counter=$((counter + 1))
  if [ $((counter % 5)) -eq 0 ]; then
    echo "⏳ Đang đợi database... ($counter/$max_attempts)"
  fi
done

if [ $counter -ge $max_attempts ]; then
  echo "❌ Không thể kết nối database sau $max_attempts lần thử"
  exit 1
fi

echo "✅ Database đã sẵn sàng, chạy queue worker..."
php artisan queue:work --tries=3 --timeout=90
```

**Cải tiến:**
- ✅ Dùng PDO để test kết nối trực tiếp (không cần migration table)
- ✅ Có timeout (60 giây) để tránh loop vô hạn
- ✅ Có counter và log để dễ debug
- ✅ Exit với code 1 nếu không kết nối được sau nhiều lần thử

**Áp dụng fix:**

```bash
# 1. Sửa file start-queue.sh (đã sửa ở trên)
# 2. Set quyền thực thi
chmod +x start-queue.sh

# 3. Rebuild container
docker compose -f docker-compose.dev.yml build queue

# 4. Restart container
docker compose -f docker-compose.dev.yml restart queue
```

## 🧪 Kiểm tra sau khi fix

### 1. Kiểm tra container status

```bash
$ docker ps -a | grep elearning-queue
af93b15197cd   elearning_cms-queue   "docker-php-entrypoi…"   X minutes ago   Up X seconds   9000/tcp   elearning-queue
```

Status phải là `Up`, không phải `Restarting`.

### 2. Kiểm tra logs

```bash
$ docker logs elearning-queue --tail 20
⏳ Đợi database khởi động...
✅ Database đã sẵn sàng, chạy queue worker...
```

Logs phải hiển thị:
- ✅ "Database đã sẵn sàng"
- ✅ Queue worker đang chạy (không có lỗi)

### 3. Kiểm tra process

```bash
$ docker exec elearning-queue ps aux | grep queue
root         1  0.0  0.0   2388   748 ?        Ss   13:54   0:00 /bin/sh ./start-queue.sh
root        15  0.1  2.5 123456 12345 ?        S    13:54   0:05 php artisan queue:work --tries=3 --timeout=90
```

Phải thấy process `php artisan queue:work` đang chạy.

## 📝 Tóm tắt các bước fix

1. ✅ **Sửa Dockerfile:** Thêm `RUN chmod +x start-queue.sh`
2. ✅ **Sửa .env:** 
   - `DB_HOST=mysql` (không phải `127.0.0.1`)
   - `DB_PORT=3306` (không phải `3307`)
   - `DB_PASSWORD=root` (không để trống)
3. ✅ **Sửa start-queue.sh:** Dùng PDO test connection thay vì `migrate:status`
4. ✅ **Clear config cache:** `php artisan config:clear`
5. ✅ **Rebuild và restart:** `docker compose build queue && docker compose restart queue`

## 🔄 Prevention (Phòng ngừa)

Để tránh lỗi tương tự trong tương lai:

1. **Luôn set quyền thực thi trong Dockerfile:**
   ```dockerfile
   RUN chmod +x start-queue.sh
   ```

2. **Kiểm tra cấu hình .env trước khi chạy:**
   - `DB_HOST` phải là tên service trong docker-compose
   - `DB_PORT` phải là port trong container (không phải port exposed)

3. **Dùng healthcheck trong docker-compose:**
   ```yaml
   queue:
     healthcheck:
       test: ["CMD", "php", "artisan", "queue:work", "--help"]
       interval: 30s
       timeout: 10s
       retries: 3
   ```

4. **Test script trước khi commit:**
   ```bash
   # Test script trên host trước
   chmod +x start-queue.sh
   ./start-queue.sh
   ```

## 🐛 Bug 2: Nginx Container Không Áp Dụng Port Mapping Mới Sau Khi Thay Đổi

### 📋 Tóm tắt

Sau khi thay đổi port mapping trong `docker-compose.dev.yml` (ví dụ từ `80:80` sang `8081:80`), chỉ restart container nginx không đủ để áp dụng port mới. Container vẫn giữ port mapping cũ.

### 🔍 Mô tả lỗi

#### Triệu chứng

Sau khi sửa port trong `docker-compose.dev.yml`:
```yaml
nginx:
  ports:
    - "8081:80"  # Đã đổi từ "80:80"
```

Và chạy:
```bash
docker compose -f docker-compose.dev.yml restart nginx
```

**Kết quả:** Container vẫn expose port cũ, không thể truy cập qua port mới.

#### Kiểm tra

```bash
# Kiểm tra port mapping hiện tại
$ docker port elearning-nginx
80/tcp -> 0.0.0.0:80    # ❌ Vẫn là port 80, không phải 8081

# Kiểm tra container status
$ docker compose -f docker-compose.dev.yml ps
elearning-nginx   nginx:alpine   ...   0.0.0.0:80->80/tcp    # ❌ Port cũ
```

#### Test truy cập

```bash
# Truy cập port mới - FAIL
$ curl http://localhost:8081/api/ho
curl: (7) Failed to connect to localhost port 8081: Connection refused

# Truy cập port cũ - Vẫn hoạt động
$ curl http://localhost/api/ho
8  # ✅ Vẫn hoạt động
```

### 🔎 Nguyên nhân

**Vấn đề:** Port mapping được set khi container được **tạo** (create), không phải khi container được **khởi động** (start).

**Giải thích:**
- Khi chạy `docker compose restart`, Docker chỉ dừng và khởi động lại container hiện có
- Port mapping đã được bind khi container được tạo lần đầu
- Docker không đọc lại cấu hình từ `docker-compose.yml` khi restart
- Cần **recreate** container để Docker đọc lại cấu hình mới

**Flow:**
```
Create container → Bind port mapping → Start container
     ↑                    ↑
     └────────────────────┘
   Chỉ xảy ra khi CREATE, không phải RESTART
```

### ✅ Giải pháp

#### Cách 1: Stop → Remove → Up (Chi tiết)

```bash
# Bước 1: Dừng container
docker compose -f docker-compose.dev.yml stop nginx

# Bước 2: Xóa container (giữ lại volumes)
docker compose -f docker-compose.dev.yml rm -f nginx

# Bước 3: Tạo và khởi động lại container với cấu hình mới
docker compose -f docker-compose.dev.yml up -d nginx
```

#### Cách 2: Down → Up (Đơn giản nhất)

```bash
# Dừng và xóa tất cả containers
docker compose -f docker-compose.dev.yml down

# Khởi động lại tất cả với cấu hình mới
docker compose -f docker-compose.dev.yml up -d
```

**Lưu ý:** Cách này sẽ dừng tất cả containers, không chỉ nginx.

#### Cách 3: Force Recreate (Khuyến nghị)

```bash
# Recreate chỉ service nginx với cấu hình mới
docker compose -f docker-compose.dev.yml up -d --force-recreate nginx
```

**Ưu điểm:** Chỉ recreate service cần thiết, các service khác không bị ảnh hưởng.

### 🧪 Kiểm tra sau khi fix

#### 1. Kiểm tra port mapping

```bash
$ docker port elearning-nginx
80/tcp -> 0.0.0.0:8081    # ✅ Đúng port mới
80/tcp -> [::]:8081      # ✅ IPv6 cũng đúng
```

#### 2. Kiểm tra container status

```bash
$ docker compose -f docker-compose.dev.yml ps
elearning-nginx   nginx:alpine   ...   0.0.0.0:8081->80/tcp    # ✅ Port mới
```

#### 3. Test truy cập

```bash
# Test API
$ curl http://localhost:8081/api/ho
8  # ✅ Hoạt động

# Test CMS
$ curl -I http://localhost:8081/
HTTP/1.1 302 Found  # ✅ Hoạt động (redirect)
```

### 📝 Tóm tắt

**Vấn đề:** Restart container không áp dụng port mapping mới từ docker-compose.yml

**Nguyên nhân:** Port mapping được bind khi container được tạo, không phải khi restart

**Giải pháp:** Cần recreate container (stop + remove + up) hoặc dùng `--force-recreate`

**Lệnh khuyến nghị:**
```bash
docker compose -f docker-compose.dev.yml up -d --force-recreate nginx
```

### 🔄 Prevention (Phòng ngừa)

1. **Luôn dùng `--force-recreate` khi thay đổi port:**
   ```bash
   docker compose -f docker-compose.dev.yml up -d --force-recreate nginx
   ```

2. **Hoặc down và up lại:**
   ```bash
   docker compose -f docker-compose.dev.yml down
   docker compose -f docker-compose.dev.yml up -d
   ```

3. **Kiểm tra port mapping sau khi thay đổi:**
   ```bash
   docker port <container-name>
   ```

4. **Test ngay sau khi thay đổi:**
   ```bash
   curl http://localhost:<new-port>/api/ho
   ```

### ⚠️ Lưu ý

- **Không dùng `restart`** khi thay đổi port mapping
- **Không dùng `start`** sau khi sửa docker-compose.yml
- **Luôn dùng `up -d`** hoặc `up -d --force-recreate` để áp dụng cấu hình mới

## 📚 Tài liệu tham khảo

- [Docker Exit Codes](https://docs.docker.com/engine/reference/run/#exit-status)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Docker Compose Healthcheck](https://docs.docker.com/compose/compose-file/compose-file-v3/#healthcheck)
- [Docker Compose Up Command](https://docs.docker.com/compose/reference/up/)
- [Docker Port Mapping](https://docs.docker.com/config/containers/container-networking/)

