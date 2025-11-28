# Backend Developer Guide

## Quick Start

### 1. Understanding the Architecture

The backend follows a **Clean Architecture** pattern:

```
Request → Controller → Service → Repository → Database
```

- **Controllers** (`app/Http/Controllers/`) - Handle HTTP requests, validate input, return responses
- **Services** (`app/Application/Services/`) - Orchestrate business logic
- **Repositories** (`app/Domain/*/Repositories/`) - Data access layer
- **Request Classes** (`app/Http/Requests/`) - Validation rules

### 2. Adding a New API Endpoint

#### Step 1: Create Request Validation Class

```php
<?php
// app/Http/Requests/CreateProductRequest.php

namespace App\Http\Requests;

final class CreateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'categoryId' => 'required|string',
            'price' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'price.numeric' => 'The price must be a valid number.',
        ];
    }
}
```

#### Step 2: Create Controller

```php
<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Application\Services\ProductService;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Exceptions\NotFoundException;
use PDO;

final class ProductController extends Controller
{
    private ProductService $service;

    public function __construct()
    {
        $db = $this->getDatabase();
        $repository = new ProductRepository($db);
        $this->service = new ProductService($repository);
    }

    public function create(): void
    {
        $this->handle(function () {
            $this->requireAuth();

            $validated = $this->validate(\App\Http\Requests\CreateProductRequest::class);
            $product = $this->service->create($validated);

            $this->success(['product' => $product], 201);
        });
    }

    private function getDatabase(): PDO
    {
        return function_exists('getDB') ? getDB() : \App\Database\Connection::getInstance();
    }
}
```

#### Step 3: Create Endpoint File

```php
<?php
// api/admin/products/create.php

require_once __DIR__ . '/../../../bootstrap/app.php';

use App\Http\Controllers\ProductController;

$controller = new ProductController();
$controller->create();
```

### 3. Validation Rules

Available validation rules:

- `required` - Field must be present
- `string` - Must be a string
- `integer` / `int` - Must be an integer
- `numeric` - Must be a number
- `email` - Must be a valid email
- `url` - Must be a valid URL
- `min:value` - Minimum value/length
- `max:value` - Maximum value/length
- `in:value1,value2` - Must be one of the values
- `array` - Must be an array
- `boolean` / `bool` - Must be true/false

### 4. Error Handling

All exceptions are automatically caught and converted to JSON responses:

```php
// In your service or controller
throw new \App\Domain\Exceptions\NotFoundException('Product not found.');
// Returns: 404 with error code "NOT_FOUND"
```

Available exceptions:
- `ValidationException` - Validation errors (422)
- `NotFoundException` - Resource not found (404)
- `UnauthorizedException` - Authentication required (401)
- `ForbiddenException` - Insufficient permissions (403)
- `ConflictException` - Resource conflict (409)

### 5. Logging

```php
use App\Infrastructure\Logging\Logger;

Logger::info('Product created', [
    'product_id' => $productId,
    'user_id' => $userId,
]);

Logger::error('Failed to create product', [
    'error' => $exception->getMessage(),
    'context' => $payload,
]);
```

Log levels: `DEBUG`, `INFO`, `WARNING`, `ERROR`, `CRITICAL`

### 6. Authentication

```php
// Require authentication
$this->requireAuth();

// Check if authenticated
if (\App\Http\Middleware\Authenticate::check()) {
    // User is authenticated
}

// Get user ID
$userId = \App\Http\Middleware\Authenticate::userId();
```

### 7. Response Formatting

```php
// Success response
$this->success(['product' => $product], 201);

// Error response
$this->error('Product not found.', 404, 'NOT_FOUND');
```

### 8. Database Access

```php
// Get database connection
$db = \App\Database\Connection::getInstance();

// Or use existing function
$db = getDB();
```

### 9. Best Practices

1. **Always validate input** - Use Request classes
2. **Use exceptions** - Throw domain exceptions, don't return error arrays
3. **Log important events** - Use Logger for operations
4. **Keep controllers thin** - Delegate to services
5. **Use type hints** - Strict types everywhere
6. **Handle errors gracefully** - Let ExceptionHandler catch them

### 10. Testing

```php
<?php
// tests/Integration/ProductTest.php

use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function testCreateProduct(): void
    {
        // Test implementation
    }
}
```

## Code Examples

### Complete Endpoint Example

```php
<?php
// api/admin/products/index.php

require_once __DIR__ . '/../../../bootstrap/app.php';

use App\Application\Services\ProductService;
use App\Domain\Catalog\ProductRepository;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ExceptionHandler;
use App\Http\Request;
use PDO;

class ProductIndexController extends Controller
{
    private ProductService $service;

    public function __construct()
    {
        $db = function_exists('getDB') ? getDB() : \App\Database\Connection::getInstance();
        $repository = new ProductRepository($db);
        $this->service = new ProductService($repository);
    }

    public function index(): void
    {
        $this->handle(function () {
            Authenticate::requireAuth();

            $filters = [
                'status' => Request::query('status'),
                'search' => Request::query('search'),
            ];

            $limit = (int) Request::query('limit', 25);
            $offset = (int) Request::query('offset', 0);

            $products = $this->service->list($filters, $limit, $offset);
            $total = $this->service->count($filters);

            $this->success([
                'products' => $products,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => $total,
                ],
            ]);
        });
    }
}

$controller = new ProductIndexController();
$controller->index();
```

## Migration from Old Pattern

### Old Pattern
```php
// Direct database access, no validation
$db = getDB();
$stmt = $db->prepare('SELECT * FROM products');
JsonResponse::success(['products' => $stmt->fetchAll()]);
```

### New Pattern
```php
// Controller → Service → Repository
$controller = new ProductController();
$controller->index();
```

## Troubleshooting

### Common Issues

1. **"Class not found"** - Run `composer dump-autoload` or check autoloader
2. **"Database connection failed"** - Check `config/database.php`
3. **"Validation errors"** - Check Request class rules
4. **"Unauthorized"** - Ensure user is logged in

## Resources

- Architecture: `docs/backend-architecture-new.md`
- Current State: `docs/backend-architecture-current.md`
- API Examples: See `app/Http/Controllers/` for examples

