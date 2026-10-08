# MiniMon - Device Management Guide

This guide explains how to add and manage different types of devices in MiniMon via the web interface.

## Quick Start

1. Go to **Devices** in the navigation menu
2. Click **+ Add Device**
3. Fill in the device details
4. Click **Add Device** to register and discover ports

## Adding Devices via Web UI

### Device Registration Form

The device registration form has the following fields:

| Field | Required | Description |
|-------|----------|-------------|
| **Device Name** | ✓ | Unique name for identification (e.g., "Core-Switch-01") |
| **Device Type** | ✓ | Category of device (see types below) |
| **Floor** | - | Physical location (e.g., "3rd Floor", "Server Room") |
| **IP Address** | ✓ | IPv4 address of the device (e.g., 192.168.1.1) |
| **Hostname** | - | DNS hostname (optional) |
| **Vendor** | - | Manufacturer (e.g., Cisco, Huawei, Arista) |
| **Model** | - | Model number (e.g., Catalyst 2960) |
| **Operating System** | - | OS version or firmware (e.g., IOS 15.2) |
| **Status** | ✓ | Initial status (Unknown/Online/Offline) |
| **SNMP Port** | - | UDP port for SNMP (default: 161) |
| **SNMP Version** | - | Protocol version: v1, v2c, or v3 |
| **SNMP Community** | - | Community string for SNMP authentication |
| **Description** | - | Notes or documentation |

### Device Types Supported

#### 1. **Router**
Routers for inter-network communication and traffic routing.

**Example Configuration:**
- Name: `Border-Router-01`
- Type: Router
- IP Address: `10.0.0.1`
- Vendor: Cisco
- Model: ASR 1001-X
- SNMP Community: `router_community`

**What gets monitored:**
- CPU and memory utilization
- Port status and throughput
- Routing table health
- Interface errors and packet loss

---

#### 2. **Switch**
Layer 2/3 switches for network connectivity and VLAN management.

**Example Configuration:**
- Name: `Core-Switch-01`
- Type: Switch
- IP Address: `192.168.1.10`
- Vendor: Hewlett Packard Enterprise
- Model: Aruba 5406R
- Floor: Server Room
- SNMP Community: `switch_community`

**What gets monitored:**
- Port status (up/down)
- Port statistics (bytes in/out, errors)
- VLAN membership
- STP (Spanning Tree) status
- Port security violations

---

#### 3. **Firewall**
Firewalls for security and traffic control.

**Example Configuration:**
- Name: `Perimeter-Firewall-01`
- Type: Firewall
- IP Address: `203.0.113.1`
- Vendor: Palo Alto Networks
- Model: PA-5220
- SNMP Community: `firewall_community`

**What gets monitored:**
- Connection count
- Throughput (in/out)
- Drop rate
- Security policy violations
- Interface status

---

#### 4. **Access Point**
Wireless access points for WiFi coverage.

**Example Configuration:**
- Name: `WiFi-AP-Floor3-01`
- Type: Access Point
- IP Address: `192.168.2.50`
- Vendor: Cisco
- Model: Catalyst 9120E
- Floor: 3rd Floor
- SNMP Community: `ap_community`

**What gets monitored:**
- Connected client count
- Signal strength
- Traffic throughput
- Channel utilization
- Error rates

---

#### 5. **Server**
Physical or virtual servers providing services.

**Example Configuration:**
- Name: `Web-Server-01`
- Type: Server
- IP Address: `10.0.1.5`
- Vendor: Dell
- Model: PowerEdge R750
- Operating System: Ubuntu 22.04
- Floor: Data Center

**What gets monitored:**
- CPU, memory, disk space
- Network interfaces
- Service status (via agents)
- Process utilization
- Disk I/O

---

#### 6. **Workstation**
Desktop or laptop computers.

**Example Configuration:**
- Name: `Dev-Machine-01`
- Type: Workstation
- IP Address: `192.168.100.45`
- Operating System: Windows 11
- Floor: Engineering Department

**What gets monitored:**
- System metrics (CPU, RAM, disk)
- Network connectivity
- Uptime
- Installed agents report metrics

---

#### 7. **Printer**
Network printers and multifunction devices.

**Example Configuration:**
- Name: `Printer-Building-A-01`
- Type: Printer
- IP Address: `192.168.3.100`
- Vendor: Xerox
- Model: VersaLink C7000
- Floor: Main Building

**What gets monitored:**
- Toner and supply levels
- Page counts
- Error conditions
- Network interface status
- Print job queue

---

#### 8. **Other**
Any other network device types.

## SNMP Configuration

### SNMP v2c (Common Carrier)
Most compatible version for standard network equipment.

**Setup:**
1. Select **SNMP v2c** from SNMP Version dropdown
2. Enter the SNMP community string (default is often `public` or `private`)
3. Ensure UDP port 161 is accessible from MiniMon server
4. MiniMon will attempt automatic port discovery on save

