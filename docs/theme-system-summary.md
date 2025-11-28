# Theme System Summary

## Overview
The Theme Control system is a complete, centralized theme management solution for the backend admin panel. It provides theme definition, storage, management, customization, and preview capabilities.

## Key Features

### 1. Theme Management
- **Centralized Storage**: All themes stored in `themes` table with JSON configuration
- **User Preferences**: Per-user theme selection via `user_theme_preferences` table
- **Default Themes**: System-wide default theme support
- **Active/Inactive**: Theme activation control

### 2. Theme Preview System
- **Detailed Preview Page** (`ae-admin/theme-preview.php`):
  - Theme information (name, slug, status)
  - Complete color palette visualization
  - Typography examples with all font weights
  - Button styles and interactions
  - Link styles
  - Form elements (inputs, selects, checkboxes, radios)
  - Cards and surfaces with shadows
  - Badges and status indicators
  - Tables
  - **"What This Theme Does" Section**: 
    - Visual Design details (colors, radius, shadows)
    - Typography specifications
    - Interactive element behaviors
    - Customization capabilities
  - Design principles explanation
  - Quick actions (Customize, Use This Theme)

### 3. Theme Customization System
- **Visual Customization Page** (`ae-admin/theme-customize.php`):
  - Color pickers for all theme colors
  - Typography controls (font family, sizes, weights, spacing)
  - Border radius adjustments
  - Shadow customization
  - Live preview panel
  - Save/Reset functionality
  - Deep config merging (preserves existing values)

### 4. Theme Selection Interface
- **Backend Appearance Page** (`ae-admin/backend-appearance.php`):
  - Grid of theme cards with previews
  - Color swatches showing theme colors
  - Quick links to Preview and Customize
  - One-click theme activation
  - Current theme indicator

## Design Token Structure

Each theme contains a `config` JSON with:

```json
{
  "colors": {
    "background": "#FAFBFC",
    "surface": "#FFFFFF",
    "primary": "#2563EB",
    "primaryText": "#FFFFFF",
    "text": "#1F2937",
    "mutedText": "#6B7280",
    "border": "#E5E7EB",
    "error": "#DC2626",
    "success": "#059669",
    "warning": "#D97706",
    "accent": "#7C3AED",
    "secondary": "#10B981",
    "tertiary": "#F59E0B"
  },
  "typography": {
    "fontFamily": "system-ui, -apple-system, ...",
    "headingScale": 1.25,
    "bodySize": 15,
    "lineHeight": 1.6,
    "fontWeightNormal": 400,
    "fontWeightMedium": 500,
    "fontWeightSemibold": 600,
    "fontWeightBold": 700,
    "letterSpacing": "normal"
  },
  "radius": {
    "small": 6,
    "medium": 10,
    "large": 16,
    "pill": 9999
  },
  "shadows": {
    "card": "0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06)",
    "elevated": "0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)",
    "subtle": "0 1px 2px rgba(0,0,0,0.05)",
    "button": "0 1px 3px rgba(0,0,0,0.1)",
    "buttonHover": "0 2px 6px rgba(0,0,0,0.15)"
  }
}
```

## API Endpoints

### Admin Management
- `GET /api/admin/themes` - List all themes
- `GET /api/admin/themes/item.php?id={id}` - Get single theme
- `POST /api/admin/themes` - Create new theme
- `PUT /api/admin/themes/item.php?id={id}` - Update theme (with deep config merge)
- `DELETE /api/admin/themes/item.php?id={id}` - Delete theme
- `POST /api/admin/themes/set-default.php` - Set default theme

### User Theme Selection
- `GET /api/admin/theme/backend.php` - Get current backend theme
- `POST /api/admin/theme/backend.php` - Set backend theme preference

## Theme Application

Themes are applied via CSS variables injected in `ae-admin/includes/header.php`:
- Loads user preference → default theme → first active theme
- Injects CSS variables into `<style>` block
- Sets `data-theme` attribute on `<body>` for theme-specific CSS
- All UI elements use these variables via `admin-styles.css`

## Customization Workflow

1. **Select Theme**: Choose from available themes in Backend Appearance
2. **Preview Theme**: View detailed preview with all UI elements
3. **Customize Theme**: Adjust colors, typography, radius, shadows
4. **Save Changes**: Updates are merged with existing config (deep merge)
5. **Apply Theme**: Set as active theme for immediate effect

## Available Themes

1. **Ant Elite Default** (Default)
   - Modern professional theme
   - Balanced colors and clean typography
   - Optimized for productivity

2. **MacBook Style**
   - Authentic macOS Big Sur/Monterey design
   - SF Pro font system
   - Glassmorphism effects
   - Apple Human Interface Guidelines

3. **Windows 11 Style**
   - Fluent Design System
   - Segoe UI Variable font
   - Mica effects

4. **Dark Pro**
   - Professional dark theme
   - High contrast
   - Optimized for extended use

5. **Minimal**
   - Ultra-minimal design
   - Maximum focus
   - Clean aesthetics

6. **High Contrast**
   - Maximum visibility
   - Accessibility compliance

## Technical Implementation

- **Domain Layer**: `ThemeRepository`, `ThemeService`, `UserThemePreferenceRepository`, `UserThemePreferenceService`
- **HTTP Layer**: `ThemeController`, Request validation classes
- **Database**: Migrations for schema and theme seeding
- **Frontend**: JavaScript for theme switching and customization
- **CSS**: Theme-aware styles using CSS variables

## Error Handling

- Robust fallback mechanism for theme loading
- Deep config merging prevents data loss
- Comprehensive error handling in API endpoints
- User-friendly error messages in UI

## Future Enhancements

- Theme export/import
- Theme templates
- Advanced customization options
- Theme versioning
- Preview in different contexts

