# Theme Control API Reference

## Admin Endpoints (Require Authentication)

### List All Themes
```
GET /api/admin/themes
GET /api/admin/themes?is_active=true
GET /api/admin/themes?is_default=true
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "themes": [
      {
        "id": "theme_light",
        "name": "Light",
        "slug": "light",
        "description": "Clean, minimal light theme...",
        "is_default": true,
        "is_active": true,
        "config": { ... },
        "createdAt": "2025-01-16...",
        "updatedAt": "2025-01-16..."
      }
    ]
  },
  "error": null,
  "meta": { ... }
}
```

### Get Single Theme
```
GET /api/admin/themes/item.php?id=theme_light
GET /api/admin/themes/item.php?slug=light
```

### Create Theme
```
POST /api/admin/themes
Content-Type: application/json

{
  "name": "Pro Theme",
  "slug": "pro",
  "description": "Professional theme",
  "is_default": false,
  "is_active": true,
  "config": {
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
      "bodySize": 16,
      "lineHeight": 1.5
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
}
```

### Update Theme
```
PUT /api/admin/themes/item.php?id=theme_light
Content-Type: application/json

{
  "name": "Updated Light Theme",
  "config": { ... }
}
```

### Delete Theme (Soft Delete)
```
DELETE /api/admin/themes/item.php?id=theme_light
```

### Set Default Theme
```
POST /api/admin/themes/set-default.php?id=theme_dark
POST /api/admin/themes/set-default.php?slug=dark
```

## Public Endpoints (No Authentication Required)

### Get Active Theme
```
GET /api/theme/active.php
GET /api/theme/active.php?scope=public_frontend
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "theme": {
      "name": "Light",
      "slug": "light",
      "config": {
        "colors": { ... },
        "typography": { ... },
        "radius": { ... },
        "shadows": { ... }
      }
    }
  },
  "error": null,
  "meta": { ... }
}
```

### List Public Themes
```
GET /api/themes/public.php
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "themes": [
      {
        "id": "theme_light",
        "name": "Light",
        "slug": "light",
        "description": "...",
        "config": { ... }
      }
    ]
  },
  "error": null,
  "meta": { ... }
}
```

## Error Responses

### Validation Error (422)
```json
{
  "status": "error",
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Theme name is required.",
    "details": {
      "fields": {
        "name": ["Theme name is required."]
      }
    }
  },
  "meta": { ... }
}
```

### Conflict Error (409)
```json
{
  "status": "error",
  "data": null,
  "error": {
    "code": "CONFLICT",
    "message": "Cannot delete the last active theme.",
    "details": {}
  },
  "meta": { ... }
}
```

## Running the Migration

To set up the database tables and seed default themes:

```bash
php database/run-migration.php database/migrations/20250116_themes_system.php
```

Or manually run the migration using your migration runner.

## Design Token Structure

All themes must include these required design tokens in the `config` JSON:

### Required Structure
- `colors` (object) - Must include: `background`, `surface`, `primary`, `text`
- `typography` (object) - Font settings
- `radius` (object) - Border radius values

### Optional Structure
- `shadows` (object) - Shadow definitions
- Additional custom tokens as needed

## Business Rules

1. **Default Theme**: Only one theme can be `is_default = true` at a time
2. **Active Theme**: At least one theme must remain active
3. **Soft Delete**: Themes are soft-deleted (set `is_active = false`) rather than hard-deleted
4. **Slug Uniqueness**: Theme slugs must be unique
5. **Config Validation**: Theme config must include required design tokens

