# Features Overview - Organized by Sections

## 📋 Admin Panel Sections

### 1. **Catalog Management**
- **Products** (`/admin/products.php`)
  - Create, read, update, delete products
  - Manage product images, specs, highlights
  - Set product status (Draft/Published/Archived)
  - Category assignment

- **Categories** (`/admin/categories.php`)
  - Create, read, update, delete categories
  - Set priority for display order
  - Category descriptions and icons

### 2. **Customer Relations**
- **Quotes** (`/admin/quotes.php`)
  - View all quote requests
  - Update quote status (New/In Progress/Resolved/Closed)
  - View quote details and items
  - Manage customer inquiries

### 3. **Content Management**
- **Testimonials** (`/admin/testimonials.php`)
  - Create, read, update, delete testimonials
  - Star ratings (1-5)
  - Featured testimonials
  - Priority ordering
  - Status management (Draft/Published/Archived)
  - Avatar/photo support

- **Newsletter** (`/admin/newsletter.php`)
  - View all subscribers
  - Active subscriber count
  - Unsubscribe management
  - Delete subscribers
  - Track subscription source

### 4. **Configuration**
- **Site Options** (`/admin/options.php`)
  - **General Settings**: Site name, logo, favicon, features toggle
  - **Design & Styling**: Colors, fonts, spacing, buttons, cards, animations
  - **Contact Information**: Email, phone, address, business hours
  - **Social Media**: Facebook, LinkedIn, Twitter, YouTube links
  - **Homepage Content**: Hero title, subtitle
  - **Footer Settings**: Copyright text
  - **SEO & Analytics**: Meta tags, Google Analytics, Facebook Pixel

---

## 🌐 Frontend Features

### 1. **Homepage** (`/`)
- Dynamic hero section (customizable)
- Featured products grid
- Featured categories
- Call-to-action buttons
- Feature highlights

### 2. **Products** (`/products.php`)
- Product listing with filters
- Category filtering
- Search functionality
- Product cards with images
- Price display
- Status indicators

### 3. **Product Details** (`/product.php`)
- Product information
- Specifications
- Highlights
- Image gallery
- Related products
- Social sharing buttons

### 4. **Testimonials** (`/testimonials.php`)
- Customer testimonials display
- Star ratings
- Customer photos/avatars
- Company information
- Featured testimonials highlighting

### 5. **Quote Request** (`/quote.php`)
- Quote request form
- Product selection
- Company and contact information
- Status tracking

### 6. **Contact** (`/contact.php`)
- Contact information display
- Contact form
- Map integration (if available)

---

## 🎨 Design & Customization

### **Design System** (`includes/design-system.php`)
Dynamic CSS generation based on site options:

- **Typography**:
  - Font family (custom fonts supported)
  - Base font size
  - Font weights (normal, bold)
  - Line height
  - Heading fonts (separate from body)

- **Colors**:
  - Primary color
  - Secondary color
  - Accent color
  - Link colors
  - Header/Footer backgrounds

- **Layout**:
  - Border radius
  - Spacing units
  - Container max width
  - Header/Footer heights

- **Components**:
  - Button styles (Rounded/Square/Pill)
  - Button padding
  - Card shadows (None/Small/Medium/Large)

- **Background**:
  - Patterns (Dots/Grid/Lines)
  - Background images
  - Overlay opacity

- **Custom CSS**:
  - Advanced styling capability

---

## 🔧 Modern Features

### 1. **Dark Mode**
- Toggle button (bottom-right)
- System preference detection
- localStorage persistence
- Smooth transitions

### 2. **Search**
- Real-time product search
- Search results dropdown
- Debounced queries
- Click outside to close

### 3. **Mobile Navigation**
- Responsive design
- Slide-in menu
- Touch-friendly
- Smooth animations

### 4. **Toast Notifications**
- Success, error, warning, info types
- Auto-dismiss with timer
- Stackable notifications
- Smooth animations

### 5. **Image Upload**
- Logo and favicon upload
- Image validation
- File size limits (5MB)
- Preview after upload
- Stored in `/uploads/site/`

### 6. **Social Sharing**
- Facebook sharing
- Twitter sharing
- LinkedIn sharing
- Copy link to clipboard
- Widget component

### 7. **Newsletter Subscription**
- Email validation
- Subscription widget
- Status management
- Source tracking
- API endpoint for subscription

### 8. **SEO & Analytics**
- Open Graph tags
- Twitter Cards
- Meta descriptions
- Keywords
- Google Analytics integration
- Facebook Pixel integration

---

## 📦 Widgets

### 1. **Newsletter Signup** (`includes/widgets/newsletter-signup.php`)
- Email subscription form
- Name field (optional)
- AJAX submission
- Success/error messages
- Toast notifications

### 2. **Social Sharing** (`includes/widgets/social-share.php`)
- Share to Facebook, Twitter, LinkedIn
- Copy link functionality
- Customizable per page
- Beautiful button design

### 3. **Testimonials** (`includes/widgets/testimonials.php`)
- Display featured testimonials
- Configurable limit
- Star ratings
- Responsive grid layout

---

## 🔌 API Endpoints

### **Public APIs**
- `GET /api/products/index.php` - List products
- `GET /api/products/show.php?slug=X` - Get product by slug
- `GET /api/categories/index.php` - List categories
- `POST /api/quotes/index.php` - Submit quote request
- `POST /api/newsletter/subscribe.php` - Subscribe to newsletter

### **Admin APIs** (Protected)
- `GET /api/admin/products/index.php` - List all products
- `POST /api/admin/products/index.php` - Create product
- `GET /api/admin/products/item.php?id=X` - Get product
- `PUT /api/admin/products/item.php?id=X` - Update product
- `DELETE /api/admin/products/item.php?id=X` - Delete product

- Similar CRUD endpoints for:
  - Categories (`/api/admin/categories/`)
  - Quotes (`/api/admin/quotes/`)
  - Testimonials (`/api/admin/testimonials/`)
  - Options (`/api/admin/options/`)

- `POST /api/admin/upload.php` - Image upload

---

## 🗄️ Database Tables

1. **categories** - Product categories
2. **products** - Product catalog
3. **product_media** - Product images
4. **product_tags** - Product tags
5. **quote_requests** - Customer quote requests
6. **site_options** - Site configuration
7. **testimonials** - Customer testimonials
8. **newsletter_subscribers** - Email subscribers
9. **blog_posts** - Blog/news posts
10. **homepage_widgets** - Homepage content blocks

---

## 🎯 Key Features Summary

✅ **Full CRUD** for all content types  
✅ **Modern UI/UX** with dark mode and animations  
✅ **Fully Customizable** design system  
✅ **SEO Optimized** with Open Graph and Twitter Cards  
✅ **Analytics Ready** with Google Analytics and Facebook Pixel  
✅ **Mobile Responsive** design  
✅ **Search Functionality** for products  
✅ **Social Sharing** buttons  
✅ **Newsletter Integration**  
✅ **Testimonials System** with ratings  
✅ **Image Upload** system  
✅ **Toast Notifications**  
✅ **Performance Options** (lazy loading, caching)  

---

## 🚀 How to Use

1. **Access Admin**: `/admin/` (login required)
2. **Manage Content**: Use the sidebar to navigate sections
3. **Customize Design**: Go to Site Options → Design & Styling
4. **Add Widgets**: Include widgets in your templates
5. **Configure SEO**: Site Options → SEO & Analytics

Everything is organized and ready to use!

