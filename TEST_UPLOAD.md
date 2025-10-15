# 🧪 Test Upload Instructions

## What Was Fixed

Your error:
```
Maximum execution time of 30 seconds exceeded
at vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php:169
```

Has been fixed with **code-level changes**:
- ✅ Guzzle HTTP timeout: 600 seconds (was 30s)
- ✅ PHP execution timeout: set_time_limit(600) in code
- ✅ Automatic retry: 3 attempts on failure
- ✅ Memory limit: 512M
- ✅ Middleware: IncreaseTimeout applied to all routes
- ✅ Cache: Cleared config, route, and application cache

## 🚀 Quick Test

### Step 1: Try Upload Immediately

**You can test right now!** The code changes are active.

1. Go to your file manager
2. Click "Upload Files"
3. Select a test file (start with 10-50MB)
4. Choose visibility (public/private)
5. Click "Upload Files"

**Expected:** Upload should complete without timeout error.

### Step 2: If Still Getting Timeout

Apply server-level configuration:

```bash
./fix-timeout.sh
```

Or manually restart PHP:
```bash
sudo systemctl restart php8.1-fpm
# or
sudo systemctl restart apache2
```

## 📝 Testing Checklist

Test with progressively larger files:

- [ ] **Small file (1-5MB)** - Should upload in seconds
- [ ] **Medium file (10-50MB)** - Should upload in under 1 minute
- [ ] **Large file (100-200MB)** - Should upload in 2-5 minutes
- [ ] **Very large file (300-500MB)** - Should upload in 5-10 minutes
- [ ] **Multiple files** - Select 3-5 files at once

## 🔍 Monitoring Upload

Watch Laravel logs in real-time:
```bash
tail -f storage/logs/laravel.log
```

You should see:
- No timeout errors
- Upload progress (if you add logging)
- Success messages

## ✅ Success Indicators

Upload is working if you see:
1. Progress bar animation in browser
2. "X file(s) uploaded successfully" message
3. Files appear in the list
4. No errors in Laravel log

## ❌ If Still Failing

### Check 1: Verify Code Changes Applied

```bash
cd /Users/artempuzik/work/almus/s3-file-manager
php artisan route:list | grep file-manager
```

Should show IncreaseTimeout middleware.

### Check 2: Check PHP Settings

Create `public/test-timeout.php`:
```php
<?php
set_time_limit(600);
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

// Test long operation
for ($i = 0; $i < 40; $i++) {
    sleep(1);
    echo ".";
    flush();
}
echo "\nCompleted 40 seconds without timeout!\n";
?>
```

Visit it. Should complete without timeout.
**DELETE after testing!**

### Check 3: Review Error

If you still get timeout, check **where** it times out:

**30 seconds** = PHP level issue
- Server configuration overrides `set_time_limit()`
- Need to edit php.ini or php-fpm config
- See: PHP_CONFIGURATION.md

**Different timeout** = Network or S3 issue
- Check S3 credentials
- Check network connection
- Check S3 bucket permissions

### Check 4: Server Configuration

Some hosting providers **block** `set_time_limit()`:
```bash
php -r "set_time_limit(600); echo ini_get('max_execution_time');"
```

If output is still 30, your host blocks it.
**Solution:** Contact hosting support OR edit php.ini directly.

## 🎯 Upload Time Expectations

With 10Mbps upload speed:

| File Size | Expected Time |
|-----------|---------------|
| 10 MB     | 8-10 seconds  |
| 50 MB     | 40-50 seconds |
| 100 MB    | 80-90 seconds |
| 200 MB    | 2.5-3 minutes |
| 500 MB    | 6-7 minutes   |

**Note:** Times vary based on:
- Your internet upload speed
- Server location
- S3 region
- Network congestion

## 🐛 Common Issues

### Issue: Upload starts but times out at 30s
**Cause:** Server blocks `set_time_limit()`
**Fix:** Edit php.ini or php-fpm config (see PHP_CONFIGURATION.md)

### Issue: Upload fails immediately
**Cause:** File too large for PHP config
**Fix:** Check `upload_max_filesize` in phpinfo

### Issue: Upload completes but file not in S3
**Cause:** S3 credentials or permissions
**Fix:** Check S3 bucket settings and credentials

### Issue: Memory exhausted
**Cause:** File too large for memory_limit
**Fix:** Increase memory_limit in php.ini

## 📊 Debug Mode

Enable detailed error messages in `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

**Don't forget to disable in production!**

## 🔧 Advanced Testing

Test programmatically:
```bash
php artisan tinker
```

```php
$file = new \Illuminate\Http\UploadedFile(
    '/path/to/test/file.jpg',
    'test.jpg',
    'image/jpeg',
    null,
    true
);

$controller = app(\App\Http\Controllers\FileManagerController::class);
// Test upload logic here
```

## 📞 Still Need Help?

1. Check: `storage/logs/laravel.log`
2. Check: Web server error logs
3. Run: `./fix-timeout.sh`
4. Read: `PHP_CONFIGURATION.md`
5. Read: `.context/guzzle-timeout-fix-2025-10-15.md`

## 🎉 Expected Result

After fixes:
- ✅ No timeout errors
- ✅ Large files upload successfully
- ✅ Multiple files upload at once
- ✅ Progress indication works
- ✅ Public/private visibility works
- ✅ Copy URL button works for public files

---

**Good luck with testing! 🚀**

If it works, mark these as complete:
- [ ] Small file upload tested ✅
- [ ] Large file upload tested ✅
- [ ] Multiple files upload tested ✅
- [ ] No timeout errors ✅
- [ ] Files visible in manager ✅