**Example:**
```
SNMP Version: v2c
SNMP Port:    161
SNMP Community: router_community
```

### SNMP v3 (Secure)
More secure version with authentication and encryption (requires device support).

**Setup:**
1. Select **SNMP v3** from dropdown
2. Configure on the device with username/password
3. Note the authentication method and encryption settings
4. Enter in community field as: `username:authpass:privpass:method`

---

## Adding Devices via API

Devices can also be added programmatically via the REST API:

```bash
curl -X POST http://localhost:8000/api/devices \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "name": "API-Device-01",
    "type": "Switch",
    "ip_address": "192.168.1.50",
    "vendor": "Cisco",
    "model": "2960",
    "snmp_community": "public",
    "snmp_version": "2c",
    "snmp_port": 161
  }'
```

---

## Troubleshooting Device Registration

### Device shows "Offline" status

**Cause:** MiniMon cannot reach the device IP address.

**Solutions:**
1. Verify network connectivity: `ping 192.168.1.10` from the server
2. Check firewall rules allow UDP port 161 (SNMP)
3. Verify the IP address is correct
4. Check the device is powered on and configured for IP communication

### "SNMP discovery failed" message

**Cause:** Device doesn't respond to SNMP queries.

**Solutions:**
1. Verify SNMP is enabled on the device
2. Check SNMP community string matches device configuration
3. Ensure SNMP version (v1/v2c/v3) is correct and supported by device
4. Verify firewall allows UDP 161 outbound from MiniMon server
5. Check device firewall/access lists permit SNMP from MiniMon IP

### No ports discovered

**Cause:** SNMP is accessible but MiniMon's SNMP queries don't return port data.

**Solutions:**
1. Some devices require specific SNMP OIDs configured
2. Try re-checking SNMP with "Check SNMP" button on device detail page
3. Verify device supports standard SNMP interface MIBs (IF-MIB)
4. Check device documentation for required SNMP community strings (some have different read/write communities)

---

## Managing Devices

### View Devices List
- Go to **Devices** menu: Shows all registered devices
- Status: Online (green), Offline (red), Unknown (gray)
- Last Checked: Shows when the device was last polled

### View Device Details
- Click **View** on any device row
- Shows device properties, SNMP info, discovered ports
- Lists all interfaces with statistics

### Edit Device
- Click **Edit** on device row
- Modify any field (name, vendor, SNMP settings, etc.)
- Changes take effect immediately

### Delete Device
- Click **Delete** button
- Confirms deletion of device and associated port/alert data
- **Warning:** This is permanent

### Check SNMP Connectivity
- Go to device detail page
- Click **Check SNMP** button
- Tests SNMP connectivity and re-discovers ports
- Updates device status

---

## Device Monitoring Schedule

Once registered, devices are monitored automatically on these schedules:

| Task | Frequency | Purpose |
|------|-----------|---------|
| SNMP Poll | Every 1 minute | Port status, interface stats |
| Network Ping | Every 30 seconds | Host availability |
| Alert Check | Every 1 minute | Threshold evaluation |

All monitoring runs on the scheduler configured in [DEPLOYMENT.md](DEPLOYMENT.md).

---

## Best Practices

### Naming Conventions
Use descriptive names that include location and function:
- ✓ `Core-Switch-01` (good)
- ✓ `WiFi-AP-Building-A-Floor3` (good)
- ✗ `Device1` (too generic)
- ✗ `Switch` (ambiguous)

### Vendor/Model Information
Always fill in vendor and model when available:
- Helps with troubleshooting
- Enables vendor-specific SNMP templates
- Documents equipment for auditing

### SNMP Security
- Store SNMP community strings securely
- Use different read/write communities if possible
- Prefer SNMP v3 with authentication when available
- Limit SNMP access via firewall rules

### Port Monitoring
- Check discovered ports after adding devices
- Delete unused ports to reduce noise
- Configure port descriptions for identification
- Monitor important ports for alerts

### Alerts & Thresholds
After adding devices:
1. Go to device detail page
2. Review default alert thresholds
3. Adjust based on business requirements
4. Set escalation policies

---

## Supported Device Templates

MiniMon includes vendor-specific SNMP templates for optimized monitoring. See [vendor documentation](VENDOR_SNMP_TEMPLATES.md) for:
- Cisco (IOS, IOS-XE, ASA)
- Arista
- Huawei
- HP/HPE
- Juniper Networks
- Generic (works with most devices)

---

## Next Steps

1. **Add Hosts:** Register servers with monitoring agents (see [AGENTS.md](AGENTS.md))
2. **Configure Alerts:** Set thresholds and notification rules
3. **View Dashboard:** Monitor device status in real-time
4. **Generate Reports:** Review historical device performance

For deployment questions, see [DEPLOYMENT.md](DEPLOYMENT.md).
