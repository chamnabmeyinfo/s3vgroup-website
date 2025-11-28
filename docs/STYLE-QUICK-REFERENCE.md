# Backend Style Quick Reference

**Use this as a quick copy-paste reference when creating or updating any admin page.**

## Page Structure Template

```html
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[COLOR]-500 to-[COLOR]-600 flex items-center justify-center">
                    <!-- SVG Icon -->
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Page Title</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Page description</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Card -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Section Title</h2>
        </div>
        <div class="p-6">
            <!-- Your content here -->
        </div>
    </div>
</div>
```

## Form Input

```html
<label class="block text-sm font-semibold text-gray-700 mb-2">Label</label>
<input type="text" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
```

## Primary Button

```html
<button class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg text-sm font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-md hover:shadow-lg">
    Button Text
</button>
```

## Secondary Button

```html
<button class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all shadow-sm hover:shadow">
    Button Text
</button>
```

## Statistics Card

```html
<div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-2">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Label</p>
        <svg class="w-4 h-4 text-gray-400">...</svg>
    </div>
    <p class="text-2xl font-semibold text-gray-900">Value</p>
</div>
```

## Info Message

```html
<div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-blue-800">Message text</p>
    </div>
</div>
```

## Empty State

```html
<div class="px-6 py-12 text-center">
    <svg class="w-12 h-12 text-gray-400 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <!-- Icon path -->
    </svg>
    <p class="text-sm font-medium text-gray-900">No items found</p>
    <p class="text-xs text-gray-500 mt-1">Description or action</p>
</div>
```

## Color Replacements

Replace `[COLOR]` with:
- `blue` - General settings, primary actions
- `purple` - Media, creative content
- `indigo` - Plugins, extensions
- `emerald` - Database, sync operations
- `amber` - Features, options
- `green` - Success, active states
- `red` - Danger, delete actions
- `yellow` - Warnings

## Common Icon SVGs

**Settings:**
```html
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
</svg>
```

**Database/Sync:**
```html
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
</svg>
```

**Media:**
```html
<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
</svg>
```

---

**Remember: Always check existing pages for consistency before creating new ones!**

