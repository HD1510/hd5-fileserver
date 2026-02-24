@props(['user'])

@php
    $percent = min($user->storageUsedPercent(), 100);
    $color = $percent >= 90 ? 'bg-red-500' : ($percent >= 70 ? 'bg-yellow-500' : 'bg-blue-500');
@endphp

<div class="flex items-center gap-3 text-xs text-gray-500">
    <span class="whitespace-nowrap">{{ $user->storageUsedFormatted() }} / {{ $user->storageQuotaFormatted() }}</span>
    <div class="flex-1 bg-gray-200 rounded-full h-1.5 max-w-xs">
        <div class="{{ $color }} h-1.5 rounded-full transition-all" style="width: {{ $percent }}%"></div>
    </div>
    <span>{{ $percent }}%</span>
</div>
