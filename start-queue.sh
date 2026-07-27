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

echo "✅ Database đã sẵn sàng, chạy queue worker với Redis..."
php -d memory_limit=1G artisan queue:work redis --tries=3 --timeout=300
