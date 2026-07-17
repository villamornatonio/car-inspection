# Architecture & Design Patterns

## Overview

This Laravel API follows clean architecture principles using the **Repository Pattern** and **Service Layer Pattern** to maintain clean, testable, and maintainable code.

## Directory Structure

```
app/
├── Repositories/
│   ├── CarRepository.php                 # Interface for car data access
│   └── Eloquent/
│       └── EloquentCarRepository.php    # Eloquent implementation
├── Services/
│   └── CarService.php                   # Business logic layer
├── Http/
│   ├── Controllers/
│   │   └── Api/V1/
│   │       ├── AuthController.php
│   │       ├── CarController.php       # Thin controller (uses service)
│   │       └── InspectionController.php
│   ├── Requests/
│   │   ├── StoreCarRequest.php
│   │   └── ...
│   └── Resources/
│       ├── CarResource.php
│       └── ...
├── Models/
│   ├── Car.php
│   ├── Inspection.php
│   └── User.php
├── Jobs/
│   └── CreateCarJob.php               # Async job handler
├── Providers/
│   ├── AppServiceProvider.php         # Binds repositories
│   └── ...
└── Traits/
    └── ApiResponse.php                # Response formatting
```

## Design Patterns

### 1. Repository Pattern

**Purpose:** Abstract data access logic from business logic.

#### Interface (Contract)

File: `app/Repositories/CarRepository.php`

```php
interface CarRepository {
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Car;
    public function create(array $data): Car;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
```

#### Implementation

File: `app/Repositories/Eloquent/EloquentCarRepository.php`

```php
class EloquentCarRepository implements CarRepository {
    public function __construct(private Car $model) {}
    
    public function paginate(int $perPage = 15): LengthAwarePaginator {
        return $this->model->query()->paginate($perPage);
    }
    
    // ... other methods
}
```

#### Service Provider Binding

File: `app/Providers/AppServiceProvider.php`

```php
public function register(): void {
    $this->app->bind(CarRepository::class, EloquentCarRepository::class);
}
```

#### Benefits

- **Decoupling:** Business logic doesn't depend on Eloquent directly
- **Testability:** Easy to mock in tests
- **Flexibility:** Swap implementations without changing service/controller
- **Maintainability:** Changes to ORM logic stay in repository

**Example Use Case:** Switching from MySQL to MongoDB requires only updating `EloquentCarRepository`, no controller/service changes needed.

### 2. Service Layer Pattern

**Purpose:** Encapsulate business logic and orchestrate between repositories, jobs, and other services.

#### Service Class

File: `app/Services/CarService.php`

```php
class CarService {
    public function __construct(private CarRepository $carRepository) {}
    
    public function createAsync(array $data): array {
        $trackingId = (string) Str::uuid();
        
        // Business logic: dispatch job to queue
        CreateCarJob::dispatch($data, $trackingId)->onQueue('cars');
        
        return [
            'trackingId' => $trackingId,
            'status' => 'queued',
            'payload' => $data,
        ];
    }
    
    public function getAllPaginated(int $perPage = 15) {
        return $this->carRepository->paginate($perPage);
    }
}
```

#### Lean Controller

File: `app/Http/Controllers/Api/V1/CarController.php`

```php
class CarController extends Controller {
    public function __construct(private CarService $carService) {}
    
    public function store(StoreCarRequest $request) {
        $payload = $request->validated();
        $result = $this->carService->createAsync($payload);
        
        return $this->accepted($result);
    }
    
    public function index(Request $request) {
        $cars = $this->carService->getAllPaginated(15);
        return $this->success(CarResource::collection($cars));
    }
}
```

#### Benefits

- **Reusability:** Service can be used from controllers, CLI commands, events, etc.
- **Testability:** Services are easy to unit test
- **Centralization:** All car business logic in one place
- **Maintainability:** Modify business logic without touching controllers

### Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                        HTTP Request                         │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────────┐
        │        Controller                  │
        │ - Validate request                 │
        │ - Call service method              │
        │ - Format response                  │
        └────────┬─────────────────────────┬─┘
                 │                         │
                 ▼                         ▼
    ┌──────────────────────┐  ┌─────────────────────┐
    │   CarService         │  │  ApiResponse Trait  │
    │ - createAsync()      │  │  - success()        │
    │ - createSync()       │  │  - error()          │
    │ - getAllPaginated()  │  │  - accepted()       │
    └──────────┬───────────┘  └─────────────────────┘
               │
       ┌───────┴──────────────────┐
       │                          │
       ▼                          ▼
┌──────────────────┐    ┌────────────────────┐
│ CarRepository    │    │  CreateCarJob      │
│ - paginate()     │    │  (Async Handler)   │
│ - find()         │    │                    │
│ - create()       │    │  Dispatched to     │
│ - update()       │    │  Horizon Queue     │
│ - delete()       │    │                    │
└──────────┬───────┘    └────────────────────┘
           │                     │
           ▼                     ▼
    ┌──────────────┐      ┌──────────────┐
    │ Car Model    │      │ Redis Queue  │
    │ (Eloquent)   │      │ (Horizon)    │
    └──────┬───────┘      └──────┬───────┘
           │                     │
           ▼                     ▼
    ┌──────────────┐      ┌──────────────┐
    │   MySQL      │      │  Job Worker  │
    │  Database    │      │  Processing  │
    └──────────────┘      └──────────────┘
```

## Adding New Features

### Example: Add InspectionService

1. **Create Repository Interface**
   ```php
   // app/Repositories/InspectionRepository.php
   interface InspectionRepository {
       public function create(array $data): Inspection;
       public function findByCar(int $carId): Collection;
       // ...
   }
   ```

2. **Create Repository Implementation**
   ```php
   // app/Repositories/Eloquent/EloquentInspectionRepository.php
   class EloquentInspectionRepository implements InspectionRepository {
       // ...
   }
   ```

3. **Register in ServiceProvider**
   ```php
   // app/Providers/AppServiceProvider.php
   $this->app->bind(InspectionRepository::class, EloquentInspectionRepository::class);
   ```

4. **Create Service**
   ```php
   // app/Services/InspectionService.php
   class InspectionService {
       public function __construct(private InspectionRepository $repo) {}
       // Business logic here
   }
   ```

5. **Use in Controller**
   ```php
   class InspectionController extends Controller {
       public function __construct(private InspectionService $service) {}
       // Use service methods
   }
   ```

## Testing

### Repository Testing

Mock the repository in tests:

```php
$mockRepository = Mockery::mock(CarRepository::class);
$mockRepository->shouldReceive('paginate')->andReturn($carCollection);

$service = new CarService($mockRepository);
$result = $service->getAllPaginated(15);
```

### Service Testing

Test business logic independently:

```php
public function test_create_async_returns_tracking_id() {
    $service = new CarService($this->carRepository);
    $result = $service->createAsync(['name' => 'Toyota']);
    
    $this->assertArrayHasKey('trackingId', $result);
    $this->assertEquals('queued', $result['status']);
}
```

## Best Practices

1. **Keep Controllers Thin** - Only handle HTTP concerns (validation, response)
2. **Keep Services Focused** - Each service handles one domain (Car, Inspection, etc.)
3. **Use Repositories for Data Access** - Never query models directly from services
4. **Type Hint Interfaces** - Depend on contracts, not implementations
5. **Test Business Logic** - Unit test services independently
6. **Document Complex Logic** - Use PHPDoc for service methods
7. **Reuse Services** - Consider how services will be used beyond controllers

## Related Files

- `.php-cs-fixer.php` - Code style and PSR compliance
- `phpunit.xml` - Test configuration
- `app/Traits/ApiResponse.php` - Centralized response formatting
- `app/Http/Middleware/Authenticate.php` - Sanctum auth middleware
