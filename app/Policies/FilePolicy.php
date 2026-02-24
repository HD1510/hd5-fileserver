<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    public function view(User $user, File $file): bool
    {
        return $user->id === $file->user_id || $user->is_admin;
    }

    public function update(User $user, File $file): bool
    {
        return $user->id === $file->user_id || $user->is_admin;
    }

    public function delete(User $user, File $file): bool
    {
        return $user->id === $file->user_id || $user->is_admin;
    }

    public function download(User $user, File $file): bool
    {
        return $user->id === $file->user_id || $user->is_admin;
    }

    public function move(User $user, File $file): bool
    {
        return $user->id === $file->user_id || $user->is_admin;
    }
}
