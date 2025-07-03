#!/bin/sh

# Chờ đến khi database sẵn sàng (khoảng 30s tối đa)
echo "⏳ Đợi database khởi động..."
until php artisan migrate:status > /dev/null 2>&1; do
  sleep 2
done

echo "✅ Database đã sẵn sàng, chạy queue worker..."
php artisan queue:work --tries=3 --timeout=90
