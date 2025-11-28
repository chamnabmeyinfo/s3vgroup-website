# S3V Group Website

A complete PHP website for S3V Group, a leading supplier of warehouse and factory equipment in Cambodia.

## 📋 Features

- **Product Catalog** - Browse and view warehouse and factory equipment
- **Quote Request System** - Customers can request quotes online
- **Admin Dashboard** - Manage products, categories, quotes, team, testimonials, and more
- **Responsive Design** - Works on all devices (desktop, tablet, mobile)
- **Modern UI** - Beautiful, professional design with animations
- **MySQL Database** - Uses standard MySQL/MariaDB for cPanel hosting

## 🚀 Quick Start

### For Local Development (XAMPP)

1. **Install XAMPP** - https://www.apachefriends.org/
2. **Copy project** to `C:\xampp\htdocs\s3vgroup\`
3. **Create database** in phpMyAdmin
4. **Import database** - See `LOCAL-SETUP.md` for details
5. **Visit** - `http://localhost/s3vgroup/`

### For Live Server (cPanel)

1. **Push to GitHub** - Code is already on GitHub
2. **Deploy to cPanel** - Use Git Version Control in cPanel
3. **Import database** - Visit `import-database.php` (delete after use!)
4. **Visit** - `https://s3vgroup.com/`

See `LIVE-SETUP-GUIDE.md` for complete setup instructions.

## 📚 Documentation

- **README.md** (this file) - Project overview and quick start
- **docs/backend-architecture-current.md** - Current backend architecture analysis
- **docs/backend-architecture-new.md** - New Apple-like backend architecture design
- **FEATURES-OVERVIEW.md** - Complete features list
- **ADMIN-ORGANIZATION.md** - Admin panel structure
- **DATABASE-MANAGER-GUIDE.md** - Database management tool guide
- **DATABASE-SYNC-GUIDE.md** - Database synchronization guide (local ↔ cPanel)
- **SCHEMA-SYNC-GUIDE.md** - Database schema synchronization
- **IMAGE-OPTIMIZATION-GUIDE.md** - Image optimization reference
- **AUTOMATIC-IMAGE-OPTIMIZATION.md** - Automatic image optimization guide
- **PERFORMANCE-RECOMMENDATIONS.md** - Performance optimization guide

## 🏗️ Backend Overview

### Architecture

The backend follows a **Clean Architecture / Layered Architecture** pattern with clear separation of concerns:

- **HTTP Layer** (`app/Http/`) - Controllers, middleware, request validation, responses
- **Application Layer** (`app/Application/`) - Application services that orchestrate use cases
- **Domain Layer** (`app/Domain/`) - Business logic, repositories, domain exceptions
- **Infrastructure Layer** (`app/Infrastructure/`) - Database implementations, validation, logging

### Key Features

- ✅ **Consistent API responses** - All endpoints return standardized JSON format
- ✅ **Centralized error handling** - All exceptions handled consistently
- ✅ **Request validation** - Clean validation with friendly error messages
- ✅ **Structured logging** - Logging with levels and context
- ✅ **Authentication middleware** - Secure session-based authentication
- ✅ **Type safety** - Strict types throughout

### API Response Format

**Success:**
```json
{
  "data": { ... },
  "error": null,
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

**Error:**
```json
{
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The product name is required.",
    "details": { ... }
  },
  "meta": {
    "timestamp": "2025-01-27T10:00:00Z"
  }
}
```

### Environment Configuration

Create a `.env` file (or use `env.example`):

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=info
DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_user
DB_PASS=your_password
```

### Running Tests

```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test suite
php vendor/bin/phpunit tests/Unit
php vendor/bin/phpunit tests/Integration
```

### Adding a New Endpoint

1. Create Request class (`app/Http/Requests/CreateXxxRequest.php`)
2. Create Controller (`app/Http/Controllers/XxxController.php`)
3. Create/Update Service (`app/Application/Services/XxxService.php`)
4. Create/Update Repository (`app/Domain/Xxx/XxxRepository.php`)
5. Add route in endpoint file

