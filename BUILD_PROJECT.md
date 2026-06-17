# 🎓 E-Learning CMS Admin – Laravel 11

Đây là mã nguồn back-end quản lý nội dung (CMS) cho một nền tảng học trực tuyến. Dự án sử dụng Laravel 11, cho phép quản trị viên thêm/sửa/xoá khóa học, bài học và thực hiện upload video lên Vimeo thông qua hàng đợi (`Queue Job`).

## 🚀 Công nghệ sử dụng

- Laravel 11 (PHP 8.2)
- MySQL 8.0
- Nginx
- Docker & Docker Compose
- Vimeo API
- VNPAY
- Laravel Queue Job

## 📋 Yêu cầu hệ thống

- Docker Engine 20.10+
- Docker Compose 2.0+ (hoặc docker-compose 1.29+)
- Git

## 🐳 Cài đặt và chạy với Docker (Khuyến nghị)

### Bước 1: Clone repository

```bash
git clone https://github.com/Kamadee/Elearning_CMS
cd Elearning_CMS
```

### Bước 2: Tạo file `.env`

Tạo file `.env` từ `.env.example` và cấu hình:

```bash
cp .env.example .env
```

**Cấu hình database trong `.env` (quan trọng):**

```env
DB_CONNECTION=mysql
DB_HOST=mysql          # Tên service trong docker-compose, KHÔNG dùng 127.0.0.1
DB_PORT=3306           # Port trong container, KHÔNG dùng 3307
DB_DATABASE=vfl-academy
DB_USERNAME=root
DB_PASSWORD=root
```

### Bước 3: Build và khởi động containers

```bash
# Build và chạy tất cả containers
docker compose -f docker-compose.dev.yml up -d --build
```

**Lưu ý:** Dự án sử dụng `docker-compose.dev.yml` cho môi trường development. File này không chứa các cấu hình SSL của production.

### Bước 4: Cấu hình Laravel

```bash
# Tạo APP_KEY (nếu chưa có)
docker compose -f docker-compose.dev.yml exec app php artisan key:generate

# Chạy migrations
docker compose -f docker-compose.dev.yml exec app php artisan migrate --force

# Chạy seeders (tạo dữ liệu mẫu)
docker compose -f docker-compose.dev.yml exec app php artisan db:seed

# Tạo symlink cho storage (nếu cần)
docker compose -f docker-compose.dev.yml exec app php artisan storage:link
```

**Lưu ý về Seeders:**
- Lệnh trên sẽ chạy tất cả seeders được định nghĩa trong `DatabaseSeeder.php`
- Để chạy seeder cụ thể: `docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=UserSeeder`
- Để refresh database và seed lại: `docker compose -f docker-compose.dev.yml exec app php artisan migrate:fresh --seed --force`

### Bước 5: Build Vite Assets (Quan trọng)

Laravel sử dụng Vite để build CSS/JS. Cần build assets trước khi sử dụng:

**Cách 1: Build trên host (Khuyến nghị cho dev)**

```bash
# Cài đặt Node.js dependencies
pnpm install

# Build assets cho production
pnpm run build

# Hoặc chạy dev server (tự động rebuild khi có thay đổi)
pnpm run dev
```

**Cách 2: Build trong Docker container (nếu container có Node.js)**

Nếu Dockerfile đã có Node.js, có thể build trong container:

```bash
docker compose -f docker-compose.dev.yml exec app pnpm install
docker compose -f docker-compose.dev.yml exec app pnpm run build
```

**Lưu ý:**
- Sau khi build, file `public/build/manifest.json` sẽ được tạo
- Nếu không build, sẽ gặp lỗi "Vite manifest not found"
- Trong môi trường dev, có thể dùng `pnpm run dev` để tự động rebuild

### Bước 6: Cài đặt dependencies (nếu cần)

Do volume mount, có thể cần cài lại composer trong container:

```bash
docker compose -f docker-compose.dev.yml exec app composer install
```

### Bước 7: Kiểm tra containers

```bash
# Xem trạng thái tất cả containers
docker compose -f docker-compose.dev.yml ps

# Xem logs
docker compose -f docker-compose.dev.yml logs -f
```

