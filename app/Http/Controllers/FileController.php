<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use App\Services\FolderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function __construct(private FolderService $folderService) {}

    public function upload(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file', 'max:102400'],
            'folder_id' => ['nullable', 'integer', 'exists:folders,id'],
            'relative_paths' => ['nullable', 'array'],
            'relative_paths.*' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $baseFolder = null;

        if ($request->filled('folder_id')) {
            $baseFolder = Folder::findOrFail($request->folder_id);
            $this->authorize('view', $baseFolder);
        }

        $relativePaths = $request->input('relative_paths', []);
        $uploaded = [];
        $errors = [];

        foreach ($request->file('files') as $index => $uploadedFile) {
            $size = $uploadedFile->getSize();
            $originalName = $uploadedFile->getClientOriginalName();

            if (!$user->hasStorageAvailable($size)) {
                $errors[] = $originalName . ': Storage quota exceeded.';
                continue;
            }

            // Duplicate detection via MD5 hash
            $hash = md5_file($uploadedFile->getRealPath());
            $duplicate = $user->files()->where('hash', $hash)->first();
            if ($duplicate) {
                $errors[] = $originalName . ': Duplicate of \'' . $duplicate->name . '\'';
                continue;
            }

            // Determine target folder from relative path
            $targetFolder = $baseFolder;
            $relativePath = $relativePaths[$index] ?? null;

            if ($relativePath) {
                $segments = explode('/', $relativePath);
                array_pop($segments); // remove filename, keep folder segments
                if (!empty($segments)) {
                    $targetFolder = $this->findOrCreateFolderPath($user, $segments, $baseFolder);
                }
            }

            $ext = $uploadedFile->getClientOriginalExtension();
            $diskName = Str::uuid() . ($ext ? '.' . $ext : '');
            $diskPath = $user->id . '/' . $diskName;

            Storage::disk('uploads')->putFileAs($user->id, $uploadedFile, $diskName);

            $file = $user->files()->create([
                'folder_id' => $targetFolder?->id,
                'name' => $originalName,
                'disk_name' => $diskName,
                'disk_path' => $diskPath,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $size,
                'extension' => $ext ?: null,
                'hash' => $hash,
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

    private function findOrCreateFolderPath(User $user, array $segments, ?Folder $parent): Folder
    {
        $current = $parent;
        foreach ($segments as $segment) {
            $path = $this->folderService->buildPath($current, $segment);
            $existing = $user->folders()->where('path', $path)->first();
            if ($existing) {
                $current = $existing;
            } else {
                $current = $user->folders()->create([
                    'parent_id' => $current?->id,
                    'name' => $segment,
                    'path' => $path,
                ]);
            }
        }
        return $current;
    }

    public function preview(File $file)
    {
        $this->authorize('download', $file);

        if (!Storage::disk('uploads')->exists($file->disk_path)) {
            abort(404, 'File not found on disk.');
        }

        return Storage::disk('uploads')->response($file->disk_path, $file->name, [
            'Content-Type' => $file->mime_type ?? 'application/octet-stream',
        ]);
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

        if (Storage::disk('uploads')->exists($file->disk_path)) {
            Storage::disk('uploads')->delete($file->disk_path);
        }

        $file->user->storage_used = max(0, $file->user->storage_used - $file->size);
        $file->user->save();
        $file->delete();

        return back()->with('success', 'File deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:files,id'],
        ]);

        $user = $request->user();
        $files = $user->files()->whereIn('id', $data['ids'])->get();

        foreach ($files as $file) {
            if (Storage::disk('uploads')->exists($file->disk_path)) {
                Storage::disk('uploads')->delete($file->disk_path);
            }
            $user->storage_used = max(0, $user->storage_used - $file->size);
            $file->delete();
        }

        $user->save();

        return response()->json(['deleted' => $files->count()]);
    }

    public function bulkMove(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:files,id'],
            'folder_id' => ['nullable', 'integer', 'exists:folders,id'],
        ]);

        $user = $request->user();

        if (!empty($data['folder_id'])) {
            $folder = Folder::findOrFail($data['folder_id']);
            $this->authorize('view', $folder);
        }

        $user->files()
            ->whereIn('id', $data['ids'])
            ->update(['folder_id' => $data['folder_id'] ?? null]);

        return response()->json(['moved' => count($data['ids'])]);
    }
}
