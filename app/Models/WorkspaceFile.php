<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'uploaded_by_user_id',
        'workspace_task_id',
        'title',
        'original_filename',
        'stored_filename',
        'storage_path',
        'mime_type',
        'file_size',
        'visibility',
        'category',
        'description',
        'downloads_count',
    ];

    protected $casts = [
        'workspace_id'        => 'integer',
        'uploaded_by_user_id' => 'integer',
        'workspace_task_id'   => 'integer',
        'file_size'           => 'integer',
        'downloads_count'     => 'integer',
    ];

    // ── Category labels ───────────────────────────────────────────────────

    public static function categoryLabels(): array
    {
        return [
            'general'          => 'General',
            'task_attachment'  => 'Task Attachment',
            'brief'            => 'Brief',
            'deliverable'      => 'Deliverable',
            'invoice_support'  => 'Invoice Support',
            'other'            => 'Other',
        ];
    }

    public function categoryLabel(): string
    {
        return static::categoryLabels()[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category ?? 'general'));
    }

    // ── MIME type / extension helpers ─────────────────────────────────────

    /**
     * Allowed MIME types for upload validation.
     * Keep this in sync with the controller validate() call.
     */
    public static function allowedMimes(): array
    {
        return [
            'pdf',
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'doc', 'docx',
            'xls', 'xlsx',
            'ppt', 'pptx',
            'txt', 'csv',
            'zip',
        ];
    }

    /**
     * Material Symbols icon name for the file's MIME type.
     */
    public function typeIcon(): string
    {
        $mime = strtolower($this->mime_type ?? '');

        if (str_contains($mime, 'pdf'))                                   return 'picture_as_pdf';
        if (str_starts_with($mime, 'image/'))                            return 'image';
        if (str_contains($mime, 'word') || str_contains($mime, 'msword')) return 'description';
        if (str_contains($mime, 'excel') || str_contains($mime, 'spreadsheet')) return 'table_view';
        if (str_contains($mime, 'powerpoint') || str_contains($mime, 'presentation')) return 'slideshow';
        if (str_contains($mime, 'text'))                                  return 'text_snippet';
        if (str_contains($mime, 'zip') || str_contains($mime, 'archive')) return 'folder_zip';

        return 'attach_file';
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isInternal(): bool
    {
        return $this->visibility === 'internal';
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    /**
     * Human-readable file size (bytes → KB / MB).
     */
    public function formattedSize(): string
    {
        if (! $this->file_size) {
            return '—';
        }

        $kb = $this->file_size / 1024;

        if ($kb < 1024) {
            return number_format($kb, 1) . ' KB';
        }

        return number_format($kb / 1024, 2) . ' MB';
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkspaceTask::class, 'workspace_task_id');
    }
}
