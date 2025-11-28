# Menu System Guide

## Overview

A WordPress-like menu management system with **mega menu support** that allows you to create, organize, and manage navigation menus from the backend, which automatically appear on the frontend.

## Features

✅ **WordPress-like Interface** - Familiar drag-and-drop menu management  
✅ **Mega Menu Support** - Create beautiful multi-column mega menus  
✅ **Multiple Menu Locations** - Primary, Footer, Mobile menus  
✅ **Hierarchical Structure** - Unlimited nested menu items  
✅ **Icon Support** - Add emoji icons to menu items  
✅ **Custom CSS Classes** - Style individual menu items  
✅ **Modern Design** - Follows unified backend design system  
✅ **Responsive** - Works perfectly on mobile and desktop  

---

## Getting Started

### 1. Run Database Migration

The menu tables are automatically created. If you need to recreate them:

```bash
php database/migrations/create_menus_tables.php
```

### 2. Access Menu Management

1. Go to **Admin Panel** → **Settings** → **Menus**
2. You'll see the menu management interface

---

## Creating a Menu

1. Click **"Create New Menu"** button
2. Enter:
   - **Menu Name**: e.g., "Main Menu"
   - **Location**: Choose from:
     - `primary` - Primary Navigation (default)
     - `footer` - Footer Menu
     - `mobile` - Mobile Menu
   - **Description**: Optional description
3. Click **"Create Menu"**

---

## Adding Menu Items

1. Select a menu from the sidebar
2. Click **"Add Item"** button
3. Fill in the form:
   - **Title**: Menu item text (e.g., "Products")
   - **URL**: Link destination (e.g., `/products.php` or `https://example.com`)
   - **Icon**: Optional emoji icon (e.g., 🏠, 📦, 📞)
   - **Target**: `_self` (same window) or `_blank` (new window)
   - **CSS Classes**: Custom CSS classes for styling
4. Click **"Save Item"**

---

## Mega Menu Setup

Mega menus allow you to create beautiful multi-column dropdown menus with images and custom content.

### Enable Mega Menu

1. When adding/editing a menu item, check **"Enable Mega Menu"**
2. Configure:
   - **Mega Menu Columns**: 2, 3, 4, or 5 columns
   - **Mega Menu Image**: Optional header image URL
   - **Mega Menu Content**: Custom HTML content

### Mega Menu Structure

- The parent item becomes the mega menu trigger
- Child items are displayed in columns
- You can add custom HTML content
- Images can be displayed at the top

### Example Mega Menu

```
Products (Mega Menu - 3 columns)
├── Column 1
│   ├── Forklifts
│   ├── Pallet Jacks
│   └── Stackers
├── Column 2
│   ├── Shelving
│   ├── Racking
│   └── Conveyors
└── Column 3
    ├── Accessories
    └── Parts
```

---

## Organizing Menu Items

### Drag and Drop (Coming Soon)

Currently, menu items are ordered by creation time. Drag-and-drop reordering will be added in a future update.

### Creating Submenus

1. Add a parent menu item
2. Add child items and set them as children of the parent
3. Child items will appear in a dropdown

---

## Menu Locations

### Primary Navigation

- Location: `primary`
- Displayed in: Main header navigation
- Usage: Main site navigation

### Footer Menu

- Location: `footer`
- Displayed in: Footer area
- Usage: Footer links, legal pages

### Mobile Menu

- Location: `mobile`
- Displayed in: Mobile navigation
- Usage: Mobile-specific menu

---

## Frontend Integration

The menu system automatically integrates with your frontend header. The dynamic menu widget (`ae-includes/widgets/dynamic-menu.php`) is included in the header and displays menus based on location.

### Manual Integration

If you need to display a menu manually:

```php
<?php
$location = 'primary'; // or 'footer', 'mobile'
include __DIR__ . '/ae-includes/widgets/dynamic-menu.php';
?>
```

---

## API Endpoints

### Admin Endpoints (Protected)

