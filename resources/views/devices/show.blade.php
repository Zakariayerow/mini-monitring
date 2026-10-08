<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MiniMon - {{ $device->name }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 40px;
        }

        .container {
            max-width: 1300px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
        }

        .label {
            font-size: 13px;
            color: #64748b;
        }

        .value {
            font-size: 17px;
            font-weight: bold;
            margin-top: 5px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-number {
            font-size: 30px;
            font-weight: bold;
        }

        .up {
            color: green;
            font-weight: bold;
        }

        .down {
            color: red;
            font-weight: bold;
        }

        .unknown {
            color: #777;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #1f2937;
            color: white;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .success {
            background: #d1fae5;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .empty {
            padding: 20px;
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>

<div class="container">

    <a href="{{ route('devices.index') }}">
        ← Back to Devices
    </a>

    <h1>{{ $device->name }}</h1>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @php
        $totalPorts = $device->ports->count();
        $upPorts = $device->ports->where('status', 'up')->count();
        $downPorts = $device->ports->where('status', 'down')->count();
    @endphp

    <div class="card">

        <h2>Device Information</h2>

        <div class="info-grid">

            <div class="info">
                <div class="label">Device Name</div>
                <div class="value">{{ $device->name }}</div>
            </div>

            <div class="info">
                <div class="label">Type</div>
                <div class="value">{{ $device->type }}</div>
            </div>

            <div class="info">
                <div class="label">Floor</div>
                <div class="value">{{ $device->floor ?: '-' }}</div>
            </div>

            <div class="info">
                <div class="label">IP Address</div>
                <div class="value">{{ $device->ip_address }}</div>
            </div>

            <div class="info">
                <div class="label">Device Status</div>
                <div class="value">

                    @if($device->status === 'online')
                        <span class="up">● Online</span>
                    @elseif($device->status === 'offline')
                        <span class="down">● Offline</span>
                    @else
                        <span class="unknown">● Unknown</span>
                    @endif

                </div>
            </div>

            <div class="info">
                <div class="label">Last Checked</div>
                <div class="value">
                    {{ $device->last_checked_at?->format('Y-m-d H:i:s') ?? '-' }}
                </div>
            </div>

            <div class="info">
                <div class="label">SNMP Port</div>
                <div class="value">
                    {{ $device->snmp_port ?: '-' }}
                </div>
            </div>

            <div class="info">
                <div class="label">SNMP Version</div>
                <div class="value">
                    {{ $device->snmp_version ?: '-' }}
                </div>
            </div>

            <div class="info">
                <div class="label">Last Up</div>
                <div class="value">
                    {{ $device->last_up_at?->format('Y-m-d H:i:s') ?? '-' }}
                </div>
            </div>

        </div>

    </div>

    <div class="stats">

        <div class="stat">
            <div>Total Ports</div>
            <div class="stat-number">
                {{ $totalPorts }}
            </div>
        </div>

        <div class="stat">
            <div>Ports UP</div>
            <div class="stat-number up">
                {{ $upPorts }}
            </div>
        </div>

        <div class="stat">
            <div>Ports DOWN</div>
            <div class="stat-number down">
                {{ $downPorts }}
            </div>
        </div>

    </div>

    <div class="card">

        <h2>Ports / Interfaces</h2>

        @if($device->ports->count())

            <table>

                <thead>

                    <tr>
                        <th>Interface</th>
                        <th>Status</th>
                        <th>Speed</th>
                        <th>Traffic In</th>
                        <th>Traffic Out</th>
                        <th>Errors In</th>
                        <th>Errors Out</th>
                        <th>Last Checked</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                @foreach($device->ports as $port)

                    <tr>

                        <td>
                            {{ $port->interface_name }}
                        </td>

                        <td>

                            @if($port->status === 'up')
                                <span class="up">● UP</span>

                            @elseif($port->status === 'down')
                                <span class="down">● DOWN</span>

                            @else
                                <span class="unknown">
                                    ● {{ strtoupper($port->status) }}
                                </span>
                            @endif

                        </td>

                        <td>
                            {{ $port->speed ? number_format($port->speed / 1000000, 0) . ' Mbps' : '-' }}
                        </td>

                        <td>
                            {{ number_format($port->bytes_in ?? 0) }}
                        </td>

                        <td>
                            {{ number_format($port->bytes_out ?? 0) }}
                        </td>

                        <td>
                            {{ number_format($port->errors_in ?? 0) }}
                        </td>

                        <td>
                            {{ number_format($port->errors_out ?? 0) }}
                        </td>

                        <td>
                            {{ $port->last_checked_at?->format('Y-m-d H:i:s') ?? '-' }}
                        </td>

                        <td>

                            <form
                                action="{{ route('devices.ports.destroy', [$device, $port]) }}"
                                method="POST"
                            >

                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    onclick="return confirm('Delete this port?')"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        @else

            <div class="empty">
                No ports have been discovered for this device yet.
            </div>

        @endif

    </div>

</div>

</body>
</html>