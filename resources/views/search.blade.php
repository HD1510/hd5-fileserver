<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Search results for <span class="text-blue-600">{{ $query }}</span>
            </h2>
        </div>
    </x-slot>

    @if($query === '')
        <div class="text-center py-16 text-gray-400">
            <p class="text-lg font-medium">Enter a search term above.</p>
        </div>
    @elseif($files->isEmpty() && $folders->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-4 text-gray-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z" />
            </svg>
            <p class="text-lg font-medium">No results for "{{ $query }}"</p>
        </div>
    @else
        @if($folders->isNotEmpty())
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Folders ({{ $folders->count() }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 mb-6">
                @foreach($folders as $folder)
                    <a href="{{ route('folders.show', $folder) }}"
                        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-shadow block">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-yellow-400 mx-auto mb-2">
                            <path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 12h-15a4.483 4.483 0 0 0-3 1.146Z" />
                        </svg>
                        <p class="text-sm text-gray-700 text-center truncate">{{ $folder->name }}</p>
                        <p class="text-xs text-gray-400 text-center truncate">{{ $folder->path }}</p>
                    </a>
                @endforeach
            </div>
        @endif

        @if($files->isNotEmpty())
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Files ({{ $files->count() }})</h3>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-gray-500">
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium hidden sm:table-cell">Folder</th>
                            <th class="px-4 py-3 font-medium hidden sm:table-cell">Size</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($files as $file)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <x-file-icon :file="$file" class="w-5 h-5 text-gray-400 shrink-0" />
                                        <button type="button" class="truncate max-w-xs text-left hover:text-blue-600"
                                            onclick="window.dispatchEvent(new CustomEvent('open-detail', {detail: {id: {{ $file->id }}, name: '{{ addslashes($file->name) }}', size: '{{ $file->sizeFormatted() }}', mime: '{{ $file->mime_type }}', date: '{{ $file->created_at->format('Y-m-d H:i') }}', folder: '{{ addslashes($file->folder?->name ?? 'Root') }}', previewUrl: '{{ route('files.preview', $file) }}', downloadUrl: '{{ route('files.download', $file) }}'}}))">
                                            {{ $file->name }}
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">
                                    @if($file->folder)
                                        <a href="{{ route('folders.show', $file->folder) }}" class="hover:text-blue-600">{{ $file->folder->name }}</a>
                                    @else
                                        <a href="{{ route('files.index') }}" class="hover:text-blue-600">Root</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $file->sizeFormatted() }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('files.download', $file) }}" class="text-blue-600 hover:text-blue-800 text-xs">Download</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</x-app-layout>
