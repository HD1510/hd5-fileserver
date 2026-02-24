<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'file_count' => $user->files()->count(),
            'folder_count' => $user->folders()->count(),
            'share_count' => $user->shares()->where('is_active', true)->count(),
            'storage_used' => $user->storage_used,
            'storage_quota' => $user->storage_quota,
            'storage_used_formatted' => $user->storageUsedFormatted(),
            'storage_quota_formatted' => $user->storageQuotaFormatted(),
            'storage_percent' => $user->storageUsedPercent(),
        ];

        $recentFiles = $user->files()
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentFiles'));
    }
}
