# Production deployment checklist for MiniMon

Use this checklist to verify your Ubuntu server deployment is complete and production-ready.

## Before deployment

- [ ] Domain and DNS configured and pointing to your server IP
- [ ] SSL certificate obtained (Let's Encrypt or commercial)
- [ ] Ubuntu 22.04+ server provisioned and accessible via SSH
- [ ] Backup strategy in place for the database
- [ ] Monitoring setup for the server itself (CPU, disk, memory)

## Deployment steps

- [ ] Run `deploy/ubuntu-deploy.sh` or manually follow `DEPLOYMENT.md`
- [ ] Database created and migrations run
- [ ] `.env` file updated with real credentials
- [ ] `APP_KEY` generated
- [ ] Nginx configured with your domain and SSL cert
- [ ] HTTPS redirects HTTP traffic
- [ ] PHP-FPM is running and enabled
- [ ] Queue worker is running (`systemctl status minimon-worker`)
- [ ] Scheduler cron job is installed

## Post-deployment testing

- [ ] `php artisan test` passes all tests
- [ ] Dashboard page loads at `https://your-domain.com`
- [ ] Host dashboard shows registered hosts
- [ ] Device status page loads
- [ ] Alert thresholds are seeded and working
- [ ] API endpoints accept metrics: `POST /api/metrics` with valid data

## Security hardening

- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `APP_ENV=production` in `.env`
- [ ] `APP_FORCE_HTTPS=true` in `.env`
- [ ] `SESSION_SECURE_COOKIE=true` in `.env`
- [ ] Database user has least-privilege (only CRUD on `minimon` database)
- [ ] SSH key-based authentication only (no password auth)
- [ ] Firewall rules restrict access to ports 22, 80, 443 only
- [ ] No world-readable `.env` file (`chmod 600 .env`)
- [ ] Database backups automated and tested
- [ ] Log rotation configured for Laravel logs

## Monitoring and maintenance

- [ ] Set up log aggregation or monitoring for `/var/log/nginx/error.log`
- [ ] Set up alerts for high disk usage
- [ ] Monthly: Test database restore procedure
- [ ] Monthly: Review security logs
- [ ] Quarterly: Update PHP and system packages
- [ ] Document any custom configurations or deviations from the deployment guide

## Ongoing operations

- [ ] Dashboard accessible to authorized users
- [ ] Hosts and devices registering successfully
- [ ] Alerts triggering and resolving as expected
- [ ] No PHP errors in Laravel logs
- [ ] Queue worker processing jobs (check with `php artisan queue:work` on test run)
- [ ] Scheduler running (verify with `php artisan schedule:list`)

## Contact and rollback

- [ ] Document your deployment date and version
- [ ] Keep a backup of the database before any major updates
- [ ] Have a rollback procedure if something goes wrong
