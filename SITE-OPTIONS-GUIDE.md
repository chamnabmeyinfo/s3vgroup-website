# Site Options Guide - Organized by Sections

## 📋 Complete Site Options Organization

The Site Options page (`/admin/options.php`) is now organized into **14 logical sections** for easy navigation and management.

---

## 🏠 1. General Settings ⚙️
**Basic site information and core settings**

- Site Name
- Site Tagline
- Site Logo (image upload)
- Site Favicon (image upload)
- Enable Newsletter
- Enable Search
- Enable Toast Notifications
- Enable Blog
- Enable Testimonials
- Newsletter API Key

---

## 🏠 2. Home Page Design 🏠
**Customize your homepage hero section and content**

- Hero Title (textarea)
- Hero Subtitle (textarea)

These control the main hero section on your homepage.

---

## ✍️ 3. Typography & Fonts ✍️
**Configure fonts, sizes, weights, and typography settings**

- Font Family (main font)
- Base Font Size (px)
- Normal Font Weight (100-900)
- Bold Font Weight (100-900)
- Line Height (multiplier)
- Heading Font (optional separate font for headings)

**Tips:**
- Use system fonts for better performance: `system-ui, -apple-system, sans-serif`
- Or add Google Fonts: `'Roboto', sans-serif`
- Line height typically 1.5-1.8 for readability

---

## 🎨 4. Colors & Theme 🎨
**Customize color scheme, primary colors, and theme colors**

- Primary Color (main brand color)
- Secondary Color (secondary brand color)
- Accent Color (CTA/button color)
- Header Background Color
- Footer Background Color
- Link Color
- Link Hover Color

**Tips:**
- Primary color is used for headers, buttons, and key elements
- Accent color is used for call-to-action buttons
- Use color picker or enter hex codes like `#0b3a63`

---

## 📐 5. Layout & Spacing 📐
**Adjust layout dimensions, spacing units, and container settings**

- Border Radius (px)
- Spacing Unit (px) - Base unit for margins/padding
- Container Max Width (px)
- Header Height (px)
- Footer Height (auto or specific px)

**Tips:**
- Spacing unit is typically 4px or 8px (used throughout the site)
- Container width: 1280px is standard for desktop
- Border radius: 8px gives modern, rounded appearance

---

## 🧩 6. Components & UI Elements 🧩
**Style buttons, cards, shadows, and background patterns**

- Button Style (Rounded/Square/Pill)
- Button Padding X (px)
- Button Padding Y (px)
- Card Shadow (None/Small/Medium/Large)
- Background Pattern (None/Dots/Grid/Lines)
- Background Image (uploadable)
- Background Overlay Opacity (0-100)

**Tips:**
- Rounded buttons: Modern, friendly
- Pill buttons: Fully rounded ends
- Medium shadow: Good balance for cards
- Background patterns add subtle texture

---

## 🌐 7. Language & Localization 🌐
**Set language, locale, date/time formats, and currency**

- Site Language (en, kh, etc.)
- Site Locale (en_US, km_KH, etc.)
- Date Format (e.g., M d, Y)
- Time Format (e.g., g:i A)
- Currency Symbol ($, ₱, ៛, etc.)
- Currency Code (USD, KHR, etc.)

**Examples:**
- Date: `M d, Y` = Jan 15, 2025
- Date: `d/m/Y` = 15/01/2025
- Time: `g:i A` = 3:45 PM
- Time: `H:i` = 15:45

---

## 📊 8. SEO & Analytics 📊
**Configure SEO meta tags, Open Graph, and analytics tracking**

- SEO Title (default page title)
- SEO Description (default meta description)
- SEO Keywords (comma-separated)
- Open Graph Image (for social media sharing)
- Twitter Card Type (summary_large_image, etc.)
- Google Analytics ID (UA-xxx or G-xxx)
- Facebook Pixel ID

