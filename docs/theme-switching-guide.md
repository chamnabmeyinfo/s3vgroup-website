# Theme Switching Guide - Backend Admin

## ✅ Yes, Theme Switching Applies to Backend!

When you switch a theme in **Settings > Backend Appearance**, it **immediately applies to the entire admin panel**.

## How It Works

### Step-by-Step Process:

1. **Select Theme** → Go to **Settings > Backend Appearance**
2. **Click Theme Card** → Click on any theme (MacBook, Windows 11, Dark Pro, etc.)
3. **Save Preference** → Theme preference is saved to database with scope `backend_admin`
4. **Page Reloads** → Page automatically refreshes
5. **Theme Applied** → All admin pages now use the selected theme

### Technical Flow:

```
User clicks theme
    ↓
JavaScript sends POST to /api/admin/theme/backend.php
    ↓
API saves to user_theme_preferences table (scope: 'backend_admin')
    ↓
Page reloads
    ↓
header.php loads theme preference from database
    ↓
CSS variables injected into <head>
    ↓
All admin-styles.css uses these variables
    ↓
✅ Entire admin panel uses new theme!
```

## What Gets Themed

When you switch themes, **everything** changes:

- ✅ **Colors** - Backgrounds, text, borders, buttons
- ✅ **Fonts** - Font family, sizes, line heights
- ✅ **Shadows** - Card shadows, elevated shadows
- ✅ **Border Radius** - All rounded corners
- ✅ **Buttons** - Primary, secondary, danger buttons
- ✅ **Forms** - Inputs, selects, textareas
- ✅ **Tables** - Headers, rows, borders
- ✅ **Cards** - All card components
- ✅ **Modals** - Dialog boxes
- ✅ **Toasts** - Notifications
- ✅ **Navigation** - Sidebar, menu items
- ✅ **Icons** - Icon colors

## Available Themes

1. **Light** (Default) - Clean light theme
2. **Dark** - Elegant dark mode
3. **MacBook Style** - macOS-inspired
4. **Windows 11 Style** - Fluent Design
5. **Dark Pro** - Professional dark
6. **Minimal** - Ultra-minimal
7. **High Contrast** - Accessibility-focused

## Testing Theme Switching

1. Go to **Settings > Backend Appearance**
2. Click on **MacBook Style** theme
3. Page reloads → You should see:
   - San Francisco font
   - Apple blue colors
   - Soft shadows
4. Navigate to **Products** page → Theme still applied
5. Navigate to **Dashboard** → Theme still applied
6. **All pages** use the same theme!

## Troubleshooting

### Theme Not Applying?

1. **Check Database** - Make sure migrations ran:
   ```bash
   php database/run-migration.php
   ```

2. **Check Session** - Make sure you're logged in as admin

3. **Clear Cache** - Hard refresh browser (Ctrl+F5)

4. **Check Console** - Open browser console for errors

### Theme Partially Applied?

- Make sure `admin-styles.css` is loaded
- Check that CSS variables are injected in `<head>`
- Verify theme has all required config fields

## Per-User Themes

Each admin user can have their own theme preference. The system:
- Stores preference per user ID
- Uses scope `backend_admin` (only affects admin panel)
- Falls back to default theme if no preference set

## Quick Test

1. Visit: `/ae-admin/backend-appearance.php`
2. Click **Windows 11 Style** theme
3. Wait for page reload
4. Check sidebar → Should have Segoe UI font
5. Check buttons → Should have Windows 11 blue
6. Navigate to any page → Theme persists!

**The theme applies to the entire backend admin panel!** 🎨

