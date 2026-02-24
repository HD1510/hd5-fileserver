<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class Share extends Model
{
    protected $fillable = [
        'user_id',
        'file_id',
        'folder_id',
        'token',
        'label',
        'password_hash',
        'expires_at',
        'download_count',
        'max_downloads',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'download_count' => 'integer',
            'max_downloads' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->max_downloads !== null && $this->download_count >= $this->max_downloads;
    }

    public function isAccessible(): bool
    {
        return $this->is_active && !$this->isExpired() && !$this->isExhausted();
    }

    public function requiresPassword(): bool
    {
        return $this->password_hash !== null;
    }

    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password_hash);
    }

    public function publicUrl(): string
    {
        return route('shares.show', $this->token);
    }
}
