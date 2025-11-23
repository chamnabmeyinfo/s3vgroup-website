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

- **README.md** (this file) - Project overview
- **FEATURES-OVERVIEW.md** - Complete features list
- **ADMIN-ORGANIZATION.md** - Admin panel structure
- **LOCAL-SETUP.md** - Local development setup
- **LIVE-SETUP-GUIDE.md** - Live server setup
- **AUTO-IMPORT-DATABASE.md** - Database import guide

## 🗄️ Database

The project includes SQL files for easy setup:

- `sql/schema.sql` - Database structure (tables, indexes)
- `sql/site_options.sql` - Site configuration options
- `sql/sample_data.sql` - Sample data (products, teams, testimonials, etc.)

**Import automatically:** Visit `import-database.php` after deployment (delete after use!)

## 🛠️ Project Structure

```
s3vgroup/
├── admin/              # Admin panel
├── api/                # API endpoints
├── app/                # Application core (Domain, Support, Database)
├── bin/                # Utility scripts (migrations, seeding)
├── bootstrap/          # Application bootstrap
├── config/             # Configuration files
├── database/           # Database migrations
├── includes/           # Templates, CSS, JS
├── sql/                # SQL files (schema, data)
├── uploads/            # Uploaded files
└── index.php           # Homepage
```

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
