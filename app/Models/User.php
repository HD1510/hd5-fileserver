<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'storage_quota',
        'storage_used',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'storage_quota' => 'integer',
            'storage_used' => 'integer',
        ];
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    public function storageUsedFormatted(): string
    {
        return $this->formatBytes($this->storage_used);
    }

    public function storageQuotaFormatted(): string
    {
        return $this->formatBytes($this->storage_quota);
    }

    public function storageUsedPercent(): float
    {
        if ($this->storage_quota === 0) {
            return 0;
        }
        return round(($this->storage_used / $this->storage_quota) * 100, 1);
    }

    public function hasStorageAvailable(int $bytes): bool
    {
        return ($this->storage_used + $bytes) <= $this->storage_quota;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
