#!/usr/bin/env sh
set -e

# Wait for a service to be ready (host:port)
wait_for() {
  host="$1"; shift
  port="$1"; shift
  timeout=${1:-60}
  while ! nc -z "$host" "$port"; do
    timeout=$((timeout - 1))
    if [ "$timeout" -le 0 ]; then
      echo "Timed out waiting for $host:$port"
      exit 1
    fi
    sleep 1
  done
}

# Wait for mysql and redis
if [ -n "$DB_HOST" ]; then
  wait_for "$DB_HOST" "$DB_PORT"
fi

if [ -n "$REDIS_HOST" ]; then
  wait_for "$REDIS_HOST" "6379"
fi

# If vendor missing, install
if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Set correct permissions
chown -R www:www /var/www/html

# Run migrations and optionally seed
php artisan migrate --force || true
if [ "$RUN_SEEDERS" = "true" ]; then
  php artisan db:seed --force || true
fi

# Cache config in production
if [ "$APP_ENV" = "production" ]; then
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi

# Start php-fpm
php-fpm
