<laravel-boost-guidelines>
# MiniMon Host Monitoring Agent

The MiniMon Python agent collects system metrics from hosts and reports them to the central monitoring server. It requires minimal setup and supports Linux, macOS, and Windows.

## Features

- **System Metrics**: CPU, memory, disk space, network interface stats
- **Uptime Tracking**: Host availability and reboot detection
- **Process Monitoring**: Configurable process/service checking
- **Low Overhead**: ~1-2% CPU impact, runs in background
- **Secure**: Bearer token authentication, HTTPS only
- **Flexible**: Configurable collection interval and metrics

---

## Quick Install

### Ubuntu/Debian (Recommended)

```bash
# 1. Install Python and packages
sudo apt update
sudo apt install -y python3 python3-pip
pip3 install requests psutil

# 2. Download agent
sudo mkdir -p /opt/minimon
sudo wget -O /opt/minimon/agent.py \
  https://raw.githubusercontent.com/Zakariayerow/mini-monitring/main/agent/python/minimon_agent.py

# 3. Get agent key from MiniMon dashboard
# Go to: Hosts → [Your Host] → Copy agent_key

# 4. Run agent
python3 /opt/minimon/agent.py \
  --server https://your-monitoring-server.com \
  --key YOUR_AGENT_KEY \
  --interval 60

# 5. (Optional) Run as systemd service
# See "Running as Service" section below
```

### CentOS/RHEL

```bash
# 1. Install Python
sudo yum update -y
sudo yum install -y python3 python3-pip gcc

# 2. Install Python packages
pip3 install requests psutil

# 3. Download agent
sudo mkdir -p /opt/minimon
sudo wget -O /opt/minimon/agent.py \
  https://raw.githubusercontent.com/Zakariayerow/mini-monitring/main/agent/python/minimon_agent.py

# 4. Run (same as Ubuntu/Debian)
python3 /opt/minimon/agent.py \
  --server https://your-monitoring-server.com \
  --key YOUR_AGENT_KEY \
  --interval 60
```

### macOS

```bash
# 1. Install Python (if needed)
brew install python3

# 2. Install packages
pip3 install requests psutil

# 3. Download agent
mkdir -p ~/minimon
wget -O ~/minimon/agent.py \
  https://raw.githubusercontent.com/Zakariayerow/mini-monitring/main/agent/python/minimon_agent.py

# 4. Run agent
python3 ~/minimon/agent.py \
  --server https://your-monitoring-server.com \
  --key YOUR_AGENT_KEY \
  --interval 60

# 5. (Optional) Run at startup
# See "Running as LaunchAgent" section below
```

### Windows (PowerShell)

```powershell
# 1. Install Python from python.org if not already installed
# Verify: python --version

# 2. Install packages
pip install requests psutil

# 3. Create directory
mkdir C:\minimon
cd C:\minimon

# 4. Download agent
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/Zakariayerow/mini-monitring/main/agent/python/minimon_agent.py" `
  -OutFile "agent.py"

# 5. Run agent
python agent.py --server https://your-monitoring-server.com --key YOUR_AGENT_KEY --interval 60

# 6. (Optional) Run as Windows Service
# See "Running as Windows Service" section below
```

---

## Getting Your Agent Key

1. **From Web Dashboard**:
   - Go to `https://your-server/hosts`
   - Click on your host
   - Copy the `agent_key` value (32-character string)

2. **Via API**:
   ```bash
   curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://your-server/api/hosts/1
   ```
   Look for the `agent_key` field in the response.

---

## Agent Configuration

### Command-Line Options

```bash
python3 agent.py \
  --server https://your-server.com    # Required: Server URL
  --key YOUR_AGENT_KEY                # Required: Agent key from dashboard
  --interval 60                        # Optional: Collection interval (seconds, default: 60)
  --hostname custom-name              # Optional: Override system hostname
  --verify-ssl true                   # Optional: Verify SSL cert (default: true)
```

### Configuration File

Create `agent-config.json` for persistent settings:

```json
{
  "server": "https://your-monitoring-server.com",
  "agent_key": "your_32_character_agent_key",
  "interval": 60,
  "hostname": "app-server-01",
  "verify_ssl": true,
  "enable_process_monitoring": true,
  "processes_to_monitor": [
    "nginx",
    "php-fpm",
    "mysql",
    "redis-server"
  ],
  "network_interfaces": [
    "eth0",
    "eth1"
  ]
}
```

Run with config:
```bash
python3 agent.py --config agent-config.json
```

