<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\Host;
use App\Models\Metric;
use App\Models\Port;

class DashboardController extends Controller
{
    public function index()
    {
        $hosts = Host::latest()->get();

        $devices = Device::latest()->get();

        $hostsOnline = Host::where(
            'status',
            'online'
        )->count();

        $hostsOffline = Host::where(
            'status',
            '!=',
            'online'
        )->count();

        $switches = Device::where(
            'type',
            'Switch'
        )->count();

        $portsUp = Port::where(
            'status',
            'up'
        )->count();

        $portsDown = Port::where(
            'status',
            'down'
        )->count();

        $activeAlerts = Alert::whereNull(
            'resolved_at'
        )
            ->latest('triggered_at')
            ->limit(10)
            ->get();

        $recentMetrics = Metric::latest(
            'recorded_at'
        )
            ->limit(100)
            ->get();

        return view(
            'dashboard',
            compact(
                'hosts',
                'devices',
                'hostsOnline',
                'hostsOffline',
                'switches',
                'portsUp',
                'portsDown',
                'activeAlerts',
                'recentMetrics'
            )
        );
    }
}