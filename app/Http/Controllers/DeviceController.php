<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\MonitoringService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::latest()->get();

        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(
        Request $request,
        MonitoringService $monitoring
    ) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'nullable|string|max:255',
            'type' => 'required|string|max:100',
            'floor' => 'nullable|string|max:100',
            'ip_address' => 'required|ip',
            'vendor' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'operating_system' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'snmp_version' => 'nullable|string|max:20',
            'snmp_community' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['status'] = $validated['status'] ?? 'unknown';
        $validated['snmp_port'] = $validated['snmp_port'] ?? 161;
        $validated['snmp_version'] = $validated['snmp_version'] ?? '2c';

        $device = Device::create($validated);

        try {
            $result = $monitoring->collect($device);

            return redirect()
                ->route('devices.show', $device)
                ->with(
                    'success',
                    'Device registered and monitored successfully. '
                    . $result['interfaces']
                    . ' interfaces discovered.'
                );
        } catch (\Throwable $e) {
            $device->update([
                'status' => 'offline',
                'last_checked_at' => now(),
            ]);

            return redirect()
                ->route('devices.show', $device)
                ->with(
                    'error',
                    'Device registered, but SNMP discovery failed: '
                    . $e->getMessage()
                );
        }
    }

    public function show(Device $device)
    {
        $device->load('ports');

        return view('devices.show', compact('device'));
    }

    public function edit(Device $device)
    {
        return view('devices.edit', compact('device'));
    }

    public function update(
        Request $request,
        Device $device
    ) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'nullable|string|max:255',
            'type' => 'required|string|max:100',
            'floor' => 'nullable|string|max:100',
            'ip_address' => 'required|ip',
            'vendor' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'operating_system' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'snmp_version' => 'nullable|string|max:20',
            'snmp_community' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $device->update($validated);

        return redirect()
            ->route('devices.show', $device)
            ->with(
                'success',
                'Device updated successfully.'
            );
    }

    public function destroy(Device $device)
    {
        $device->ports()->delete();

        $device->delete();

        return redirect()
            ->route('devices.index')
            ->with(
                'success',
                'Device deleted successfully.'
            );
    }

    public function checkSnmp(
        Device $device,
        MonitoringService $monitoring
    ) {
        try {
            $result = $monitoring->collect($device);

            return redirect()
                ->route('devices.show', $device)
                ->with(
                    'success',
                    'Monitoring check successful. '
                    . $result['interfaces']
                    . ' interfaces discovered.'
                );
        } catch (\Throwable $e) {
            $device->update([
                'status' => 'offline',
                'last_checked_at' => now(),
            ]);

            return redirect()
                ->route('devices.show', $device)
                ->with(
                    'error',
                    'Monitoring check failed: '
                    . $e->getMessage()
                );
        }
    }
}