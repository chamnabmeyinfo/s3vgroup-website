# Backend Style Requirements

## ⚠️ CRITICAL: Universal Style Application

**ALL backend pages MUST follow the unified design system. This applies to:**
- ✅ Existing pages
- ✅ Newly created pages
- ✅ Updated pages
- ✅ Refactored pages
- ✅ Any admin interface component

**There are NO exceptions.** Every page in the admin panel must maintain visual and functional consistency.

---

## Design System Checklist

When creating or updating ANY admin page, use this checklist:

### ✅ Header Section (REQUIRED)
- [ ] Modern header with gradient icon badge
- [ ] Icon color matches page purpose (see color guide below)
- [ ] Page title: `text-2xl font-semibold text-gray-900`
- [ ] Description: `text-sm text-gray-500 mt-0.5`
- [ ] Container: `bg-white rounded-xl border border-gray-200 shadow-sm`
- [ ] Header padding: `px-6 py-5`
- [ ] Border bottom: `border-b border-gray-200`

### ✅ Statistics Cards (if applicable)
- [ ] Card container: `bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow`
- [ ] Label: `text-xs font-medium text-gray-500 uppercase tracking-wide`
- [ ] Value: `text-2xl font-semibold text-gray-900`
- [ ] Icon in top-right corner: `w-4 h-4 text-gray-400`
- [ ] Grid layout: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4`

### ✅ Form Elements (REQUIRED)
- [ ] Input fields: `w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[color]-500 focus:border-[color]-500 transition-all`
- [ ] Labels: `block text-sm font-semibold text-gray-700 mb-2`
- [ ] Select dropdowns: Same styling as inputs
- [ ] Textareas: Same styling as inputs
- [ ] Checkboxes: `w-4 h-4 text-[color]-600 rounded focus:ring-[color]-500`
- [ ] Radio buttons: `w-4 h-4 text-[color]-600 focus:ring-[color]-500`

### ✅ Buttons (REQUIRED)
**Primary Button:**
```html
<button class="px-6 py-2.5 bg-gradient-to-r from-[color]-600 to-[color]-700 text-white rounded-lg text-sm font-semibold hover:from-[color]-700 hover:to-[color]-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color]-500 transition-all shadow-md hover:shadow-lg">
    Button Text
</button>
```

**Secondary Button:**
```html
<button class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all shadow-sm hover:shadow">
    Button Text
</button>
```

### ✅ Cards/Sections (REQUIRED)
- [ ] Container: `bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden`
- [ ] Header section: `px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200`
- [ ] Content section: `p-6`
- [ ] Section title: `text-lg font-semibold text-gray-900`

### ✅ Tables (if applicable)
- [ ] Container: `bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden`
- [ ] Header: `px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200`
- [ ] Table header: `bg-gray-50`
- [ ] Table header cells: `px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider`
- [ ] Table rows: `hover:bg-gray-50 transition-colors`
- [ ] Table cells: `px-6 py-4`

### ✅ Empty States (REQUIRED)
- [ ] Container: `px-6 py-12 text-center`
- [ ] Icon: `w-12 h-12 text-gray-400 mb-4`
- [ ] Title: `text-sm font-medium text-gray-900`
- [ ] Description: `text-xs text-gray-500 mt-1`

### ✅ Info/Warning/Error Messages
**Info:**
```html
<div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5">...</svg>
        <p class="text-sm text-blue-800">Message</p>
    </div>
</div>
```

**Warning:**
```html
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5">...</svg>
        <p class="text-sm text-yellow-800">Message</p>
    </div>
</div>
```

**Error:**
```html
<div class="bg-red-50 border border-red-200 rounded-xl p-4">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5">...</svg>
        <p class="text-sm text-red-800">Message</p>
    </div>
