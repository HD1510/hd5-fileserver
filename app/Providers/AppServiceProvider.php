<?php

namespace App\Providers;

use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use App\Policies\FilePolicy;
use App\Policies\FolderPolicy;
use App\Policies\SharePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(File::class, FilePolicy::class);
        Gate::policy(Folder::class, FolderPolicy::class);
        Gate::policy(Share::class, SharePolicy::class);
    }
}
