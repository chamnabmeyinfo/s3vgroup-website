# Enhanced Menu System Guide

## 🎉 New Features

### ✅ Multiple Item Types
- **Custom Links** - Add any URL manually
- **Pages** - Browse and add existing pages
- **Posts** - Browse and add blog posts
- **Categories** - Add product categories
- **Products** - Add individual products

### ✅ Multiple Menu Locations
- **Primary Navigation** - Main header menu
- **Secondary Navigation** - Secondary header menu
- **Footer Menu** - Footer links
- **Mobile Menu** - Mobile-specific menu

### ✅ Drag & Drop Ordering
- Drag menu items to reorder
- Visual feedback during dragging
- Automatic save on drop
- Supports nested items

### ✅ Enhanced UI
- Tabbed interface for different item types
- Search functionality for each type
- One-click add from browse lists
- Modern, unified design

---

## How to Use

### 1. Create a Menu

1. Go to **Admin → Settings → Menus**
2. Click **"Create New Menu"**
3. Enter:
   - **Menu Name**: e.g., "Main Menu"
   - **Location**: Choose from:
     - Primary Navigation
     - Secondary Navigation
     - Footer Menu
     - Mobile Menu
4. Click **"Create Menu"**

### 2. Add Menu Items

#### Option A: Custom Link

1. Click **"Add Items"** button
2. Stay on **"Custom Link"** tab
3. Fill in:
   - Title
   - URL
   - Icon (emoji)
   - Target
   - CSS Classes
   - Enable Mega Menu (optional)
4. Click **"Add to Menu"**

#### Option B: Add from Pages/Posts/Categories/Products

1. Click **"Add Items"** button
2. Click on the appropriate tab:
   - **Pages** - Browse your pages
   - **Posts** - Browse blog posts
   - **Categories** - Browse product categories
   - **Products** - Browse products
3. Use search to find items
4. Click **"Add"** button next to any item
5. Item is automatically added with correct URL

### 3. Reorder Menu Items

1. **Drag and Drop**: Click and hold the drag handle (☰) on any menu item
2. Drag to desired position
3. Release to drop
4. Order is automatically saved

### 4. Edit Menu Items

1. Click **"Edit"** button on any menu item
2. Modify:
   - Title
   - URL
   - Icon
   - Target
   - CSS Classes
   - Mega Menu settings
3. Click **"Save Item"**

### 5. Delete Menu Items

1. Click **"Delete"** button on any menu item
2. Confirm deletion
3. Item is removed immediately

---

## Menu Locations

### Primary Navigation

- **Location**: `primary`
- **Display**: Main header navigation
- **Usage**: Primary site navigation
- **Example**: Home, Products, About, Contact

### Secondary Navigation

- **Location**: `secondary`
- **Display**: Secondary header area (if implemented)
- **Usage**: Additional navigation links
- **Example**: Support, Blog, Resources

### Footer Menu

- **Location**: `footer`
- **Display**: Footer area
- **Usage**: Footer links, legal pages
- **Example**: Privacy Policy, Terms, Sitemap

### Mobile Menu

- **Location**: `mobile`
- **Display**: Mobile navigation
- **Usage**: Mobile-specific menu
- **Example**: Simplified navigation for mobile

---

## Item Types

### Custom Link

- **Type**: `custom`
- **Use Case**: External links, custom pages, special URLs
- **Fields**: Title, URL, Icon, Target, CSS Classes

### Page

- **Type**: `page`
- **Use Case**: Link to existing pages
- **Auto-filled**: Title, URL from page
- **Object ID**: Page ID stored for reference

### Post

- **Type**: `post`
- **Use Case**: Link to blog posts
- **Auto-filled**: Title, URL from post
- **Object ID**: Post ID stored for reference

### Category

- **Type**: `category`
- **Use Case**: Link to product categories
- **Auto-filled**: Title, URL from category
- **Object ID**: Category ID stored for reference

### Product

- **Type**: `product`
- **Use Case**: Link to individual products
- **Auto-filled**: Title, URL from product
- **Object ID**: Product ID stored for reference

---

## Drag & Drop Tips

1. **Drag Handle**: Use the ☰ icon on the left to drag
2. **Visual Feedback**: Item becomes semi-transparent while dragging
3. **Nested Items**: Drag child items to reorder within parent
4. **Auto-Save**: Changes save automatically when you drop
5. **Success Indicator**: "Save Menu" button shows "✓ Saved!" when order updates

---

## Mega Menu Setup

1. When adding/editing an item, check **"Enable Mega Menu"**
2. Configure:
   - **Columns**: 2-5 columns
   - **Image**: Optional header image
   - **Content**: Custom HTML content
3. Add child items to populate columns
4. Child items automatically appear in columns

---

## Best Practices

1. **Organize by Location**: Create separate menus for each location
2. **Use Descriptive Names**: Name menus clearly (e.g., "Main Menu", "Footer Links")
3. **Limit Depth**: Keep menu depth to 2-3 levels maximum
4. **Test Responsive**: Always test menus on mobile devices
5. **Use Icons Sparingly**: Icons enhance but don't overuse
6. **Search Before Adding**: Use search to find existing content before creating custom links

---

## Troubleshooting

### Drag & Drop Not Working

- Ensure SortableJS library is loaded
- Check browser console for errors
- Try refreshing the page
- Verify menu has items

### Items Not Appearing on Frontend

- Check menu location matches frontend location
- Verify menu has items
- Clear browser cache
- Check if menu widget is included in template

### Search Not Finding Items

- Verify items exist in database
- Check item status (published vs draft)
- Try different search terms
- Check API endpoint is working

---

## API Reference

### Browse Items

```
GET /api/admin/menus/browse.php?type=pages&search=term
```

**Types**: `pages`, `posts`, `categories`, `products`

### Update Order

```
POST /api/admin/menus/items.php?action=update-order
Body: { "items": [{ "id": "...", "parent_id": "...", "menu_order": 0 }] }
```

---

**Enjoy your enhanced menu system! 🚀**

