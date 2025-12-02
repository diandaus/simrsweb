#!/bin/bash

# Script Deployment Otomatis untuk SIMRS Khanza Web
# Usage: sudo ./deploy.sh

set -e

echo "=========================================="
echo "SIMRS Khanza Web - Deployment Script"
echo "=========================================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

# Get script directory and determine app root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BACKEND_DIR="$SCRIPT_DIR"
APP_DIR="$(dirname "$BACKEND_DIR")"
FRONTEND_DIR="$APP_DIR/frontend"
WEB_USER="www-data"

# Verify directories exist
if [ ! -d "$BACKEND_DIR" ]; then
    echo -e "${RED}Backend directory not found: $BACKEND_DIR${NC}"
    exit 1
fi

if [ ! -d "$FRONTEND_DIR" ]; then
    echo -e "${RED}Frontend directory not found: $FRONTEND_DIR${NC}"
    exit 1
fi

echo -e "${GREEN}App Directory: $APP_DIR${NC}"
echo -e "${GREEN}Backend Directory: $BACKEND_DIR${NC}"
echo -e "${GREEN}Frontend Directory: $FRONTEND_DIR${NC}"

echo -e "${GREEN}Step 1: Checking prerequisites...${NC}"

# Check PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP 8.2+ first.${NC}"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
PHP_MAJOR=$(php -r 'echo PHP_MAJOR_VERSION;')
PHP_MINOR=$(php -r 'echo PHP_MINOR_VERSION;')

if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 2 ]); then
    echo -e "${RED}PHP version $PHP_VERSION is installed, but PHP 8.2+ is required.${NC}"
    echo -e "${YELLOW}Please upgrade PHP first. See UPGRADE_PHP_UBUNTU.md for instructions.${NC}"
    exit 1
fi

echo -e "${GREEN}PHP version: $PHP_VERSION${NC}"

# Check Composer
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Composer not found. Installing...${NC}"
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Check Node.js
if ! command -v node &> /dev/null; then
    echo -e "${RED}Node.js is not installed. Please install Node.js first.${NC}"
    exit 1
fi

echo -e "${GREEN}Step 2: Setting up backend...${NC}"

cd $BACKEND_DIR

# Install dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}.env file not found. Creating from .env.example...${NC}"
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${YELLOW}Please edit .env file with your database credentials!${NC}"
    else
        echo -e "${RED}.env.example not found. Please create .env manually.${NC}"
        exit 1
    fi
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating APP_KEY..."
    php artisan key:generate
fi

# Create storage link
echo "Creating storage symlink..."
php artisan storage:link || true

# Cache config
echo "Caching configuration..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo -e "${GREEN}Step 3: Setting up frontend...${NC}"

cd $FRONTEND_DIR

# Install dependencies
echo "Installing npm dependencies..."
npm install

# Build for production
echo "Building frontend for production..."
npm run build

echo -e "${GREEN}Step 4: Setting permissions...${NC}"

# Set ownership
chown -R $WEB_USER:$WEB_USER $APP_DIR

# Set directory permissions
find $APP_DIR -type d -exec chmod 755 {} \;

# Set file permissions
find $APP_DIR -type f -exec chmod 644 {} \;

# Special permissions for storage and cache
chmod -R 775 $BACKEND_DIR/storage
chmod -R 775 $BACKEND_DIR/bootstrap/cache

# Secure .env
chmod 600 $BACKEND_DIR/.env

echo -e "${GREEN}Step 5: Checking Apache configuration...${NC}"

# Check if Apache is running
if ! systemctl is-active --quiet apache2; then
    echo -e "${YELLOW}Apache is not running. Starting...${NC}"
    systemctl start apache2
fi

# Enable mod_rewrite
a2enmod rewrite || true
a2enmod headers || true

echo -e "${GREEN}=========================================="
echo "Deployment completed successfully!"
echo "==========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Configure .env file with your database credentials"
echo "2. Create Apache virtual host (see DEPLOYMENT_UBUNTU_APACHE.md)"
echo "3. Create admin user in database (see create_admin_user.sql)"
echo "4. Test the application"
echo ""
echo -e "${YELLOW}Don't forget to:${NC}"
echo "- Setup Apache virtual host"
echo "- Create admin user"
echo "- Test login"
echo "- Setup SSL (recommended)"