---

## Running as Background Service

### Linux - Systemd Service

**1. Create service file:**
```bash
sudo nano /etc/systemd/system/minimon-agent.service
```

**2. Paste this content:**
```ini
[Unit]
Description=MiniMon Host Monitoring Agent
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/opt/minimon
ExecStart=/usr/bin/python3 /opt/minimon/agent.py --server https://your-server.com --key YOUR_AGENT_KEY --interval 60
Restart=on-failure
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

**3. Start service:**
```bash
sudo systemctl daemon-reload
sudo systemctl enable minimon-agent
sudo systemctl start minimon-agent

# Check status
sudo systemctl status minimon-agent

# View logs
sudo journalctl -u minimon-agent -f
```

### macOS - LaunchAgent

**1. Create plist file:**
```bash
nano ~/Library/LaunchAgents/com.minimon.agent.plist
```

**2. Paste (modify paths as needed):**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.minimon.agent</string>
    <key>ProgramArguments</key>
    <array>
        <string>/usr/local/bin/python3</string>
        <string>/Users/YOUR_USERNAME/minimon/agent.py</string>
        <string>--server</string>
        <string>https://your-server.com</string>
        <string>--key</string>
        <string>YOUR_AGENT_KEY</string>
        <string>--interval</string>
        <string>60</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>StandardOutPath</key>
    <string>/tmp/minimon-agent.log</string>
    <key>StandardErrorPath</key>
    <string>/tmp/minimon-agent-error.log</string>
</dict>
</plist>
```

**3. Load service:**
```bash
launchctl load ~/Library/LaunchAgents/com.minimon.agent.plist

# Check status
launchctl list | grep minimon

# View logs
tail -f /tmp/minimon-agent.log
```

### Windows - Task Scheduler

**1. Open Task Scheduler:**
- Press `Win + R`, type `taskschd.msc`, press Enter

**2. Create Basic Task:**
- Name: `MiniMon Agent`
- Trigger: `At Startup` (or `On a Schedule`)
- Action:
  - Program: `C:\Python311\python.exe` (or your Python path)
  - Arguments: `agent.py --server https://your-server.com --key YOUR_AGENT_KEY --interval 60`
  - Start in: `C:\minimon`

**3. Settings:**
- ✓ Run with highest privileges
- ✓ Run whether user is logged in or not
- ✓ If the task fails, restart after 1 minute

---

## Verification

### Check Agent is Running

**Linux/macOS:**
```bash
ps aux | grep minimon_agent
# Should show running Python process

# Check recent metrics
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
  https://your-server/api/metrics?host_id=1
```

**Windows:**
```powershell
Get-Process python | Where-Object {$_.CommandLine -like "*minimon*"}

# Or view in Task Scheduler: Task Scheduler Library → MiniMon Agent
```

### View Agent Logs

**Systemd:**
```bash
sudo journalctl -u minimon-agent -f
```

**macOS:**
```bash
tail -f /tmp/minimon-agent.log
```

**Windows:**
```powershell
Get-Content -Tail 20 C:\minimon\agent.log
```

### Dashboard Verification

1. Go to `https://your-monitoring-server/hosts`
2. Click your host
3. Check:
   - ✅ Last metric timestamp is recent (within last 2 minutes)
   - ✅ CPU, memory, disk values are present
   - ✅ Status shows "online"

---

## Metrics Collected

| Metric | Unit | Update Frequency | Description |
|--------|------|-----------------|-------------|
| **CPU Usage** | % | Every interval | Total CPU utilization across all cores |
| **Memory** | % | Every interval | RAM usage (excludes cache/buffers) |
| **Disk Usage** | % | Every interval | Disk space used / total for `/` partition |
| **Network In** | bytes | Every interval | Total bytes received (all interfaces) |
| **Network Out** | bytes | Every interval | Total bytes transmitted (all interfaces) |
| **Uptime** | seconds | Every interval | Seconds since last system boot |
| **Load Average** | average | Every interval | 1-minute, 5-minute, 15-minute load |
| **Process Count** | count | Every interval | Number of running processes |
| **Disk I/O** | ops/sec | Every interval | Disk read/write operations per second |

---

## Troubleshooting

### Agent won't start

**Error: `ModuleNotFoundError: No module named 'requests'`**
```bash
# Install missing packages
pip3 install requests psutil

# Or with apt
sudo apt install python3-requests
```

**Error: `Connection refused`**
- Verify server URL is correct and accessible
- Check firewall allows HTTPS (port 443)
- Verify SSL certificate is valid (if using self-signed, see below)

