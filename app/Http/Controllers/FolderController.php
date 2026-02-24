<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\FolderService;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function __construct(private FolderService $folderService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $folders = $user->folders()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $files = $user->files()
            ->whereNull('folder_id')
            ->orderBy('name')
            ->get();

        return view('files.index', compact('folders', 'files'));
    }

    public function show(Request $request, Folder $folder)
    {
        $this->authorize('view', $folder);

        $folders = $folder->children()
            ->orderBy('name')
            ->get();

        $files = $folder->files()
            ->orderBy('name')
            ->get();

        $breadcrumbs = $folder->breadcrumbs();

        return view('files.folder', compact('folder', 'folders', 'files', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:folders,id'],
        ]);

        $user = $request->user();
        $parent = null;

        if (!empty($data['parent_id'])) {
            $parent = Folder::findOrFail($data['parent_id']);
            $this->authorize('view', $parent);
        }

        $path = $this->folderService->buildPath($parent, $data['name']);

        // Check uniqueness
        $exists = $user->folders()->where('path', $path)->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'A folder with this name already exists here.']);
        }

        $user->folders()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'path' => $path,
        ]);

        return back()->with('success', 'Folder created.');
    }

    public function update(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $parent = $folder->parent_id ? Folder::find($folder->parent_id) : null;
        $newPath = $this->folderService->buildPath($parent, $data['name']);

        $folder->update([
            'name' => $data['name'],
            'path' => $newPath,
        ]);

        return back()->with('success', 'Folder renamed.');
    }

    public function destroy(Folder $folder)
    {
        $this->authorize('delete', $folder);

        $this->folderService->deleteRecursive($folder);

        return back()->with('success', 'Folder deleted.');
    }
}