## 🌐 Truy cập ứng dụng

### API (cho Frontend)
- **API Base URL:** `http://localhost:8081/api`
- **Test endpoint:** `http://localhost:8081/api/ho` (sẽ trả về `8`)

### CMS Admin Panel (cho Admin)
- **CMS URL:** `http://localhost:8081`
- **Login page:** `http://localhost:8081/auth/login`
- **Dashboard:** `http://localhost:8081/home` (sau khi login)

### Database
- **Host:** `localhost:3307`
- **User:** `root`
- **Password:** `root`
- **Database:** `vfl-academy`

## 📝 Các lệnh Docker hữu ích

### Quản lý containers

```bash
# Dừng tất cả containers
docker compose -f docker-compose.dev.yml stop

# Dừng và xóa containers
docker compose -f docker-compose.dev.yml down

# Dừng và xóa containers + volumes (xóa database)
docker compose -f docker-compose.dev.yml down -v

# Khởi động lại containers
docker compose -f docker-compose.dev.yml restart

# Rebuild lại containers
docker compose -f docker-compose.dev.yml up -d --build
```

### Xem logs

```bash
# Logs của tất cả services
docker compose -f docker-compose.dev.yml logs -f

# Logs của service cụ thể
docker compose -f docker-compose.dev.yml logs -f app
docker compose -f docker-compose.dev.yml logs -f nginx
docker compose -f docker-compose.dev.yml logs -f queue
docker compose -f docker-compose.dev.yml logs -f mysql
```

### Chạy Artisan commands

```bash
# Vào container app
docker compose -f docker-compose.dev.yml exec app bash

# Chạy artisan commands
docker compose -f docker-compose.dev.yml exec app php artisan migrate
docker compose -f docker-compose.dev.yml exec app php artisan cache:clear
docker compose -f docker-compose.dev.yml exec app php artisan config:clear

# Chạy seeders
docker compose -f docker-compose.dev.yml exec app php artisan db:seed
docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=UserSeeder
docker compose -f docker-compose.dev.yml exec app php artisan migrate:fresh --seed --force
```

**Lưu ý:** Trong lệnh `docker compose exec app`, `app` là **tên service** (service name) trong docker-compose.yml, không phải tên container. Service name `app` tương ứng với container name `elearning-app`.

### Rebuild service cụ thể

```bash
# Rebuild chỉ service app
docker compose -f docker-compose.dev.yml build app

# Rebuild và restart service
docker compose -f docker-compose.dev.yml up -d --build app
```

## 🔧 Cấu trúc Docker

### Services

1. **app** - PHP-FPM application container
   - Port: `8000:8000`
   - Image: Built from `Dockerfile`
   - Command: `php-fpm`

2. **nginx** - Nginx web server
   - Port: `8081:80` (external:internal)
   - Config: `docker-compose/nginx/nginx.dev.conf`
   - Serves: `/var/www/public`
   - **Lưu ý:** Port 8081 được dùng để tránh cần quyền root (port 80 cần sudo)

3. **mysql** - MySQL 8.0 database
   - Port: `3307:3306` (external:internal)
   - Database: `vfl-academy`
   - Volume: `mysql_data` (persistent storage)

4. **queue** - Laravel queue worker
   - Command: `./start-queue.sh`
   - Xử lý background jobs (upload video lên Vimeo, etc.)

### Volumes

- `mysql_data` - Persistent storage cho MySQL database
- `.` (project root) - Mount vào `/var/www` trong containers

## 🔌 Cấu hình Frontend

### Base URL cho API

```javascript
// Ví dụ với axios
const api = axios.create({
  baseURL: 'http://localhost:8081/api',
  headers: {
    'Content-Type': 'application/json',
  }
});
```

### CORS Configuration

CORS đã được cấu hình trong `config/cors.php` cho:
- `http://localhost:5173` (Vite dev server)
- `https://elearning-landing.netlify.app` (Production)

Nếu frontend chạy ở port khác, thêm vào `config/cors.php`:

```php
'allowed_origins' => [
    'http://localhost:5173',
    'http://localhost:3000',  // Thêm port khác nếu cần
],
```

