<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $uploadsUsed  = \App\Models\File::sum('size');
        $diskFree     = disk_free_space(storage_path('app/private/uploads'));
        $uploadsTotal = $uploadsUsed + $diskFree;
        $diskPercent  = $uploadsTotal > 0 ? round(($uploadsUsed / $uploadsTotal) * 100, 1) : 0;

        $stats = [
            'file_count'     => $user->files()->count(),
            'folder_count'   => $user->folders()->count(),
            'share_count'    => $user->shares()->where('is_active', true)->count(),
            'disk_used'      => $uploadsUsed,
            'disk_total'     => $uploadsTotal,
            'disk_free'      => $diskFree,
            'disk_percent'   => $diskPercent,
            'disk_used_fmt'  => $this->formatBytes($uploadsUsed),
            'disk_free_fmt'  => $this->formatBytes($diskFree),
            'disk_total_fmt' => $this->formatBytes($uploadsTotal),
        ];

        $recentFiles = $user->files()->latest()->limit(5)->get();

        return view('dashboard', compact('stats', 'recentFiles'));
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
