<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniMon - Devices</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f5f6fa;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #1f2937;
            color: white;
        }

        .online {
            color: green;
            font-weight: bold;
        }

        .offline {
            color: red;
            font-weight: bold;
        }

        .unknown {
            color: #777;
        }

        .actions a {
            margin-right: 10px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>MiniMon - Devices</h1>

    <a href="{{ route('devices.create') }}" class="button">
        + Add Device
    </a>

    @if(session('success'))
        <div style="padding:10px;background:#d1fae5;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    <table>

        <thead>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>IP Address</th>
            <th>Vendor</th>
            <th>Status</th>
            <th>Last Checked</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>

        @forelse($devices as $device)

            <tr>

                <td>
                    {{ $device->name }}
                </td>

                <td>
                    {{ $device->type }}
                </td>

                <td>
                    {{ $device->ip_address }}
                </td>

                <td>
                    {{ $device->vendor ?: '-' }}
                </td>

                <td>

                    @if($device->status === 'online')
                        <span class="online">● Online</span>

                    @elseif($device->status === 'offline')
                        <span class="offline">● Offline</span>

                    @else
                        <span class="unknown">● Unknown</span>
                    @endif

                </td>

                <td>
                    {{ $device->last_checked_at?->format('Y-m-d H:i:s') ?? '-' }}
                </td>

                <td class="actions">

                    <a href="{{ route('devices.show', $device) }}">
                        View
                    </a>

                    <a href="{{ route('devices.edit', $device) }}">
                        Edit
                    </a>

                    <form
                        action="{{ route('devices.destroy', $device) }}"
                        method="POST"
                        style="display:inline;"
                    >

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            onclick="return confirm('Delete this device?')"
                        >
                            Delete
                        </button>

                    </form>

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="7">
                    No devices found.
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>

</div>

</body>
</html>