<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MiniMon - Add Device</title>

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

        input,
        select,
        textarea {
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

    <h1>Add Device</h1>

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

    <form action="{{ route('devices.store') }}" method="POST">

        @csrf

        <label>Device Name</label>

        <input
            type="text"
            name="name"
            value="{{ old('name') }}"
            placeholder="Core-Switch-01"
            required
        >

        <label>Device Type</label>

        <select name="type" required>

            <option value="">Select device type</option>

            <option value="Router">Router</option>
            <option value="Switch">Switch</option>
            <option value="Firewall">Firewall</option>
            <option value="Access Point">Access Point</option>
            <option value="Server">Server</option>
            <option value="Workstation">Workstation</option>
            <option value="Printer">Printer</option>
            <option value="Other">Other</option>

        </select>

        <label>Floor</label>

        <input
            type="text"
            name="floor"
            value="{{ old('floor') }}"
            placeholder="3rd Floor"
        >

        <label>IP Address</label>

        <input
            type="text"
            name="ip_address"
            value="{{ old('ip_address') }}"
            placeholder="192.168.1.1"
            required
        >

        <label>Status</label>

        <select name="status" required>

            <option value="unknown">Unknown</option>
            <option value="online">Online</option>
            <option value="offline">Offline</option>

        </select>

        <label>SNMP Port</label>

        <input
            type="number"
            name="snmp_port"
            value="{{ old('snmp_port', 161) }}"
            min="1"
            max="65535"
        >

        <label>SNMP Version</label>

        <select name="snmp_version">

            <option value="">Not configured</option>
            <option value="1">SNMP v1</option>
            <option value="2c">SNMP v2c</option>
            <option value="3">SNMP v3</option>

        </select>

        <label>SNMP Community</label>

        <input
            type="password"
            name="snmp_community"
            placeholder="SNMP community"
        >

        <label>Description</label>

        <textarea
            name="description"
            rows="4"
            placeholder="Optional notes"
        >{{ old('description') }}</textarea>

        <button type="submit">
            Add Device
        </button>

    </form>

    <br>

    <a href="{{ route('devices.index') }}">
        ← Back to Devices
    </a>

</div>

</body>
</html>