# Site Options Refactoring

## Overview

The site options/settings functionality has been refactored to follow the new Apple-like backend architecture.

## What Was Changed

### 1. Application Service Layer ✅

Created `app/Application/Services/SiteOptionService.php`:
- Orchestrates site option operations
- Delegates to domain service
- Provides clean API for controllers

### 2. Request Validation Classes ✅

Created validation classes:
- `UpdateSiteOptionRequest` - Validates single option updates
- `BulkUpdateSiteOptionsRequest` - Validates bulk updates

**Features:**
- Type validation (text, textarea, number, boolean, json, color, image, url)
- Group validation (general, design, contact, social, homepage, footer, advanced)
- Friendly error messages

### 3. Controller ✅

Created `app/Http/Controllers/SiteOptionController.php`:
- `index()` - Get all options (optionally filtered by group)
- `show($id)` - Get single option by ID
- `update($id)` - Update single option
- `bulkUpdate()` - Bulk update multiple options

**Features:**
- Automatic exception handling
- Request validation
- Structured logging
- Consistent response format

### 4. Domain Service Updates ✅

Updated `app/Domain/Settings/SiteOptionService.php`:
- Uses domain exceptions instead of `InvalidArgumentException`
- Added `getByGroup()` method
- Added `findById()` method
- Better error messages

### 5. Repository Updates ✅

Updated `app/Domain/Settings/SiteOptionRepository.php`:
- Uses `NotFoundException` instead of `RuntimeException`
- Consistent with domain exception patterns

### 6. Endpoint Refactoring ✅

Refactored endpoints:
- `api/admin/options/index.php` - Now uses controller
- `api/admin/options/item.php` - Now uses controller

**Before:**
```php
// Direct service calls, manual error handling
$service = new SiteOptionService($repository);
$grouped = $service->getGrouped();
JsonResponse::success(['options' => $grouped]);
```

**After:**
```php
// Clean controller pattern
$controller = new SiteOptionController();
$controller->index();
```

## API Endpoints

### GET `/api/admin/options`
Get all site options, optionally filtered by group.

**Query Parameters:**
- `group` (optional) - Filter by group name

**Response:**
```json
{
  "data": {
    "options": {
      "general": [...],
      "design": [...]
    }
  },
  "error": null,
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

### GET `/api/admin/options/item?id={id}`
Get single site option by ID.

**Response:**
```json
{
  "data": {
    "option": {
      "id": "opt_001",
      "key_name": "site_name",
      "value": "S3V Group",
      "type": "text",
      "group_name": "general",
      "label": "Site Name",
      "description": "The name of your website",
      "priority": 100
    }
  },
  "error": null,
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

### PUT/PATCH `/api/admin/options/item?id={id}`
Update a site option.

**Request Body:**
```json
{
  "value": "New Site Name",
  "label": "Updated Label",
  "description": "Updated description"
}
```

**Response:**
```json
{
  "data": {
    "option": { ... }
  },
  "error": null,
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

### POST `/api/admin/options`
Bulk update site options.

**Request Body:**
```json
{
  "bulk": {
    "site_name": "New Name",
    "primary_color": "#ff0000",
    "site_logo": "/path/to/logo.png"
  }
}
```

**Response:**
```json
{
  "data": {
    "message": "Options updated successfully.",
    "count": 3
  },
  "error": null,
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

## Error Handling

All errors are now handled consistently:

**Validation Error:**
```json
{
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed. Please check your input.",
    "details": {
      "fields": {
        "type": ["The type must be one of: text, textarea, number, boolean, json, color, image, url."]
      }
    }
  },
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

**Not Found Error:**
```json
{
  "data": null,
  "error": {
    "code": "NOT_FOUND",
    "message": "Site option not found.",
    "details": {}
  },
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

## Logging

All operations are logged with context:

```php
Logger::info('Site option updated', [
    'option_id' => $id,
    'key' => $option['key_name'],
    'user_id' => $userId,
]);
```

## Benefits

1. **Consistent Architecture** - Follows same patterns as other endpoints
2. **Better Validation** - Clear validation rules with friendly messages
3. **Error Handling** - Consistent error responses
4. **Logging** - All operations logged with context
5. **Type Safety** - Strict types throughout
6. **Maintainability** - Clear separation of concerns

## Migration Notes

The refactoring maintains **backward compatibility**:
- Same API endpoints
- Same request/response structure (with improved format)
- Existing frontend code should work without changes

## Next Steps

1. Test all site option operations
2. Verify frontend integration
3. Consider adding more validation rules if needed
4. Add unit tests for SiteOptionController

