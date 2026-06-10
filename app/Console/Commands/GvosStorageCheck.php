<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * php artisan gvos:storage-check
 *
 * Phase 20: Storage health-check command. Verifies:
 *   1. Default filesystem disk is configured.
 *   2. Local disk root is outside the public web path (private).
 *   3. Local disk 'serve' is disabled (should be false for private files).
 *   4. Storage directory is writable.
 *   5. PHP upload limits (informational).
 *   6. Public storage symlink status.
 *   7. Write/delete round-trip test on the local disk.
 *
 * Run ad-hoc:
 *   php artisan gvos:storage-check
 */
class GvosStorageCheck extends Command
{
    protected $signature = 'gvos:storage-check';

    protected $description = 'Check GVOS file storage health: disk config, writability, PHP limits, symlink status';

    public function handle(): int
    {
        $this->info('GVOS Storage Health Check');
        $this->info('══════════════════════════════════════════════════════════');

        $allPassed = true;

        // ── 1. Configured disk ────────────────────────────────────────────
        $disk       = config('filesystems.default', 'local');
        $diskConfig = config("filesystems.disks.{$disk}");

        $this->line("Default disk : {$disk}");

        if (! $diskConfig) {
            $this->error("  ✗ Disk '{$disk}' is not defined in filesystems.php.");
            $allPassed = false;
        } else {
            $driver = $diskConfig['driver'] ?? 'unknown';
            $this->line("  ✓ Disk '{$disk}' configured (driver: {$driver}).");
        }

        // ── 2. Local disk root is outside public path ─────────────────────
        $localRoot = config('filesystems.disks.local.root', '');
        $publicPath = rtrim(public_path(), DIRECTORY_SEPARATOR);

        if (str_starts_with(realpath($localRoot) ?: $localRoot, $publicPath)) {
            $this->error('  ✗ Local disk root is inside the public directory! Private files may be web-accessible.');
            $allPassed = false;
        } else {
            $this->line("  ✓ Local disk root is outside the public path (private).");
            $this->line("    Root: {$localRoot}");
        }

        // ── 3. 'serve' setting on local disk ──────────────────────────────
        $serve = config('filesystems.disks.local.serve', false);

        if ($serve) {
            $this->warn("  ⚠ Local disk has 'serve: true'. Storage::url() can expose private files. Set to false.");
        } else {
            $this->line("  ✓ Local disk 'serve' is false — Storage::url() disabled for private files.");
        }

        // ── 4. Storage directory writability ──────────────────────────────
        $workspacesDir = storage_path('app/private/workspaces');

        if (! is_dir($workspacesDir)) {
            // Attempt to create it
            if (@mkdir($workspacesDir, 0755, true)) {
                $this->line("  ✓ Created workspace storage directory: {$workspacesDir}");
            } else {
                $this->error("  ✗ Could not create storage directory: {$workspacesDir}");
                $allPassed = false;
            }
        } elseif (! is_writable($workspacesDir)) {
            $this->error("  ✗ Storage directory exists but is not writable: {$workspacesDir}");
            $allPassed = false;
        } else {
            $this->line("  ✓ Workspace storage directory exists and is writable.");
        }

        // ── 5. PHP upload limits (informational) ──────────────────────────
        $uploadMax = ini_get('upload_max_filesize') ?: 'unknown';
        $postMax   = ini_get('post_max_size') ?: 'unknown';

        $this->newLine();
        $this->line("PHP upload_max_filesize : {$uploadMax}");
        $this->line("PHP post_max_size       : {$postMax}");
        $this->line("GVOS file limit         : 10 MB (hardcoded — mimes whitelist enforced in controller)");

        // ── 6. Public symlink status ──────────────────────────────────────
        $symlinkPath = public_path('storage');

        $this->newLine();
        if (is_link($symlinkPath)) {
            $target = @readlink($symlinkPath) ?: '(unresolvable)';
            $this->line("  ✓ Public symlink exists: public/storage → {$target}");

            // Warn if symlink target accidentally points into the private storage area
            if (str_contains(strtolower($target), 'private')) {
                $this->error('  ✗ Symlink points into a directory named "private" — review immediately.');
                $allPassed = false;
            }
        } elseif (is_dir($symlinkPath)) {
            $this->warn("  ⚠ public/storage is a real directory, not a symlink. Run 'php artisan storage:link'.");
        } else {
            $this->warn("  ⚠ No public/storage symlink. Run 'php artisan storage:link' if public file serving is needed.");
        }

        // ── 7. Write / delete round-trip test ─────────────────────────────
        $this->newLine();
        try {
            $testPath = '_gvos_storage_check_' . time() . '.txt';
            Storage::disk('local')->put($testPath, 'GVOS Phase 20 storage check — safe to delete.');

            if (Storage::disk('local')->exists($testPath)) {
                Storage::disk('local')->delete($testPath);
                $this->line("  ✓ Write/delete round-trip test passed on local disk.");
            } else {
                $this->error('  ✗ File not found after put() — storage may not be writable.');
                $allPassed = false;
            }
        } catch (\Throwable $e) {
            $this->error('  ✗ Write/delete test threw an exception: ' . $e->getMessage());
            $allPassed = false;
        }

        // ── Summary ───────────────────────────────────────────────────────
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════');

        if ($allPassed) {
            $this->info('✓ All checks passed. Storage is healthy.');
        } else {
            $this->error('✗ One or more checks failed. Review the output above before deploying.');
        }

        return $allPassed ? self::SUCCESS : self::FAILURE;
    }
}
