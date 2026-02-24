<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Share Links</h2>
    </x-slot>

    @if($shares->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-4 text-gray-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.935-2.186 2.25 2.25 0 0 0-3.935 2.186Z" />
            </svg>
            <p class="text-lg font-medium">No share links yet</p>
            <p class="text-sm mt-1">Create share links from the file browser.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-4 py-3 font-medium">Item</th>
                        <th class="px-4 py-3 font-medium">Label</th>
                        <th class="px-4 py-3 font-medium hidden md:table-cell">Downloads</th>
                        <th class="px-4 py-3 font-medium hidden md:table-cell">Expires</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($shares as $share)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                @if($share->file)
                                    <span class="text-gray-700">{{ $share->file->name }}</span>
                                @elseif($share->folder)
                                    <span class="text-yellow-600">📁 {{ $share->folder->name }}</span>
                                @else
                                    <span class="text-gray-400 italic">Deleted</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $share->label ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                                {{ $share->download_count }}{{ $share->max_downloads ? ' / ' . $share->max_downloads : '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                                {{ $share->expires_at?->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if(!$share->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Disabled</span>
                                @elseif($share->isExpired())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Expired</span>
                                @elseif($share->isExhausted())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-600">Exhausted</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-600">Active</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ $share->publicUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs">Open</a>
                                    <button onclick="navigator.clipboard.writeText('{{ $share->publicUrl() }}')" class="text-xs text-gray-500 hover:text-gray-700">Copy</button>
                                    <form method="POST" action="{{ route('shares.destroy', $share) }}" onsubmit="return confirm('Delete this share link?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $shares->links() }}</div>
    @endif
</x-app-layout>
