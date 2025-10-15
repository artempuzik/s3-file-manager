#!/bin/bash

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  S3 File Manager - Timeout Fix Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${YELLOW}Note: This script is not running as root.${NC}"
    echo -e "${YELLOW}Some operations may require sudo password.${NC}"
    echo ""
fi

# Detect PHP version
echo -e "${BLUE}1. Detecting PHP version...${NC}"
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
echo -e "${GREEN}   PHP Version: ${PHP_VERSION}${NC}"
echo ""

# Check current PHP settings
echo -e "${BLUE}2. Current PHP Settings:${NC}"
echo -e "   max_execution_time: $(php -r 'echo ini_get("max_execution_time");')"
echo -e "   upload_max_filesize: $(php -r 'echo ini_get("upload_max_filesize");')"
echo -e "   post_max_size: $(php -r 'echo ini_get("post_max_size");')"
echo -e "   memory_limit: $(php -r 'echo ini_get("memory_limit");')"
echo ""

# Check if .user.ini files exist
echo -e "${BLUE}3. Checking configuration files...${NC}"
if [ -f ".user.ini" ]; then
    echo -e "${GREEN}   ✅ .user.ini found in root${NC}"
else
    echo -e "${RED}   ❌ .user.ini NOT found in root${NC}"
fi

if [ -f "public/.user.ini" ]; then
    echo -e "${GREEN}   ✅ public/.user.ini found${NC}"
else
    echo -e "${RED}   ❌ public/.user.ini NOT found${NC}"
fi

if [ -f "public/.htaccess" ]; then
    echo -e "${GREEN}   ✅ public/.htaccess found${NC}"
else
    echo -e "${RED}   ❌ public/.htaccess NOT found${NC}"
fi
echo ""

# Detect web server
echo -e "${BLUE}4. Detecting web server...${NC}"
if command -v apache2 &> /dev/null; then
    echo -e "${GREEN}   Apache detected${NC}"
    SERVER="apache"
elif command -v nginx &> /dev/null; then
    echo -e "${GREEN}   Nginx detected${NC}"
    SERVER="nginx"
else
    echo -e "${YELLOW}   Could not detect web server${NC}"
    SERVER="unknown"
fi
echo ""

# Offer to restart services
echo -e "${BLUE}5. Restart PHP/Web Server?${NC}"
echo -e "${YELLOW}   This will apply the new configuration immediately.${NC}"
echo ""
echo "   Select an option:"
echo "   1) Restart PHP-FPM only"
echo "   2) Restart Apache"
echo "   3) Restart Nginx + PHP-FPM"
echo "   4) Skip (wait 5-10 minutes for changes)"
echo "   5) Exit"
echo ""
read -p "   Enter choice [1-5]: " choice

case $choice in
    1)
        echo -e "${BLUE}   Restarting PHP-FPM...${NC}"
        if command -v systemctl &> /dev/null; then
            sudo systemctl restart php${PHP_VERSION}-fpm 2>/dev/null || \
            sudo systemctl restart php-fpm 2>/dev/null || \
            echo -e "${RED}   Failed to restart PHP-FPM${NC}"
        else
            sudo service php${PHP_VERSION}-fpm restart 2>/dev/null || \
            sudo service php-fpm restart 2>/dev/null || \
            echo -e "${RED}   Failed to restart PHP-FPM${NC}"
        fi
        echo -e "${GREEN}   Done!${NC}"
        ;;
    2)
        echo -e "${BLUE}   Restarting Apache...${NC}"
        if command -v systemctl &> /dev/null; then
            sudo systemctl restart apache2 2>/dev/null || \
            sudo systemctl restart httpd 2>/dev/null || \
            echo -e "${RED}   Failed to restart Apache${NC}"
        else
            sudo service apache2 restart 2>/dev/null || \
            sudo service httpd restart 2>/dev/null || \
            echo -e "${RED}   Failed to restart Apache${NC}"
        fi
        echo -e "${GREEN}   Done!${NC}"
        ;;
    3)
        echo -e "${BLUE}   Restarting Nginx + PHP-FPM...${NC}"
        if command -v systemctl &> /dev/null; then
            sudo systemctl restart nginx
            sudo systemctl restart php${PHP_VERSION}-fpm 2>/dev/null || \
            sudo systemctl restart php-fpm 2>/dev/null
        else
            sudo service nginx restart
            sudo service php${PHP_VERSION}-fpm restart 2>/dev/null || \
            sudo service php-fpm restart 2>/dev/null
        fi
        echo -e "${GREEN}   Done!${NC}"
        ;;
    4)
        echo -e "${YELLOW}   Skipping restart. Wait 5-10 minutes for changes to apply.${NC}"
        ;;
    5)
        echo -e "${BLUE}   Exiting...${NC}"
        exit 0
        ;;
    *)
        echo -e "${RED}   Invalid choice. Exiting.${NC}"
        exit 1
        ;;
esac

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}  Next Steps:${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "1. Visit: ${YELLOW}http://your-domain/check-php-limits.php${NC}"
echo -e "2. Verify all settings show ${GREEN}✅${NC}"
echo -e "3. ${RED}DELETE${NC} check-php-limits.php for security!"
echo ""
echo -e "If settings are still wrong:"
echo -e "  - Read: ${YELLOW}QUICK_FIX.md${NC}"
echo -e "  - Or: ${YELLOW}PHP_CONFIGURATION.md${NC}"
echo ""
echo -e "${GREEN}Good luck! 🚀${NC}"
echo ""

