<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Returns array of ancestor folders from root to this folder.
     *
     * @return array<Folder>
     */
    public function breadcrumbs(): array
    {
        $crumbs = [];
        $folder = $this;

        while ($folder !== null) {
            array_unshift($crumbs, $folder);
            $folder = $folder->parent_id ? $folder->parent()->first() : null;
        }

        return $crumbs;
    }
}
