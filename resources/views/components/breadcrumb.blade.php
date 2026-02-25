@props(['crumbs' => []])

<nav class="flex items-center gap-1 text-sm text-gray-500 flex-wrap">
    <a href="{{ route('files.index') }}"
        class="hover:text-gray-800 rounded px-1 py-0.5 transition-colors"
        ondragover="folderDragOver(event)"
        ondragleave="folderDragLeave(event)"
        ondrop="folderDrop(event, null)">Home</a>
    @foreach ($crumbs as $crumb)
        <span>/</span>
        @if ($loop->last)
            <span class="text-gray-800 font-medium px-1">{{ $crumb->name }}</span>
        @else
            <a href="{{ route('folders.show', $crumb) }}"
                class="hover:text-gray-800 rounded px-1 py-0.5 transition-colors"
                ondragover="folderDragOver(event)"
                ondragleave="folderDragLeave(event)"
                ondrop="folderDrop(event, {{ $crumb->id }})">{{ $crumb->name }}</a>
        @endif
    @endforeach
</nav>
