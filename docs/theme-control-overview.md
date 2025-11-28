# Theme Control System - Architecture Overview

## Framework & Stack

- **Framework**: Custom PHP 8 application (not Laravel/Symfony)
- **PHP Version**: PHP 8+ (uses strict types, match expressions, readonly properties)
- **Database**: MySQL/MariaDB with PDO
- **Architecture Pattern**: Domain-Driven Design (DDD) with Repository pattern
- **API Style**: RESTful JSON APIs

## Current Architecture

### Directory Structure
```
app/
├── Domain/          # Business logic, repositories, services
├── Http/            # Controllers, middleware, requests, responses
├── Infrastructure/  # Database, validation, logging
├── Config/          # Configuration classes
└── Support/         # Helpers, utilities

api/
├── admin/           # Admin-only endpoints (require authentication)
└── [public]/        # Public endpoints

database/
└── migrations/      # Database migration files
```

### Key Patterns

1. **Repository Pattern**: Data access abstraction (e.g., `ProductRepository`, `SiteOptionRepository`)
2. **Service Layer**: Business logic orchestration (e.g., `ProductService`)
3. **Request Validation**: FormRequest classes with Validator
4. **Response Format**: Consistent JSON via `JsonResponse::success()` / `JsonResponse::error()`
5. **Authentication**: `AdminGuard::requireAuth()` for admin endpoints
6. **Migrations**: Custom migration classes extending `App\Database\Migration`

### Database Connection

- Uses `getDB()` function that returns a PDO instance
- Connection configured in `config/database.php`
- Supports local overrides via `config/database.local.php`

## Theme Control System Integration

### Where It Plugs In

1. **Domain Layer** (`app/Domain/Theme/`):
   - `ThemeRepository.php` - Data access for themes
   - `ThemeService.php` - Business logic for theme operations
   - `UserThemePreferenceRepository.php` - User theme preferences
   - `UserThemePreferenceService.php` - User preference logic

2. **HTTP Layer** (`app/Http/`):
   - `Controllers/ThemeController.php` - Theme management controller
   - `Requests/CreateThemeRequest.php` - Validation for theme creation
   - `Requests/UpdateThemeRequest.php` - Validation for theme updates

3. **API Endpoints** (`api/`):
   - `api/admin/themes/index.php` - Admin theme management (CRUD)
   - `api/admin/themes/item.php` - Single theme operations
   - `api/admin/themes/set-default.php` - Set default theme
   - `api/theme/active.php` - Get active theme for current user/context
   - `api/themes/public.php` - List public themes

### Database Schema

#### `themes` Table
- `id` VARCHAR(255) PRIMARY KEY
- `name` VARCHAR(255) NOT NULL
- `slug` VARCHAR(255) UNIQUE NOT NULL
- `description` TEXT
- `is_default` BOOLEAN DEFAULT FALSE
- `is_active` BOOLEAN DEFAULT TRUE
- `config` JSON - Design tokens (colors, typography, radius, shadows)
- `createdAt` TIMESTAMP
- `updatedAt` TIMESTAMP

#### `user_theme_preferences` Table (Optional)
- `id` VARCHAR(255) PRIMARY KEY
- `user_id` VARCHAR(255) - References users table (if exists)
- `theme_id` VARCHAR(255) - References themes.id
- `scope` VARCHAR(50) DEFAULT 'public_frontend' - e.g., 'backend_admin', 'public_frontend'
- `createdAt` TIMESTAMP
- `updatedAt` TIMESTAMP

### Constraints & Considerations

1. **Multi-tenancy**: Currently single-brand (no multi-tenant support detected)
2. **User System**: User authentication exists but user table structure not fully explored - using flexible `user_id` VARCHAR
3. **Default Theme**: Only one theme can be `is_default = true` at a time
4. **Active Theme**: At least one theme must remain active
5. **Config Validation**: Theme `config` JSON must contain required design tokens

### Design Token Structure

The `config` JSON field follows an Apple-like aesthetic:

```json
{
  "colors": {
    "background": "#FFFFFF",
    "surface": "#F5F5F7",
    "primary": "#007AFF",
    "primaryText": "#FFFFFF",
    "text": "#111111",
    "mutedText": "#8E8E93",
    "border": "#D1D1D6"
  },
  "typography": {
    "fontFamily": "system-ui",
    "headingScale": 1.25,
    "bodySize": 16
  },
  "radius": {
    "small": 6,
    "medium": 12,
    "large": 20
  },
  "shadows": {
    "card": "0 2px 8px rgba(0,0,0,0.08)"
  }
}
```

### API Response Format

All endpoints follow the standard format:

```json
{
  "status": "success",
  "data": { ... },
  "error": null,
  "meta": {
    "timestamp": "2025-01-XX..."
  }
}
```

### Migration Strategy

- Migration file: `database/migrations/YYYYMMDD_themes_system.php`
- Seeds two default themes: `light` (default) and `dark`
- Idempotent: Safe to run multiple times

## Future Extensibility

The system is designed to easily extend with:
- Additional design tokens (spacing, animations, etc.)
- Theme variants (e.g., "Light Pro", "Dark High Contrast")
- Per-scope themes (admin vs. public frontend)
- Theme preview/export functionality
- Theme templates/presets

