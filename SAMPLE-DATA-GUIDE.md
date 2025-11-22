# Sample Data Guide

## ✅ Created Sample Data

### 1. Hero Slider Slides (5 slides)

**All slides feature warehouse/factory equipment related images:**

1. **Warehouse & Factory Equipment Solutions**
   - Title: "Leading Supplier in Cambodia"
   - Image: Modern warehouse interior
   - CTA: "Explore Products" → `/products.php`
   - Priority: 100

2. **Forklift Solutions**
   - Title: "Premium Quality Equipment"
   - Image: Forklift in warehouse
   - CTA: "View Forklifts" → `/products.php?category=forklifts`
   - Priority: 90

3. **Material Handling Systems**
   - Title: "Efficient & Reliable"
   - Image: Material handling equipment
   - CTA: "Learn More" → `/products.php?category=material-handling`
   - Priority: 80

4. **Professional Installation & Service**
   - Title: "Expert Support"
   - Image: Professional service team
   - CTA: "Request Service" → `/quote.php`
   - Priority: 70

5. **Industrial Storage Solutions**
   - Title: "Maximize Your Space"
   - Image: Storage racking systems
   - CTA: "View Storage Solutions" → `/products.php?category=storage`
   - Priority: 60

**Status:** All slides are set to `PUBLISHED` and ready to display on homepage.

### 2. Testimonials (6 testimonials)

**All testimonials feature Cambodian customers:**

1. **Sok Pisey** - ABC Logistics Co., Ltd.
   - Position: Operations Manager
   - Rating: 5/5 stars
   - Featured: Yes
   - Priority: 100

2. **Chan Sophal** - Cambodia Manufacturing Inc.
   - Position: Factory Manager
   - Rating: 5/5 stars
   - Featured: Yes
   - Priority: 90

3. **Lim Srey Pich** - Royal Distribution Center
   - Position: Warehouse Director
   - Rating: 5/5 stars
   - Featured: Yes
   - Priority: 80

4. **Meas Ratha** - Phnom Penh Trading Co.
   - Position: Supply Chain Manager
   - Rating: 5/5 stars
   - Featured: Yes
   - Priority: 70

5. **Heng Sokunthea** - Modern Factory Solutions
   - Position: CEO
   - Rating: 5/5 stars
   - Featured: Yes
   - Priority: 60

6. **Kong Vannak** - Southeast Distribution
   - Position: Logistics Coordinator
   - Rating: 5/5 stars
   - Featured: Yes
   - Priority: 50

**Status:** All testimonials are set to `PUBLISHED` and `featured=true`.

### 3. Newsletter Widget

**Location:**
- Homepage: Dedicated section after categories
- Footer: Integrated in footer section

**Features:**
- Email input (required)
- Name input (optional)
- Beautiful gradient design
- Success/error messaging
- Toast notifications

## 📍 Where to View

### Frontend:
1. **Homepage** (`/index.php`):
   - Hero slider at the top
   - Newsletter section (after categories)
   - Testimonials section (after newsletter)

2. **Footer** (`/includes/footer.php`):
   - Newsletter widget (if enabled)

3. **Testimonials Page** (`/testimonials.php`):
   - All published testimonials

### Admin Panel:
1. **Hero Slider** (`/admin/sliders.php`):
   - View, create, edit, delete slides
   - Manage priorities and status

2. **Testimonials** (`/admin/testimonials.php`):
   - View, create, edit, delete testimonials
   - Mark as featured

3. **Newsletter** (`/admin/newsletter.php`):
   - View subscribers
   - Manage subscriptions

## 🔄 Re-running Seed Script

To reset and recreate sample data:

```bash
# Reset sliders only
php bin/reset-sliders.php

# Create all sample data
php bin/seed-sample-data.php

# Or run both
php bin/reset-sliders.php && php bin/seed-sample-data.php
```

## 🎨 Customization

### Hero Slider:
- Edit slides: `/admin/sliders.php`
- Change images: Upload new images or use image URLs
- Adjust priorities: Higher priority = shown first
- Control display: Enable/disable in Site Options → Homepage Design

### Testimonials:
- Edit testimonials: `/admin/testimonials.php`
- Add avatars: Use avatar URLs or leave empty for initials
- Control ratings: Set 1-5 stars
- Feature testimonials: Mark `featured=true` to show on homepage

### Newsletter:
- Enable/disable: Site Options → General Settings
- View subscribers: `/admin/newsletter.php`
- Customize design: Edit `/includes/widgets/newsletter-signup.php`

## 📸 Image Sources

Hero slider images are from Unsplash with warehouse/factory equipment themes:
- Modern warehouse interiors
- Forklifts and material handling
- Storage systems and racking
- Professional service teams

You can replace these with your own images:
1. Upload to `/uploads/site/`
2. Update slider image URL in admin panel
3. Or use external image URLs

## ✨ Features

- **Responsive Design**: Works on mobile, tablet, and desktop
- **Smooth Animations**: Fade-in effects and transitions
- **Professional Layout**: Modern, clean design
- **SEO Friendly**: Proper alt tags and semantic HTML
- **Easy Management**: Admin panel for all content

## 🚀 Next Steps

1. Visit `/admin/sliders.php` to customize hero slider
2. Visit `/admin/testimonials.php` to add more testimonials
3. Check `/index.php` to see everything in action
4. Customize colors and styling in Site Options

---

**All sample data is ready and displayed on your website!** 🎉

