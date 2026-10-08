<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Host;
use App\Models\Metric;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    public function index(Request $request)
    {
        $query = Metric::query()->latest('recorded_at');

        if ($request->filled('host_id')) {
            $query->where('host_id', $request->integer('host_id'));
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->integer('device_id'));
        }

        if ($request->filled('metric')) {
            $query->where('metric', $request->string('metric'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(100)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'host_id' => 'nullable|exists:hosts,id',
            'device_id' => 'nullable|exists:devices,id',
            'metric' => 'required|string|max:255',
            'value' => 'required|numeric',
            'unit' => 'nullable|string|max:50',
            'recorded_at' => 'nullable|date',
        ]);

        if (empty($validated['host_id']) && empty($validated['device_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Either host_id or device_id is required.',
            ], 422);
        }

        $metric = Metric::create([
            'host_id' => $validated['host_id'] ?? null,
            'device_id' => $validated['device_id'] ?? null,
            'metric' => $validated['metric'],
            'value' => $validated['value'],
            'unit' => $validated['unit'] ?? null,
            'recorded_at' => $validated['recorded_at'] ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metric recorded successfully.',
            'data' => $metric,
        ], 201);
    }

    public function hostMetrics(Host $host)
    {
        return response()->json([
            'success' => true,
            'host_id' => $host->id,
            'data' => $host->metrics()->latest('recorded_at')->limit(100)->get(),
        ]);
    }

    public function deviceMetrics(Device $device)
    {
        return response()->json([
            'success' => true,
            'device_id' => $device->id,
            'data' => $device->metrics()->latest('recorded_at')->limit(100)->get(),
        ]);
    }
}