## ⚙️ Cài đặt local (không dùng Docker)

Nếu không muốn dùng Docker, có thể cài đặt trực tiếp:

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
```

## 🐛 Troubleshooting

Nếu gặp lỗi, xem file [BUG_DOC.md](./BUG_DOC.md) để biết các lỗi thường gặp và cách khắc phục.

### Lỗi thường gặp

1. **Permission denied cho `start-queue.sh`**
   - Đảm bảo file có quyền thực thi: `chmod +x start-queue.sh`
   - Hoặc rebuild container: `docker compose -f docker-compose.dev.yml build queue`

2. **Database connection failed**
   - Kiểm tra `DB_HOST=mysql` (không phải `127.0.0.1`)
   - Kiểm tra `DB_PORT=3306` (không phải `3307`)
   - Clear config cache: `docker compose -f docker-compose.dev.yml exec app php artisan config:clear`

3. **Container queue bị restart liên tục**
   - Xem logs: `docker compose -f docker-compose.dev.yml logs queue`
   - Kiểm tra database connection
   - Đảm bảo migrations đã chạy
   - Xem chi tiết trong [BUG_DOC.md](./BUG_DOC.md)

## 🚀 CI/CD và Deployment

### Tại sao cần CI/CD?

**Không có CI/CD (Deployment thủ công):**
- ❌ Phải SSH vào server mỗi lần deploy
- ❌ Chạy từng lệnh thủ công: `git pull`, `docker build`, `docker compose up`, etc.
- ❌ Dễ quên bước, dễ sai sót
- ❌ Mất thời gian, không tự động
- ❌ Khó rollback khi có lỗi

**Có CI/CD (Tự động):**
- ✅ Tự động build và deploy khi push code
- ✅ Chạy tests trước khi deploy
- ✅ Deploy nhất quán, không thiếu bước
- ✅ Tiết kiệm thời gian
- ✅ Dễ rollback về version trước

### Các công cụ CI/CD phổ biến

1. **GitHub Actions** (Khuyến nghị cho GitHub)
   - Miễn phí cho public repos
   - Tích hợp sẵn với GitHub
   - Dễ setup

2. **GitLab CI/CD**
   - Tích hợp với GitLab
   - Runner tự host hoặc dùng GitLab.com

3. **Jenkins**
   - Self-hosted
   - Linh hoạt, mạnh mẽ
   - Phức tạp hơn

4. **CircleCI, Travis CI**
   - Cloud-based
   - Dễ sử dụng

### GitHub Actions Workflow (Ví dụ)

Tạo file `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - main  # Chỉ deploy khi push vào branch main
  workflow_dispatch:  # Cho phép chạy thủ công

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
      
      - name: Setup SSH
        uses: webfactory/ssh-agent@v0.7.0
        with:
          ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}
      
      - name: Deploy to server
        run: |
          ssh ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }} << 'EOF'
            cd /path/to/your/project
            git pull origin main
            docker compose -f docker-compose.yml down
            docker compose -f docker-compose.yml build --no-cache
            docker compose -f docker-compose.yml up -d
            docker compose -f docker-compose.yml exec app php artisan migrate --force
            docker compose -f docker-compose.yml exec app php artisan config:clear
            docker compose -f docker-compose.dev.yml exec app php artisan cache:clear
          EOF
```

  **Cấu hình SSH Key và Secrets:**

### Bước 1: Tạo SSH Key Pair

Trên máy local hoặc server, tạo SSH key pair:

```bash
# Tạo SSH key (nếu chưa có)
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy

# Hoặc dùng RSA (nếu ed25519 không được hỗ trợ)
ssh-keygen -t rsa -b 4096 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy
```

Sau khi tạo, bạn sẽ có 2 files:
- `~/.ssh/github_actions_deploy` (private key) - **Giữ bí mật!**
- `~/.ssh/github_actions_deploy.pub` (public key) - Có thể chia sẻ

### Bước 2: Thêm Public Key vào Server

Copy public key lên server:

```bash
# Copy public key lên server
ssh-copy-id -i ~/.ssh/github_actions_deploy.pub user@your-server.com

