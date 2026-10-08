# MiniMon Quick Start Guide

Get MiniMon up and running in 15 minutes.

## Option 1: Local Development (5 minutes)

### 1. Clone & Install
```bash
git clone https://github.com/Zakariayerow/mini-monitring.git
cd minimon
composer install
npm install
```

### 2. Setup
```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

### 3. Start
```bash
php artisan serve
```

Visit **http://localhost:8000** in your browser.

**✅ Done!** Dashboard is ready with sample data.

---

## Option 2: Ubuntu Production (15 minutes)

### Prerequisites
- Ubuntu 22.04+ server
- SSH access with sudo
- Domain name (for HTTPS)

### 1. Download Deploy Script
```bash
git clone https://github.com/Zakariayerow/mini-monitring.git
cd minimon/deploy
chmod +x ubuntu-deploy.sh
```

### 2. Run Deployment
```bash
sudo ./ubuntu-deploy.sh
```

Follow the prompts for:
- Server IP/Domain
- MySQL password
- SSL certificate email (Let's Encrypt)

### 3. Verify
```bash
sudo systemctl status minimon-worker      # Queue worker
sudo systemctl status php8.3-fpm          # PHP service
curl https://your-domain.com              # Check HTTPS redirect
```

**✅ Done!** Your monitoring system is live.

Access: **https://your-domain.com**

---

## First Steps After Installation

### 1. Add Your First Device
```
Go to: Devices → + Add Device

Example - Cisco Switch:
├─ Name: Office-Switch-01
├─ Type: Switch
├─ IP Address: 192.168.1.10
├─ Vendor: Cisco
├─ SNMP Community: public
└─ SNMP Port: 161

Click "Add Device" → Ports auto-discovered ✅
```

### 2. Add Your First Host
```
Go to: Hosts → + Add Host

Example - Ubuntu Server:
├─ Name: App-Server-01
├─ Hostname: app01.example.com
├─ IP Address: 10.0.1.50
├─ Operating System: Ubuntu 22.04
└─ Architecture: x86_64

Click "Create Host" → Copy agent_key ✅
```

### 3. Install Python Agent on Host
```bash
# SSH into the host server
ssh root@app01.example.com

# Install Python and packages
sudo apt update
sudo apt install -y python3 python3-pip
pip3 install requests psutil

# Create agent script
mkdir -p /opt/minimon
wget -O /opt/minimon/agent.py \
  https://raw.githubusercontent.com/Zakariayerow/mini-monitring/main/agent/python/minimon_agent.py

# Run agent (replace with your values)
python3 /opt/minimon/agent.py \
  --server https://your-monitoring-server.com \
  --key YOUR_AGENT_KEY_FROM_STEP_2 \
  --interval 60
```

**✅ Metrics flowing!** Check Dashboard → Hosts

### 4. Set Alert Thresholds (Optional)
Alerts are pre-configured with defaults:
- CPU > 80% → Alert triggered
- Memory > 85% → Alert triggered
- Disk > 90% → Alert triggered

To customize, edit database or use API.

### 5. Generate First Report
```
Go to: Reports → Create Report

Select:
├─ Report Type: Summary
├─ Period: Last 7 days
└─ Generate

↓ Download CSV for analysis
```

---

## Architecture Overview

```
┌─────────────────────────────────────────────────┐
│           Your Monitoring Dashboard              │
│       (https://your-domain.com)                  │
└──────────────┬──────────────────────────────────┘
               │
        ┌──────┴──────────┐
        │                 │
   ┌────▼────┐      ┌────▼────┐
   │ SNMP    │      │ REST API │
   │ Devices │      │ Agents   │
   └────┬────┘      └────┬─────┘
        │                │
   ┌────▼────┐      ┌────▼──────┐
   │ Switches│      │ Python    │
   │ Routers │      │ Agent     │
   │ Firewalls       │ CPU/Disk  │
   └─────────┘      │ Metrics   │
                    └───────────┘
```

---

## Key Features at a Glance

### 📊 Dashboard
- Real-time host/device/alert status
- Quick overview of infrastructure health

### 🔴 Alerts
- Threshold-based automatic triggering
- Status tracking: New → Acknowledged → Resolved
- Extensible for notifications (email, Slack, etc.)

### 📈 Reports
- Summary: Host count, device count, online %
- Host: CPU/Memory/Disk trends
- Device: Port status and traffic
- CSV export for archiving

### 🖥️ Devices (SNMP)
Automatic discovery of:
- Routers, Switches, Firewalls
- Access Points, Servers, Workstations
- Port statistics and interface health

### 💾 Hosts (Python Agent)
Collect:
- CPU utilization and load average
- Memory and swap usage
- Disk space and I/O stats
- Network interface traffic
- Process and service monitoring

### 🔐 Security
- HTTPS enforcement
- Bearer token auth (API & Agents)
- CSRF protection
- Secure session cookies

---

## Common Tasks

### Add more devices
```bash
curl https://your-domain.com/devices/create
# OR via API:
curl -X POST https://your-domain.com/api/devices \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Device-02","type":"Router","ip_address":"192.168.1.1"}'
```

### View Reports
```
Go to: Reports
├─ Click "Create Report"
├─ Select type, period
└─ Download CSV
```

### Export All Data
```bash
# Backup database
mysqldump -u minimon -p minimon > backup.sql

# Export reports as CSV (via UI)
Reports → [Report Name] → Download
```

### Monitor via API
```bash
# Get all alerts
curl https://your-domain.com/api/alerts \
  -H "Authorization: Bearer YOUR_API_TOKEN"

# Get host metrics last 24h
curl https://your-domain.com/api/metrics \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d 'host_id=1&hours=24'
```

---

## Troubleshooting

### "Devices show offline"
1. Verify network: `ping <device-ip>`
2. Check SNMP enabled: `snmpwalk -v 2c -c public <device-ip>`
3. Check firewall allows UDP 161

### "No metrics from host"
1. Verify agent running: `ps aux | grep minimon_agent`
2. Check connectivity: `curl <server-url>`
3. Verify correct agent key and server URL

### "Dashboard slow"
1. Check database: `mysql -u root status`
2. Reduce polling frequency (in cron schedule)
3. Archive old reports (>90 days auto-deleted)

### "HTTPS certificate errors"
1. Re-run deployment script: `sudo ./ubuntu-deploy.sh`
2. Certbot renewal: `sudo certbot renew --dry-run`
3. Check DNS: `nslookup your-domain.com`

---

## Next Steps

1. **Read Full Docs**
   - [DEVICES.md](DEVICES.md) - Device type details
   - [AGENTS.md](AGENTS.md) - Agent configuration
   - [DEPLOYMENT.md](DEPLOYMENT.md) - Production setup
   - [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md) - Pre-launch checklist

2. **Scale Up**
   - Add 10+ devices
   - Install agents on 5+ hosts
   - Configure alert notifications
   - Schedule report generation

3. **Integrate**
   - API calls from monitoring scripts
   - Webhook alerts to Slack/email
   - Custom SNMP templates for vendors
   - Database backups

4. **Secure**
   - Firewall rules (restrict API access)
   - API key rotation quarterly
   - Database encryption at rest
   - Log aggregation and retention

---

**Need Help?**
- Check [DEPLOYMENT.md](DEPLOYMENT.md) for manual setup steps
- Review [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)
- Run tests: `php artisan test`
- Check logs: `tail -f storage/logs/laravel.log`
