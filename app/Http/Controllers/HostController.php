<?php

namespace App\Http\Controllers;

use App\Models\Host;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hosts = Host::latest()->get();

        return view('hosts.index', compact('hosts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hosts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'operating_system' => 'nullable|string|max:255',
            'architecture' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ]);

        $validated['agent_key'] = $this->generateAgentKey();
        $validated['status'] = $validated['status'] ?? 'unknown';

        $host = Host::create($validated);

        return redirect()
            ->route('hosts.show', $host)
            ->with('success', 'Host registered successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Host $host)
    {
        return view('hosts.show', compact('host'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Host $host)
    {
        return view('hosts.edit', compact('host'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Host $host)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'operating_system' => 'nullable|string|max:255',
            'architecture' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ]);

        $host->update($validated);

        return redirect()
            ->route('hosts.show', $host)
            ->with('success', 'Host updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Host $host)
    {
        $host->delete();

        return redirect()
            ->route('hosts.index')
            ->with('success', 'Host deleted successfully.');
    }

    protected function generateAgentKey(): string
    {
        do {
            $key = 'minimon-' . Str::random(32);
        } while (Host::where('agent_key', $key)->exists());

        return $key;
    }
}
