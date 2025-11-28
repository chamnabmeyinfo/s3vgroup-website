# Theme UI Integration - Complete Guide

## Overview

All admin UI pages now automatically follow the selected theme. The theme system is fully integrated into all UI components.

## What Was Updated

### 1. Admin Styles (`ae-admin/includes/admin-styles.css`)

**All components now use theme CSS variables:**

- ✅ **Buttons** - Use `--theme-primary`, `--theme-primary-text`, `--theme-radius-md`
- ✅ **Forms** - Inputs, textareas, selects use theme colors, borders, and fonts
- ✅ **Tables** - Headers, rows, borders all use theme variables
- ✅ **Cards** - Background, borders, shadows from theme
- ✅ **Badges** - Status colors from theme (success, error, warning)
- ✅ **Modals** - Background, borders, shadows from theme
- ✅ **Toasts** - All notification types use theme colors
- ✅ **Checkboxes/Radios** - Use theme primary color when checked
- ✅ **Typography** - Font family, sizes, line heights from theme
- ✅ **Shadows** - All shadow effects use theme shadow variables
- ✅ **Border Radius** - All rounded corners use theme radius values

### 2. Tailwind Override

Added CSS overrides at the bottom of `admin-styles.css` to make Tailwind classes theme-aware:

```css
.bg-white { background-color: var(--theme-bg) !important; }
.text-gray-900 { color: var(--theme-text) !important; }
.border-gray-200 { border-color: var(--theme-border) !important; }
/* ... and more */
```

This ensures that even pages using Tailwind classes will respect the theme.

## Pages That Now Follow Theme

All these pages automatically use the selected theme:

1. ✅ **Dashboard** (`index.php`)
2. ✅ **Products** (`products.php`)
3. ✅ **Categories** (`categories.php`)
4. ✅ **Pages** (`pages.php`)
5. ✅ **Visual Builder** (`homepage-builder-v2.php`)
6. ✅ **Teams** (`team.php`)
7. ✅ **Sliders** (`sliders.php`)
8. ✅ **Testimonials** (`testimonials.php`)
9. ✅ **Site Options** (`options.php`)
10. ✅ **Media** (`media-library.php`)
11. ✅ **Plugins** (`plugins.php`)
12. ✅ **Database Sync** (`database-sync.php`)
13. ✅ **Optional Features** (`optional-features.php`)
14. ✅ **Menus** (`menus.php`)
15. ✅ **Backend Appearance** (`backend-appearance.php`)

## Theme Variables Used

### Colors
- `--theme-bg` - Main background
- `--theme-surface` - Card/surface background
- `--theme-primary` - Primary action color
- `--theme-primary-text` - Text on primary buttons
- `--theme-text` - Main text color
- `--theme-text-muted` - Secondary/muted text
- `--theme-border` - Border color
- `--theme-error` - Error states
- `--theme-success` - Success states
- `--theme-warning` - Warning states

### Typography
- `--theme-font-family` - Font family
- `--theme-body-size` - Base font size
- `--theme-line-height` - Line height

### Spacing & Effects
- `--theme-radius-sm` - Small border radius
- `--theme-radius-md` - Medium border radius
- `--theme-radius-lg` - Large border radius
- `--theme-shadow-card` - Card shadow
- `--theme-shadow-elevated` - Elevated shadow
- `--theme-shadow-subtle` - Subtle shadow

## How It Works

1. **Theme Selection**: Admin selects theme in Backend Appearance
2. **CSS Injection**: `header.php` injects theme CSS variables into page `<head>`
3. **Automatic Application**: All components use these variables
4. **Instant Update**: Changing theme applies to all pages immediately

## Custom Classes Available

You can use these theme-aware utility classes in your pages:

```html
<!-- Theme-aware backgrounds -->
<div class="theme-bg">Background</div>
<div class="theme-surface">Surface</div>

<!-- Theme-aware text -->
<p class="theme-text">Main text</p>
<p class="theme-text-muted">Muted text</p>

<!-- Theme-aware borders -->
<div class="theme-border">Border</div>

<!-- Theme-aware primary color -->
<span class="theme-primary">Primary text</span>
<button class="theme-primary-bg">Primary button</button>
```

## Best Practices

1. **Use Admin Classes**: Prefer `.admin-btn`, `.admin-card`, `.admin-form-input` over Tailwind
2. **Avoid Hardcoded Colors**: Don't use `bg-blue-500`, use `admin-btn-primary` instead
3. **Use Theme Variables**: Reference `var(--theme-*)` in custom CSS
4. **Test All Themes**: Verify your page looks good in all 7 themes

## Testing

To test theme integration:

1. Go to **Settings > Backend Appearance**
2. Select each theme one by one
3. Navigate through all admin pages
4. Verify:
   - Colors match theme
   - Fonts match theme
   - Borders and shadows match theme
   - All UI elements are consistent

## Future Enhancements

- Theme preview without page reload
- Per-section theme customization
- Theme export/import
- Custom theme builder

