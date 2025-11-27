# Translation System Documentation

## Overview

A comprehensive translation system that supports:
- **Manual Translation Management** - Admin interface for managing translations
- **Automatic Translation** - Integration with Google Translate API and LibreTranslate
- **Frontend Language Switcher** - User-friendly language selection component
- **Multi-language Support** - Support for multiple languages with namespaces

## Features

### 1. Backend Translation Management

#### Admin Interface
- **Location**: `/admin/translations.php`
- **Features**:
  - View translations by language and namespace
  - Add/Edit/Delete translations manually
  - Auto-translate missing translations
  - Mark translations as auto-translated or needing review
  - Export translations

#### Database Structure

**Languages Table**:
- `id` - Language ID (e.g., 'en', 'km')
- `name` - Language name in English
- `native_name` - Language name in native script
- `code` - Language code (ISO 639-1)
- `flag` - Emoji flag
- `is_default` - Default language flag
- `is_active` - Active status
- `sort_order` - Display order

**Translations Table**:
- `id` - Translation ID
- `language_code` - Language code (FK to languages)
- `key_name` - Translation key
- `namespace` - Namespace (e.g., 'general', 'products', 'homepage')
- `value` - Translated text
- `is_auto_translated` - Auto-translation flag
- `needs_review` - Review flag

### 2. Automatic Translation

#### Supported Providers

1. **Google Translate API** (Requires API Key)
   - High quality translations
   - Requires API key in site options (`google_translate_api_key`)
   - Paid service (free tier available)

2. **LibreTranslate** (Free, Open Source)
   - No API key required
   - Free alternative
   - Good quality for common languages

#### Usage

1. Go to `/admin/translations.php`
2. Select source and target languages
3. Click "Auto Translate Missing"
4. System will translate all missing keys
5. Translations are marked as `is_auto_translated = true` and `needs_review = true`

### 3. Frontend Language Switcher

#### Component Location
- **File**: `includes/language-switcher.php`
- **Usage**: Include in header or footer

```php
<?php include __DIR__ . '/includes/language-switcher.php'; ?>
```

#### Features
- Dropdown menu with all active languages
- Visual flag indicators
- Current language highlighted
- Automatic page reload on language change
- Cookie and session persistence

### 4. Translation Helper Functions

#### `__(string $key, ?string $namespace = 'general', ?string $default = null): string`
Translate a key in the current language.

```php
echo __('welcome_message', 'general', 'Welcome');
```

#### `getCurrentLanguage(): string`
Get the current language code.

```php
$lang = getCurrentLanguage(); // Returns 'en', 'km', etc.
```

#### `setCurrentLanguage(string $languageCode): void`
Set the current language.

```php
setCurrentLanguage('km');
```

#### `getTranslations(?string $namespace = null): array`
Get all translations for current language.

```php
$translations = getTranslations('products');
```

#### `getAvailableLanguages(bool $activeOnly = true): array`
Get list of available languages.

```php
$languages = getAvailableLanguages();
```

## API Endpoints

### Public APIs

#### `GET /api/translations/index.php?lang=en&namespace=general`
Get translations for a language and namespace.

**Response**:
```json
{
  "status": "success",
  "data": {
    "translations": {
      "general": {
        "welcome": {
          "value": "Welcome",
          "is_auto_translated": false,
          "needs_review": false
        }
      }
    },
    "language": "en"
  }
}
```

#### `POST /api/translations/set-language.php`
Set the current language.

**Request**:
```json
{
  "language": "km"
}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "message": "Language changed successfully.",
    "language": "km"
  }
}
```

### Admin APIs

#### `GET /api/admin/translations/index.php?lang=en&namespace=general`
Get translations for admin panel.

#### `POST /api/admin/translations/index.php`
Create or update a translation.

**Request**:
```json
{
  "key": "welcome_message",
  "language_code": "km",
  "namespace": "general",
  "value": "សូមស្វាគមន៍",
  "is_auto_translated": false,
  "needs_review": false
}
```

#### `DELETE /api/admin/translations/index.php?key=welcome_message&lang=km&namespace=general`
Delete a translation.

#### `POST /api/admin/translations/auto-translate.php`
Auto-translate missing translations.

**Request**:
```json
{
  "source_lang": "en",
  "target_lang": "km",
  "namespace": "general"
}
```

## Default Languages

The system comes with 4 default languages:

1. **English (en)** 🇺🇸 - Default
2. **Khmer (km)** 🇰🇭 - ភាសាខ្មែរ
3. **Chinese (zh)** 🇨🇳 - 中文
4. **Thai (th)** 🇹🇭 - ไทย

## Usage Examples

### In PHP Templates

```php
<?php
require_once __DIR__ . '/includes/functions.php';
?>

<h1><?php echo __('welcome_title', 'homepage', 'Welcome'); ?></h1>
<p><?php echo __('welcome_message', 'homepage', 'Welcome to our website'); ?></p>
```

### In JavaScript

```javascript
// Load translations
fetch('/api/translations/index.php?lang=km&namespace=general')
  .then(res => res.json())
  .then(data => {
    const translations = data.data.translations.general;
    document.getElementById('title').textContent = translations.welcome_title.value;
  });
```

### Setting Up Google Translate API

1. Go to `/admin/options.php`
2. Find or create option: `google_translate_api_key`
3. Enter your Google Translate API key
4. Save

## Namespaces

Organize translations by namespace:

- `general` - General site translations
- `products` - Product-related translations
- `homepage` - Homepage-specific translations
- `navigation` - Navigation menu translations
- `footer` - Footer translations
- Custom namespaces as needed

## Best Practices

1. **Use Descriptive Keys**: `welcome_message` instead of `msg1`
2. **Organize by Namespace**: Group related translations
3. **Review Auto-translations**: Always review auto-translated content
4. **Fallback to Default**: Always provide a default value
5. **Cache Translations**: Consider caching for performance

## Migration

To set up the translation system:

```bash
php database/run-translation-migration.php
```

This creates:
- `languages` table
- `translations` table
- Default language entries

## Files Created

1. `database/migrations/20241203_translations.php` - Database migration
2. `app/Domain/Translation/TranslationRepository.php` - Data access layer
3. `app/Domain/Translation/TranslationService.php` - Business logic
4. `app/Domain/Translation/AutoTranslationService.php` - Auto-translation
5. `admin/translations.php` - Admin interface
6. `api/admin/translations/index.php` - Admin API
7. `api/admin/translations/auto-translate.php` - Auto-translate API
8. `api/translations/index.php` - Public translations API
9. `api/translations/set-language.php` - Language switcher API
10. `includes/translation.php` - Helper functions
11. `includes/language-switcher.php` - Frontend component

## Future Enhancements

- Translation import/export (JSON, CSV)
- Translation memory
- Context-aware translations
- Pluralization support
- Date/time localization
- Number/currency formatting

