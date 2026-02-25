<div
    x-data="{
        isOpen: false,
        url: '',
        name: '',
        mime: '',
        size: '',
        textContent: '',
        isImage()  { return this.mime.startsWith('image/'); },
        isVideo()  { return this.mime.startsWith('video/'); },
        isAudio()  { return this.mime.startsWith('audio/'); },
        isPdf()    { return this.mime === 'application/pdf'; },
        isText()   { return this.mime.startsWith('text/'); },
        open(detail) {
            this.url  = detail.url;
            this.name = detail.name;
            this.mime = detail.mime;
            this.size = detail.size;
            this.textContent = '';
            this.isOpen = true;
            if (this.isText()) {
                fetch(this.url)
                    .then(r => r.text())
                    .then(t => { this.textContent = t; })
                    .catch(() => { this.textContent = 'Could not load file.'; });
            }
        },
        close() { this.isOpen = false; this.url = ''; }
    }"
    @open-preview.window="open($event.detail)"
    @keydown.escape.window="close()"
>
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
        @click.self="close()"
        style="display: none;"
    >
        <div class="bg-white rounded-2xl shadow-2xl flex flex-col w-full max-w-4xl max-h-[90vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <p class="font-medium text-gray-800 truncate" x-text="name"></p>
                    <span class="text-xs text-gray-400" x-text="size"></span>
                </div>
                <button @click="close()" class="text-gray-400 hover:text-gray-600 shrink-0 ml-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-auto flex items-center justify-center bg-gray-50 rounded-b-2xl min-h-0">

                {{-- Image --}}
                <template x-if="isImage()">
                    <img :src="url" :alt="name" class="max-w-full max-h-full object-contain p-4">
                </template>

                {{-- Video --}}
                <template x-if="isVideo()">
                    <video :src="url" controls class="max-w-full max-h-full rounded-b-2xl">
                        Your browser does not support video playback.
                    </video>
                </template>

                {{-- Audio --}}
                <template x-if="isAudio()">
                    <div class="p-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-gray-300 mx-auto mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                        </svg>
                        <audio :src="url" controls class="w-full max-w-sm">
                            Your browser does not support audio playback.
                        </audio>
                    </div>
                </template>

                {{-- PDF --}}
                <template x-if="isPdf()">
                    <iframe :src="url" class="w-full rounded-b-2xl" style="height: 75vh;"></iframe>
                </template>

                {{-- Text / Code --}}
                <template x-if="isText()">
                    <pre x-text="textContent || 'Loading...'" class="w-full h-full overflow-auto p-5 text-sm text-gray-700 font-mono whitespace-pre-wrap bg-gray-50 rounded-b-2xl"></pre>
                </template>

                {{-- Unsupported --}}
                <template x-if="!isImage() && !isVideo() && !isAudio() && !isPdf() && !isText()">
                    <div class="p-10 text-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-3 text-gray-300">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <p class="font-medium">No preview available</p>
                        <p class="text-sm mt-1" x-text="mime"></p>
                        <a :href="url.replace('/preview', '/download')" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Download</a>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
