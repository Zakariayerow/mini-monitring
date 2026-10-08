<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Host - MiniMon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 7px; font-weight: bold; }
        input, textarea, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
        textarea { min-height: 100px; }
        .button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        .back { display: inline-block; margin-left: 10px; text-decoration: none; color: #333; }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Host</h1>

    @if ($errors->any())
        <div class="error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('hosts.update', $host) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Host Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $host->name) }}" required>
        </div>

        <div class="form-group">
            <label for="hostname">Hostname</label>
            <input type="text" id="hostname" name="hostname" value="{{ old('hostname', $host->hostname) }}" required>
        </div>

        <div class="form-group">
            <label for="ip_address">IP Address</label>
            <input type="text" id="ip_address" name="ip_address" value="{{ old('ip_address', $host->ip_address) }}" required>
        </div>

        <div class="form-group">
            <label for="operating_system">Operating System</label>
            <input type="text" id="operating_system" name="operating_system" value="{{ old('operating_system', $host->operating_system) }}">
        </div>

        <div class="form-group">
            <label for="architecture">Architecture</label>
            <select id="architecture" name="architecture">
                <option value="">Select architecture</option>
                <option value="x64" {{ old('architecture', $host->architecture) === 'x64' ? 'selected' : '' }}>x64</option>
                <option value="x86" {{ old('architecture', $host->architecture) === 'x86' ? 'selected' : '' }}>x86</option>
                <option value="ARM64" {{ old('architecture', $host->architecture) === 'ARM64' ? 'selected' : '' }}>ARM64</option>
                <option value="ARM" {{ old('architecture', $host->architecture) === 'ARM' ? 'selected' : '' }}>ARM</option>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="unknown" {{ old('status', $host->status) === 'unknown' ? 'selected' : '' }}>Unknown</option>
                <option value="online" {{ old('status', $host->status) === 'online' ? 'selected' : '' }}>Online</option>
                <option value="offline" {{ old('status', $host->status) === 'offline' ? 'selected' : '' }}>Offline</option>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description">{{ old('description', $host->description) }}</textarea>
        </div>

        <button type="submit" class="button">Update Host</button>
        <a href="{{ route('hosts.show', $host) }}" class="back">Cancel</a>
    </form>
</div>
</body>
</html>
