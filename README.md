Cars & Inspections API — Production-Grade Laravel 11 REST API

[![CI](https://github.com/OWNER/REPO/actions/workflows/ci.yml/badge.svg)](https://github.com/OWNER/REPO/actions/workflows/ci.yml)

A production-grade Laravel 11 REST API for managing vehicles and their inspections. Features include Laravel Sanctum authentication, Horizon-based asynchronous job processing, comprehensive test coverage, Docker Compose deployment, and OpenAPI/Swagger documentation.

## Quick Start (Docker)

### Setup

Start the entire stack with a single command:

```bash
./setup.sh
```

This script will:
- ✅ Copy `.env.example` to `.env` (if needed)
- ✅ Build Docker images
- ✅ Start all services (app, MySQL, Redis, Nginx, Horizon)
- ✅ Run database migrations
- ✅ Seed demo data

**Endpoints:**
- 🌐 **API Base**: http://localhost:8080/api/v1
- 📚 **Swagger UI**: http://localhost:8080/docs/
- 🔑 **Demo User**: `demo@example.com` / `password`

### Teardown

Stop all services and remove containers:

```bash
./setdown.sh
```

This removes containers but preserves volumes. To also remove data, run:

```bash
docker compose down -v
```

## Running Tests

Use the convenient `test.sh` script for all testing needs:

```bash
./test.sh                                  # Full test suite
./test.sh --coverage                       # With coverage report
./test.sh --file tests/Feature/CarApiTest.php   # Specific test file
./test.sh --filter "AuthApiTest"          # Tests matching filter
./test.sh --local                         # Run locally (no Docker)
./test.sh --watch                         # Watch mode (local only)
./test.sh --help                          # Show all options
```

### Test Output

The test suite includes 9 feature tests covering:
- ✅ User authentication (Sanctum token issuance)
- ✅ Car CRUD operations (list, create async via Horizon)
- ✅ Inspection CRUD operations (list, create)
- ✅ Authorization middleware enforcement
- ✅ API response envelope format validation

All tests use SQLite in-memory database for isolation and speed.

### Manual Test Commands

If you prefer manual commands without the script:

**In Docker:**
```bash
docker exec cars_app ./vendor/bin/phpunit
docker exec cars_app ./vendor/bin/phpunit tests/Feature/AuthApiTest.php
docker exec cars_app ./vendor/bin/phpunit --coverage-html=coverage/
```

**Locally (requires PHP 8.4+, Composer, MySQL 8.0+, Redis 7+):**
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
./vendor/bin/phpunit
```

## Code Quality & PSR Compliance

### PSR-4 & PSR-12 Compliance

Check and fix code style issues using PHP CS Fixer:

```bash
./lint.sh                  # Check style violations (dry-run)
./lint.sh --fix            # Automatically fix code style
```

**Enabled Rules:**
- PSR-12 coding standards compliance
- PSR-4 autoloading compliance
- Import organization and ordering
- Type hints and return type declarations
- PHPDoc formatting and consistency
- Arrow functions and modern PHP syntax
- Strict comparison operators
- Unused import removal

**Last Run:** 31 files fixed and compliant with PSR-4/PSR-12 standards

## Architecture

### Services

- **App (PHP 8.4-FPM)**: Laravel 11 API with Sanctum authentication
- **Nginx (1.25-Alpine)**: Reverse proxy and static file server for Swagger UI
- **MySQL 8.0**: Primary database for cars, inspections, and users
- **Redis 7**: Cache store and queue broker for Horizon
- **Horizon**: Background job processor (CreateCarJob for async vehicle creation)

### Design Patterns

#### Repository Pattern

The repository pattern abstracts data access logic and provides a consistent interface for database operations.

**Structure:**
```
app/Repositories/
├── CarRepository.php              # Interface
└── Eloquent/
    └── EloquentCarRepository.php  # Implementation
```

**Usage:**
```php
class CarService {
    public function __construct(private CarRepository $carRepository) {}
    
    public function getAllPaginated($perPage = 15) {
        return $this->carRepository->paginate($perPage);
    }
}
```

**Benefits:**
- Decouples business logic from data persistence
- Easy to swap implementations (MongoDB, API client, etc.)
- Testable with mock repositories
- Single responsibility principle

#### Service Layer Pattern

The service layer encapsulates business logic and coordinates between repositories, jobs, and other services.

**Location:** `app/Services/CarService.php`

**Current Services:**
- `createAsync()` - Dispatch async creation job to Horizon
- `createSync()` - Create car synchronously
- `getAllPaginated()` - Paginate cars from repository
- `getById()` - Fetch single car
- `update()` - Update car record
- `delete()` - Delete car record

**Benefits:**
- Centralized business logic
- Reusable across controllers, commands, and events
- Easy to modify without touching controllers
- Clear separation of concerns

#### Data Flow

```
Request → Controller → Service → Repository → Model → Database
                          ↓
                      Job Dispatch (Horizon)
```

### Test Coverage

9 feature tests covering:
- ✅ User authentication (Sanctum tokens)
- ✅ Car CRUD operations (list, create async)
- ✅ Inspection CRUD operations (list, create)
- ✅ Authorization middleware
- ✅ API response envelope format

## API Documentation

### Authentication

**Issue Bearer Token:**

```bash
curl -X POST http://localhost:8080/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "demo@example.com",
    "password": "password"
  }'
```

Response:
```json
{
  "success": true,
  "message": "Token issued",
  "data": {
    "token": "YOUR_BEARER_TOKEN"
  }
}
```

### Protected Endpoints

Include bearer token in `Authorization` header:

```bash
curl -X GET http://localhost:8080/api/v1/cars \
  -H "Authorization: Bearer YOUR_BEARER_TOKEN"
```

**Available Endpoints:**

- `GET /api/v1/cars` - List all vehicles
- `POST /api/v1/cars` - Create vehicle (async via Horizon)
- `GET /api/v1/inspections` - List all inspections
- `POST /api/v1/inspections` - Create inspection

### Response Format

All responses follow standard envelope:

```json
{
  "success": true|false,
  "message": "Human-readable message",
  "data": {...},
  "errors": []
}
```

## Local Development (without Docker)

### Prerequisites

- PHP 8.4 or higher
- Composer 2.2+
- MySQL 8.0+
- Redis 7+

### Setup

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Start services (MySQL, Redis must be running separately)
php artisan migrate
php artisan db:seed

# Run tests
./vendor/bin/phpunit

# Start development servers in separate terminals
php artisan serve                    # API on :8000
php artisan horizon                 # Job processor
```

## Build Progress

- [x] Core app scaffolding (models, migrations, factories, seeders)
- [x] API controllers with Sanctum authentication
- [x] Async job processing via Horizon
- [x] Comprehensive test suite (9 tests, 14 assertions)
- [x] Docker Compose stack (app, MySQL, Redis, Nginx, Horizon)
- [x] OpenAPI/Swagger documentation
- [x] Setup and teardown scripts
- [x] Test runner script with multiple modes
- [x] PHP CS Fixer for PSR-4/PSR-12 compliance checking
