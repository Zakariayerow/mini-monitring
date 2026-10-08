<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>MiniMon Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #111827;
        }

        .navbar {
            background: #111827;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .navbar h1 {
            margin: 0;
        }

        .nav-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            background: #374151;
        }

        .nav-links a:hover {
            background: #4b5563;
        }

        .container {
            max-width: 1400px;
            margin: auto;
            padding: 30px;
        }

        .subtitle {
            color: #64748b;
            margin-top: -10px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
            margin: 25px 0;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        .card-title {
            color: #64748b;
            font-size: 14px;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
            margin-top: 8px;
        }

        .section {
            margin-top: 30px;
        }

        .section h2 {
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #f8fafc;
            color: #374151;
        }

        tr:hover {
            background: #f8fafc;
        }

        .online {
            color: #15803d;
            font-weight: bold;
        }

        .offline {
            color: #dc2626;
            font-weight: bold;
        }

        .warning {
            color: #ca8a04;
            font-weight: bold;
        }

        .critical {
            color: #dc2626;
            font-weight: bold;
        }

        .normal {
            color: #15803d;
        }

        .button {
            display: inline-block;
            padding: 8px 12px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .charts {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .chart {
            height: 180px;
            display: flex;
            align-items: end;
            gap: 4px;
            padding-top: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .bar {
            flex: 1;
            background: #2563eb;
            min-height: 3px;
            border-radius: 3px 3px 0 0;
        }

        .empty {
            padding: 25px;
            text-align: center;
            color: #64748b;
        }

        .status-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .status-online {
            background: #16a34a;
        }

        .status-offline {
            background: #dc2626;
        }

        .footer {
            text-align: center;
            color: #64748b;
            padding: 30px;
        }

        @media(max-width: 1100px) {

            .cards {
                grid-template-columns: repeat(3, 1fr);
            }

            .charts {
                grid-template-columns: 1fr;
            }

        }

        @media(max-width: 700px) {

            .cards {
                grid-template-columns: 1fr 1fr;
            }

            .container {
                padding: 15px;
            }

            table {
                font-size: 13px;
            }

            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            table {
                display: block;
                overflow-x: auto;
            }

        }

    </style>

</head>


<body>


<!-- NAVIGATION -->

<div class="navbar">

    <h1>
        MINIMON
    </h1>

    <div class="nav-links">

        <a href="{{ route('dashboard') }}">
            Dashboard
        </a>

        <a href="{{ route('hosts.index') }}">
            Hosts
        </a>

        <a href="{{ route('devices.index') }}">
            Devices
        </a>

        <a href="{{ route('hosts.create') }}">
            Register Host
        </a>

        <a href="{{ route('devices.create') }}">
            Register Device
        </a>

    </div>

</div>


<!-- MAIN -->

<div class="container">

    <h2>
        System Health, Network Devices & Alert Overview
    </h2>

    <p class="subtitle">
        Real-time infrastructure monitoring dashboard
    </p>


    <!-- SUMMARY CARDS -->

    <div class="cards">


        <!-- HOSTS ONLINE -->

        <div class="card">

            <div class="card-title">
                Hosts Online
            </div>

            <div class="number online">
                {{ $hostsOnline }}
            </div>

        </div>


        <!-- HOSTS OFFLINE -->

        <div class="card">

            <div class="card-title">
                Hosts Offline
            </div>

            <div class="number offline">
                {{ $hostsOffline }}
            </div>

        </div>


        <!-- SWITCHES -->

        <div class="card">

            <div class="card-title">
                Switches
            </div>

            <div class="number">
                {{ $switches }}
            </div>

        </div>


        <!-- PORTS UP -->

        <div class="card">

            <div class="card-title">
                Ports Up
            </div>

            <div class="number online">
                {{ $portsUp }}
            </div>

        </div>


        <!-- PORTS DOWN -->

        <div class="card">

            <div class="card-title">
                Ports Down
            </div>

            <div class="number offline">
                {{ $portsDown }}
            </div>

        </div>


        <!-- ALERTS -->

        <div class="card">

            <div class="card-title">
                Active Alerts
            </div>

            <div class="number critical">
                {{ $activeAlerts->count() }}
            </div>

        </div>


    </div>


    <!-- HOST UTILIZATION -->

    <div class="section">

        <h2>
            HOST UTILIZATION
        </h2>

        <table>

            <thead>

            <tr>

                <th>
                    Host
                </th>

                <th>
                    Status
                </th>

                <th>
                    Last Seen
                </th>

                <th>
                    CPU %
                </th>

                <th>
                    Memory %
                </th>

                <th>
                    Disk %
                </th>

                <th>
                    Action
                </th>

            </tr>

            </thead>


            <tbody>

            @forelse($hosts as $host)

                <tr>

                    <td>
                        {{ $host->name }}
                    </td>


                    <td>

                        @if($host->status === 'online')

                            <span class="status-dot status-online"></span>

                            <span class="online">
                                Online
                            </span>

                        @else

                            <span class="status-dot status-offline"></span>

                            <span class="offline">
                                {{ ucfirst(
                                    $host->status ?? 'unknown'
                                ) }}
                            </span>

                        @endif

                    </td>


                    <td>

                        {{ $host->last_seen_at
                            ? $host->last_seen_at->diffForHumans()
                            : 'Never' }}

                    </td>


                    <td>

                        @if($host->cpu_percent !== null)

                            {{ number_format(
                                $host->cpu_percent,
                                1
                            ) }}%

                        @else

                            -

                        @endif

                    </td>


                    <td>

                        @if($host->memory_percent !== null)

                            {{ number_format(
                                $host->memory_percent,
                                1
                            ) }}%

                        @else

                            -

                        @endif

                    </td>


                    <td>

                        @if($host->disk_percent !== null)

                            {{ number_format(
                                $host->disk_percent,
                                1
                            ) }}%

                        @else

                            -

                        @endif

                    </td>


                    <td>

                        <a
                            class="button"
                            href="{{ route(
                                'hosts.show',
                                $host
                            ) }}"
                        >
                            View
                        </a>

                    </td>

                </tr>


            @empty

                <tr>

                    <td
                        colspan="7"
                        class="empty"
                    >
                        No hosts registered yet.
                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>


    <!-- NETWORK DEVICES -->

    <div class="section">

        <h2>
            NETWORK DEVICES
        </h2>


        <table>

            <thead>

            <tr>

                <th>
                    Device
                </th>

                <th>
                    Type
                </th>

                <th>
                    IP Address
                </th>

                <th>
                    Status
                </th>

                <th>
                    Ports
                </th>

                <th>
                    Last Checked
                </th>

                <th>
                    Action
                </th>

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

                        @if($device->status === 'online')

                            <span class="status-dot status-online"></span>

                            <span class="online">
                                Online
                            </span>

                        @else

                            <span class="status-dot status-offline"></span>

                            <span class="offline">
                                {{ ucfirst(
                                    $device->status ?? 'unknown'
                                ) }}
                            </span>

                        @endif

                    </td>


                    <td>
                        {{ $device->ports()->count() }}
                    </td>


                    <td>

                        {{ $device->last_checked_at
                            ? $device->last_checked_at->diffForHumans()
                            : 'Never' }}

                    </td>


                    <td>

                        <a
                            class="button"
                            href="{{ route(
                                'devices.show',
                                $device
                            ) }}"
                        >
                            View
                        </a>

                    </td>

                </tr>


            @empty

                <tr>

                    <td
                        colspan="7"
                        class="empty"
                    >
                        No network devices registered yet.
                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>


    <!-- ALERTS -->

    <div class="section">

        <h2>
            RECENT ALERTS
        </h2>


        <table>

            <thead>

            <tr>

                <th>
                    Time
                </th>

                <th>
                    Source
                </th>

                <th>
                    Metric
                </th>

                <th>
                    Value
                </th>

                <th>
                    Severity
                </th>

                <th>
                    Message
                </th>

            </tr>

            </thead>


            <tbody>

            @forelse($activeAlerts as $alert)

                <tr>

                    <td>

                        {{ $alert->triggered_at
                            ? $alert->triggered_at->diffForHumans()
                            : '-' }}

                    </td>


                    <td>

                        {{ $alert->alertable_type
                            ? class_basename(
                                $alert->alertable_type
                            )
                            : 'System' }}

                        #{{ $alert->alertable_id }}

                    </td>


                    <td>

                        {{ strtoupper(
                            $alert->metric
                        ) }}

                    </td>


                    <td>

                        {{ $alert->value }}%

                    </td>


                    <td
                        class="{{ $alert->severity }}"
                    >

                        {{ strtoupper(
                            $alert->severity
                        ) }}

                    </td>


                    <td>

                        {{ $alert->message }}

                    </td>

                </tr>


            @empty

                <tr>

                    <td
                        colspan="6"
                        class="empty"
                    >
                        No active alerts.
                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>


    <!-- NETWORK HEALTH -->

    <div class="section">

        <h2>
            NETWORK HEALTH
        </h2>


        <div class="charts">


            <!-- PORT STATUS -->

            <div class="card">

                <strong>
                    Port Status
                </strong>


                <div class="chart">

                    @if($portsUp > 0)

                        @for(
                            $i = 0;
                            $i < min(20, $portsUp);
                            $i++
                        )

                            <div
                                class="bar"
                                style="height: 80%;"
                            ></div>

                        @endfor

                    @else

                        <span class="empty">
                            No active ports
                        </span>

                    @endif

                </div>


                <p>

                    <strong class="online">
                        Up:
                    </strong>

                    {{ $portsUp }}

                    &nbsp;&nbsp;

                    <strong class="offline">
                        Down:
                    </strong>

                    {{ $portsDown }}

                </p>

            </div>


            <!-- CPU -->

            <div class="card">

                <strong>
                    CPU History
                </strong>


                <div class="chart">

                    @php

                        $cpuMetrics = $recentMetrics
                            ->where('metric', 'cpu')
                            ->take(20)
                            ->reverse();

                    @endphp


                    @forelse(
                        $cpuMetrics
                        as $metric
                    )

                        <div
                            class="bar"
                            style="
                                height:
                                {{ min(
                                    100,
                                    max(
                                        3,
                                        $metric->value
                                    )
                                ) }}%;
                            "
                            title="{{ $metric->value }}%"
                        ></div>

                    @empty

                        <span class="empty">
                            No CPU data
                        </span>

                    @endforelse

                </div>

            </div>


            <!-- MEMORY -->

            <div class="card">

                <strong>
                    Memory History
                </strong>


                <div class="chart">

                    @php

                        $memoryMetrics = $recentMetrics
                            ->where('metric', 'memory')
                            ->take(20)
                            ->reverse();

                    @endphp


                    @forelse(
                        $memoryMetrics
                        as $metric
                    )

                        <div
                            class="bar"
                            style="
                                height:
                                {{ min(
                                    100,
                                    max(
                                        3,
                                        $metric->value
                                    )
                                ) }}%;
                            "
                            title="{{ $metric->value }}%"
                        ></div>

                    @empty

                        <span class="empty">
                            No memory data
                        </span>

                    @endforelse

                </div>

            </div>


        </div>

    </div>


    <!-- FOOTER -->

    <div class="footer">

        MiniMon Network & Infrastructure Monitoring System

    </div>


</div>


</body>

</html>