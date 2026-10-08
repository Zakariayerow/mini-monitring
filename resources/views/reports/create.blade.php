@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Generate Report</h1>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form method="POST" action="{{ route('reports.store') }}">
                @csrf

                <div class="mb-6">
                    <label for="type" class="block text-sm font-semibold mb-2">Report Type *</label>
                    <select name="type" id="type" class="w-full border rounded px-3 py-2" required onchange="updateReportOptions()">
                        <option value="">Select a report type</option>
                        <option value="summary">Infrastructure Summary</option>
                        <option value="host">Host Report</option>
                        <option value="device">Device Report</option>
                    </select>
                    @error('type')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div id="host-section" style="display:none;" class="mb-6">
                    <label for="host_id" class="block text-sm font-semibold mb-2">Select Host *</label>
                    <select name="host_id" id="host_id" class="w-full border rounded px-3 py-2">
                        <option value="">Choose a host</option>
                        @foreach ($hosts as $host)
                            <option value="{{ $host->id }}">{{ $host->name }}</option>
                        @endforeach
                    </select>
                    @error('host_id')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div id="device-section" style="display:none;" class="mb-6">
                    <label for="device_id" class="block text-sm font-semibold mb-2">Select Device *</label>
                    <select name="device_id" id="device_id" class="w-full border rounded px-3 py-2">
                        <option value="">Choose a device</option>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->name }}</option>
                        @endforeach
                    </select>
                    @error('device_id')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="period_days" class="block text-sm font-semibold mb-2">Report Period (days) *</label>
                    <input type="number" name="period_days" id="period_days" value="30" min="1" max="365" class="w-full border rounded px-3 py-2" required>
                    <p class="text-gray-600 text-sm mt-1">Number of days to include in the report (1-365)</p>
                    @error('period_days')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Generate Report
                    </button>
                    <a href="{{ route('reports.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateReportOptions() {
    const type = document.getElementById('type').value;
    document.getElementById('host-section').style.display = type === 'host' ? 'block' : 'none';
    document.getElementById('device-section').style.display = type === 'device' ? 'block' : 'none';

    if (type === 'host') document.getElementById('host_id').required = true;
    else document.getElementById('host_id').required = false;

    if (type === 'device') document.getElementById('device_id').required = true;
    else document.getElementById('device_id').required = false;
}
</script>
@endsection
