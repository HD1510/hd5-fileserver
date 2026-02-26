<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HD5 Fileserver') }} – @yield('title', 'Files')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Storage Bar -->
            @auth
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
                    <x-storage-bar />
                </div>
            @endauth

            <!-- Page Content -->
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                {{ $slot }}
            </main>
        </div>
        <x-preview-modal />

        {{-- File Details Panel --}}
        <div x-data="fileDetailPanel()" @open-detail.window="open($event.detail)" x-cloak>
            {{-- Backdrop --}}
            <div x-show="visible" x-transition:enter="transition-opacity duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/20 z-40"
                @click="visible = false"></div>

            {{-- Panel --}}
            <div x-show="visible"
                x-transition:enter="transition transform duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition transform duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl z-50 flex flex-col">

                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 text-sm">File Details</h3>
                    <button @click="visible = false" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Preview --}}
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-center bg-gray-50 min-h-32">
                    <template x-if="file.mime && file.mime.startsWith('image/')">
                        <img :src="file.previewUrl" :alt="file.name" class="max-h-40 max-w-full rounded-lg object-contain shadow">
                    </template>
                    <template x-if="!file.mime || !file.mime.startsWith('image/')">
                        <div class="text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                    </template>
                </div>

                {{-- Metadata --}}
                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Name</p>
                        <p class="text-sm font-medium text-gray-800 break-all" x-text="file.name"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Size</p>
                        <p class="text-sm text-gray-700" x-text="file.size"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Type</p>
                        <p class="text-sm text-gray-700" x-text="file.mime || '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Uploaded</p>
                        <p class="text-sm text-gray-700" x-text="file.date"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Folder</p>
                        <p class="text-sm text-gray-700" x-text="file.folder"></p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
                    <a :href="file.downloadUrl" class="flex-1 text-center px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                        Download
                    </a>
                    <a :href="file.previewUrl" target="_blank" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                        Preview
                    </a>
                </div>
            </div>
        </div>

        <script>
        function fileDetailPanel() {
            return {
                visible: false,
                file: {},
                open(detail) {
                    this.file = detail;
                    this.visible = true;
                },
            };
        }
        </script>
    </body>
</html>
