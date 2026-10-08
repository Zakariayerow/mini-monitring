# MiniMon deployment notes

This directory contains example production deployment files for a Linux server running Nginx + PHP-FPM.

## Required server setup

- Install PHP 8.3+, Composer, Nginx, and PHP-FPM
- Clone the app to /var/www/minimon
- Copy .env.example to .env and configure secrets
- Run composer install --no-dev
- Run npm install && npm run build
- Set APP_ENV=production and APP_DEBUG=false
- Set APP_KEY using php artisan key:generate
- Run php artisan migrate --force
- Run php artisan db:seed --force
- Configure queue worker and scheduler

## Nginx

- Copy nginx-minimon.conf to /etc/nginx/sites-available/minimon.conf
- Enable the site and reload Nginx

## Queue worker

- Copy minimon-worker.service to /etc/systemd/system/minimon-worker.service
- Run systemctl daemon-reload
- Run systemctl enable --now minimon-worker

## Scheduler

Add this cron entry for the Laravel scheduler:

* * * * * /usr/bin/php /var/www/minimon/artisan schedule:run >> /dev/null 2>&1

## Security notes

- Keep APP_KEY secret
- Restrict database and app access
- Use HTTPS with valid certificates
- Keep `SESSION_SECURE_COOKIE=true` in production
- Set `APP_FORCE_HTTPS=true` in .env