- `GET /api/admin/menus/index.php` - List all menus
- `POST /api/admin/menus/index.php` - Create menu
- `GET /api/admin/menus/item.php?id=X` - Get menu with items
- `PUT /api/admin/menus/item.php?id=X` - Update menu
- `DELETE /api/admin/menus/item.php?id=X` - Delete menu
- `POST /api/admin/menus/items.php?action=create` - Create menu item
- `PUT /api/admin/menus/items.php?id=X` - Update menu item
- `DELETE /api/admin/menus/items.php?id=X` - Delete menu item
- `POST /api/admin/menus/items.php?action=update-order` - Update menu order

### Public Endpoints

- `GET /api/menus/location.php?location=primary` - Get menu by location

---

## Styling

### Custom CSS Classes

Add custom CSS classes to menu items for styling:

1. Edit a menu item
2. Add classes in "CSS Classes" field (e.g., `highlight-button special-link`)
3. Style in your CSS:

```css
.menu-item.highlight-button .menu-link {
    background: orange;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
}
```

### Mega Menu Styling

Mega menus use these classes:
- `.mega-menu-item` - Parent item with mega menu
- `.mega-menu-dropdown` - Mega menu container
- `.mega-menu-content` - Columns container
- `.mega-menu-column` - Individual column
- `.mega-menu-image` - Header image
- `.mega-menu-custom` - Custom content area

---

## Best Practices

1. **Keep it Simple**: Don't create too many nested levels (max 2-3 levels recommended)
2. **Use Icons Sparingly**: Icons enhance but don't overuse them
3. **Test Responsive**: Always test menus on mobile devices
4. **Mega Menu Width**: Keep mega menu content reasonable (3-4 columns max)
5. **Performance**: Limit menu items to 50-100 per menu for best performance

---

## Troubleshooting

### Menu Not Showing

1. Check menu location matches frontend location
2. Verify menu has items
3. Check if menu widget is included in header
4. Clear browser cache

### Mega Menu Not Working

1. Ensure "Enable Mega Menu" is checked
2. Verify parent item has children
3. Check browser console for JavaScript errors
4. Ensure mega menu columns are set correctly

### Styling Issues

1. Check custom CSS classes are correct
2. Verify CSS is loaded after menu styles
3. Use browser inspector to debug
4. Check for CSS conflicts

---

## Database Structure

### `menus` Table

- `id` - Menu ID
- `name` - Menu name
- `slug` - Menu slug
- `location` - Menu location (primary, footer, mobile)
- `description` - Menu description

### `menu_items` Table

- `id` - Item ID
- `menu_id` - Parent menu ID
- `parent_id` - Parent item ID (for submenus)
- `title` - Item title
- `url` - Item URL
- `type` - Item type (custom, page, post, etc.)
- `menu_order` - Display order
- `icon` - Icon (emoji)
- `target` - Link target
- `is_mega_menu` - Mega menu flag
- `mega_menu_columns` - Number of columns
- `mega_menu_image` - Header image URL
- `mega_menu_content` - Custom HTML content

---

## Examples

### Simple Menu

```
Home (/)
Products (/products.php)
About (/about.php)
Contact (/contact.php)
```

### Menu with Submenus

```
Products
├── Forklifts (/products.php?category=forklifts)
├── Pallet Jacks (/products.php?category=pallet-jacks)
└── Stackers (/products.php?category=stackers)
Services
├── Sales (/services/sales.php)
├── Rental (/services/rental.php)
└── Maintenance (/services/maintenance.php)
```

### Mega Menu

```
Products (Mega Menu - 3 columns)
├── [Image: Product showcase]
├── Column 1: Equipment
│   ├── Forklifts
│   ├── Pallet Jacks
│   └── Stackers
├── Column 2: Storage
│   ├── Shelving
│   ├── Racking
│   └── Conveyors
└── Column 3: Custom Content
    └── [HTML: Special offers banner]
```

---

## Support

For issues or questions:
1. Check this guide first
2. Review the code in `app/Domain/Menus/`
3. Check browser console for errors
4. Verify database tables exist

---

**Enjoy your new menu system! 🎉**

