@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">{{ $report->name }}</h1>
            <p class="text-gray-600 mt-2">
                Type: <span class="font-semibold">{{ ucfirst($report->type) }}</span> | 
                Status: <span class="font-semibold">{{ ucfirst($report->status) }}</span>
            </p>
            <p class="text-gray-600">
                Period: {{ $report->period_start->format('M d, Y H:i') }} - {{ $report->period_end->format('M d, Y H:i') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('reports.download', $report) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Download CSV
            </a>
            <form method="POST" action="{{ route('reports.destroy', $report) }}" style="display:inline;">
                @csrf
                @method('DELETE')
                <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" onclick="return confirm('Delete this report?')">
                    Delete
                </button>
            </form>
        </div>
    </div>

    @if ($report->metrics)
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-4">Report Data</h2>

            @if ($report->type === 'summary')
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 border-l-4 border-blue-600 p-4">
                        <p class="text-gray-600 text-sm font-semibold">Total Hosts</p>
                        <p class="text-3xl font-bold">{{ $report->metrics['hosts']['total'] }}</p>
                    </div>
                    <div class="bg-green-50 border-l-4 border-green-600 p-4">
                        <p class="text-gray-600 text-sm font-semibold">Online Hosts</p>
                        <p class="text-3xl font-bold">{{ $report->metrics['hosts']['online'] }}</p>
                    </div>
                    <div class="bg-red-50 border-l-4 border-red-600 p-4">
                        <p class="text-gray-600 text-sm font-semibold">Offline Hosts</p>
                        <p class="text-3xl font-bold">{{ $report->metrics['hosts']['offline'] }}</p>
                    </div>
                    <div class="bg-purple-50 border-l-4 border-purple-600 p-4">
                        <p class="text-gray-600 text-sm font-semibold">Total Devices</p>
                        <p class="text-3xl font-bold">{{ $report->metrics['devices']['total'] }}</p>
                    </div>
                </div>
            @elseif ($report->type === 'host')
                <table class="w-full">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Metric</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold">Count</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold">Average</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold">Minimum</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold">Maximum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report->metrics as $metric => $data)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold">{{ $metric }}</td>
                                <td class="px-6 py-4 text-right">{{ $data['count'] }}</td>
                                <td class="px-6 py-4 text-right">{{ number_format($data['avg'], 2) }}</td>
                                <td class="px-6 py-4 text-right">{{ number_format($data['min'], 2) }}</td>
                                <td class="px-6 py-4 text-right">{{ number_format($data['max'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif ($report->type === 'device')
                <table class="w-full">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Port</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold">Alerts</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Last Checked</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report->metrics as $port => $data)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold">{{ $port }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-block px-3 py-1 rounded text-sm {{ $data['status'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($data['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">{{ $data['alerts'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $data['last_checked'] ?? 'Never' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @else
        <div class="bg-gray-100 text-gray-600 p-8 rounded text-center">
            <p>No data available for this report.</p>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('reports.index') }}" class="text-blue-600 hover:underline">← Back to Reports</a>
    </div>
</div>
@endsection
