<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $host->name }} - MiniMon
    </title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .button {
            background: #2563eb;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
        }

        .grid {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow:
                0 2px 10px rgba(0,0,0,0.08);
        }

        .metric {
            font-size: 30px;
            font-weight: bold;
            margin-top: 10px;
        }

        .label {
            color: #666;
        }

        .online {
            color: #15803d;
        }

        .offline {
            color: #dc2626;
        }

        .unknown {
            color: #6b7280;
        }

        .info {
            display: grid;
            grid-template-columns:
                repeat(2, 1fr);
            gap: 15px;
        }

        .info-item {
            padding: 12px;
            background: #f8fafc;
            border-radius: 6px;
        }

        .agent {
            background: #111827;
            color: white;
            padding: 15px;
            border-radius: 6px;
            word-break: break-all;
            font-family: monospace;
        }

        @media(max-width: 800px) {

            .grid,
            .info {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <div>

            <h1>
                {{ $host->name }}
            </h1>

            <p>
                {{ $host->hostname }}
                —
                {{ $host->ip_address }}
            </p>

        </div>

        <div>

            <a
                href="{{ route('hosts.index') }}"
                class="button"
            >
                ← Hosts
            </a>

            <a
                href="{{ route('hosts.edit', $host) }}"
                class="button"
            >
                Edit
            </a>

        </div>

    </div>

    @if(session('success'))

        <div class="card">

            {{ session('success') }}

        </div>

    @endif

    <div class="grid">

        <div class="card">

            <div class="label">
                Status
            </div>

            <div class="metric
                {{ $host->status === 'online'
                    ? 'online'
                    : 'offline' }}"
            >

                {{ ucfirst($host->status ?? 'unknown') }}

            </div>

        </div>

        <div class="card">

            <div class="label">
                CPU Usage
            </div>

            <div class="metric">

                {{ $host->cpu_percent !== null
                    ? number_format(
                        $host->cpu_percent,
                        1
                    ) . '%'
                    : '-' }}

            </div>

        </div>

        <div class="card">

            <div class="label">
                Memory Usage
            </div>

            <div class="metric">

                {{ $host->memory_percent !== null
                    ? number_format(
                        $host->memory_percent,
                        1
                    ) . '%'
                    : '-' }}

            </div>

        </div>

    </div>

    <div class="grid">

        <div class="card">

            <div class="label">
                Disk Usage
            </div>

            <div class="metric">

                {{ $host->disk_percent !== null
                    ? number_format(
                        $host->disk_percent,
                        1
                    ) . '%'
                    : '-' }}

            </div>

        </div>

        <div class="card">

            <div class="label">
                Last Seen
            </div>

            <div class="metric">

                {{ $host->last_seen_at
                    ? $host->last_seen_at->diffForHumans()
                    : 'Never' }}

            </div>

        </div>

        <div class="card">

            <div class="label">
                Operating System
            </div>

            <div class="metric">

                {{ $host->operating_system ?? '-' }}

            </div>

        </div>

    </div>

    <div class="card">

        <h2>
            Host Information
        </h2>

        <div class="info">

            <div class="info-item">
                <strong>Hostname:</strong>
                {{ $host->hostname }}
            </div>

            <div class="info-item">
                <strong>IP Address:</strong>
                {{ $host->ip_address }}
            </div>

            <div class="info-item">
                <strong>Architecture:</strong>
                {{ $host->architecture ?? '-' }}
            </div>

            <div class="info-item">
                <strong>Uptime:</strong>
                {{ $host->uptime ?? '-' }}
            </div>

        </div>

    </div>

    <br>

    <div class="card">

        <h2>
            Agent Key
        </h2>

        <p>
            Configure the MiniMon agent with this key.
        </p>

        <div class="agent">
            {{ $host->agent_key }}
        </div>

        <p>
            Keep this key private. It authenticates
            monitoring reports from this host.
        </p>

    </div>

</div>

</body>

</html>