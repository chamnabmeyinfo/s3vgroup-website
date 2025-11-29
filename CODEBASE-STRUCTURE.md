# Codebase Structure - Mind Map Ready

This file can be imported into mind mapping tools to visualize the project structure.

## Project Root: S3V Group Website

### Frontend Pages
- index.php (Homepage)
- products.php (Product Listing)
- product.php (Product Details)
- about.php (About Page)
- contact.php (Contact Page)
- team.php (Team Page)
- testimonials.php (Testimonials)
- quote.php (Quote Request)
- page.php (Dynamic Page Router)
- 404.php (Error Page)

### Core System
- ae-load.php (Bootstrap)
- ae-config.php (Configuration)
- wp-config.php (WordPress Config)
- wp-load.php (WordPress Loader)

### Core Includes (ae-includes/)
- functions.php (Core Functions)
- header.php (Header Template)
- footer.php (Footer Template)
- helpers.php (Helper Functions)
- design-system.php (Design System)
- wp-functions.php (WordPress Functions)
- plugin-api.php (Plugin API)

#### Widgets (ae-includes/widgets/)
- dynamic-menu.php
- footer-menu.php
- homepage-section-renderer.php
- modern-hero-slider.php
- newsletter-signup.php
- page-section-renderer.php
- testimonials.php

#### CSS (ae-includes/css/)
- tailwind.css
- frontend.css
- theme-styles.css
- responsive.css
- mobile-fixes.css
- homepage-design.css
- pages.css
- mobile-app.css
- categories.css
- modern-animations.css
- modern-frontend.css
- mobile-app-responsive.css
- category-filter.css
- price-blur.css
- products.css

#### JavaScript (ae-includes/js/)
- category-images.js
- mobile-app.js
- mobile-touch.js
- modern-animations.js
- modern-frontend.js
- modern-slider.js
- modern.js
- animations.js
- social-sharing.js
- theme-toggle.js

### Admin Panel (ae-admin/)
- index.php (Dashboard)
- login.php
- logout.php

#### Catalog Management
- products.php
- categories.php

#### Content Management
- pages.php
- homepage-builder-v2.php
- team.php
- faqs.php
- company-story.php
- ceo-message.php

#### Marketing & Engagement
- sliders.php
- testimonials.php
- reviews.php
- newsletter.php

#### Customer Relations
- quotes.php

#### Media & Assets
- media-library.php

#### Design & Appearance
- backend-appearance.php
- menus.php
- theme-customize.php
- theme-preview.php
- page-builder.php

#### System Settings
- options.php
- seo-tools.php
- plugins.php
- optional-features.php
- database-sync.php

#### Admin Includes (ae-admin/includes/)
- header.php
- footer.php
- admin-styles.css
- homepage-section-canvas.php
- homepage-section-item.php
- theme-loader.php

#### Admin JavaScript (ae-admin/js/)
- homepage-builder-v2.js
- pages.js

### Application Core (app/)
- Application/ (Application Services)
- Config/ (Configuration Classes)
- Core/ (Core Classes)
- Database/ (Database Layer)
- Domain/ (Business Logic)
  - Catalog/ (Products, Categories)
  - Content/ (Pages, Blog, Testimonials)
  - Quotes/ (Quote Requests)
  - Settings/ (Site Options)
  - Theme/ (Theme System)
  - Translation/ (Translations)
- Http/ (HTTP Layer)
  - Controllers/
  - Middleware/
  - Requests/
  - Responses/
- Infrastructure/ (Infrastructure Layer)
- Support/ (Support Classes)
  - Autoloader.php
  - AssetHelper.php
  - AssetVersion.php
  - Env.php
  - helpers.php
  - Id.php
  - ImageOptimizer.php
  - Str.php

### API Endpoints (api/)
- admin/ (Admin APIs)
  - categories/
  - ceo-message/
  - company-story/
  - database/
  - homepage/
  - menus/
  - newsletter/
  - options/
  - pages/
  - products/
  - quotes/
  - seo/
  - sliders/
  - team/
  - testimonials/
  - theme/
  - themes/
  - upload.php
  - woocommerce/
  - wordpress/
