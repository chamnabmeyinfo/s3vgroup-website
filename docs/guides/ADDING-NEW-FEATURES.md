# 🚀 Adding New Features - Quick Guide

## Overview

This guide shows you how to add new features to the S3V Group website following the established architecture patterns.

---

## 📋 Feature Addition Checklist

### 1. Domain Layer (Business Logic)

#### Create Repository
```php
<?php
// File: app/Domain/[Domain]/[Feature]Repository.php
namespace App\Domain\[Domain];

use PDO;

class [Feature]Repository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findById(string $id): ?array
    {
        // Implementation
    }

    public function create(array $data): string
    {
        // Implementation
    }
}
```

#### Create Service
```php
<?php
// File: app/Domain/[Domain]/[Feature]Service.php
namespace App\Domain\[Domain];

class [Feature]Service
{
    public function __construct(
        private [Feature]Repository $repository
    ) {}

    public function create(array $data): array
    {
        // Validation
        // Business logic
        return $this->repository->create($data);
    }
}
```

**Domain Folder Examples:**
- `app/Domain/Catalog/` - Products, Categories
- `app/Domain/Content/` - Pages, Team, Testimonials
- `app/Domain/Quotes/` - Quote requests
- `app/Domain/Settings/` - Site options

---

### 2. Database Migration

```php
<?php
// File: database/migrations/YYYYMMDD_create_[feature].php
namespace App\Database;

use PDO;

class Migration_YYYYMMDD_Create[Feature] extends Migration
{
    public function up(PDO $pdo): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS [feature] (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    -- Add your fields here
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS [feature]");
    }
}
```

**Run Migration:**
```bash
php database/run-migration.php YYYYMMDD_create_[feature]
```

---

### 3. API Endpoints

#### Admin API (Requires Authentication)
```php
<?php
// File: api/v1/admin/[domain]/[feature].php
require_once __DIR__ . '/../../../../bootstrap/app.php';
require_once __DIR__ . '/../../../../config/database.php';

use App\Domain\[Domain]\[Feature]Repository;
use App\Domain\[Domain]\[Feature]Service;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$db = getDB();
$repository = new [Feature]Repository($db);
$service = new [Feature]Service($repository);

switch (Request::method()) {
    case 'GET':
        $items = $service->getAll();
        JsonResponse::success($items);
        break;

    case 'POST':
        $data = Request::json();
        $item = $service->create($data);
        JsonResponse::created($item);
        break;

    case 'PUT':
        $id = Request::query('id');
        $data = Request::json();
        $item = $service->update($id, $data);
        JsonResponse::success($item);
        break;

    case 'DELETE':
        $id = Request::query('id');
        $service->delete($id);
        JsonResponse::success(['message' => 'Deleted']);
        break;
}
```

#### Public API (No Authentication)
```php
<?php
// File: api/v1/public/[feature].php
require_once __DIR__ . '/../../../bootstrap/app.php';
require_once __DIR__ . '/../../../config/database.php';

use App\Domain\[Domain]\[Feature]Repository;
use App\Http\JsonResponse;
use App\Http\Request;

$db = getDB();
$repository = new [Feature]Repository($db);

$items = $repository->getPublished();
JsonResponse::success($items);
```

---

### 4. Admin Page

```php
<?php
// File: admin/[domain]/[feature].php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = '[Feature Name]';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">[Domain]</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">[Feature Name]</h1>
        </div>
        <button type="button" id="new-item-btn" class="admin-btn admin-btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>New [Item]</span>
        </button>
    </div>

    <!-- Add your table/form here -->
    <div id="items-container" class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <!-- Content loaded via JavaScript -->
    </div>
</div>

<script>
// Add your JavaScript here
// Load items from API: /api/v1/admin/[domain]/[feature].php
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
```

**Add to Navigation:**
```php
// File: admin/includes/header.php
// Add link to sidebar navigation
```

---

### 5. Frontend Pages

#### List Page
```php
<?php
// File: public/[feature].php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getDB();
$repository = new App\Domain\[Domain]\[Feature]Repository($db);
$items = $repository->getPublished();

$pageTitle = '[Feature Name]';
include __DIR__ . '/../includes/header.php';
?>

<!-- Add your content here -->
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">[Feature Name]</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($items as $item): ?>
            <!-- Use component if available -->
            <?php include __DIR__ . '/../resources/views/components/[feature]-card.php'; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

#### Detail Page
```php
<?php
// File: public/[feature]-detail.php
$slug = $_GET['slug'] ?? '';

$db = getDB();
$repository = new App\Domain\[Domain]\[Feature]Repository($db);
$item = $repository->findBySlug($slug);

if (!$item) {
    header('Location: /404.php');
    exit;
}

$pageTitle = $item['name'];
include __DIR__ . '/../includes/header.php';
?>

<!-- Add detail view here -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

---

### 6. Component (Optional but Recommended)

