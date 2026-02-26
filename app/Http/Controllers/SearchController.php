<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->input('q', ''));

        if ($query === '') {
            return view('search', ['query' => '', 'files' => collect(), 'folders' => collect()]);
        }

        $user = $request->user();
        $like = '%' . $query . '%';

        $files = $user->files()
            ->where('name', 'like', $like)
            ->with('folder')
            ->orderBy('name')
            ->limit(50)
            ->get();

        $folders = $user->folders()
            ->where('name', 'like', $like)
            ->orderBy('name')
            ->limit(20)
            ->get();

        return view('search', compact('query', 'files', 'folders'));
    }
}
