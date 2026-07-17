#!/usr/bin/env bash
set -e

# Bootstrap script to create Laravel project and install required packages.
# Run from repository root.

PROJECT_DIR="."

if [ ! -d "$PROJECT_DIR/vendor" ]; then
  echo "Installing composer dependencies (if Laravel app exists, this installs packages)"
  composer install --prefer-dist --no-interaction || true
fi

# If no artisan, create project (note: composer must be installed locally)
if [ ! -f "artisan" ]; then
  echo "Creating Laravel project in place (this may take a few minutes)"
  composer create-project laravel/laravel "$PROJECT_DIR" --prefer-dist --no-interaction
fi

cd "$PROJECT_DIR"

# Require packages
composer require laravel/horizon laravel/sanctum predis/predis --no-interaction || true

# Publish Horizon & Sanctum
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider" --tag=config || true
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag=config || true
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag=migrations || true

# Copy example env if missing
if [ ! -f ".env" ]; then
  cp .env.example .env
  php artisan key:generate
fi

# Run migrations
php artisan migrate --force || true

# Seed demo user (if seeder present)
if php artisan db:seed --class=UserSeeder --force >/dev/null 2>&1; then
  echo "Seeded demo user"
fi

echo "Bootstrap complete. You can now:"
cat <<'EOF'
  docker-compose up -d --build
  # or run locally
  php artisan serve
  php artisan horizon
EOF
