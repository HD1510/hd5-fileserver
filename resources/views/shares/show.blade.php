<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} – Share</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-lg">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ config('app.name') }}</h1>
            <p class="text-gray-500 text-sm mt-1">Shared by someone</p>
        </div>

        @if($share->label)
            <p class="text-center text-gray-600 mb-6">{{ $share->label }}</p>
        @endif

        @if($share->file)
            {{-- Single file share --}}
            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-4 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-400 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ $share->file->name }}</p>
                    <p class="text-xs text-gray-500">{{ $share->file->sizeFormatted() }}</p>
                </div>
            </div>
            <a href="{{ route('shares.download', $share->token) }}"
               class="block w-full text-center bg-blue-600 text-white font-medium py-3 rounded-xl hover:bg-blue-700 transition-colors">
                Download
            </a>
        @elseif($share->folder)
            {{-- Folder share --}}
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-yellow-400">
                        <path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15Z" />
                    </svg>
                    <span class="font-semibold text-gray-800">{{ $share->folder->name }}</span>
                </div>
                <div class="space-y-2">
                    @forelse($share->folder->files as $file)
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                            <span class="text-sm text-gray-700 truncate flex-1">{{ $file->name }}</span>
                            <span class="text-xs text-gray-400 mx-3">{{ $file->sizeFormatted() }}</span>
                            <a href="{{ route('shares.download', [$share->token, $file->id]) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 shrink-0">Download</a>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm text-center py-4">This folder is empty.</p>
                    @endforelse
                </div>
            </div>
        @endif

        @if($share->expires_at)
            <p class="text-center text-xs text-gray-400 mt-4">Expires {{ $share->expires_at->format('Y-m-d H:i') }}</p>
        @endif
        @if($share->max_downloads)
            <p class="text-center text-xs text-gray-400 mt-1">{{ $share->download_count }} / {{ $share->max_downloads }} downloads used</p>
        @endif
    </div>
</body>
</html>
