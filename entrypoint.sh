#!/bin/sh

if [ "$APP_ROLE" = "worker" ]; then
  echo "Running Laravel queue worker..."
  php artisan queue:work
else
  echo "Running Laravel web server..."
  php artisan serve --host=0.0.0.0 --port=8000
fi