See `docs/backend-architecture-new.md` for detailed architecture documentation.

## 🗄️ Database

The project includes SQL files for easy setup:

- `sql/schema.sql` - Database structure (tables, indexes)
- `sql/site_options.sql` - Site configuration options
- `sql/sample_data.sql` - Sample data (products, teams, testimonials, etc.)

**Import automatically:** Visit `import-database.php` after deployment (delete after use!)

**Sync databases:** Use `bin/sync-database.php` to sync between localhost and cPanel - See `DATABASE-SYNC-GUIDE.md`

## 🛠️ Project Structure

```
s3vgroup/
├── admin/              # Admin panel
├── api/                # API endpoints
├── app/                # Application core (Domain, Support, Database)
├── bin/                # Utility scripts (see below)
├── bootstrap/          # Application bootstrap
├── config/             # Configuration files
├── database/           # Database migrations
├── includes/           # Templates, CSS, JS
├── sql/                # SQL files (schema, data)
├── uploads/            # Uploaded files
└── index.php           # Homepage
```

## 🔧 Utility Scripts (bin/)

### Database Management
- `db-manager.php` - Database management tool (backup, restore, etc.)
- `sync-database.php` - Sync database between local and live
- `auto-sync-database.php` - Automated database sync
- `auto-sync-schema.php` - Sync database schema
- `verify-database-schema.php` - Verify schema integrity
- `migrate-wordpress-content.php` - WordPress content migration

### Image Optimization
- `compress-large-images-to-300kb.php` - Compress large images to 300KB
- `optimize-product-images.php` - Optimize product images
- `check-gd-support.php` - Check if GD extension is available

### Maintenance
- `cleanup.php` - General cleanup tasks
- `comprehensive-cleanup.php` - Comprehensive cleanup
- `project-cleanup.php` - Remove temporary files and old documentation

### Automation
- `auto-sync-scheduled.ps1` - Scheduled database sync (PowerShell)
- `auto-sync-schema-scheduled.ps1` - Scheduled schema sync (PowerShell)

## 🔧 Configuration

### Database Configuration

Edit `config/database.local.php` (create from template):
- Database host
- Database name
- Username
- Password

### Site Configuration

Edit `config/site.php`:
- Site name
- Contact information
- Social media links

### Cache Configuration

- Toggle caching in **Admin → Site Options → Performance** (`enable_caching`)
- Override globally via `.env`:
  - `CACHE_MODE=auto|on|off`
  - `CACHE_TTL=3600` (seconds)
- When `APP_ENV` is `local`/`development`, caching stays off to keep updates instant.

## 📦 Admin Panel

Access: `/admin/login.php`

**Default credentials:**
- Email: `admin@s3vtgroup.com`
- Password: `admin123` (change in production!)

**Features:**
- Product management
- Category management
- Quote request management
- Team member management
- Testimonials
- Hero slider
- Site options/settings
- And more!

## 🚀 Deployment

### Using GitHub + cPanel

1. **Push to GitHub:**
   ```powershell
   git add .
   git commit -m "Your message"
   git push
   ```

2. **Deploy to cPanel:**
   - Go to cPanel → Git Version Control
   - Pull or Deploy → Update

3. **Import Database:**
   - Visit: `https://yourdomain.com/import-database.php`
   - Click "Start Import"
   - **Delete `import-database.php` after use!**

## 📝 Requirements

- PHP 7.4+ (8.2+ recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite
- PHP Extensions: `pdo`, `pdo_mysql`, `mbstring`, `json`

## 🔒 Security Notes

- **Delete `import-database.php`** after importing database
- Change admin password in production
- Keep `.env` file gitignored
- Don't commit `config/database.local.php` (already gitignored)

## 📄 License

Proprietary - S3V Group

## 🆘 Support

For setup help, see:
- `LOCAL-SETUP.md` - Local development
- `LIVE-SETUP-GUIDE.md` - Live server setup
- `AUTO-IMPORT-DATABASE.md` - Database import

---

**Status:** ✅ Production Ready

**Last Updated:** 2025
