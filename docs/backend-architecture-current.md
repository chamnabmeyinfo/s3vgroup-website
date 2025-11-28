# Current Backend Architecture

## Overview

This document summarizes the current state of the backend architecture before the Apple-like redesign.

## Technology Stack

- **Language**: PHP 7.4+ (8.2+ recommended)
- **Framework**: Custom PHP application (no framework)
- **Database**: MySQL/MariaDB via PDO
- **Architecture Pattern**: Partial Domain-Driven Design (DDD) with some layered architecture
- **Plugin System**: Custom WordPress-inspired plugin system

## Current Structure

```
├── api/                    # API endpoints (REST)
│   ├── admin/             # Admin API endpoints
│   ├── catalog/           # Public catalog endpoints
│   ├── categories/        # Public category endpoints
│   ├── products/          # Public product endpoints
│   ├── quotes/            # Public quote submission
│   └── newsletter/        # Newsletter subscription
├── app/                   # Application core
│   ├── Config/           # Configuration classes
│   ├── Core/             # Core system (plugins, hooks, registries)
│   ├── Database/         # Database connection and migrations
│   ├── Domain/           # Domain logic (Services, Repositories)
│   │   ├── Catalog/      # Products, Categories
│   │   ├── Content/      # Pages, Team, Testimonials, etc.
│   │   ├── Quotes/       # Quote requests
│   │   └── Settings/     # Site options
│   ├── Http/             # HTTP layer (Request, Response, Guards)
│   └── Support/          # Utilities and helpers
├── ae-admin/             # Admin panel (legacy structure)
├── config/               # Configuration files
├── database/             # Database migrations
└── bootstrap/            # Application bootstrap
```

## Architecture Layers

### 1. API Layer (`api/`)
- **Structure**: Flat file-based routing (one file per endpoint)
- **Pattern**: Mixed - some use Service/Repository pattern, others don't
- **Authentication**: Inconsistent - `AdminGuard::requireAuth()` used in some endpoints, not all
- **Error Handling**: Inconsistent - some use try/catch, others don't
- **Validation**: Basic validation in Services, no centralized validation layer

### 2. Domain Layer (`app/Domain/`)
- **Services**: Business logic (e.g., `ProductService`, `CategoryService`)
- **Repositories**: Data access layer (e.g., `ProductRepository`, `CategoryRepository`)
- **Pattern**: Good separation of concerns in some areas, inconsistent in others

### 3. HTTP Layer (`app/Http/`)
- **Request**: Basic request helper class
- **JsonResponse**: Response formatter (inconsistent usage)
- **AdminGuard**: Simple session-based authentication guard

### 4. Database Layer (`app/Database/`)
- **Connection**: PDO singleton via `getDB()` function
- **Migrations**: Migration system exists but not consistently used

## Current Problems & Risks

### 1. **Inconsistent Architecture**
- Some endpoints follow Service/Repository pattern, others access database directly
- Mixed response formats (some use `JsonResponse`, others don't)
- No consistent error handling strategy

### 2. **Security Concerns**
- Authentication not consistently applied across admin endpoints
- No input sanitization layer
- SQL injection protection via PDO, but no query builder abstraction
- No CSRF protection
- Session security could be improved

### 3. **Error Handling**
- No centralized error handling middleware
- Inconsistent error response formats
- Some errors logged, others not
- No structured error codes

### 4. **Validation**
- Validation logic scattered across Services
- No centralized validation rules
- No validation library (basic PHP validation)
- Inconsistent error messages

### 5. **Code Duplication**
- Similar endpoint patterns repeated across files
- Filter/pagination logic duplicated in repositories
- Response formatting duplicated

### 6. **Testing**
- No test suite
- No test infrastructure
- No test coverage

### 7. **Documentation**
- No API documentation (OpenAPI/Swagger)
- Inconsistent code comments
- No developer guide for adding new endpoints

### 8. **Performance**
- No query optimization layer
- Potential N+1 query issues
- No caching strategy (mentioned in config but not implemented)
- No pagination limits enforced

### 9. **Maintainability**
- Large repository files with duplicated logic
- No clear conventions for adding new features
- Mixed naming conventions

### 10. **Dependencies**
- No dependency injection container
- Global `getDB()` function instead of dependency injection
- Services instantiated directly in endpoints

## Current API Patterns

### Example: Product Endpoint (Good Pattern)
```php
// Uses Service/Repository pattern
$repository = new ProductRepository(getDB());
$service = new ProductService($repository);
$product = $service->create($payload);
JsonResponse::success(['product' => $product]);
```

### Example: Inconsistent Pattern
```php
// Direct database access, no service layer
$db = getDB();
$stmt = $db->prepare('SELECT * FROM ...');
// No error handling, no validation
```

## Database Access

- **Connection**: PDO singleton via `getDB()` function in `config/database.php`
- **Pattern**: Direct PDO usage in repositories
- **Prepared Statements**: Used consistently (good)
- **Transactions**: Not used (risk for data integrity)

## Authentication & Authorization

- **Method**: Session-based (`$_SESSION['admin_logged_in']`)
- **Guard**: `AdminGuard::requireAuth()` (not used consistently)
- **Public Endpoints**: No authentication required
- **Admin Endpoints**: Should require authentication but not enforced everywhere

## Response Formats

### Current Success Format
```json
{
  "status": "success",
  "data": { ... }
}
```

### Current Error Format
```json
{
  "status": "error",
  "message": "...",
  "context": { ... }
}
```

**Issues**: Not consistently applied, missing error codes, inconsistent structure

## Logging

- **Method**: `error_log()` function calls
- **No structured logging**: No levels, no context, no centralized logger
- **Inconsistent**: Some errors logged, others not

## Configuration

- **Database**: `config/database.php` (not in version control)
- **Site Config**: `config/site.php`
- **Environment**: `.env` file support exists but not fully utilized

## Migration System

- **Location**: `database/migrations/`
- **Runner**: `MigrationRunner` class exists
- **Usage**: Not consistently used for schema changes

## Plugin System

- **Location**: `ae-content/plugins/`
- **Manager**: `PluginManager` class
- **Registry**: `PluginRegistry` class
- **Hooks**: `HookSystem` class
- **Status**: Functional but could be better integrated

## Summary

The current architecture has **good foundations** (some DDD patterns, PDO usage, plugin system) but suffers from:

1. **Inconsistency** - Mixed patterns and approaches
2. **Missing Layers** - No validation layer, no centralized error handling
3. **Security Gaps** - Inconsistent authentication, no input sanitization layer
4. **No Testing** - No test infrastructure
5. **Poor DX** - Hard to add new features, no clear conventions
6. **Documentation** - Missing API docs and developer guides

The redesign will address all these issues while maintaining backward compatibility where possible.

