<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Port;

class PortController extends Controller
{
    public function destroy(Device $device, Port $port)
    {
        if ($port->device_id !== $device->id) {
            abort(404);
        }

        $port->delete();

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Port deleted successfully.');
    }
}