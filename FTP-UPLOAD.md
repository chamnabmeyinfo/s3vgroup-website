# FTP/SFTP Upload Guide

If you want to upload via FTP/SFTP, you'll need:

## Required Information

1. **FTP Host:** Usually `ftp.yourdomain.com` or your server IP
2. **FTP Username:** Your cPanel username
3. **FTP Password:** Your cPanel password
4. **FTP Port:** Usually `21` for FTP or `22` for SFTP
5. **Remote Directory:** Usually `/public_html/` or `/home/username/public_html/`

## Option 1: Manual FTP Upload

### Using FileZilla (Free)

1. Download FileZilla: https://filezilla-project.org/
2. Open FileZilla
3. Enter your FTP credentials:
   - Host: `ftp.yourdomain.com`
   - Username: `your_cpanel_username`
   - Password: `your_cpanel_password`
   - Port: `21`
4. Click **Quickconnect**
5. Navigate to `public_html/` on remote server
6. Upload all files from `s3v-web-php/` folder
7. Maintain folder structure

## Option 2: Automated Script

If you provide your FTP credentials, I can create an automated upload script.

**Note:** For security, you should:
- Use SFTP instead of FTP (more secure)
- Change password after deployment
- Use FTP only on secure networks

## Option 3: cPanel File Manager (Recommended)

The easiest method is using cPanel File Manager:
1. No FTP client needed
2. Secure (uses HTTPS)
3. Built into cPanel
4. See UPLOAD-INSTRUCTIONS.md for details

---

**Which method would you prefer?**
