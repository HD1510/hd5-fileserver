@props(['fileId' => null, 'folderId' => null, 'modalId' => 'share-modal'])

<div x-data="{ open: false }" id="{{ $modalId }}">
    <button @click="open = true" type="button"
        class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.935-2.186 2.25 2.25 0 0 0-3.935 2.186Z" />
        </svg>
        Share
    </button>

    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="open = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md" @click.stop>
            <h3 class="text-lg font-semibold mb-4">Create Share Link</h3>
            <form method="POST" action="{{ route('shares.store') }}">
                @csrf
                @if($fileId)
                    <input type="hidden" name="file_id" value="{{ $fileId }}">
                @endif
                @if($folderId)
                    <input type="hidden" name="folder_id" value="{{ $folderId }}">
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Label (optional)</label>
                        <input type="text" name="label" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. For client">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password (optional)</label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expires at (optional)</label>
                        <input type="datetime-local" name="expires_at" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max downloads (optional)</label>
                        <input type="number" name="max_downloads" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
