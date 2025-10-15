# PHP Configuration for Large File Uploads

## Problem: "Maximum execution time of 30 seconds exceeded"

This error occurs when PHP's default timeout settings are too low for uploading large files to S3.

## Solutions (Apply ONE of these based on your server setup)

### Solution 1: Using .user.ini (Recommended for shared hosting)

Files already created:
- `/public/.user.ini`
- `/.user.ini`

**Important:** After creating these files:
1. Wait 5-10 minutes for changes to take effect
2. Or restart PHP-FPM service

**Verify it worked:**
Create a test file `public/phpinfo.php`:
```php
<?php
phpinfo();
```

Visit `http://your-domain/phpinfo.php` and search for:
- `max_execution_time` - should show 600
- `upload_max_filesize` - should show 512M
- `post_max_size` - should show 512M

**Delete phpinfo.php after checking for security!**

---

### Solution 2: Using php.ini (VPS/Dedicated Server)

**Location:** Find your `php.ini` file:
```bash
php --ini
```

**Edit php.ini and add/modify:**
```ini
upload_max_filesize = 512M
post_max_size = 512M
max_execution_time = 600
max_input_time = 600
memory_limit = 512M
max_file_uploads = 50
```

**Restart PHP:**
```bash
# For PHP-FPM
sudo systemctl restart php8.1-fpm
# or
sudo service php8.1-fpm restart

# For Apache
sudo systemctl restart apache2
```

---

### Solution 3: Using .htaccess (Apache only)

Already configured in `/public/.htaccess`

**Requirements:**
- Apache web server
- `.htaccess` enabled in Apache config
- `AllowOverride All` in your Apache virtual host

**Test if .htaccess is working:**
Add this line to `.htaccess`:
```apache
php_value max_execution_time 999
```

Check phpinfo - if it shows 999, .htaccess is working.

---

### Solution 4: Nginx Configuration

If using Nginx, `.htaccess` and `.user.ini` might not work.

**Edit Nginx site config** (usually in `/etc/nginx/sites-available/`):

```nginx
server {
    # ... other config ...

    # Increase timeouts
    client_max_body_size 512M;
    client_body_timeout 600s;
    fastcgi_read_timeout 600s;
    proxy_read_timeout 600s;

    location ~ \.php$ {
        # ... other php config ...

        # Add these FastCGI params
        fastcgi_param PHP_VALUE "upload_max_filesize=512M \n post_max_size=512M \n max_execution_time=600 \n max_input_time=600 \n memory_limit=512M";
    }
}
```

**Also edit PHP-FPM pool config** (`/etc/php/8.1/fpm/pool.d/www.conf`):

```ini
; Add or modify these lines
php_admin_value[upload_max_filesize] = 512M
php_admin_value[post_max_size] = 512M
php_admin_value[max_execution_time] = 600
php_admin_value[max_input_time] = 600
php_admin_value[memory_limit] = 512M
```

**Restart services:**
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
```

---

### Solution 5: Docker Environment

If running in Docker, you need to modify your Dockerfile or docker-compose.yml.

**Option A: Create custom php.ini**

Create `docker/php/php.ini`:
```ini
upload_max_filesize = 512M
post_max_size = 512M
max_execution_time = 600
max_input_time = 600
memory_limit = 512M
max_file_uploads = 50
```

**In Dockerfile:**
```dockerfile
COPY docker/php/php.ini /usr/local/etc/php/conf.d/uploads.ini
```

**Option B: Use docker-compose.yml**

```yaml
services:
  app:
    environment:
      - PHP_UPLOAD_MAX_FILESIZE=512M
      - PHP_POST_MAX_SIZE=512M
      - PHP_MAX_EXECUTION_TIME=600
      - PHP_MEMORY_LIMIT=512M
```

**Rebuild container:**
```bash
docker-compose down
docker-compose up -d --build
```

---

## Quick Verification Script

Create `public/test-limits.php`:

```php
<?php
echo "<h2>Current PHP Limits:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

$settings = [
    'max_execution_time' => ['current' => ini_get('max_execution_time'), 'required' => 600],
    'upload_max_filesize' => ['current' => ini_get('upload_max_filesize'), 'required' => '512M'],
    'post_max_size' => ['current' => ini_get('post_max_size'), 'required' => '512M'],
    'memory_limit' => ['current' => ini_get('memory_limit'), 'required' => '512M'],
    'max_file_uploads' => ['current' => ini_get('max_file_uploads'), 'required' => 50],
];

foreach ($settings as $name => $data) {
    $status = '❌ Too low';

    if ($name === 'max_execution_time') {
        $status = (int)$data['current'] >= $data['required'] ? '✅ OK' : '❌ Too low';
    } else {
        $current_bytes = return_bytes($data['current']);
        $required_bytes = return_bytes($data['required']);
        $status = $current_bytes >= $required_bytes ? '✅ OK' : '❌ Too low';
    }

    echo "<tr>";
    echo "<td><strong>$name</strong></td>";
    echo "<td>{$data['current']}</td>";
    echo "<td>$status (need: {$data['required']})</td>";
    echo "</tr>";
}

echo "</table>";

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

echo "<hr>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>PHP SAPI:</strong> " . php_sapi_name() . "</p>";
?>
```

**Visit:** `http://your-domain/test-limits.php`

**Remember to delete this file after testing!**

---

## Still Having Issues?

### Check Server Error Logs

**Apache:**
```bash
tail -f /var/log/apache2/error.log
```

**Nginx:**
```bash
tail -f /var/log/nginx/error.log
```

**PHP-FPM:**
```bash
tail -f /var/log/php8.1-fpm.log
```

**Laravel:**
```bash
tail -f storage/logs/laravel.log
```

### Common Issues

1. **Settings not applying:**
   - Wait 5-10 minutes for `.user.ini` to take effect
   - Restart PHP-FPM/Apache
   - Check if your hosting provider restricts these settings

2. **Still getting timeout:**
   - Your hosting provider may have hard limits
   - Contact support to increase PHP limits
   - Consider upgrading hosting plan

3. **Files upload but timeout on processing:**
   - Increase database query timeout
   - Check S3 connection timeout (already set to 300s)
   - Monitor network latency to S3

### Contact Your Hosting Provider

If none of these solutions work, contact your hosting provider and request:
- `max_execution_time`: 600 seconds
- `upload_max_filesize`: 512M
- `post_max_size`: 512M
- `memory_limit`: 512M

---

## Summary

1. ✅ Files `.user.ini` created
2. ✅ `.htaccess` configured
3. ⚠️ Choose additional solution based on your server type
4. 🔍 Use test script to verify
5. 🔄 Restart PHP/web server
6. 🗑️ Delete test files after verification

Good luck! 🚀