### Agent running but no metrics appear

**Check 1: Verify connection**
```bash
# Can agent reach server?
curl https://your-monitoring-server.com

# Can agent authenticate?
curl -H "Authorization: Bearer YOUR_AGENT_KEY" \
  https://your-monitoring-server.com/api/agent/verify
```

**Check 2: Verify host is registered**
- Dashboard → Hosts
- Find your hostname in the list
- Copy exact agent_key value

**Check 3: Check server response**
```bash
# Run agent with verbose output
python3 agent.py \
  --server https://your-server \
  --key YOUR_KEY \
  --interval 60 \
  --verbose
```

### SSL certificate errors

**If using self-signed certificate:**
```bash
# Disable SSL verification (DEV ONLY, not recommended for production)
python3 agent.py \
  --server https://your-server \
  --key YOUR_KEY \
  --verify-ssl false
```

**Better: Use Let's Encrypt HTTPS** (see [DEPLOYMENT.md](DEPLOYMENT.md))

### High CPU usage from agent

**Solution: Increase collection interval**
```bash
# Instead of 60 seconds, use 300 (5 minutes)
python3 agent.py \
  --server https://your-server \
  --key YOUR_KEY \
  --interval 300
```

### Agent crashes after start

**Check Python version:**
```bash
python3 --version  # Should be 3.7+
```

**Install required packages:**
```bash
pip3 install --upgrade requests psutil
```

**Run in foreground to see errors:**
```bash
python3 agent.py --server https://your-server --key YOUR_KEY
# Wait for error message
```

---

## Performance & Scale

| Aspect | Details |
|--------|---------|
| **CPU Impact** | 1-2% per agent |
| **Memory Usage** | 20-50 MB per agent |
| **Network** | ~1-5 KB per report (depends on metrics) |
| **Servers Supported** | 100+ agents per monitoring server |
| **Update Interval** | Configurable (default 60 seconds) |

**Optimizing for Scale:**
- Increase interval to 300+ seconds for many hosts (5 min instead of 60 sec)
- Use reliable network (avoid high latency)
- Consider agent on dedicated system for 1000+ hosts

---

## Advanced: Custom Metrics

To add custom metrics, modify `agent.py`:

```python
# Around line 80, add custom metrics dict
metrics = {
    'cpu': get_cpu_percent(),
    'memory': get_memory_percent(),
    # ... existing metrics ...
    
    # Add custom metric:
    'custom_app_status': check_custom_app(),
    'custom_queue_size': get_queue_size(),
}

def check_custom_app():
    """Return 1 if app healthy, 0 if unhealthy"""
    try:
        response = requests.get('http://localhost:8000/health', timeout=2)
        return 1 if response.status_code == 200 else 0
    except:
        return 0

def get_queue_size():
    """Return number of items in queue"""
    # Implementation depends on your queue system
    return 0  # Placeholder
```

Then set alerts/thresholds on these custom metrics in the dashboard.

---

## Security

✅ **Agent Security Measures:**
- HTTPS-only communication (no HTTP fallback)
- Bearer token authentication
- Per-host unique agent keys
- No sensitive data in logs
- Minimal permissions needed (no root required, but helpful for some metrics)

✅ **Best Practices:**
- Rotate agent keys periodically (via dashboard)
- Restrict firewall access to monitoring server only
- Keep Python/requests/psutil updated
- Review agent.py source before running on critical systems
- Use least-privileged user account (preferably non-root)

---

## Uninstalling Agent

**Stop agent:**
```bash
# Systemd
sudo systemctl stop minimon-agent

# macOS LaunchAgent
launchctl unload ~/Library/LaunchAgents/com.minimon.agent.plist

# Windows Task Scheduler
# Delete the MiniMon Agent task
```

**Remove files:**
```bash
# Linux/macOS
sudo rm -rf /opt/minimon

# Windows
Remove-Item -Recurse C:\minimon
```

**Remove from dashboard:**
- Go to Hosts → [Your Host] → Delete

---

## More Information

- [QUICKSTART.md](QUICKSTART.md) - Quick setup guide
- [DEPLOYMENT.md](DEPLOYMENT.md) - Server setup
- [README.md](README.md) - Full documentation

---

**Need Help?**
- Check logs: `journalctl -u minimon-agent` (Linux) or tail agent log file
- Verify connectivity: `curl https://your-server`
- Test metrics: Check dashboard after 2-3 metric intervals

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
