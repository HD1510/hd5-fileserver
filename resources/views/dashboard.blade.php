<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <p class="text-sm text-gray-500">Files</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['file_count'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <p class="text-sm text-gray-500">Folders</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['folder_count'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <p class="text-sm text-gray-500">Active Shares</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['share_count'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <p class="text-sm text-gray-500">Storage Used</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['storage_used_formatted'] }}</p>
            <div class="mt-2 bg-gray-200 rounded-full h-1.5">
                <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $stats['storage_percent'] }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">of {{ $stats['storage_quota_formatted'] }} ({{ $stats['storage_percent'] }}%)</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Recent Files</h3>
            <a href="{{ route('files.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Browse all →</a>
        </div>

        @if($recentFiles->isEmpty())
            <p class="text-gray-500 text-sm">No files yet. <a href="{{ route('files.index') }}" class="text-blue-600 hover:underline">Upload your first file.</a></p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-medium">Name</th>
                        <th class="pb-2 font-medium">Size</th>
                        <th class="pb-2 font-medium">Uploaded</th>
                        <th class="pb-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentFiles as $file)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2">
                                <div class="flex items-center gap-2">
                                    <x-file-icon :file="$file" class="w-4 h-4 text-gray-400" />
                                    {{ $file->name }}
                                </div>
                            </td>
                            <td class="py-2 text-gray-500">{{ $file->sizeFormatted() }}</td>
                            <td class="py-2 text-gray-500">{{ $file->created_at->diffForHumans() }}</td>
                            <td class="py-2 text-right">
                                <a href="{{ route('files.download', $file) }}" class="text-blue-600 hover:text-blue-800">Download</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
