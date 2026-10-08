# MiniMon deployment guide for Ubuntu server

This guide explains how to deploy MiniMon on an Ubuntu server by cloning the repository directly from Git, without Docker.

## 1. Install system dependencies

Run the following on the Ubuntu server:

```bash
sudo apt update
sudo apt install -y git nginx curl unzip composer php8.3 php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml php8.3-curl php8.3-mbstring php8.3-zip php8.3-bcmath php8.3-gd
```

If your system uses a different PHP version, adjust the package names to the version installed on your server.

## 2. Clone the project

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone <your-git-repo-url> minimon
cd /var/www/minimon
```

## 3. Create the environment file

```bash
cp .env.example .env
sudo nano .env
```

Set production values like this:

```env
APP_NAME=MiniMon
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_FORCE_HTTPS=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=minimon
DB_USERNAME=minimon
DB_PASSWORD=your_secure_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_CHANNEL=stack
LOG_LEVEL=error
```

Generate the application key:

```bash
php artisan key:generate
```

## 4. Create the database

Log in to MySQL and create the database and user:

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE minimon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'minimon'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON minimon.* TO 'minimon'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 5. Install PHP and frontend dependencies

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## 6. Run database migrations and seed data

```bash
php artisan migrate --force
php artisan db:seed --force
```

## 7. Set file permissions

```bash
sudo chown -R www-data:www-data /var/www/minimon
sudo chmod -R 775 /var/www/minimon/storage /var/www/minimon/bootstrap/cache
```

## 8. Configure Nginx

Copy the provided config:

```bash
sudo cp /var/www/minimon/deploy/nginx-minimon.conf /etc/nginx/sites-available/minimon.conf
sudo ln -s /etc/nginx/sites-available/minimon.conf /etc/nginx/sites-enabled/minimon.conf
sudo rm -f /etc/nginx/sites-enabled/default
```

Edit the file and update the domain and SSL settings:

```bash
sudo nano /etc/nginx/sites-available/minimon.conf
```

Update:
- `server_name` to your real domain
- `ssl_certificate` and `ssl_certificate_key` to the correct certificate paths
- PHP socket path if necessary

Then validate and reload Nginx:

```bash
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl enable --now php8.3-fpm
```

## 9. Enable HTTPS with Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

After the certificate is generated, reload Nginx:

```bash
sudo systemctl restart nginx
```

## 10. Configure Laravel scheduler

Add this to the server's cron:

```bash
crontab -e
```

Then add:

```bash
* * * * * cd /var/www/minimon && php artisan schedule:run >> /dev/null 2>&1
```

## 11. Configure queue worker

Copy the example service file:

```bash
sudo cp /var/www/minimon/deploy/minimon-worker.service /etc/systemd/system/minimon-worker.service
sudo systemctl daemon-reload
sudo systemctl enable --now minimon-worker
```

Check status:

```bash
sudo systemctl status minimon-worker
```

## 12. Final verification

Test the app:

```bash
cd /var/www/minimon
php artisan test
```

Open the site in a browser using your configured domain. You should see the MiniMon dashboard and app pages.

## 13. Recommended production notes

- Use MySQL or PostgreSQL instead of SQLite in production.
- Keep `APP_KEY` secret and never commit it to Git.
- Keep `APP_DEBUG=false` in production.
- Keep `APP_FORCE_HTTPS=true` when using HTTPS.
- Keep the server patched and restrict SSH access.
- Back up the database regularly.

This setup is suitable for a standard Ubuntu VM or server hosting MiniMon without Docker.
