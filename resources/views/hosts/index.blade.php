<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hosts - MiniMon</title>

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

        h1 {
            margin: 0;
        }

        .button {
            background: #2563eb;
            color: white;
            padding: 11px 18px;
            text-decoration: none;
            border-radius: 6px;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8fafc;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .online {
            background: #dcfce7;
            color: #166534;
        }

        .offline {
            background: #fee2e2;
            color: #991b1b;
        }

        .unknown {
            background: #e5e7eb;
            color: #374151;
        }

        .actions a {
            margin-right: 8px;
            text-decoration: none;
        }

        .view {
            color: #2563eb;
        }

        .edit {
            color: #d97706;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">

        <div>
            <h1>MiniMon Hosts</h1>

            <p>
                Servers and workstations monitored by MiniMon.
            </p>
        </div>

        <a
            href="{{ route('hosts.create') }}"
            class="button"
        >
            + Register Host
        </a>

    </div>

    @if (session('success'))

        <div class="success">
            {{ session('success') }}
        </div>

    @endif

    <div class="card">

        @if ($hosts->count() > 0)

            <table>

                <thead>

                    <tr>
                        <th>Host</th>
                        <th>Hostname</th>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>CPU %</th>
                        <th>Memory %</th>
                        <th>Disk %</th>
                        <th>Last Seen</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach ($hosts as $host)

                        @php
                            $status = strtolower(
                                $host->status ?? 'unknown'
                            );
                        @endphp

                        <tr>

                            <td>
                                <strong>
                                    {{ $host->name }}
                                </strong>
                            </td>

                            <td>
                                {{ $host->hostname }}
                            </td>

                            <td>
                                {{ $host->ip_address }}
                            </td>

                            <td>

                                <span
                                    class="status {{ $status }}"
                                >
                                    {{ ucfirst($status) }}
                                </span>

                            </td>

                            <td>
                                {{ $host->cpu_percent !== null
                                    ? number_format($host->cpu_percent, 1)
                                    : '-' }}
                            </td>

                            <td>
                                {{ $host->memory_percent !== null
                                    ? number_format($host->memory_percent, 1)
                                    : '-' }}
                            </td>

                            <td>
                                {{ $host->disk_percent !== null
                                    ? number_format($host->disk_percent, 1)
                                    : '-' }}
                            </td>

                            <td>
                                {{ $host->last_seen_at
                                    ? $host->last_seen_at->diffForHumans()
                                    : 'Never' }}
                            </td>

                            <td class="actions">

                                <a
                                    href="{{ route('hosts.show', $host) }}"
                                    class="view"
                                >
                                    View
                                </a>

                                <a
                                    href="{{ route('hosts.edit', $host) }}"
                                    class="edit"
                                >
                                    Edit
                                </a>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <div class="empty">

                <h3>No hosts registered</h3>

                <p>
                    Register your first server or workstation
                    to start monitoring it.
                </p>

                <a
                    href="{{ route('hosts.create') }}"
                    class="button"
                >
                    Register First Host
                </a>

            </div>

        @endif

    </div>

</div>

</body>
</html>