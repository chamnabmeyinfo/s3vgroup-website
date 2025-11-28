# Project Restructure Plan

## 🎯 Goals
1. Clean, organized codebase for future scalability
2. Consistent structure across backend and frontend
3. Proper separation of concerns
4. Easy to add new features
5. Maintainable and testable code

## 📋 Current Issues Identified

### Backend Issues
1. **Inconsistent API structure** - Some APIs in `/api/admin/`, some in `/api/`
2. **Mixed responsibilities** - Repositories doing business logic
3. **No service layer consistency** - Some features use Services, others don't
4. **Error handling inconsistency** - Different error response formats
5. **Configuration scattered** - Some in `config/`, some in `site_options` table

### Frontend Issues
1. **Mixed template organization** - Some in root, some in `includes/`
2. **CSS/JS organization** - Could be better organized by feature
3. **No component system** - Repeated code in templates
4. **Inconsistent asset loading** - Mix of CDN and local assets

### Structure Issues
1. **Admin pages scattered** - All in `/admin/` root
2. **API endpoints unorganized** - No clear grouping
3. **Utility scripts everywhere** - In `bin/`, root, etc.
4. **Documentation scattered** - Many `.md` files in root

## 🏗️ Proposed Structure

```
s3vgroup/
├── app/
│   ├── Config/              # ✅ Already good
│   ├── Database/            # ✅ Already good
│   ├── Domain/              # ✅ Already good (needs cleanup)
│   │   ├── Catalog/         # Products, Categories
│   │   ├── Content/         # Pages, Team, Testimonials, etc.
│   │   ├── Quotes/          # Quote requests
│   │   ├── Settings/        # Site options
│   │   └── [Future domains] # Easy to add
│   ├── Http/                # ✅ Already good
│   │   ├── Controllers/     # NEW: Request handlers
│   │   ├── Middleware/      # NEW: Auth, validation, etc.
│   │   └── Responses/       # NEW: Standardized responses
│   └── Support/             # ✅ Already good
│
├── admin/
│   ├── dashboard.php        # Main dashboard
│   ├── auth/
│   │   ├── login.php
│   │   └── logout.php
│   ├── catalog/
│   │   ├── products.php
│   │   └── categories.php
│   ├── content/
│   │   ├── pages.php
│   │   ├── team.php
│   │   ├── testimonials.php
│   │   └── sliders.php
│   ├── quotes/
│   │   └── index.php
│   ├── settings/
│   │   ├── options.php
│   │   └── media-library.php
│   └── includes/            # Shared admin templates
│
├── api/
│   ├── v1/                  # API versioning
│   │   ├── admin/           # Admin APIs (require auth)
│   │   │   ├── catalog/
│   │   │   ├── content/
│   │   │   └── quotes/
│   │   └── public/          # Public APIs
│   │       ├── products/
│   │       ├── categories/
│   │       └── quotes/
│   └── middleware/          # API middleware
│
├── public/                  # NEW: Public-facing files
│   ├── index.php
│   ├── products.php
│   ├── product.php
│   ├── about.php
│   ├── team.php
│   ├── contact.php
│   └── quote.php
│
├── resources/               # NEW: Frontend resources
│   ├── views/               # Templates
│   │   ├── layouts/
│   │   ├── components/
│   │   └── pages/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── lang/                # Future: translations
│
├── config/                  # ✅ Already good
├── database/
│   └── migrations/          # ✅ Already good
├── storage/
│   ├── logs/
│   ├── cache/
│   └── uploads/             # Move from root
│
├── tests/                   # NEW: Unit/integration tests
├── docs/                    # NEW: All documentation
│   ├── setup/
│   ├── features/
│   └── guides/
│
└── bin/                     # ✅ Utility scripts (keep as is)
```

## 🔄 Migration Steps

### Phase 1: Cleanup (Current)
- ✅ Remove translation features
- ✅ Clean repositories
- ⏳ Organize admin pages
- ⏳ Standardize API structure

### Phase 2: Backend Restructure
1. Create service layer consistency
2. Standardize error handling
3. Add middleware system
4. Organize API endpoints

### Phase 3: Frontend Restructure
1. Create component system
2. Organize assets
3. Standardize templates
4. Move public files to `public/`

### Phase 4: Documentation
1. Move all `.md` files to `docs/`
2. Create API documentation
3. Add code comments
4. Create developer guide

## 📝 Implementation Priority

### High Priority (Do First)
1. ✅ Clean translation code
2. Organize admin pages by feature
3. Standardize API responses
4. Create consistent error handling

### Medium Priority
1. Organize frontend assets
2. Create reusable components
3. Move documentation to `docs/`
4. Add API versioning

### Low Priority (Future)
1. Move to `public/` directory
2. Add test suite
3. Implement full middleware system
4. Add translation system (properly)

## 🎨 Code Standards

### Naming Conventions
- **Classes**: PascalCase (`ProductRepository`)
- **Methods**: camelCase (`findById`)
- **Files**: Match class name
- **Variables**: camelCase (`$productId`)
- **Constants**: UPPER_SNAKE_CASE (`MAX_FILE_SIZE`)

### File Organization
- One class per file
- Namespace matches directory structure
- Use type hints everywhere
- Add PHPDoc comments

### API Standards
- Consistent JSON responses
- Proper HTTP status codes
- Error messages in consistent format
- Version APIs (`/api/v1/`)

## 🔍 Next Steps

1. **Immediate**: Finish cleaning remaining repositories
2. **Short-term**: Organize admin pages into feature folders
3. **Short-term**: Standardize API structure
4. **Medium-term**: Create component system
5. **Long-term**: Full restructure with `public/` directory

