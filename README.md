# MiniMon

MiniMon is a Laravel-based infrastructure monitoring application for tracking host health, SNMP device status, alerts, metrics, and threshold-based notifications.

## Features

- Host registration and agent-based telemetry collection
- Device and port monitoring
- Metric storage and retrieval APIs
- Alert creation and resolution based on thresholds
- Dashboard overview for hosts, devices, ports, and alerts
- Scheduled monitoring commands for ping and health checks

## Tech stack

- Laravel 13
- PHP 8.3+
- MySQL or SQLite for local development
- Nginx + PHP-FPM for Ubuntu server deployment
- Node.js + Vite for frontend assets

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run dev
```

Then open the app in your browser.

## Device Management

For detailed instructions on adding and managing device types (routers, switches, firewalls, access points, servers, etc.), see [DEVICES.md](DEVICES.md).

## Ubuntu server deployment

For deployment on an Ubuntu server without Docker, use the guide in `DEPLOYMENT.md`.

## Production checklist

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set `APP_URL=https://your-domain.com`
- Set `APP_FORCE_HTTPS=true`
- Use MySQL/PostgreSQL instead of SQLite in production
- Set a strong `APP_KEY`
- Keep `SESSION_SECURE_COOKIE=true`
- Configure cron for the scheduler
- Configure a queue worker for background jobs
- Put the app behind Nginx and HTTPS

## Test suite

```bash
php artisan test
```

## Project structure

- `app/` — Laravel application code
- `agent/python/` — host monitoring agent
- `database/` — migrations, factories, seeders
- `resources/` — Blade templates and frontend assets
- `routes/` — web and API routes
- `deploy/` — Ubuntu server deployment examples
- `DEPLOYMENT.md` — direct Ubuntu deployment guide

## License

This project is for internal or project-use deployment and can be adapted under your preferred licensing terms.