**Tips:**
- SEO title: Keep under 60 characters
- SEO description: 150-160 characters optimal
- OG Image: 1200x630px recommended for social sharing

---

## 📱 9. Social Media 📱
**Add social media links and profiles**

- Facebook URL
- LinkedIn URL
- Twitter URL
- YouTube URL

These appear in your footer and can be used for sharing.

---

## 📞 10. Contact Information 📞
**Update contact details, address, and business hours**

- Contact Email
- Contact Phone
- Address
- Business Hours

Displayed in footer and contact pages.

---

## 📧 11. Email Settings 📧
**Configure email sender settings and SMTP configuration**

- Email From Name
- Email From Address
- SMTP Host
- SMTP Port (587 for TLS, 465 for SSL)
- SMTP Username
- SMTP Password

**SMTP Setup:**
- Host: `smtp.gmail.com` (for Gmail)
- Port: `587` (TLS) or `465` (SSL)
- Requires authentication credentials

---

## ✨ 12. Features & Functionality ✨
**Enable or disable website features and functionality**

- Enable Dark Mode
- Enable Search
- Enable Animations
- Enable Toast Notifications
- Enable Newsletter
- Enable Social Sharing
- Enable Blog
- Enable Testimonials

Toggle features on/off as needed.

---

## ⚡ 13. Performance & Optimization ⚡
**Optimize website performance with caching and compression**

- Enable Lazy Loading
- Enable Caching
- Cache Duration (seconds)
- Enable Compression (GZIP)

**Tips:**
- Lazy loading: Load images as user scrolls
- Caching: Store pages for faster loading
- Compression: Reduce file sizes for faster transfer

---

## ⬇️ 14. Footer Settings ⬇️
**Customize footer content and copyright information**

- Footer Copyright Text

---

## 🔧 15. Advanced Settings 🔧
**Custom CSS, JavaScript, and advanced customization options**

- Custom CSS (textarea with syntax hinting)
- Custom JavaScript (Head) - Runs in `<head>`
- Custom JavaScript (Footer) - Runs before `</body>`

**Custom CSS Examples:**
```css
/* Change all headings color */
h1, h2, h3 {
    color: #ff6b6b;
}

/* Add custom background to body */
body {
    background-image: url('pattern.png');
}

/* Style specific class */
.my-custom-class {
    padding: 20px;
    border-radius: 10px;
}
```

**Custom JavaScript Examples:**
```javascript
// Track custom events
document.addEventListener('click', function(e) {
    if (e.target.matches('.track-me')) {
        console.log('Clicked!');
    }
});

// Add custom functionality
window.myCustomFunction = function() {
    alert('Hello!');
};
```

---

## 📝 Usage Tips

1. **Save Changes**: Always click "Save All Changes" after making edits
2. **Reset**: Use "Reset Changes" to revert to original values
3. **Image Upload**: Click "Upload" button for logo, favicon, or background images
4. **Color Picker**: Use color picker or enter hex codes manually
5. **Code Fields**: Custom CSS/JS fields use monospace font for better readability
6. **Validation**: Some fields validate input (e.g., email addresses, URLs)

---

## 🎯 Recommended Settings

**For Best Performance:**
- Enable Lazy Loading ✅
- Enable Compression ✅
- Enable Caching ✅
- Cache Duration: 3600 (1 hour)

**For Best SEO:**
- Fill in all SEO fields
- Upload Open Graph image
- Add Google Analytics ID
- Add Facebook Pixel ID

**For Best UX:**
- Enable Dark Mode ✅
- Enable Search ✅
- Enable Animations ✅
- Enable Toast Notifications ✅

---

## 🔍 Quick Reference

- **Access**: `/admin/options.php`
- **Sections**: 15 organized sections
- **Save**: Click "Save All Changes" button
- **Reset**: Click "Reset Changes" button
- **Upload**: Use "Upload" button for images

Everything is organized and easy to find! 🎉