- analytics/
- catalog/
- categories/
- menus/
- newsletter/
- products/
- quotes/
- theme/
- themes/
- translations/

### Utilities (bin/)
- db-manager.php
- sync-database.php
- auto-sync-database.php
- auto-sync-schema.php
- auto-sync-scheduled.ps1
- auto-sync-schema-scheduled.ps1
- verify-database-schema.php
- optimize-product-images.php
- compress-large-images-to-300kb.php
- check-gd-support.php
- assign-optimized-product-images.php
- verify-system.php
- cleanup.php

### Database (database/)
- migrations/ (19 migration files)
  - 20241121_initial_schema.php
  - 20241122_site_options.php
  - 20241123_modern_features.php
  - 20241124_design_options.php
  - 20241125_advanced_features.php
  - 20241126_organized_options.php
  - 20241127_frontend_features.php
  - 20241128_company_showcase.php
  - 20241129_team_enhancements.php
  - 20241130_homepage_builder_option.php
  - 20241130_homepage_builder.php
  - 20241201_pages_system.php
  - 20241202_innovation_features.php
  - 20250115_create_plugin_system.php
  - 20250116_themes_system.php
  - 20250117_backend_themes.php
  - 20250118_add_ios_theme.php
  - 20250118_design_all_themes.php
  - create_menus_tables.php
- run-migration.php

### Configuration (config/)
- database.php.example
- database.live.php.example
- database.local.php
- database.php
- site.local.php.example
- site.php

### SQL Files (sql/)
- schema.sql
- site_options.sql
- sample_data.sql

### Bootstrap (bootstrap/)
- app.php

### Content (ae-content/)
- plugins/
  - example-plugin/
  - wordpress-demo/
- themes/
- uploads/

### Resources (resources/)
- css/
  - tailwind.css

### Storage (storage/)
- logs/

### Tests (tests/)
- bootstrap.php
- Unit/

### Documentation (docs/)
- Architecture/
  - backend-architecture-current.md
  - backend-architecture-new.md
  - BACKEND-DEVELOPER-GUIDE.md
- Theme System/
  - backend-theme-system.md
  - theme-system-redesign.md
  - theme-ui-integration.md
  - theme-switching-guide.md
  - backend-themes-list.md
- Style & Design/
  - ADMIN-UI-DESIGN-SYSTEM.md
  - BACKEND-STYLE-REQUIREMENTS.md
  - STYLE-QUICK-REFERENCE.md
- Menu System/
  - MENU-SYSTEM-GUIDE.md
- Guides/
  - ADDING-NEW-FEATURES.md
  - ADMIN-ORGANIZATION.md
  - DATABASE-MANAGER-GUIDE.md
  - IMAGE-OPTIMIZATION-GUIDE.md
  - PERFORMANCE-RECOMMENDATIONS.md
  - PLUGIN-DEVELOPMENT-GUIDE.md
- Setup/
  - DATABASE-SYNC-GUIDE.md
  - QUICK-REMOTE-SETUP.md
  - REMOTE-DATABASE-SETUP.md
  - SCHEMA-SYNC-GUIDE.md

### Root Documentation
- README.md
- DEPLOYMENT-CHECKLIST.md
- FEATURES-OVERVIEW.md
- ARCHITECTURE-QUICK-REFERENCE.md
- PLUGIN-SYSTEM-ARCHITECTURE.md
- CHANGE-PHP-VERSION-GUIDE.md
- CODE-STATISTICS.md
- PROJECT-TRACKING.md
- PERFORMANCE-ANALYSIS.md
- DEEP-CLEANUP-SUMMARY.md
- DOCUMENTATION-CLEANUP-SUMMARY.md

### Configuration Files
- .htaccess
- .gitignore
- package.json
- package-lock.json
- tailwind.config.js
- phpunit.xml
- env.example

