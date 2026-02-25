@php
    $diskTotal = disk_total_space(storage_path('app/private/uploads'));
    $diskFree  = disk_free_space(storage_path('app/private/uploads'));
    $diskUsed  = $diskTotal - $diskFree;
    $percent   = $diskTotal > 0 ? min(round(($diskUsed / $diskTotal) * 100, 1), 100) : 0;
    $color     = $percent >= 90 ? 'bg-red-500' : ($percent >= 70 ? 'bg-yellow-500' : 'bg-blue-500');

    $fmt = function(int $bytes): string {
        $units = ['B','KB','MB','GB','TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
        return round($bytes, 1) . ' ' . $units[$i];
    };
@endphp

<div class="flex items-center gap-3 text-xs text-gray-500">
    <span class="whitespace-nowrap">Server: {{ $fmt($diskUsed) }} / {{ $fmt($diskTotal) }}</span>
    <div class="flex-1 bg-gray-200 rounded-full h-1.5 max-w-xs">
        <div class="{{ $color }} h-1.5 rounded-full transition-all" style="width: {{ $percent }}%"></div>
    </div>
    <span>{{ $percent }}%</span>
</div>
