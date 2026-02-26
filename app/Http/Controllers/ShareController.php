<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShareController extends Controller
{
    // ── Auth-protected methods ────────────────────────────────────────────────

    public function index(Request $request)
    {
        $shares = $request->user()
            ->shares()
            ->with(['file', 'folder'])
            ->latest()
            ->paginate(20);

        return view('shares.index', compact('shares'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file_id' => ['nullable', 'integer', 'exists:files,id'],
            'folder_id' => ['nullable', 'integer', 'exists:folders,id'],
            'label' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:4'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'max_downloads' => ['nullable', 'integer', 'min:1'],
        ]);

        if (empty($data['file_id']) && empty($data['folder_id'])) {
            return back()->withErrors(['file_id' => 'Either a file or folder must be selected.']);
        }

        $user = $request->user();

        if (!empty($data['file_id'])) {
            $file = File::findOrFail($data['file_id']);
            $this->authorize('view', $file);
        }

        if (!empty($data['folder_id'])) {
            $folder = Folder::findOrFail($data['folder_id']);
            $this->authorize('view', $folder);
        }

        $share = $user->shares()->create([
            'file_id' => $data['file_id'] ?? null,
            'folder_id' => $data['folder_id'] ?? null,
            'token' => Str::random(48),
            'label' => $data['label'] ?? null,
            'password_hash' => !empty($data['password']) ? Hash::make($data['password']) : null,
            'expires_at' => $data['expires_at'] ?? null,
            'max_downloads' => $data['max_downloads'] ?? null,
        ]);

        return back()->with('success', 'Share link created: ' . $share->publicUrl());
    }

    public function update(Request $request, Share $share)
    {
        $this->authorize('update', $share);

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
            'label' => ['nullable', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
            'max_downloads' => ['nullable', 'integer', 'min:1'],
        ]);

        $share->update($data);

        return back()->with('success', 'Share updated.');
    }

    public function destroy(Share $share)
    {
        $this->authorize('delete', $share);

        $share->delete();

        return back()->with('success', 'Share deleted.');
    }

    // ── Public methods ────────────────────────────────────────────────────────

    public function show(string $token, Request $request)
    {
        $share = Share::where('token', $token)->firstOrFail();

        if (!$share->isAccessible()) {
            return view('shares.expired', compact('share'));
        }

        if ($share->requiresPassword() && !$request->session()->get('share_unlocked_' . $share->id)) {
            return view('shares.password', compact('share'));
        }

        return view('shares.show', compact('share'));
    }

    public function authenticate(string $token, Request $request)
    {
        $share = Share::where('token', $token)->firstOrFail();

        if (!$share->isAccessible()) {
            return view('shares.expired', compact('share'));
        }

        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!$share->checkPassword($data['password'])) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $request->session()->put('share_unlocked_' . $share->id, true);

        return redirect()->route('shares.show', $token);
    }

    public function download(Request $request, string $token, ?int $fileId = null)
    {
        $share = Share::where('token', $token)->with(['file', 'folder.files'])->firstOrFail();

        if (!$share->isAccessible()) {
            abort(403, 'This share link is no longer valid.');
        }

        if ($share->requiresPassword() && !$request->session()->get('share_unlocked_' . $share->id)) {
            abort(403, 'Password required.');
        }

        // Single file share
        if ($share->file_id && !$fileId) {
            $file = $share->file;
        } elseif ($fileId && $share->folder_id) {
            // File within a shared folder
            $file = $share->folder->files()->findOrFail($fileId);
        } else {
            abort(404);
        }

        if (!\Illuminate\Support\Facades\Storage::disk('uploads')->exists($file->disk_path)) {
            abort(404, 'File not found.');
        }

        $share->increment('download_count');

        return \Illuminate\Support\Facades\Storage::disk('uploads')->download($file->disk_path, $file->name);
    }
}
