<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <x-breadcrumb :crumbs="[]" />
                <h2 class="font-semibold text-xl text-gray-800 mt-1">My Files</h2>
            </div>
            <div class="flex items-center gap-3">
                {{-- New Folder --}}
                <form method="POST" action="{{ route('folders.store') }}" class="flex items-center gap-2" id="new-folder-form">
                    @csrf
                    <input type="text" name="name" placeholder="New folder name" required
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

    {{-- Folders --}}
    @if($folders->isNotEmpty())
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Folders</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 mb-6">
            @foreach($folders as $folder)
                <div class="group bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-shadow"
                    ondragover="folderDragOver(event)"
                    ondragleave="folderDragLeave(event)"
                    ondrop="folderDrop(event, {{ $folder->id }})">
                    <a href="{{ route('folders.show', $folder) }}" class="block">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-yellow-400 mx-auto mb-2">
                            <path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 12h-15a4.483 4.483 0 0 0-3 1.146Z" />
                        </svg>
                        <p class="text-sm text-gray-700 text-center truncate">{{ $folder->name }}</p>
                    </a>
                    <div class="flex justify-center gap-2 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <form method="POST" action="{{ route('folders.update', $folder) }}" class="inline" onsubmit="return promptRename(event, '{{ addslashes($folder->name) }}')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="name" class="rename-input">
                            <button type="submit" class="text-xs text-gray-500 hover:text-blue-600">Rename</button>
                        </form>
                        <span class="text-gray-300">|</span>
                        <x-share-modal :folderId="$folder->id" :modalId="'share-folder-'.$folder->id" />
                        <span class="text-gray-300">|</span>
                        <form method="POST" action="{{ route('folders.destroy', $folder) }}" onsubmit="return confirm('Delete folder and all contents?')">
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
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Files</h3>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium hidden sm:table-cell">Size</th>
                        <th class="px-4 py-3 font-medium hidden md:table-cell">Uploaded</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($files as $file)
                        <tr class="hover:bg-gray-50 group cursor-grab" draggable="true"
                            ondragstart="fileDragStart(event, {{ $file->id }})"
                            ondragend="this.classList.remove('opacity-40')">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <x-file-icon :file="$file" class="w-5 h-5 text-gray-400 shrink-0" />
                                    <span class="truncate max-w-xs">{{ $file->name }}</span>
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
        </div>
    @endif

    @if($folders->isEmpty() && $files->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-4 text-gray-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
            </svg>
            <p class="text-lg font-medium">No files yet</p>
            <p class="text-sm mt-1">Drop files above to get started.</p>
        </div>
    @endif

    <script>
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

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
            if (relativePaths[i]) formData.append('relative_paths[]', relativePaths[i]);
            else formData.append('relative_paths[]', '');
        }
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

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
                    bar.classList.replace('bg-blue-500', 'bg-green-500');
                    setTimeout(() => location.reload(), 1000);
                } catch(e) {
                    label.textContent = 'Server error. Check logs.';
                    bar.classList.replace('bg-blue-500', 'bg-red-500');
                }
            } else {
                try {
                    const data = JSON.parse(xhr.responseText);
                    const msg = data.message || Object.values(data.errors || {}).flat().join(', ');
                    label.textContent = 'Error: ' + msg;
                } catch(e) {
                    label.textContent = 'Upload failed (HTTP ' + xhr.status + ')';
                }
                bar.classList.replace('bg-blue-500', 'bg-red-500');
            }
        };

        xhr.onerror = function() {
            label.textContent = 'Network error. Please try again.';
            bar.classList.replace('bg-blue-500', 'bg-red-500');
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

    function fileDragStart(event, fileId) {
        event.dataTransfer.setData('application/fileid', fileId);
        event.dataTransfer.effectAllowed = 'move';
        event.currentTarget.classList.add('opacity-40');
    }

    function folderDragOver(event) {
        if (!event.dataTransfer.types.includes('application/fileid')) return;
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        event.currentTarget.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50');
    }

    function folderDragLeave(event) {
        event.currentTarget.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50');
    }

    function folderDrop(event, folderId) {
        event.preventDefault();
        event.currentTarget.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50');
        const fileId = event.dataTransfer.getData('application/fileid');
        if (!fileId) return;
        fetch(`/files/${fileId}/move`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ folder_id: folderId ?? null }),
        }).then(r => { if (r.ok || r.redirected) location.reload(); });
    }
    </script>
</x-app-layout>
