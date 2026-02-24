<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file', 'max:102400'], // 100 MB
            'folder_id' => ['nullable', 'integer', 'exists:folders,id'],
        ]);

        $user = $request->user();
        $folder = null;

        if ($request->filled('folder_id')) {
            $folder = Folder::findOrFail($request->folder_id);
            $this->authorize('view', $folder);
        }

        $uploaded = [];
        $errors = [];

        foreach ($request->file('files') as $uploadedFile) {
            $size = $uploadedFile->getSize();

            if (!$user->hasStorageAvailable($size)) {
                $errors[] = $uploadedFile->getClientOriginalName() . ': Storage quota exceeded.';
                continue;
            }

            $ext = $uploadedFile->getClientOriginalExtension();
            $diskName = Str::uuid() . ($ext ? '.' . $ext : '');
            $diskPath = $user->id . '/' . $diskName;

            Storage::disk('uploads')->putFileAs($user->id, $uploadedFile, $diskName);

            $file = $user->files()->create([
                'folder_id' => $folder?->id,
                'name' => $uploadedFile->getClientOriginalName(),
                'disk_name' => $diskName,
                'disk_path' => $diskPath,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $size,
                'extension' => $ext ?: null,
            ]);

            $user->increment('storage_used', $size);
            $uploaded[] = $file->name;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'uploaded' => $uploaded,
                'errors' => $errors,
            ]);
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->with('success', count($uploaded) . ' file(s) uploaded.');
        }

        return back()->with('success', count($uploaded) . ' file(s) uploaded.');
    }

    public function download(File $file)
    {
        $this->authorize('download', $file);

        if (!Storage::disk('uploads')->exists($file->disk_path)) {
            abort(404, 'File not found on disk.');
        }

        return Storage::disk('uploads')->download($file->disk_path, $file->name);
    }

    public function update(Request $request, File $file)
    {
        $this->authorize('update', $file);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $file->update(['name' => $data['name']]);

        return back()->with('success', 'File renamed.');
    }

    public function move(Request $request, File $file)
    {
        $this->authorize('move', $file);

        $data = $request->validate([
            'folder_id' => ['nullable', 'integer', 'exists:folders,id'],
        ]);

        if (!empty($data['folder_id'])) {
            $folder = Folder::findOrFail($data['folder_id']);
            $this->authorize('view', $folder);
        }

        $file->update(['folder_id' => $data['folder_id'] ?? null]);

        return back()->with('success', 'File moved.');
    }

    public function destroy(File $file)
    {
        $this->authorize('delete', $file);

        // Remove from disk
        if (Storage::disk('uploads')->exists($file->disk_path)) {
            Storage::disk('uploads')->delete($file->disk_path);
        }

        $file->user->decrement('storage_used', $file->size);
        $file->delete();

        return back()->with('success', 'File deleted.');
    }
}
