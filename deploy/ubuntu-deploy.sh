#!/bin/bash
# MiniMon Ubuntu Server Deployment Script
# Run this script on a fresh Ubuntu 22.04+ server

set -e

echo "=========================================="
echo "    MiniMon Ubuntu Deployment Script"
echo "=========================================="

# Update and install system dependencies
echo "[1/8] Installing system dependencies..."
sudo apt update
sudo apt install -y git nginx curl unzip composer \
    php8.3 php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml \
    php8.3-curl php8.3-mbstring php8.3-zip php8.3-bcmath php8.3-gd \
    mysql-server

# Clone the repository
echo "[2/8] Cloning MiniMon repository..."
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/your-org/minimon.git minimon
cd /var/www/minimon
sudo chown -R $USER:$USER /var/www/minimon

# Copy environment file
echo "[3/8] Setting up environment configuration..."
cp .env.production .env
echo "⚠️  Edit .env and set your database credentials and domain:"
echo "   nano /var/www/minimon/.env"
read -p "Press ENTER after editing .env to continue..."

# Generate APP_KEY
echo "[4/8] Generating application key..."
php artisan key:generate

# Install PHP dependencies
echo "[5/8] Installing PHP and frontend dependencies..."
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Create database and user
echo "[6/8] Setting up database..."
read -p "Enter MySQL root password (hit ENTER if no password): " mysql_root_pass
if [ -z "$mysql_root_pass" ]; then
    sudo mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS minimon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'minimon'@'localhost' IDENTIFIED BY 'minimon_secure_password';
GRANT ALL PRIVILEGES ON minimon.* TO 'minimon'@'localhost';
FLUSH PRIVILEGES;
EXIT;
EOF
else
    sudo mysql -u root -p$mysql_root_pass <<EOF
CREATE DATABASE IF NOT EXISTS minimon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'minimon'@'localhost' IDENTIFIED BY 'minimon_secure_password';
GRANT ALL PRIVILEGES ON minimon.* TO 'minimon'@'localhost';
FLUSH PRIVILEGES;
EXIT;
EOF
fi

# Run migrations and seed
echo "[7/8] Running database migrations and seeding..."
php artisan migrate --force
php artisan db:seed --force

# Setup Nginx
echo "[8/8] Configuring Nginx and system services..."
sudo cp /var/www/minimon/deploy/nginx-minimon.conf /etc/nginx/sites-available/minimon.conf
sudo ln -sf /etc/nginx/sites-available/minimon.conf /etc/nginx/sites-enabled/minimon.conf
sudo rm -f /etc/nginx/sites-enabled/default

echo ""
echo "⚠️  Before continuing, edit the Nginx config:"
echo "   sudo nano /etc/nginx/sites-available/minimon.conf"
echo ""
echo "   Update:"
echo "   - server_name to your domain"
echo "   - ssl_certificate path"
echo "   - ssl_certificate_key path"
echo ""
read -p "Press ENTER after editing Nginx config to continue..."

# Set permissions
sudo chown -R www-data:www-data /var/www/minimon
sudo chmod -R 775 /var/www/minimon/storage /var/www/minimon/bootstrap/cache

# Validate and restart Nginx
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl enable --now php8.3-fpm

# Setup Let's Encrypt
read -p "Do you want to setup HTTPS with Let's Encrypt? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    sudo apt install -y certbot python3-certbot-nginx
    read -p "Enter your domain (e.g., monitoring.example.com): " domain
    sudo certbot --nginx -d $domain
    sudo systemctl restart nginx
fi

# Setup scheduler
echo "* * * * * cd /var/www/minimon && php artisan schedule:run >> /dev/null 2>&1" | crontab -

# Setup queue worker
sudo cp /var/www/minimon/deploy/minimon-worker.service /etc/systemd/system/minimon-worker.service
sudo systemctl daemon-reload
sudo systemctl enable --now minimon-worker

# Final status
echo ""
echo "=========================================="
echo "  MiniMon deployment complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Run tests: cd /var/www/minimon && php artisan test"
echo "2. Open your domain in a browser"
echo "3. Log in with your configured credentials"
echo ""
echo "For troubleshooting:"
echo "- Check Nginx logs: sudo tail -f /var/log/nginx/error.log"
echo "- Check PHP-FPM logs: sudo tail -f /var/log/php8.3-fpm.log"
echo "- Check queue worker: sudo systemctl status minimon-worker"
echo ""
