# Changelog - S3 File Manager

## October 15, 2025

### ✨ New Features

#### Multi-File Upload
- Upload multiple files simultaneously (up to 50 files at once)
- Visual file preview with sizes before upload
- Progress bar indication during upload
- Radio buttons for choosing Public/Private access

#### Clean Public URLs
- **New:** Public file URLs now clean without query parameters
- **Before:** `https://bucket.space.com/file.jpg?X-Amz-Algorithm=...&X-Amz-Signature=...`
- **After:** `https://bucket.space.com/file.jpg`
- URLs are permanent (don't expire)
- Better for sharing and SEO

#### Public/Private File Management
- Toggle file visibility with one click
- Globe icon (🌐) for public files
- Lock icon (🔒) for private files
- Copy button (📋) appears only for public files
- AJAX-based updates without page reload

### 🔧 Improvements

#### Timeout Handling
- Maximum execution time increased to 600 seconds (10 minutes)
- Guzzle HTTP client timeout: 600 seconds
- Connection timeout: 120 seconds
- Read timeout: 600 seconds
- Automatic retry: 3 attempts on network errors
- Memory limit increased to 512M

#### Upload Limits
- Max file size: 512M (was 64M)
- Max files per upload: 50
- Max total POST size: 512M

#### Code Organization
- Created `IncreaseTimeout` middleware for centralized timeout management
- Added `set_time_limit()` in controller and service methods
- Enhanced S3Client configuration with retry logic

### 📝 Configuration Files Created

#### PHP Configuration
- `.user.ini` (root directory)
- `public/.user.ini` (public directory)
- `public/.htaccess` (updated with PHP limits)

#### Helper Scripts
- `fix-timeout.sh` - Automated PHP restart and diagnostics
- `check-php-limits.php` - Visual PHP configuration checker (deleted after use)

### 📚 Documentation

#### User Guides
- `ERROR_FIXED.md` - Quick fix summary for timeout error
- `TEST_UPLOAD.md` - Step-by-step testing instructions
- `PUBLIC_URL_INFO.md` - Public URL feature documentation
- `QUICK_FIX.md` - 1-minute fix reference

#### Technical Documentation
- `PHP_CONFIGURATION.md` - Complete server configuration guide
- `TIMEOUT_FIX_SUMMARY.md` - Comprehensive timeout fix documentation
- `.context/improvements-2025-10-15.md` - Original feature implementations
- `.context/guzzle-timeout-fix-2025-10-15.md` - Guzzle HTTP timeout fix
- `.context/public-url-improvement-2025-10-15.md` - Clean URL implementation

#### Updated
- `README.md` - Updated with new features and fixes

### 🐛 Bug Fixes

#### Fixed: "Maximum execution time of 30 seconds exceeded"
**Location:** `vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php:169`

**Root Causes:**
1. PHP `max_execution_time` was 30 seconds
2. Guzzle HTTP client defaulted to 30 seconds
3. Large file uploads exceeded timeout

**Solutions Applied:**
1. Code-level: `set_time_limit(600)` in controller and service
2. Middleware: `IncreaseTimeout` applied to all file-manager routes
3. Guzzle: HTTP timeouts increased to 600 seconds
4. Config: `.user.ini` and `.htaccess` files created
5. Retry: Auto-retry logic for network failures

**Status:** ✅ Fixed

### 🔄 API Changes

#### S3Service::getPublicUrl()
**Before:**
```php
// Returned pre-signed URL with expiration
return "https://...?X-Amz-Algorithm=...&X-Amz-Signature=...";
```

**After:**
```php
// Returns clean permanent URL for public files
return "https://bucket.region.space.com/path/file.jpg";
```

**Breaking Change:** No - backward compatible
**Migration:** None required - automatically uses new format

### ⚙️ System Requirements

#### Minimum Requirements
- PHP 8.1 or higher
- Composer
- S3-compatible storage (AWS S3, DigitalOcean Spaces, MinIO, etc.)

#### Recommended
- PHP-FPM or Apache with mod_php
- 512M memory_limit
- 600s max_execution_time
- FastCGI or similar for optimal performance

### 📦 Dependencies

No new dependencies added. Uses existing:
- Laravel Framework
- AWS SDK for PHP
- Guzzle HTTP Client
- Bootstrap 5.3.0
- Bootstrap Icons 1.7.2

### 🚀 Upgrade Instructions

#### From Previous Version

1. **Pull latest code**
   ```bash
   git pull
   ```

2. **Clear Laravel cache**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

3. **Restart PHP (recommended)**
   ```bash
   sudo systemctl restart php8.1-fpm
   # or
   sudo systemctl restart apache2
   ```

4. **Test upload functionality**
   - Upload a test file
   - Verify timeout doesn't occur
   - Test public URL copy feature

#### Configuration Changes

Add to `.env` (optional, recommended):
```env
DO_SPACES_URL=https://your-bucket.region.digitaloceanspaces.com
```

### 🧪 Testing

All features tested:
- ✅ Multi-file upload (1-50 files)
- ✅ Large file upload (up to 500MB)
- ✅ Timeout handling (no errors)
- ✅ Public/Private toggle
- ✅ Clean URL copying
- ✅ Folder creation
- ✅ File deletion
- ✅ Visual indicators
- ✅ Error messages

### 📊 Performance

#### Upload Speed
Based on 10Mbps upload:
- 10MB: ~8-10 seconds
- 50MB: ~40-50 seconds
- 100MB: ~80-90 seconds
- 500MB: ~6-7 minutes

#### Memory Usage
- Small files (<10MB): Minimal memory
- Large files (500MB): Up to 512MB memory
- Concurrent uploads: Monitor PHP-FPM pool

### 🛡️ Security

- Public files are accessible to anyone with URL
- Private files require authentication
- ACL properly set on S3 (public-read vs private)
- No sensitive data exposed in URLs
- CORS configuration recommended for web access

### 🐛 Known Issues

None currently reported.

### 📞 Support

For issues:
1. Check `storage/logs/laravel.log`
2. Read relevant documentation in repo
3. Run `./fix-timeout.sh` for diagnostics
4. Check S3 bucket permissions and CORS

### 🙏 Acknowledgments

Thanks to the user for reporting issues and requesting improvements!

---

**Version:** 1.1.0
**Release Date:** October 15, 2025
**Status:** Stable ✅

