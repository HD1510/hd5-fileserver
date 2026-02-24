@props(['crumbs' => []])

<nav class="flex items-center gap-1 text-sm text-gray-500 flex-wrap">
    <a href="{{ route('files.index') }}" class="hover:text-gray-800">Home</a>
    @foreach ($crumbs as $crumb)
        <span>/</span>
        @if ($loop->last)
            <span class="text-gray-800 font-medium">{{ $crumb->name }}</span>
        @else
            <a href="{{ route('folders.show', $crumb) }}" class="hover:text-gray-800">{{ $crumb->name }}</a>
        @endif
    @endforeach
</nav>
