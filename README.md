# S3 File Manager

A powerful and user-friendly file manager for S3-compatible storage (AWS S3, DigitalOcean Spaces, etc.) built with Laravel.

> **✅ Timeout Error Fixed!**
> **Code-level fixes applied:**
> - Guzzle timeout: 600s (was 30s)
> - Auto-retry: 3 attempts
> - PHP timeout: set in code
> - Just **test upload now**! See: **[ERROR_FIXED.md](ERROR_FIXED.md)**
>
> **If still having issues:**
> - Run: `./fix-timeout.sh` (automated)
> - Or read: **[TEST_UPLOAD.md](TEST_UPLOAD.md)** (testing guide)
> - Or read: **[TIMEOUT_FIX_SUMMARY.md](TIMEOUT_FIX_SUMMARY.md)** (complete guide)

## Features

- **Multi-File Upload** - Upload multiple files simultaneously
- **Public/Private Access Control** - Set file visibility (public or private)
- **Copy Public URLs** - One-click copy of **clean URLs** (without query parameters) to clipboard
- **Large File Support** - Upload files up to 500MB per file
- **Extended Timeouts** - 5-minute upload timeout and 10-minute execution time
- **Folder Management** - Create and manage folder structures
- **File Operations** - Delete files and folders
- **Visual Progress** - Upload progress indication
- **Modern UI** - Clean, responsive Bootstrap 5 interface
- **Clean URLs** - Public file URLs without query parameters (permanent links)

## Requirements

- PHP 8.1 or higher
- Composer
- S3-compatible storage service (AWS S3, DigitalOcean Spaces, etc.)

## Installation

1. Clone the repository
2. Install dependencies:
```bash
composer install
```

3. Copy `.env.example` to `.env` and configure your S3 credentials:
```env
DO_SPACES_KEY=your_access_key
DO_SPACES_SECRET=your_secret_key
DO_SPACES_REGION=your_region
DO_SPACES_BUCKET=your_bucket_name
DO_SPACES_ENDPOINT=https://your-region.digitaloceanspaces.com
DO_SPACES_BUCKET_PDFS_PATH=your/path
DO_SPACES_URL=https://your-bucket.your-region.digitaloceanspaces.com
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Start the development server:
```bash
php artisan serve
```

6. Access the application at `http://localhost:8000`

## Usage

### Upload Files

1. Click the "Upload File" button
2. Select one or multiple files (up to 500MB each)
3. Choose visibility:
   - **Public** - File is accessible to anyone with the URL
   - **Private** - File requires authentication to access
4. Click "Upload Files"

### Manage File Visibility

- Click the **globe icon** (🌐) to make a file public
- Click the **lock icon** (🔒) to make a file private
- The button will toggle between states

### Copy Public URL

- For public files, click the **clipboard icon** (📋) to copy the public URL
- URL is **clean and permanent** (no query parameters, no expiration)
- Example: `https://bucket.region.digitaloceanspaces.com/files/image.jpg`
- A success message will appear confirming the URL was copied
- The URL is automatically copied to your clipboard
- See [PUBLIC_URL_INFO.md](PUBLIC_URL_INFO.md) for details

### Create Folders

1. Click "Create Folder"
2. Enter folder name
3. Click "Create Folder"

### Delete Files/Folders

- Click the **trash icon** next to any file or folder
- Confirm the deletion

## Configuration

### PHP Upload Limits

The `.htaccess` file is pre-configured with the following limits:

- `upload_max_filesize`: 512M
- `post_max_size`: 512M
- `max_execution_time`: 600 seconds (10 minutes)
- `max_input_time`: 600 seconds
- `memory_limit`: 512M

If using Nginx, add these to your `php.ini` or `php-fpm` configuration.

### S3 Timeouts

The S3 client is configured with:
- Upload timeout: 300 seconds (5 minutes)
- Connection timeout: 60 seconds (1 minute)

You can modify these in `app/Services/S3Service.php`.

## Troubleshooting

### ⚠️ "Maximum execution time of 30 seconds exceeded"

This is a PHP configuration issue. **See [PHP_CONFIGURATION.md](PHP_CONFIGURATION.md) for detailed solutions.**

**Quick Fix:**
1. Files `.user.ini` and `public/.user.ini` are already created
2. Wait 5-10 minutes for changes to take effect
3. OR restart PHP-FPM: `sudo systemctl restart php8.1-fpm`
4. If still failing, see PHP_CONFIGURATION.md for your specific server setup

**Verify Settings:**
Create `public/test-limits.php` with this content:
```php
<?php
echo "<pre>";
echo "max_execution_time: " . ini_get('max_execution_time') . " (need: 600)\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . " (need: 512M)\n";
echo "post_max_size: " . ini_get('post_max_size') . " (need: 512M)\n";
echo "memory_limit: " . ini_get('memory_limit') . " (need: 512M)\n";
echo "</pre>";
?>
```
Visit `/test-limits.php` and verify settings. **Delete the file after!**

### Large File Upload Fails

1. Check your PHP configuration limits (see above)
2. Verify your web server timeout settings
3. Ensure your S3 credentials are correct
4. Check S3 bucket permissions
5. Monitor server error logs

### Public URL Not Working

1. Verify file visibility is set to "public"
2. Check S3 bucket CORS settings
3. Ensure bucket has public read permissions enabled

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
