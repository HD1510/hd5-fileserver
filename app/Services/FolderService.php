<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class FolderService
{
    public function deleteRecursive(Folder $folder): void
    {
        // Delete all child folders recursively
        foreach ($folder->children()->withTrashed()->get() as $child) {
            $this->deleteRecursive($child);
        }

        // Delete all files in this folder
        foreach ($folder->files()->withTrashed()->get() as $file) {
            // Remove from disk
            if (Storage::disk('uploads')->exists($file->disk_path)) {
                Storage::disk('uploads')->delete($file->disk_path);
            }

            // Update user quota
            $user = $file->user;
            if ($user) {
                $user->storage_used = max(0, $user->storage_used - $file->size);
                $user->save();
            }

            $file->forceDelete();
        }

        $folder->forceDelete();
    }

    public function buildPath(?Folder $parent, string $name): string
    {
        if ($parent === null) {
            return '/' . $name;
        }
        return rtrim($parent->path, '/') . '/' . $name;
    }

    public function updatePathRecursive(Folder $folder): void
    {
        foreach ($folder->children as $child) {
            $newPath = $this->buildPath($folder, $child->name);
            $child->update(['path' => $newPath]);
            $this->updatePathRecursive($child);
        }
    }
}
