<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\FolderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FolderController extends Controller
{
    public function __construct(private FolderService $folderService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        [$sortCol, $sortDir] = $this->parseSortParams($request);

        $folders = $user->folders()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $files = $user->files()
            ->whereNull('folder_id')
            ->orderBy($sortCol, $sortDir)
            ->get();

        return view('files.index', compact('folders', 'files', 'sortCol', 'sortDir'));
    }

    public function show(Request $request, Folder $folder)
    {
        $this->authorize('view', $folder);
        [$sortCol, $sortDir] = $this->parseSortParams($request);

        $folders = $folder->children()
            ->orderBy('name')
            ->get();

        $files = $folder->files()
            ->orderBy($sortCol, $sortDir)
            ->get();

        $breadcrumbs = $folder->breadcrumbs();

        return view('files.folder', compact('folder', 'folders', 'files', 'breadcrumbs', 'sortCol', 'sortDir'));
    }

    private function parseSortParams(Request $request): array
    {
        $allowed = ['name', 'size', 'created_at'];
        $col = in_array($request->query('sort'), $allowed) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        return [$col, $dir];
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

    public function download(Folder $folder)
    {
        $this->authorize('view', $folder);

        $zipPath = sys_get_temp_dir() . '/' . uniqid('folder_', true) . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP archive.');
        }

        $this->addFolderToZip($zip, $folder, $folder->name);

        $zip->close();

        return response()->download($zipPath, $folder->name . '.zip')->deleteFileAfterSend(true);
    }

    private function addFolderToZip(ZipArchive $zip, Folder $folder, string $zipPrefix): void
    {
        foreach ($folder->files as $file) {
            $diskPath = Storage::disk('uploads')->path($file->disk_path);
            if (file_exists($diskPath)) {
                $zip->addFile($diskPath, $zipPrefix . '/' . $file->name);
            }
        }

        foreach ($folder->children as $child) {
            $this->addFolderToZip($zip, $child, $zipPrefix . '/' . $child->name);
        }
    }

    public function move(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:folders,id'],
        ]);

        $newParentId = $data['parent_id'] ?? null;

        // Prevent moving into itself or its own descendants
        if ($newParentId !== null) {
            $targetFolder = Folder::findOrFail($newParentId);
            $this->authorize('view', $targetFolder);

            if ($this->isDescendant($folder, $newParentId)) {
                return response()->json(['error' => 'Cannot move folder into its own descendant.'], 422);
            }
        }

        $newParent = $newParentId ? Folder::find($newParentId) : null;
        $newPath = $this->folderService->buildPath($newParent, $folder->name);

        $folder->update([
            'parent_id' => $newParentId,
            'path' => $newPath,
        ]);

        $this->folderService->updatePathRecursive($folder);

        return response()->json(['success' => true]);
    }

    private function isDescendant(Folder $folder, int $candidateId): bool
    {
        if ($folder->id === $candidateId) {
            return true;
        }
        foreach ($folder->children as $child) {
            if ($this->isDescendant($child, $candidateId)) {
                return true;
            }
        }
        return false;
    }
}
