# ⚡ Quick Fix: "Maximum execution time exceeded"

## 🚀 Fastest Solution (Automated Script)

Run the interactive fix script:
```bash
./fix-timeout.sh
```

This script will:
- ✅ Detect your PHP version
- ✅ Show current settings
- ✅ Detect your web server
- ✅ Restart services for you

---

## 🚀 Fast Solution (Manual - 1 minute)

### Option 1: Wait (No Action Required)
The `.user.ini` files are already created. Just **wait 5-10 minutes** and try again.

### Option 2: Restart PHP-FPM (Immediate)

**Ubuntu/Debian:**
```bash
sudo systemctl restart php8.1-fpm
# or
sudo systemctl restart php8.2-fpm
# or
sudo systemctl restart php-fpm
```

**CentOS/RHEL:**
```bash
sudo systemctl restart php-fpm
```

**macOS (Homebrew):**
```bash
brew services restart php
# or
brew services restart php@8.1
```

**Apache (if not using PHP-FPM):**
```bash
sudo systemctl restart apache2
# or
sudo service apache2 restart
```

### Option 3: Check if it's working

Visit: **http://your-domain/check-php-limits.php**

Look for green ✅ checkmarks. If all green = you're good!

**⚠️ DELETE THIS FILE AFTER CHECKING!**

---

## 📋 What We Fixed

Files created/updated:
- ✅ `.user.ini` (root directory)
- ✅ `public/.user.ini` (public directory)
- ✅ `public/.htaccess` (updated)

Settings configured:
- `max_execution_time`: 600 seconds (10 min)
- `upload_max_filesize`: 512M
- `post_max_size`: 512M
- `memory_limit`: 512M

---

## 🔍 Still Not Working?

1. **Check your server type:**
   - Apache → `.htaccess` should work
   - Nginx → Need to edit nginx config (see PHP_CONFIGURATION.md)
   - Shared hosting → Contact support
   - Docker → Need to rebuild container

2. **Read detailed guide:**
   - Open: `PHP_CONFIGURATION.md`
   - Find your server type section
   - Follow specific instructions

3. **Contact hosting support:**
   - Ask them to increase PHP limits
   - Provide these values:
     - `max_execution_time`: 600
     - `upload_max_filesize`: 512M
     - `post_max_size`: 512M

---

## ✅ How to Verify It's Fixed

**Method 1: Use our checker**
```
http://your-domain/check-php-limits.php
```

**Method 2: Create test file**
Create `public/test.php`:
```php
<?php
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
?>
```

Visit it, check values, then **DELETE IT**.

---

## 🎯 Need More Help?

See full documentation: **[PHP_CONFIGURATION.md](PHP_CONFIGURATION.md)**

It includes solutions for:
- Apache
- Nginx
- Docker
- Shared Hosting
- VPS/Dedicated Servers
- Plus troubleshooting tips

---

Good luck! 🚀