# Hoặc thủ công:
cat ~/.ssh/github_actions_deploy.pub
# Copy output và thêm vào server: ~/.ssh/authorized_keys
```

### Bước 3: Thêm Secrets vào GitHub

1. Vào repository trên GitHub → **Settings** → **Secrets and variables** → **Actions**
2. Click **New repository secret**
3. Thêm các secrets sau:

   - **`SSH_PRIVATE_KEY`**: 
     ```bash
     # Copy nội dung private key
     cat ~/.ssh/github_actions_deploy
     # Copy toàn bộ output (bao gồm cả -----BEGIN và -----END)
     ```
     Paste vào GitHub Secret

   - **`SSH_USER`**: Username để SSH vào server (ví dụ: `ubuntu`, `root`)
   
   - **`SSH_HOST`**: IP hoặc domain của server (ví dụ: `123.45.67.89` hoặc `yourdomain.com`)
   
   - **`PROJECT_PATH`**: (Tùy chọn) Đường dẫn project trên server. Mặc định: `/home/ubuntu/elearning`
   
   - **`DB_PASSWORD`**: (Tùy chọn) Password MySQL để backup database trước khi deploy

**Lưu ý quan trọng:**
- ⚠️ **KHÔNG BAO GIỜ** commit private key vào Git
- ⚠️ Private key phải được giữ bí mật
- ✅ Chỉ thêm vào GitHub Secrets (được mã hóa)
- ✅ Public key có thể public (không sao)

### Workflow chi tiết hơn (với tests)

Xem file `.github/workflows/deploy.yml` trong project để có workflow đầy đủ với:
- Chạy tests trước khi deploy
- Build Docker image
- Push image lên registry (nếu cần)
- Deploy lên server
- Health check sau deploy
- Rollback tự động nếu fail

### Deployment trên Server Production

**Cấu trúc thư mục trên server:**
```
/home/ubuntu/elearning/
├── docker-compose.yml          # Production config
├── .env                        # Production environment variables
├── docker-compose/nginx/
│   └── nginx.conf             # Production nginx config
└── ...
```

**Lệnh deploy thủ công (nếu không dùng CI/CD):**
```bash
# SSH vào server
ssh user@your-server.com

# Vào thư mục project
cd /home/ubuntu/elearning

# Pull code mới
git pull origin main

# Rebuild và restart containers
docker compose -f docker-compose.yml down
docker compose -f docker-compose.yml build --no-cache
docker compose -f docker-compose.yml up -d

# Chạy migrations
docker compose -f docker-compose.yml exec app php artisan migrate --force

# Clear cache
docker compose -f docker-compose.yml exec app php artisan config:clear
docker compose -f docker-compose.yml exec app php artisan cache:clear
docker compose -f docker-compose.yml exec app php artisan route:clear
docker compose -f docker-compose.yml exec app php artisan view:clear
```

### Khác biệt giữa Dev và Production

| Aspect | Development | Production |
|--------|-------------|------------|
| **File compose** | `docker-compose.dev.yml` | `docker-compose.yml` |
| **Nginx config** | `nginx.dev.conf` | `nginx.conf` |
| **Port** | `8081:80` | `80:80`, `443:443` |
| **SSL** | Không có | Có (Let's Encrypt) |
| **Debug** | `APP_DEBUG=true` | `APP_DEBUG=false` |
| **Environment** | `APP_ENV=local` | `APP_ENV=production` |
| **Volume mount** | Code mount trực tiếp | Code trong image |

### Best Practices cho CI/CD

1. **Chỉ deploy từ branch `main` hoặc `production`**
2. **Chạy tests trước khi deploy**
3. **Build Docker image với tag version (git commit hash)**
4. **Health check sau deploy**
5. **Rollback strategy nếu deploy fail**
6. **Notify team khi deploy thành công/thất bại**
7. **Backup database trước khi chạy migrations**

### Tài liệu tham khảo CI/CD

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [GitLab CI/CD Documentation](https://docs.gitlab.com/ee/ci/)
- [Docker Compose Production](https://docs.docker.com/compose/production/)

## 📚 Tài liệu tham khảo

- [Laravel Documentation](https://laravel.com/docs)
- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
