# New Backend Architecture (Apple-like Design)

## Philosophy

This architecture follows an **Apple-like philosophy** for backend development:
- **Simplicity**: Fewer concepts, well-chosen abstractions, no unnecessary complexity
- **Consistency**: Consistent naming, response formats, error formats, and conventions
- **Performance**: Fast responses, efficient queries, good caching
- **Delight**: Developer-friendly APIs, clear error messages, excellent documentation

## Architecture Overview

We follow a **Clean Architecture / Layered Architecture** pattern with clear separation of concerns:

```
Request → Controller → Service → Repository → Database
         ↓            ↓         ↓
      Validation   Business   Data Access
         ↓            ↓
      Error       Logging
      Handler
```

## Folder Structure

```
app/
├── Http/                    # HTTP Layer (Controllers, Middleware, Responses)
│   ├── Controllers/        # Request handlers (thin, delegate to services)
│   ├── Middleware/         # Authentication, CORS, Error handling
│   ├── Requests/           # Request validation classes
│   └── Responses/          # Response formatters
├── Application/             # Application Layer (Use Cases / Services)
│   ├── Services/           # Business logic (orchestrates domain)
│   └── DTOs/               # Data Transfer Objects
├── Domain/                  # Domain Layer (Business Rules)
│   ├── Entities/           # Domain entities (if needed)
│   ├── Repositories/       # Repository interfaces
│   └── Exceptions/         # Domain exceptions
├── Infrastructure/          # Infrastructure Layer
│   ├── Database/           # Database implementations
│   │   ├── Repositories/   # Repository implementations
│   │   ├── Migrations/     # Database migrations
│   │   └── Connection.php  # Database connection
│   ├── Validation/         # Validation rules
│   ├── Logging/            # Logging implementation
│   └── Cache/              # Caching implementation
├── Core/                    # Core System (Plugins, Hooks, etc.)
└── Support/                 # Utilities and helpers
```

## Layer Responsibilities

### 1. HTTP Layer (`app/Http/`)

**Purpose**: Handle HTTP concerns only - routing, request/response formatting, middleware

**Components**:
- **Controllers**: Thin controllers that validate requests and call services
- **Middleware**: Authentication, CORS, error handling, logging
- **Request Classes**: Validation rules for incoming requests
- **Response Classes**: Consistent response formatting

**Rules**:
- Controllers should NOT contain business logic
- Controllers should NOT access database directly
- Controllers delegate to Application Services
- All responses go through response formatters

### 2. Application Layer (`app/Application/`)

**Purpose**: Orchestrate business logic, coordinate between domain and infrastructure

**Components**:
- **Services**: Application services that orchestrate use cases
- **DTOs**: Data Transfer Objects for passing data between layers

**Rules**:
- Services contain use case logic
- Services coordinate between repositories and domain logic
- Services handle transactions
- Services throw domain exceptions

### 3. Domain Layer (`app/Domain/`)

**Purpose**: Core business logic and rules

**Components**:
- **Entities**: Domain entities (if needed)
- **Repository Interfaces**: Contracts for data access
- **Exceptions**: Domain-specific exceptions

**Rules**:
- Pure business logic, no infrastructure concerns
- Repository interfaces only (no implementations)
- Domain exceptions for business rule violations

### 4. Infrastructure Layer (`app/Infrastructure/`)

**Purpose**: Technical implementation details

**Components**:
- **Database Repositories**: Implement domain repository interfaces
- **Validation**: Validation rule implementations
- **Logging**: Logging implementation
- **Cache**: Caching implementation

**Rules**:
- Implements interfaces from Domain layer
- Can be swapped out (e.g., different database, cache provider)

## Data Flow Example

### Creating a Product

```
1. Request → POST /api/admin/products
2. Middleware → Authenticate user
3. Controller → Validate request (ProductRequest)
4. Controller → Call ProductService::create()
5. Service → Validate business rules
6. Service → Call ProductRepository::create()
7. Repository → Execute SQL via PDO
8. Repository → Return product data
9. Service → Return product DTO
10. Controller → Format response (JsonResponse)
11. Response → JSON sent to client
```

## Key Principles

### 1. Single Responsibility
- Each class has one reason to change
- Controllers handle HTTP, Services handle business logic, Repositories handle data

### 2. Dependency Inversion
- High-level modules (Services) depend on abstractions (Repository interfaces)
- Low-level modules (Database Repositories) implement abstractions

### 3. Explicit Boundaries
- Clear interfaces between layers
- No direct database access from controllers
- No business logic in repositories

### 4. Consistency Everywhere
- Consistent naming: `ProductController`, `ProductService`, `ProductRepository`
- Consistent response format
- Consistent error format
- Consistent validation approach

## API Design

### RESTful Endpoints

```
GET    /api/products              # List products (paginated)
GET    /api/products/{id}        # Get product by ID
POST   /api/products              # Create product
PUT    /api/products/{id}        # Update product
DELETE /api/products/{id}        # Delete product
```

