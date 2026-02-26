<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <x-breadcrumb :crumbs="$breadcrumbs" />
                <h2 class="font-semibold text-xl text-gray-800 mt-1">{{ $folder->name }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('folders.download', $folder) }}"
                    class="px-3 py-1.5 bg-white text-gray-700 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download ZIP
                </a>
                <form method="POST" action="{{ route('folders.store') }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $folder->id }}">
                    <input type="text" name="name" placeholder="New subfolder" required
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-3 py-1.5 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">
                        + Folder
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    {{-- Upload Zone --}}
    <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center mb-6 bg-white hover:border-blue-400 transition-colors"
        ondragover="if(event.dataTransfer.types.includes('Files')){event.preventDefault();this.classList.add('border-blue-500','bg-blue-50')}"
        ondragleave="this.classList.remove('border-blue-500','bg-blue-50')"
        ondrop="handleDrop(event)">
        <input type="file" id="file-input" multiple class="hidden" onchange="uploadFiles(this.files)">
        <input type="file" id="folder-input" webkitdirectory multiple class="hidden" onchange="uploadFolder(this.files)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-gray-400 mx-auto mb-3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
        </svg>
        <p class="text-gray-600 font-medium mb-3">Drop files here or choose an option below</p>
        <div class="flex items-center justify-center gap-3">
            <button type="button" onclick="document.getElementById('file-input').click()"
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                Upload Files
            </button>
            <button type="button" onclick="document.getElementById('folder-input').click()"
                class="px-4 py-2 bg-white text-gray-700 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">
                Upload Folder
            </button>
        </div>
        <p class="text-gray-400 text-xs mt-3">Max 100 MB per file</p>
        <div id="upload-status" class="hidden mt-4 px-2">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span id="upload-label">Uploading...</span>
                <span id="upload-percent">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="upload-bar" class="bg-blue-500 h-2 rounded-full transition-all duration-200" style="width: 0%"></div>
            </div>
        </div>
    </div>

    {{-- Sub-Folders --}}
    @if($folders->isNotEmpty())
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Folders</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 mb-6">
            @foreach($folders as $subfolder)
                <div class="group bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-shadow"
                    draggable="true"
                    ondragstart="folderItemDragStart(event, {{ $subfolder->id }})"
                    ondragend="folderItemDragEnd(event)"
                    ondragover="folderDragOver(event)"
                    ondragleave="folderDragLeave(event)"
                    ondrop="anyDrop(event, {{ $subfolder->id }})">
                    <a href="{{ route('folders.show', $subfolder) }}" class="block">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-yellow-400 mx-auto mb-2">
                            <path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 12h-15a4.483 4.483 0 0 0-3 1.146Z" />
                        </svg>
                        <p class="text-sm text-gray-700 text-center truncate">{{ $subfolder->name }}</p>
                    </a>
                    <div class="flex justify-center gap-2 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <form method="POST" action="{{ route('folders.update', $subfolder) }}" class="inline" onsubmit="return promptRename(event, '{{ addslashes($subfolder->name) }}')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="name" class="rename-input">
                            <button type="submit" class="text-xs text-gray-500 hover:text-blue-600">Rename</button>
                        </form>
                        <span class="text-gray-300">|</span>
                        <x-share-modal :folderId="$subfolder->id" :modalId="'share-folder-'.$subfolder->id" />
                        <span class="text-gray-300">|</span>
                        <form method="POST" action="{{ route('folders.destroy', $subfolder) }}" onsubmit="return confirm('Delete folder and all contents?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Files --}}
    @if($files->isNotEmpty())
        @php
            $baseUrl = route('folders.show', $folder);
            $flipDir = $sortDir === 'asc' ? 'desc' : 'asc';
            $sortArrow = fn($col) => $sortCol === $col ? ($sortDir === 'asc' ? ' ↑' : ' ↓') : '';
        @endphp
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Files</h3>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden" x-data="bulkSelect()">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-4 py-3 w-8">
                            <input type="checkbox" class="rounded" @change="toggleAll($event.target.checked)" x-bind:checked="allSelected">
                        </th>
                        <th class="px-4 py-3 font-medium">
                            <a href="{{ $baseUrl }}?sort=name&dir={{ $sortCol === 'name' ? $flipDir : 'asc' }}" class="hover:text-gray-700">Name{{ $sortArrow('name') }}</a>
                        </th>
                        <th class="px-4 py-3 font-medium hidden sm:table-cell">
                            <a href="{{ $baseUrl }}?sort=size&dir={{ $sortCol === 'size' ? $flipDir : 'asc' }}" class="hover:text-gray-700">Size{{ $sortArrow('size') }}</a>
                        </th>
                        <th class="px-4 py-3 font-medium hidden md:table-cell">
                            <a href="{{ $baseUrl }}?sort=created_at&dir={{ $sortCol === 'created_at' ? $flipDir : 'desc' }}" class="hover:text-gray-700">Uploaded{{ $sortArrow('created_at') }}</a>
                        </th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($files as $file)
                        <tr class="hover:bg-gray-50 cursor-grab" draggable="true"
                            ondragstart="fileDragStart(event, {{ $file->id }})"
                            ondragend="this.classList.remove('opacity-40')"
                            :class="selected.includes({{ $file->id }}) ? 'bg-blue-50' : ''">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="rounded" value="{{ $file->id }}"
                                    @change="toggle({{ $file->id }})"
                                    :checked="selected.includes({{ $file->id }})">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <x-file-icon :file="$file" class="w-5 h-5 text-gray-400 shrink-0" />
                                    <button type="button" class="truncate max-w-xs text-left hover:text-blue-600"
                                        @click="window.dispatchEvent(new CustomEvent('open-detail', {detail: {id: {{ $file->id }}, name: '{{ addslashes($file->name) }}', size: '{{ $file->sizeFormatted() }}', mime: '{{ $file->mime_type }}', date: '{{ $file->created_at->format('Y-m-d H:i') }}', folder: '{{ addslashes($folder->name) }}', previewUrl: '{{ route('files.preview', $file) }}', downloadUrl: '{{ route('files.download', $file) }}'}}))">
                                        {{ $file->name }}
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $file->sizeFormatted() }}</td>
                            <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $file->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button type="button" class="text-xs text-gray-500 hover:text-purple-600"
                                        onclick="window.dispatchEvent(new CustomEvent('open-preview', {detail: {url: '{{ route('files.preview', $file) }}', name: '{{ addslashes($file->name) }}', mime: '{{ $file->mime_type }}', size: '{{ $file->sizeFormatted() }}'}}))">Preview</button>
                                    <a href="{{ route('files.download', $file) }}" class="text-blue-600 hover:text-blue-800 text-xs">Download</a>
                                    <x-share-modal :fileId="$file->id" :modalId="'share-file-'.$file->id" />
                                    <form method="POST" action="{{ route('files.update', $file) }}" class="inline" onsubmit="return promptRename(event, '{{ addslashes($file->name) }}')">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="name" class="rename-input">
                                        <button type="submit" class="text-xs text-gray-500 hover:text-blue-600">Rename</button>
                                    </form>
                                    <form method="POST" action="{{ route('files.destroy', $file) }}" onsubmit="return confirm('Delete this file?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Bulk Action Bar --}}
            <div x-show="selected.length > 0" x-cloak
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-gray-900 text-white rounded-xl shadow-2xl px-5 py-3 flex items-center gap-4 text-sm">
                <span x-text="selected.length + ' file(s) selected'"></span>
                <button type="button" @click="bulkDelete('{{ route('files.bulk-delete') }}')"
                    class="bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg text-xs font-medium">
                    Delete
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                        class="bg-gray-700 hover:bg-gray-600 px-3 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1">
                        Move to… <span>▾</span>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak
                        class="absolute bottom-full mb-2 left-0 bg-white text-gray-800 rounded-xl border border-gray-200 shadow-xl min-w-40 py-1 max-h-48 overflow-y-auto">
                        <button type="button" @click="bulkMove('{{ route('files.bulk-move') }}', null); open = false"
                            class="w-full text-left px-4 py-2 hover:bg-gray-50 text-sm">Root</button>
                        @foreach(auth()->user()->folders()->orderBy('name')->get() as $f)
                            <button type="button"
                                @click="bulkMove('{{ route('files.bulk-move') }}', {{ $f->id }}); open = false"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 text-sm">
                                {{ $f->path }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <button type="button" @click="selected = []" class="text-gray-400 hover:text-white text-xs">✕</button>
            </div>
        </div>
    @endif

    @if($folders->isEmpty() && $files->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <p class="text-lg font-medium">This folder is empty</p>
            <p class="text-sm mt-1">Drop files above to upload.</p>
        </div>
    @endif

    <script>
    function bulkSelect() {
        return {
            selected: [],
            get allSelected() {
                const ids = Array.from(document.querySelectorAll('tbody input[type=checkbox]')).map(el => parseInt(el.value));
                return ids.length > 0 && ids.every(id => this.selected.includes(id));
            },
            toggle(id) {
                const idx = this.selected.indexOf(id);
                if (idx === -1) this.selected.push(id);
                else this.selected.splice(idx, 1);
            },
            toggleAll(checked) {
                const ids = Array.from(document.querySelectorAll('tbody input[type=checkbox]')).map(el => parseInt(el.value));
                this.selected = checked ? ids : [];
            },
            async bulkDelete(url) {
                if (!confirm(`Delete ${this.selected.length} file(s)?`)) return;
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'},
                    body: JSON.stringify({ids: this.selected}),
                });
                if (resp.ok) location.reload();
            },
            async bulkMove(url, folderId) {
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'},
                    body: JSON.stringify({ids: this.selected, folder_id: folderId}),
                });
                if (resp.ok) location.reload();
            },
        };
    }

    function promptRename(event, currentName) {
        const newName = prompt('Rename to:', currentName);
        if (!newName || newName === currentName) { event.preventDefault(); return false; }
        event.target.querySelector('.rename-input').value = newName;
        return true;
    }

    function handleDrop(event) {
        event.preventDefault();
        document.getElementById('drop-zone').classList.remove('border-blue-500', 'bg-blue-50');
        uploadFiles(event.dataTransfer.files);
    }

    function uploadFiles(files, relativePaths = []) {
        if (!files.length) return;

        const status  = document.getElementById('upload-status');
        const label   = document.getElementById('upload-label');
        const percent = document.getElementById('upload-percent');
        const bar     = document.getElementById('upload-bar');

        status.classList.remove('hidden');
        label.textContent = 'Uploading ' + files.length + ' file(s)...';
        percent.textContent = '0%';
        bar.style.width = '0%';
        bar.className = 'bg-blue-500 h-2 rounded-full transition-all duration-200';

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
            formData.append('relative_paths[]', relativePaths[i] || '');
        }
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('folder_id', '{{ $folder->id }}');

        const xhr = new XMLHttpRequest();

        xhr.upload.onprogress = function(e) {
            if (!e.lengthComputable) return;
            const pct = Math.round((e.loaded / e.total) * 100);
            bar.style.width = pct + '%';
            percent.textContent = pct + '%';
            if (pct === 100) label.textContent = 'Processing...';
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    label.textContent = data.uploaded.length + ' file(s) uploaded successfully.';
                    if (data.errors && data.errors.length) {
                        label.textContent += ' Duplicates: ' + data.errors.join(', ');
                    }
                    bar.className = 'bg-green-500 h-2 rounded-full transition-all duration-200';
                    setTimeout(() => location.reload(), 1500);
                } catch(e) {
                    label.textContent = 'Server error. Check logs.';
                    bar.className = 'bg-red-500 h-2 rounded-full transition-all duration-200';
                }
            } else {
                try {
                    const data = JSON.parse(xhr.responseText);
                    const msg = data.message || Object.values(data.errors || {}).flat().join(', ');
                    label.textContent = 'Error: ' + msg;
                } catch(e) {
                    label.textContent = 'Upload failed (HTTP ' + xhr.status + ')';
                }
                bar.className = 'bg-red-500 h-2 rounded-full transition-all duration-200';
            }
        };

        xhr.onerror = function() {
            label.textContent = 'Network error. Please try again.';
            bar.className = 'bg-red-500 h-2 rounded-full transition-all duration-200';
        };

        xhr.open('POST', '{{ route('files.upload') }}');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
    }

    function uploadFolder(files) {
        if (!files.length) return;
        const relativePaths = Array.from(files).map(f => f.webkitRelativePath);
        uploadFiles(files, relativePaths);
    }

    let draggingFolderId = null;

    function fileDragStart(event, fileId) {
        event.dataTransfer.setData('application/fileid', fileId);
        event.dataTransfer.effectAllowed = 'move';
        event.currentTarget.classList.add('opacity-40');
    }

    function folderItemDragStart(event, folderId) {
        draggingFolderId = folderId;
        event.dataTransfer.setData('application/folderid', folderId);
        event.dataTransfer.effectAllowed = 'move';
        event.currentTarget.classList.add('opacity-40');
    }

    function folderItemDragEnd(event) {
        event.currentTarget.classList.remove('opacity-40');
        draggingFolderId = null;
    }

    function folderDragOver(event) {
        const hasFile = event.dataTransfer.types.includes('application/fileid');
        const hasFolder = event.dataTransfer.types.includes('application/folderid');
        if (!hasFile && !hasFolder) return;
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        event.currentTarget.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50');
    }

    function folderDragLeave(event) {
        event.currentTarget.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50');
    }

    function anyDrop(event, targetFolderId) {
        event.preventDefault();
        event.currentTarget.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50');
        const fileId = event.dataTransfer.getData('application/fileid');
        const folderId = event.dataTransfer.getData('application/folderid');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        if (fileId) {
            fetch(`/files/${fileId}/move`, {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                body: JSON.stringify({ folder_id: targetFolderId }),
            }).then(r => { if (r.ok || r.redirected) location.reload(); });
        } else if (folderId && folderId != targetFolderId) {
            fetch(`/folders/${folderId}/move`, {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                body: JSON.stringify({ parent_id: targetFolderId }),
            }).then(r => { if (r.ok) location.reload(); });
        }
    }
    </script>
</x-app-layout>
