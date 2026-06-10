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
     * Allowed file extensions for upload validation (maps to MIME types via Laravel's mimes rule).
     * Phase 20: removed gif (not in MVP spec); added mp4, mov (video attachments).
     * Keep this in sync with the controller validate() call.
     */
    public static function allowedMimes(): array
    {
        return [
            'pdf',
            'jpg', 'jpeg', 'png', 'webp',
            'doc', 'docx',
            'xls', 'xlsx',
            'ppt', 'pptx',
            'txt', 'csv',
            'mp4', 'mov',
            'zip',
        ];
    }

    /**
     * MIME types that are always blocked, regardless of the mimes whitelist.
     * Phase 20: belt-and-suspenders protection. The mimes whitelist should already
     * reject these, but this explicit blocklist prevents any edge-case bypass.
     */
    public static function blockedMimeTypes(): array
    {
        return [
            'application/x-php',
            'application/php',
            'text/x-php',
            'text/php',
            'application/x-httpd-php',
            'application/x-httpd-php-source',
            'text/html',
            'application/xhtml+xml',
            'application/javascript',
            'text/javascript',
            'application/x-javascript',
            'image/svg+xml',
            'application/x-sh',
            'text/x-shellscript',
            'application/x-executable',
            'application/x-elf',
            'application/x-msdos-program',
            'application/x-msdownload',
        ];
    }

    /**
     * File extensions that are always blocked on upload.
     * Phase 20: these extensions are never allowed as stored filenames, even if
     * somehow the mimes check passed.
     */
    public static function blockedExtensions(): array
    {
        return [
            'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phps',
            'pl', 'py', 'rb', 'sh', 'bash', 'zsh',
            'exe', 'bat', 'cmd', 'com', 'msi', 'dll',
            'js', 'mjs', 'ts',
            'html', 'htm', 'xhtml',
            'svg', 'svgz',
            'asp', 'aspx', 'jsp', 'jspx',
            'cgi', 'htaccess', 'htpasswd',
        ];
    }

    /**
     * Sanitize an original filename for safe storage in the database and
     * safe use in HTTP Content-Disposition headers.
     *
     * Phase 20: strips path separators, null bytes, leading dots, and
     * limits to 255 characters. Does NOT change the extension — the
     * controller's upload validation is responsible for rejecting dangerous types.
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove null bytes
        $filename = str_replace("\0", '', $filename);
        // Strip path separators — prevent path traversal
        $filename = str_replace(['/', '\\'], '', $filename);
        // basename() as a final path-traversal guard
        $filename = basename($filename);
        // Strip leading dots (hidden file names)
        $filename = ltrim($filename, '.');
        // Limit to 255 characters, preserving the extension
        if (mb_strlen($filename) > 255) {
            $ext  = pathinfo($filename, PATHINFO_EXTENSION);
            $name = mb_substr(pathinfo($filename, PATHINFO_FILENAME), 0, 250 - mb_strlen($ext));
            $filename = $ext ? "{$name}.{$ext}" : $name;
        }

        return $filename ?: 'download';
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
