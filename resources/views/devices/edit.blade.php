<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniMon - Edit Device</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 40px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .error {
            background: #fee2e2;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Device</h1>

    @if($errors->any())
        <div class="error">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('devices.update', $device) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Device Name</label>
        <input type="text" name="name" value="{{ old('name', $device->name) }}" required>

        <label>Hostname</label>
        <input type="text" name="hostname" value="{{ old('hostname', $device->hostname) }}">

        <label>Device Type</label>
        <select name="type" required>
            <option value="">Select device type</option>
            <option value="Router" {{ old('type', $device->type) === 'Router' ? 'selected' : '' }}>Router</option>
            <option value="Switch" {{ old('type', $device->type) === 'Switch' ? 'selected' : '' }}>Switch</option>
            <option value="Firewall" {{ old('type', $device->type) === 'Firewall' ? 'selected' : '' }}>Firewall</option>
            <option value="Access Point" {{ old('type', $device->type) === 'Access Point' ? 'selected' : '' }}>Access Point</option>
            <option value="Server" {{ old('type', $device->type) === 'Server' ? 'selected' : '' }}>Server</option>
            <option value="Workstation" {{ old('type', $device->type) === 'Workstation' ? 'selected' : '' }}>Workstation</option>
            <option value="Printer" {{ old('type', $device->type) === 'Printer' ? 'selected' : '' }}>Printer</option>
            <option value="Other" {{ old('type', $device->type) === 'Other' ? 'selected' : '' }}>Other</option>
        </select>

        <label>Floor</label>
        <input type="text" name="floor" value="{{ old('floor', $device->floor) }}">

        <label>Vendor</label>
        <input type="text" name="vendor" value="{{ old('vendor', $device->vendor) }}">

        <label>Model</label>
        <input type="text" name="model" value="{{ old('model', $device->model) }}">

        <label>Operating System</label>
        <input type="text" name="operating_system" value="{{ old('operating_system', $device->operating_system) }}">

        <label>IP Address</label>
        <input type="text" name="ip_address" value="{{ old('ip_address', $device->ip_address) }}" required>

        <label>Status</label>
        <select name="status" required>
            <option value="unknown" {{ old('status', $device->status) === 'unknown' ? 'selected' : '' }}>Unknown</option>
            <option value="online" {{ old('status', $device->status) === 'online' ? 'selected' : '' }}>Online</option>
            <option value="offline" {{ old('status', $device->status) === 'offline' ? 'selected' : '' }}>Offline</option>
        </select>

        <label>SNMP Port</label>
        <input type="number" name="snmp_port" value="{{ old('snmp_port', $device->snmp_port ?? 161) }}" min="1" max="65535">

        <label>SNMP Version</label>
        <select name="snmp_version">
            <option value="" {{ old('snmp_version', $device->snmp_version) === null || old('snmp_version', $device->snmp_version) === '' ? 'selected' : '' }}>Not configured</option>
            <option value="1" {{ old('snmp_version', $device->snmp_version) === '1' ? 'selected' : '' }}>SNMP v1</option>
            <option value="2c" {{ old('snmp_version', $device->snmp_version) === '2c' ? 'selected' : '' }}>SNMP v2c</option>
            <option value="3" {{ old('snmp_version', $device->snmp_version) === '3' ? 'selected' : '' }}>SNMP v3</option>
        </select>

        <label>SNMP Community</label>
        <input type="password" name="snmp_community" value="{{ old('snmp_community', $device->snmp_community) }}">

        <label>Description</label>
        <textarea name="description" rows="4">{{ old('description', $device->description) }}</textarea>

        <button type="submit">Update Device</button>
    </form>

    <br>
    <a href="{{ route('devices.show', $device) }}">← Back to Device</a>
</div>
</body>
</html>