```php
<?php
// File: resources/views/components/[feature]-card.php
/**
 * @var array $item
 */
?>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <img src="<?= htmlspecialchars($item['image'] ?? '') ?>" 
         alt="<?= htmlspecialchars($item['name']) ?>" 
         class="w-full h-48 object-cover">
    <div class="p-6">
        <h3 class="text-xl font-semibold mb-2">
            <?= htmlspecialchars($item['name']) ?>
        </h3>
        <p class="text-gray-600 mb-4">
            <?= htmlspecialchars($item['summary'] ?? '') ?>
        </p>
        <a href="/[feature]-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" 
           class="btn-primary">
            View Details
        </a>
    </div>
</div>
```

---

## 🎯 Real Example: Adding "Blog" Feature

### Step 1: Domain Layer
```bash
# Create files
app/Domain/Content/BlogPostRepository.php
app/Domain/Content/BlogPostService.php
```

### Step 2: Database
```bash
# Create migration
database/migrations/20250115_create_blog_posts.php
# Run: php database/run-migration.php 20250115_create_blog_posts
```

### Step 3: API
```bash
# Create API files
api/v1/admin/content/blog-posts.php
api/v1/public/blog-posts.php
```

### Step 4: Admin
```bash
# Create admin page
admin/content/blog-posts.php
# Update admin/includes/header.php (add navigation link)
```

### Step 5: Frontend
```bash
# Create public pages
public/blog.php
public/blog-post.php
# Create component
resources/views/components/blog-post-card.php
```

### Step 6: Test
- [ ] Test API endpoints
- [ ] Test admin CRUD
- [ ] Test frontend pages
- [ ] Verify database operations

**Total Time**: ~2-3 hours for complete feature!

---

## 📝 Naming Conventions

### Files
- **Repository**: `[Feature]Repository.php`
- **Service**: `[Feature]Service.php`
- **Controller**: `[Feature]Controller.php`
- **Migration**: `YYYYMMDD_create_[feature].php`
- **Admin Page**: `[feature].php` (lowercase, kebab-case)
- **Public Page**: `[feature].php` or `[feature]-detail.php`

### Classes
- **Repository**: `[Feature]Repository`
- **Service**: `[Feature]Service`
- **Controller**: `[Feature]Controller`

### Database
- **Table**: `[feature]` (lowercase, snake_case)
- **Columns**: `camelCase` or `snake_case` (be consistent)

---

## 🔍 Common Patterns

### Repository Pattern
```php
// Always inject PDO
public function __construct(private readonly PDO $pdo) {}

// Standard methods
public function findById(string $id): ?array
public function findBySlug(string $slug): ?array
public function getAll(array $filters = []): array
public function create(array $data): string
public function update(string $id, array $data): bool
public function delete(string $id): bool
```

### Service Pattern
```php
// Always inject Repository
public function __construct(
    private [Feature]Repository $repository
) {}

// Business logic methods
public function create(array $data): array
{
    // Validate
    // Transform
    // Call repository
    return $this->repository->create($data);
}
```

### API Pattern
```php
// Always require auth for admin APIs
AdminGuard::requireAuth();

// Standard CRUD operations
GET    -> index()   -> getAll()
POST   -> store()   -> create()
PUT    -> update()  -> update()
DELETE -> destroy() -> delete()
```

---

## ✅ Quality Checklist

Before considering a feature "done":

- [ ] Repository has all CRUD methods
- [ ] Service has business logic
- [ ] Database migration created and tested
- [ ] API endpoints work (test with Postman/curl)
- [ ] Admin page has full CRUD
- [ ] Frontend pages display correctly
- [ ] No PHP errors or warnings
- [ ] No JavaScript errors
- [ ] Responsive design works
- [ ] Images optimized
- [ ] SEO meta tags added
- [ ] Navigation updated
- [ ] Documentation updated

---

## 🚀 Quick Start Template

Use this template to quickly scaffold a new feature:

```bash
# 1. Create domain files
touch app/Domain/[Domain]/[Feature]Repository.php
touch app/Domain/[Domain]/[Feature]Service.php

# 2. Create migration
touch database/migrations/$(date +%Y%m%d)_create_[feature].php

# 3. Create APIs
mkdir -p api/v1/admin/[domain]
touch api/v1/admin/[domain]/[feature].php
touch api/v1/public/[feature].php

# 4. Create admin page
mkdir -p admin/[domain]
touch admin/[domain]/[feature].php

# 5. Create frontend
touch public/[feature].php
touch public/[feature]-detail.php

# 6. Create component
mkdir -p resources/views/components
touch resources/views/components/[feature]-card.php
```

Then fill in the templates from the examples above!

---

## 📚 Related Documentation

- **Architecture Plan**: `SCALABILITY-AUDIT-AND-PLAN.md`
- **API Standards**: See API examples in `api/v1/`
- **Component Library**: See `resources/views/components/`
- **Database Schema**: See `sql/schema.sql`

---

**Happy Coding!** 🎉

