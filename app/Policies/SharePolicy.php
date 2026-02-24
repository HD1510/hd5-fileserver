<?php

namespace App\Policies;

use App\Models\Share;
use App\Models\User;

class SharePolicy
{
    public function view(User $user, Share $share): bool
    {
        return $user->id === $share->user_id || $user->is_admin;
    }

    public function update(User $user, Share $share): bool
    {
        return $user->id === $share->user_id || $user->is_admin;
    }

    public function delete(User $user, Share $share): bool
    {
        return $user->id === $share->user_id || $user->is_admin;
    }
}
