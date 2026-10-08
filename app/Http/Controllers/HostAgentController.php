<?php

namespace App\Http\Controllers;

use App\Models\Host;
use App\Services\ServerMonitor;
use Illuminate\Http\Request;

class HostAgentController extends Controller
{
    public function report(
        Request $request,
        ServerMonitor $monitor
    ) {
        $validated = $this->validatePayload($request);

        $host = $this->resolveHostFromRequest($request, $validated);

        if (!$host) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid agent key.',
            ], 401);
        }

        $host = $this->applyHostMetadata($host, $validated);
        $host = $monitor->updateHost(
            $host,
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Host metrics received.',
            'host' => [
                'id' => $host->id,
                'name' => $host->name,
                'hostname' => $host->hostname,
                'status' => $host->status,
                'cpu_percent' => $host->cpu_percent,
                'memory_percent' => $host->memory_percent,
                'disk_percent' => $host->disk_percent,
                'last_seen_at' => $host->last_seen_at,
            ],
        ]);
    }

    public function metrics(
        Request $request,
        ServerMonitor $monitor
    ) {
        $validated = $this->validatePayload($request);

        $host = $this->resolveHostFromRequest($request, $validated);

        if (!$host) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid agent key.',
            ], 401);
        }

        $host = $this->applyHostMetadata($host, $validated);
        $host = $monitor->updateHost($host, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Metrics stored successfully.',
            'host' => [
                'id' => $host->id,
                'name' => $host->name,
                'status' => $host->status,
                'last_seen_at' => $host->last_seen_at,
            ],
            'metrics' => [
                'cpu_percent' => $host->cpu_percent,
                'memory_percent' => $host->memory_percent,
                'disk_percent' => $host->disk_percent,
            ],
        ]);
    }

    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'agent_key' => 'sometimes|string',
            'hostname' => 'nullable|string|max:255',
            'operating_system' => 'nullable|string|max:255',
            'architecture' => 'nullable|string|max:100',

            'cpu_percent' => 'nullable|numeric|min:0|max:100',

            'memory_percent' => 'nullable|numeric|min:0|max:100',
            'memory_total' => 'nullable|numeric|min:0',
            'memory_used' => 'nullable|numeric|min:0',

            'disk_percent' => 'nullable|numeric|min:0|max:100',
            'disk_total' => 'nullable|numeric|min:0',
            'disk_used' => 'nullable|numeric|min:0',

            'uptime' => 'nullable|numeric|min:0',
        ]);
    }

    protected function resolveHostFromRequest(
        Request $request,
        array $validated
    ): ?Host {
        $agentKey = $validated['agent_key']
            ?? $request->header('X-API-Key')
            ?? $this->extractBearerToken($request);

        if (empty($agentKey)) {
            return null;
        }

        return Host::where('agent_key', $agentKey)->first();
    }

    protected function applyHostMetadata(
        Host $host,
        array $validated
    ): Host {
        if (!empty($validated['hostname'])) {
            $host->hostname = $validated['hostname'];
        }

        if (!empty($validated['operating_system'])) {
            $host->operating_system = $validated['operating_system'];
        }

        if (!empty($validated['architecture'])) {
            $host->architecture = $validated['architecture'];
        }

        $host->save();

        return $host;
    }

    protected function extractBearerToken(Request $request): ?string
    {
        $authorization = $request->header('Authorization', '');

        if (!str_starts_with(strtolower($authorization), 'bearer ')) {
            return null;
        }

        return trim(substr($authorization, 7));
    }
}