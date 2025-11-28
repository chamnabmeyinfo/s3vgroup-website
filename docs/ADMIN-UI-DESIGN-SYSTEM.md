# Admin UI Design System

## ⚠️ CRITICAL REQUIREMENT

**ALL backend pages MUST follow this design system. This applies to:**
- ✅ Existing pages
- ✅ Newly created pages  
- ✅ Updated pages
- ✅ Refactored pages
- ✅ Any admin interface component

**There are NO exceptions.** Every page in the admin panel must maintain visual and functional consistency.

See `BACKEND-STYLE-REQUIREMENTS.md` for the complete checklist and enforcement guidelines.

---

## Overview

All admin pages now follow a unified, Apple-like design system that emphasizes simplicity, clarity, and delightful user experience.

## Design Principles

### 1. **Consistency**
- Same header style across all pages
- Unified card-based layouts
- Consistent spacing and typography
- Matching color scheme

### 2. **Clarity**
- Clear visual hierarchy
- Intuitive navigation
- Helpful icons and indicators
- Readable typography

### 3. **Delight**
- Smooth transitions
- Hover effects
- Gradient accents
- Modern shadows

## Unified Components

### Header Pattern

All pages use the same header structure:

```html
<div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[color]-500 to-[color]-600 flex items-center justify-center">
                        <!-- Icon -->
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Page Title</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Page description</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Color Scheme by Page:**
- **Site Options**: Blue (`from-blue-500 to-blue-600`)
- **Media Library**: Purple (`from-purple-500 to-purple-600`)
- **Plugins**: Indigo (`from-indigo-500 to-indigo-600`)

### Statistics Cards

Consistent card design for displaying metrics:

```html
<div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-2">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Label</p>
        <svg class="w-4 h-4 text-gray-400"><!-- Icon --></svg>
    </div>
    <p class="text-2xl font-semibold text-gray-900">Value</p>
</div>
```

**Variations:**
- **Warning cards**: Yellow gradient (`from-yellow-50 to-yellow-100`, `border-yellow-200`)
- **Success cards**: Green gradient (`from-green-50 to-green-100`, `border-green-200`)

### Form Fields

Unified form input styling:

```html
<input
    type="text"
    class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
>
```

**Features:**
- Consistent padding (`px-4 py-3`)
- Rounded corners (`rounded-lg`)
- Blue focus ring
- Smooth transitions

### Tables

Modern table design:

```html
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Table Title</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <!-- Headers -->
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <tr class="hover:bg-gray-50 transition-colors">
                    <!-- Rows -->
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

### Buttons

Consistent button styles:

**Primary Button:**
```html
<button class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg text-sm font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-md hover:shadow-lg">
    Button Text
</button>
```

**Secondary Button:**
```html
<button class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all shadow-sm hover:shadow">
    Button Text
</button>
```

## Page-Specific Features

### Site Options
- **Sidebar Navigation**: Sticky sidebar with section navigation
- **Section Cards**: Grouped settings in expandable sections
- **Field Cards**: Each setting in its own card
- **Save Bar**: Sticky bottom bar with save/reset buttons

### Media Library
- **Statistics Dashboard**: 4-card grid showing media stats
- **Filter Card**: Advanced filtering options
- **Media Table**: Detailed file information with previews
- **Usage Tracking**: Shows where files are used

### Plugins
- **Statistics Cards**: 3-card grid (Total, Active, Inactive)
- **Plugin Table**: List with status indicators
- **Action Buttons**: Color-coded activate/deactivate buttons
- **Info Box**: Development guide with code examples

## Color Palette

### Primary Colors
- **Blue**: `#0b3a63` (Primary brand color)
- **Blue Gradient**: `from-blue-500 to-blue-600`
- **Blue Light**: `blue-50`, `blue-100`

### Status Colors
- **Success/Active**: Green (`green-600`, `green-50`)
- **Warning**: Yellow (`yellow-600`, `yellow-50`)
- **Error**: Red (`red-600`, `red-50`)
- **Info**: Blue (`blue-600`, `blue-50`)

### Neutral Colors
- **Gray Scale**: `gray-50` to `gray-900`
- **Borders**: `gray-200`, `gray-300`
- **Text**: `gray-900` (primary), `gray-600` (secondary), `gray-500` (tertiary)

## Typography

### Headings
- **H1**: `text-2xl font-semibold text-gray-900`
- **H2**: `text-xl font-semibold text-gray-900`
- **H3**: `text-lg font-semibold`

### Body Text
- **Primary**: `text-sm text-gray-900`
- **Secondary**: `text-sm text-gray-600`
- **Tertiary**: `text-xs text-gray-500`

### Labels
- **Field Labels**: `text-sm font-semibold text-gray-900`
- **Section Labels**: `text-xs font-medium text-gray-500 uppercase tracking-wide`

## Spacing System

- **Container Padding**: `px-6 py-5` (headers), `p-6` (content)
- **Card Padding**: `p-5` or `p-6`
- **Field Spacing**: `space-y-6` or `space-y-8`
- **Gap Between Elements**: `gap-4`, `gap-6`

## Shadows

- **Cards**: `shadow-sm` (default), `shadow-md` (hover)
- **Buttons**: `shadow-sm` (default), `shadow-md` (hover), `shadow-lg` (active)
- **Modals/Panels**: `shadow-lg`

## Transitions

All interactive elements use smooth transitions:

```css
transition-all
transition-colors
transition-shadow
duration-200
```

## Icons

- **Hero Icons** (SVG) used throughout
- **Consistent sizing**: `w-4 h-4` (small), `w-5 h-5` (medium), `w-6 h-6` (large)
- **Color matching**: Icons match their context (blue for primary, gray for secondary)

## Responsive Design

- **Mobile**: Single column layouts
- **Tablet**: 2-column grids where appropriate
- **Desktop**: Full multi-column layouts
- **Breakpoints**: `md:` (768px), `lg:` (1024px)

## Best Practices

1. **Always use rounded corners**: `rounded-lg` or `rounded-xl`
2. **Consistent borders**: `border border-gray-200`
3. **Hover states**: Always add hover effects
4. **Focus states**: Use `focus:ring-2 focus:ring-blue-500`
5. **Empty states**: Include helpful icons and messages
6. **Loading states**: Show spinners or skeletons
7. **Error states**: Use red color scheme with icons

## Implementation Checklist

When creating a new admin page:

- [ ] Use unified header pattern with gradient icon
- [ ] Add statistics cards if applicable
- [ ] Use consistent form field styling
- [ ] Apply modern table design
- [ ] Include helpful empty states
- [ ] Add smooth transitions
- [ ] Use consistent button styles
- [ ] Match color scheme to page purpose
- [ ] Ensure responsive design
- [ ] Add hover and focus states

**⚠️ IMPORTANT:** This checklist is MANDATORY. See `BACKEND-STYLE-REQUIREMENTS.md` for the complete requirements and enforcement guidelines.

## Examples

See these pages for reference:
- `ae-admin/options.php` - Site Options
- `ae-admin/media-library.php` - Media Library
- `ae-admin/plugins.php` - Plugins
- `ae-admin/products.php` - Products (also follows this system)

