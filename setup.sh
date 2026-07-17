#!/usr/bin/env bash

# setup.sh - Initialize and start the Cars & Inspections API with Docker Compose
# Usage: ./setup.sh

set -e

echo "🚀 Setting up Cars & Inspections API..."
echo ""

# Check if docker and docker-compose are available
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Copy .env if not present
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
else
    echo "✓ .env file already exists"
fi

# Build and start services
echo ""
echo "🔨 Building Docker images and starting services..."
docker compose up -d --build

# Wait for services to be ready
echo ""
echo "⏳ Waiting for services to be ready..."
sleep 3

# Check if MySQL is ready
echo "🗄️  Waiting for MySQL..."
until docker exec cars_mysql mysqladmin ping -hlocalhost -ularavel -psecret &> /dev/null; do
    echo "   Retrying MySQL connection..."
    sleep 2
done
echo "   ✓ MySQL is ready"

# Check if Redis is ready
echo "📦 Checking Redis..."
docker exec cars_redis redis-cli ping > /dev/null 2>&1
echo "   ✓ Redis is ready"

# Run database migrations
echo ""
echo "🗄️  Running database migrations..."
docker exec cars_app php artisan migrate --force

# Run database seeders
echo ""
echo "🌱 Seeding database..."
docker exec cars_app php artisan db:seed --force

echo ""
echo "✅ Setup complete!"
echo ""
echo "📚 Available endpoints:"
echo "  • API: http://localhost:8080/api/v1"
echo "  • Swagger UI: http://localhost:8080/docs/"
echo ""
echo "🔑 Demo credentials:"
echo "  • Email: demo@example.com"
echo "  • Password: password"
echo ""
echo "🧪 Run tests with: docker exec cars_app ./vendor/bin/phpunit"
echo "🛑 Stop services with: ./setdown.sh"