</div>
```

---

## Color Scheme by Page Type

### Page Icon Colors
- **Site Options**: Blue (`from-blue-500 to-blue-600`)
- **Media Library**: Purple (`from-purple-500 to-purple-600`)
- **Plugins**: Indigo (`from-indigo-500 to-indigo-600`)
- **Database Sync**: Emerald (`from-emerald-500 to-emerald-600`)
- **Optional Features**: Amber (`from-amber-500 to-amber-600`)
- **Products**: Blue (`from-blue-500 to-blue-600`)
- **General Settings**: Gray (`from-gray-500 to-gray-600`)

### Button Colors
- **Primary Actions**: Match page icon color
- **Danger Actions**: Red (`from-red-600 to-red-700`)
- **Success Actions**: Green (`from-green-600 to-green-700`)
- **Secondary Actions**: Gray (`border-gray-300`)

---

## Spacing System

- **Container Padding**: `px-6 py-5` (headers), `p-6` (content)
- **Card Padding**: `p-5` or `p-6`
- **Field Spacing**: `space-y-4` or `space-y-6`
- **Gap Between Elements**: `gap-4`, `gap-6`
- **Section Spacing**: `space-y-6` or `mb-6`

---

## Typography

### Headings
- **H1 (Page Title)**: `text-2xl font-semibold text-gray-900`
- **H2 (Section Title)**: `text-xl font-semibold text-gray-900`
- **H3 (Subsection)**: `text-lg font-semibold text-gray-900`

### Body Text
- **Primary**: `text-sm text-gray-900`
- **Secondary**: `text-sm text-gray-600`
- **Tertiary**: `text-xs text-gray-500`

### Labels
- **Field Labels**: `text-sm font-semibold text-gray-700`
- **Section Labels**: `text-xs font-medium text-gray-500 uppercase tracking-wide`

---

## Required Structure Template

```html
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Modern Header -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[color]-500 to-[color]-600 flex items-center justify-center">
                    <!-- Icon SVG -->
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Page Title</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Page description</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards (if needed) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card content -->
    </div>

    <!-- Main Content Cards -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Section Title</h2>
        </div>
        <div class="p-6">
            <!-- Content -->
        </div>
    </div>
</div>
```

---

## Examples to Follow

Reference these pages for correct implementation:
- ✅ `ae-admin/options.php` - Site Options
- ✅ `ae-admin/media-library.php` - Media Library
- ✅ `ae-admin/plugins.php` - Plugins
- ✅ `ae-admin/database-sync.php` - Database Sync
- ✅ `ae-admin/optional-features.php` - Optional Features
- ✅ `ae-admin/products.php` - Products

---

## Before Creating/Updating Any Page

1. **Check existing pages** - Review similar pages for consistency
2. **Use the template** - Start with the required structure template
3. **Follow color scheme** - Use appropriate colors for page type
4. **Test responsiveness** - Ensure mobile/tablet/desktop layouts work
5. **Verify spacing** - Use consistent spacing system
6. **Check typography** - Follow typography guidelines
7. **Add transitions** - Include smooth hover/focus effects
8. **Test empty states** - Include helpful empty state messages

---

## Enforcement

**This is not optional.** All backend pages must:
- ✅ Use the unified header pattern
- ✅ Follow consistent card layouts
- ✅ Use standardized form styling
- ✅ Match button styles
- ✅ Follow spacing guidelines
- ✅ Use consistent typography
- ✅ Include proper empty states
- ✅ Have smooth transitions

**If a page doesn't follow these guidelines, it must be updated immediately.**

---

## Quick Reference

### Common Classes
- **Container**: `max-w-7xl mx-auto space-y-6`
- **Card**: `bg-white rounded-xl border border-gray-200 shadow-sm`
- **Input**: `w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[color]-500`
- **Button Primary**: `px-6 py-2.5 bg-gradient-to-r from-[color]-600 to-[color]-700 text-white rounded-lg`
- **Button Secondary**: `px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white`

### Common Icons (Heroicons)
- Settings: `M10.325 4.317c.426-1.756 2.924-1.756 3.35 0...`
- Database: `M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9...`
- Media: `M4 16l4.586-4.586a2 2 0 012.828 0L16 16...`
- Plugins: `M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477...`

---

**Remember: Consistency is key. Every page should feel like part of the same system.**

