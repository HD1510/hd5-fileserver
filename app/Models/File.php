<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'folder_id',
        'name',
        'disk_name',
        'disk_path',
        'mime_type',
        'size',
        'extension',
        'hash',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    public function sizeFormatted(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function icon(): string
    {
        $mime = $this->mime_type ?? '';
        $ext = strtolower($this->extension ?? '');

        if (str_starts_with($mime, 'image/')) {
            return 'photo';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video-camera';
        }
        if (str_starts_with($mime, 'audio/')) {
            return 'musical-note';
        }
        if (in_array($ext, ['pdf'])) {
            return 'document-text';
        }
        if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'])) {
            return 'archive-box';
        }
        if (in_array($ext, ['doc', 'docx', 'odt', 'rtf'])) {
            return 'document';
        }
        if (in_array($ext, ['xls', 'xlsx', 'ods', 'csv'])) {
            return 'table-cells';
        }
        if (in_array($ext, ['ppt', 'pptx', 'odp'])) {
            return 'presentation-chart-bar';
        }
        if (in_array($ext, ['txt', 'md', 'log'])) {
            return 'document-text';
        }
        if (in_array($ext, ['php', 'js', 'ts', 'py', 'rb', 'java', 'c', 'cpp', 'h', 'cs', 'go', 'rs', 'html', 'css', 'json', 'xml', 'yaml', 'yml', 'sh', 'bash'])) {
            return 'code-bracket';
        }

        return 'document';
    }
}
