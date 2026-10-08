@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Reports</h1>
        <a href="{{ route('reports.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Generate Report
        </a>
    </div>

    @if ($reports->count())
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Type</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Period</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $report->name }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm">
                                    {{ ucfirst($report->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $report->period_start->format('M d, Y') }} - {{ $report->period_end->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded text-sm">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('reports.show', $report) }}" class="text-blue-600 hover:underline text-sm mr-3">
                                    View
                                </a>
                                <a href="{{ route('reports.download', $report) }}" class="text-green-600 hover:underline text-sm mr-3">
                                    Download
                                </a>
                                <form method="POST" action="{{ route('reports.destroy', $report) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline text-sm" onclick="return confirm('Delete this report?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $reports->links() }}
        </div>
    @else
        <div class="bg-gray-100 text-gray-600 p-8 rounded text-center">
            <p>No reports yet. <a href="{{ route('reports.create') }}" class="text-blue-600 hover:underline">Generate one now</a>.</p>
        </div>
    @endif
</div>
@endsection
