# ⚠️ Timeout Error - Complete Fix Summary

## What Happened

You encountered: **"Maximum execution time of 30 seconds exceeded"**

This happens when uploading large files because PHP's default timeout is only 30 seconds.

---

## What Was Fixed

### Files Created/Updated

1. **`.user.ini`** (root) - PHP configuration
2. **`public/.user.ini`** - PHP configuration (backup location)
3. **`public/.htaccess`** - Apache configuration (updated)
4. **`PHP_CONFIGURATION.md`** - Detailed guide for all server types
5. **`QUICK_FIX.md`** - Quick reference guide
6. **`public/check-php-limits.php`** - Visual configuration checker
7. **`fix-timeout.sh`** - Automated fix script

### Configuration Applied

All configuration files now set these values:

```ini
upload_max_filesize = 512M     # Max file size: 500MB
post_max_size = 512M           # Max total upload size
max_execution_time = 600       # 10 minutes timeout
max_input_time = 600           # 10 minutes input time
memory_limit = 512M            # 512MB memory
max_file_uploads = 50          # Up to 50 files at once
```

---

## ✅ Latest Fix Applied

**Code-level timeout fixes have been applied:**
- ✅ Guzzle HTTP client timeout increased to 600 seconds
- ✅ Added automatic retry logic (3 attempts)
- ✅ Created IncreaseTimeout middleware
- ✅ Added set_time_limit() in controller and service
- ✅ Laravel cache cleared

**You still need to apply server-level configuration:**

## 🚀 QUICK ACTION REQUIRED

Choose ONE method:

### Method 1: Run Automated Script (RECOMMENDED)
```bash
./fix-timeout.sh
```
Follow the prompts to restart PHP.

### Method 2: Manual Restart
```bash
# For PHP-FPM (most common)
sudo systemctl restart php8.1-fpm

# For Apache
sudo systemctl restart apache2

# For Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
```

### Method 3: Just Wait
Do nothing and wait **5-10 minutes** for `.user.ini` to take effect.

---

## ✅ Verify It's Working

### Option A: Use Visual Checker (Recommended)
1. Visit: `http://your-domain/check-php-limits.php`
2. Look for green ✅ checkmarks
3. **DELETE the file afterwards!** (security)

### Option B: Command Line
```bash
php -r "echo 'max_execution_time: ' . ini_get('max_execution_time') . PHP_EOL;"
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
```

Expected output:
```
max_execution_time: 600
upload_max_filesize: 512M
```

---

## 📚 If Still Having Issues

### Step 1: Identify Your Server Type

**Check what you're running:**
```bash
# Check web server
apache2 -v     # Apache
nginx -v       # Nginx

# Check PHP SAPI
php -v
```

### Step 2: Read Appropriate Guide

- **All servers:** [QUICK_FIX.md](QUICK_FIX.md)
- **Detailed guide:** [PHP_CONFIGURATION.md](PHP_CONFIGURATION.md)
  - Apache instructions
  - Nginx instructions
  - Docker instructions
  - Shared hosting tips

### Step 3: Check Logs

**Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

**Web server logs:**
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log

# PHP-FPM
tail -f /var/log/php8.1-fpm.log
```

---

## 🎯 Common Scenarios

### Scenario 1: Shared Hosting
- `.user.ini` is your only option
- Wait 5-10 minutes for changes
- If doesn't work, contact hosting support
- Some hosts don't allow these limits

### Scenario 2: VPS/Dedicated Server
- Edit `/etc/php/8.1/fpm/php.ini` directly
- Or use `.user.ini` (already created)
- Restart PHP-FPM after changes
- Full control over settings

### Scenario 3: Nginx Server
- `.htaccess` doesn't work with Nginx
- Must edit Nginx config + PHP-FPM config
- See `PHP_CONFIGURATION.md` Section 4
- Restart both services

### Scenario 4: Docker Container
- Must rebuild container after config changes
- See `PHP_CONFIGURATION.md` Section 5
- Use Dockerfile or docker-compose

### Scenario 5: Apache + mod_php
- `.htaccess` should work automatically
- If not, check `AllowOverride All` in vhost
- See `PHP_CONFIGURATION.md` Section 3

---

## 🔧 Troubleshooting Commands

**Find PHP configuration file:**
```bash
php --ini
```

**Check if PHP-FPM is running:**
```bash
systemctl status php8.1-fpm
```

**Check Apache configuration:**
```bash
apache2 -t
apachectl -S
```

**Check Nginx configuration:**
```bash
nginx -t
```

**Find PHP version:**
```bash
php -v
```

**Test PHP from command line:**
```bash
php -r "sleep(40); echo 'Success';"
```
If this fails with timeout, your PHP CLI has different settings.

---

## ⚙️ Advanced: Direct PHP.ini Edit

If you have root access and want permanent changes:

**1. Find php.ini:**
```bash
php --ini
```

**2. Edit the file:**
```bash
sudo nano /etc/php/8.1/fpm/php.ini
# or
sudo nano /etc/php/8.1/apache2/php.ini
```

**3. Find and update these lines:**
```ini
max_execution_time = 600
max_input_time = 600
upload_max_filesize = 512M
post_max_size = 512M
memory_limit = 512M
```

**4. Restart PHP:**
```bash
sudo systemctl restart php8.1-fpm
```

---

## 📞 Need More Help?

1. ✅ Run `./fix-timeout.sh` for automated diagnosis
2. ✅ Visit `/check-php-limits.php` for visual feedback
3. ✅ Read `PHP_CONFIGURATION.md` for detailed instructions
4. ✅ Check server logs for specific errors
5. ✅ Contact your hosting provider if limits are restricted

---

## ✨ Summary Checklist

- [ ] Configuration files created (`.user.ini`, etc.)
- [ ] PHP/Web server restarted OR waited 5-10 minutes
- [ ] Verified with `/check-php-limits.php`
- [ ] Deleted `check-php-limits.php` for security
- [ ] Tested file upload - should work now!

---

## 🎉 It Should Work Now!

If you've completed the steps above, large file uploads should work.

**Test it:**
1. Go to the file manager
2. Click "Upload Files"
3. Select large files (up to 500MB each)
4. Upload should complete without timeout

**Still failing?** See `PHP_CONFIGURATION.md` or contact support.

---

Good luck! 🚀

