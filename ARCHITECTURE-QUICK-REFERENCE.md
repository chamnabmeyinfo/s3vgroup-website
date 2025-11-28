# 🏗️ Architecture Quick Reference

## Current vs Proposed Structure

### Current Structure (Before)
```
s3vgroup/
├── admin/
│   ├── products.php          ❌ Flat structure
│   ├── categories.php
│   ├── team.php
│   └── [20+ files in root]
│
├── api/
│   ├── admin/
│   │   ├── products/         ⚠️ Some organization
│   │   └── [mixed structure]
│   └── products/             ❌ No versioning
│
├── app/
│   └── Domain/                ✅ Well organized
│       ├── Catalog/
│       ├── Content/
│       └── Quotes/
│
└── index.php                  ❌ Public files in root
```

### Proposed Structure (After)
```
s3vgroup/
├── admin/
│   ├── catalog/               ✅ Feature folders
│   │   ├── products.php
│   │   └── categories.php
│   ├── content/
│   │   ├── team.php
│   │   └── pages.php
│   └── quotes/
│
├── api/
│   └── v1/                    ✅ Versioned
│       ├── admin/
│       │   ├── catalog/
│       │   └── content/
│       └── public/
│
├── app/
│   ├── Domain/                ✅ Keep as is
│   └── Http/
│       ├── Controllers/       ✅ NEW: Controllers
│       ├── Middleware/        ✅ NEW: Middleware
│       └── Requests/          ✅ NEW: Validation
│
└── public/                    ✅ NEW: Public files
    ├── index.php
    └── products.php
```

---

## File Location Guide

### Where to Put What?

| What | Where | Example |
|------|-------|---------|
| **Business Logic** | `app/Domain/[Domain]/` | `app/Domain/Catalog/ProductRepository.php` |
| **API Endpoints** | `api/v1/[admin|public]/[domain]/` | `api/v1/admin/catalog/products.php` |
| **Admin Pages** | `admin/[domain]/` | `admin/catalog/products.php` |
| **Public Pages** | `public/` | `public/products.php` |
| **Components** | `resources/views/components/` | `resources/views/components/product-card.php` |
| **CSS** | `resources/assets/css/` | `resources/assets/css/app.css` |
| **JavaScript** | `resources/assets/js/` | `resources/assets/js/admin.js` |
| **Migrations** | `database/migrations/` | `database/migrations/20250115_create_products.php` |
| **Config** | `config/` | `config/database.php` |
| **Utilities** | `bin/` | `bin/db-manager.php` |

---

## Domain Organization

### Current Domains

```
app/Domain/
├── Catalog/           # Products, Categories
├── Content/           # Pages, Team, Testimonials, Blog, etc.
├── Quotes/            # Quote requests
└── Settings/          # Site options
```

### Adding New Domain

1. Create folder: `app/Domain/[NewDomain]/`
2. Create Repository: `[Feature]Repository.php`
3. Create Service: `[Feature]Service.php`
4. Follow existing patterns

**Example: Adding "E-commerce" domain**
```
app/Domain/
└── Ecommerce/
    ├── OrderRepository.php
    ├── OrderService.php
    ├── CartRepository.php
    └── CartService.php
```

---

## API Structure

### Current API Pattern
```
/api/admin/products/index.php      # List/Create
/api/admin/products/item.php      # Get/Update/Delete
```

### Proposed API Pattern
```
/api/v1/admin/catalog/products.php    # All CRUD in one file
/api/v1/public/products.php           # Public API
```

### API Methods
```php
GET    /api/v1/admin/catalog/products.php        # List all
POST   /api/v1/admin/catalog/products.php        # Create
GET    /api/v1/admin/catalog/products.php?id=X   # Get one
PUT    /api/v1/admin/catalog/products.php?id=X   # Update
DELETE /api/v1/admin/catalog/products.php?id=X   # Delete
```

---

## Code Patterns

### Repository Pattern
```php
class ProductRepository
{
    public function __construct(private readonly PDO $pdo) {}
    
    public function findById(string $id): ?array {}
    public function create(array $data): string {}
    public function update(string $id, array $data): bool {}
    public function delete(string $id): bool {}
}
```

### Service Pattern
```php
class ProductService
{
    public function __construct(
        private ProductRepository $repository
    ) {}
    
    public function create(array $data): array
    {
        // Validation
        // Business logic
        return $this->repository->create($data);
    }
}
```

### API Pattern
```php
AdminGuard::requireAuth();

$repository = new ProductRepository($db);
$service = new ProductService($repository);

switch (Request::method()) {
    case 'GET': /* ... */ break;
    case 'POST': /* ... */ break;
}
```

---

## Naming Conventions

| Type | Convention | Example |
|------|-----------|---------|
| **Class** | PascalCase | `ProductRepository` |
| **Method** | camelCase | `findById()` |
| **Variable** | camelCase | `$productId` |
| **Constant** | UPPER_SNAKE_CASE | `MAX_FILE_SIZE` |
| **File** | Match class | `ProductRepository.php` |
| **Table** | snake_case | `product_media` |
| **Column** | camelCase or snake_case | `productId` or `product_id` |

---

## Response Standards

### Success Response
```json
{
    "success": true,
    "data": { ... },
    "message": "Operation successful"
}
```

### Error Response
```json
{
    "success": false,
    "error": {
        "code": "ERROR_CODE",
        "message": "Error message"
    }
}
```

---

## Quick Decision Tree

### "Where should I put [X]?"

**Is it business logic?**
→ `app/Domain/[Domain]/[Feature]Repository.php` or `Service.php`

**Is it an API endpoint?**
→ `api/v1/[admin|public]/[domain]/[feature].php`

**Is it an admin page?**
→ `admin/[domain]/[feature].php`

**Is it a public page?**
→ `public/[feature].php`

**Is it a reusable component?**
→ `resources/views/components/[feature]-card.php`

**Is it CSS/JS?**
→ `resources/assets/[css|js]/[feature].css`

**Is it a database change?**
→ `database/migrations/YYYYMMDD_[description].php`

**Is it configuration?**
→ `config/[feature].php`

---

## Migration Checklist

When restructuring:

- [ ] Create new structure
- [ ] Move files
- [ ] Update includes/requires
- [ ] Update navigation links
- [ ] Test all pages
- [ ] Test all APIs
- [ ] Update documentation
- [ ] Remove old files (after testing)

---

## Common Questions

**Q: Should I create a new Domain folder?**  
A: Only if it's a completely new business domain. Otherwise, add to existing domain.

**Q: Do I need both Repository and Service?**  
A: Yes, Repository for data access, Service for business logic.

**Q: Can I skip the Service layer?**  
A: Not recommended. Service layer provides validation and business logic.

**Q: Where do I put validation?**  
A: In the Service layer, or create Request classes in `app/Http/Requests/`.

**Q: How do I handle errors?**  
A: Use `JsonResponse::error()` for APIs, show user-friendly messages in admin.

---

**Keep this reference handy when adding features!** 📚