### Consistent Response Format

**Success Response**:
```json
{
  "data": {
    "product": { ... }
  },
  "error": null,
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

**Error Response**:
```json
{
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The product name is required.",
    "details": {
      "field": "name",
      "rule": "required"
    }
  },
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

### Error Codes

- `VALIDATION_ERROR` - Input validation failed
- `NOT_FOUND` - Resource not found
- `UNAUTHORIZED` - Authentication required
- `FORBIDDEN` - Insufficient permissions
- `CONFLICT` - Resource conflict (e.g., duplicate)
- `INTERNAL_ERROR` - Server error

## Validation

### Request Validation Classes

Each endpoint has a corresponding Request class that validates input:

```php
class CreateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'categoryId' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'status' => 'in:DRAFT,PUBLISHED,ARCHIVED',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'name.max' => 'The product name cannot exceed 255 characters.',
        ];
    }
}
```

## Error Handling

### Centralized Exception Handler

All exceptions are caught by a global exception handler that:
1. Logs the error with context
2. Converts to appropriate HTTP status code
3. Returns consistent error format
4. Hides sensitive information in production

### Exception Hierarchy

```
Exception
├── DomainException (business rule violations)
│   ├── ValidationException
│   ├── NotFoundException
│   └── ConflictException
└── InfrastructureException (technical errors)
    └── DatabaseException
```

## Authentication & Authorization

### Middleware-Based Auth

```php
// Applied to admin routes
Route::middleware(['auth:admin'])->group(function () {
    // Admin routes
});
```

### Session-Based Authentication
- Secure session configuration
- CSRF protection
- Session regeneration on login

## Logging

### Structured Logging

```php
Logger::info('Product created', [
    'product_id' => $productId,
    'user_id' => $userId,
    'category' => $categoryId,
]);
```

### Log Levels
- `DEBUG` - Detailed debugging information
- `INFO` - General informational messages
- `WARNING` - Warning messages
- `ERROR` - Error messages
- `CRITICAL` - Critical errors

## Testing Strategy

### Test Structure

```
tests/
├── Unit/                   # Unit tests (Services, Repositories)
├── Integration/           # Integration tests (API endpoints)
└── Feature/               # Feature tests (end-to-end)
```

### Test Coverage Goals
- **Critical paths**: 100% coverage
- **Services**: 80%+ coverage
- **Controllers**: 70%+ coverage

## Performance Optimizations

### Database
- Indexed queries
- Efficient pagination
- Query result caching where appropriate
- Avoid N+1 queries

### Caching
- Response caching for read-heavy endpoints
- Query result caching
- Configuration caching

## Security Best Practices

1. **Input Validation**: All inputs validated
2. **SQL Injection**: PDO prepared statements (already in place)
3. **XSS Protection**: Output escaping
4. **CSRF Protection**: Token-based CSRF protection
5. **Authentication**: Secure session management
6. **Authorization**: Role-based access control
7. **Rate Limiting**: Prevent abuse (future enhancement)

## Developer Experience

### Adding a New Endpoint

1. **Create Request Class** (`app/Http/Requests/CreateXxxRequest.php`)
2. **Create Controller** (`app/Http/Controllers/XxxController.php`)
3. **Create Service** (`app/Application/Services/XxxService.php`)
4. **Create Repository** (`app/Infrastructure/Database/Repositories/XxxRepository.php`)
5. **Add Route** (in routing configuration)
6. **Write Tests** (`tests/Integration/XxxTest.php`)

### Code Generation
- Templates for common patterns
- Scripts to generate boilerplate

## Migration Strategy

The refactoring will be done incrementally:

1. **Phase 1**: Add new layers (Validation, Error Handling, Logging)
2. **Phase 2**: Refactor existing endpoints to use new patterns
3. **Phase 3**: Add tests
4. **Phase 4**: Documentation and polish

This ensures the system remains functional throughout the refactoring process.

## Configuration

### Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost
DB_NAME=database
DB_USER=user
DB_PASS=password
LOG_LEVEL=info
CACHE_ENABLED=true
```

### Configuration Files
- `config/app.php` - Application configuration
- `config/database.php` - Database configuration
- `config/logging.php` - Logging configuration

## Documentation

### API Documentation
- OpenAPI/Swagger specification
- Postman collection
- Example requests/responses

### Developer Documentation
- Architecture guide (this document)
- Contributing guide
- Code examples

## Summary

This architecture provides:
- ✅ **Clear separation of concerns**
- ✅ **Consistent patterns and conventions**
- ✅ **Strong typing and validation**
- ✅ **Comprehensive error handling**
- ✅ **Security best practices**
- ✅ **Excellent developer experience**
- ✅ **Production-ready structure**

The result is a backend that feels like an Apple product: simple, consistent, powerful, and delightful to use.

