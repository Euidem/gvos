<?php

namespace App\Filament\Resources\WorkspaceResource\Pages;

use App\Filament\Resources\WorkspaceResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkspace extends EditRecord
{
    protected static string $resource = WorkspaceResource::class;

    protected array $beforeSnapshot = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Capture snapshot before any edits for change-tracking in afterSave.
        $this->beforeSnapshot = $this->record->toArray();
        return $data;
    }

    protected function afterSave(): void
    {
        $after  = $this->record->fresh()->toArray();
        $before = $this->beforeSnapshot;

        // ── Log general field changes ────────────────────────────────────
        $changes = [];
        foreach (['name', 'status', 'type', 'description', 'starts_at', 'ends_at', 'task_limit', 'file_limit_mb', 'notes'] as $field) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changes[$field] = ['from' => $before[$field] ?? null, 'to' => $after[$field] ?? null];
            }
        }

        if (! empty($changes)) {
            AuditLogger::workspaceUpdated($this->record, $changes);
        }

        // ── Auto-sync primary team to member rows ────────────────────────
        // Run sync whenever primary_manager_id or primary_talent_id was changed
        // OR whenever either field is set (so saving without changing still fixes
        // missing member rows, e.g. workspaces created before this sync logic).
        $primaryChanged =
            ($before['primary_manager_id'] ?? null) !== ($after['primary_manager_id'] ?? null)
            || ($before['primary_talent_id'] ?? null) !== ($after['primary_talent_id'] ?? null);

        $hasPrimary = ! empty($after['primary_manager_id']) || ! empty($after['primary_talent_id']);

        if ($hasPrimary) {
            $syncResult = $this->record->syncPrimaryTeamToMembers();

            // Only log if something actually changed (added or reactivated rows).
            $actionCount = count($syncResult['added']) + count($syncResult['reactivated']);
            if ($actionCount > 0 || $primaryChanged) {
                AuditLogger::workspacePrimaryTeamSynced($this->record, $syncResult);

                // Log individual member audit events for added rows.
                foreach ($syncResult['added'] as $entry) {
                    $member = $this->record->members()->where('user_id', $entry['user_id'])->first();
                    if ($member) {
                        AuditLogger::workspaceMemberAdded($this->record, $member, ['source' => 'primary_team_sync']);
                    }
                }

                // Log individual member audit events for reactivated rows.
                foreach ($syncResult['reactivated'] as $entry) {
                    $member = $this->record->members()->where('user_id', $entry['user_id'])->first();
                    if ($member) {
                        AuditLogger::workspaceMemberUpdated($this->record, $member, ['source' => 'primary_team_sync', 'action' => 'reactivated']);
                    }
                }
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_primary_team')
                ->label('Sync Primary Team')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Sync Primary Team Members')
                ->modalDescription('This will create or reactivate workspace member rows for the primary manager and primary talent. Existing active member rows are not affected.')
                ->modalSubmitActionLabel('Sync Now')
                ->action(function (): void {
                    $record     = $this->record->fresh();
                    $syncResult = $record->syncPrimaryTeamToMembers();
                    AuditLogger::workspacePrimaryTeamSynced($record, $syncResult);

                    foreach ($syncResult['added'] as $entry) {
                        $member = $record->members()->where('user_id', $entry['user_id'])->first();
                        if ($member) {
                            AuditLogger::workspaceMemberAdded($record, $member, ['source' => 'manual_sync_edit_page']);
                        }
                    }
                    foreach ($syncResult['reactivated'] as $entry) {
                        $member = $record->members()->where('user_id', $entry['user_id'])->first();
                        if ($member) {
                            AuditLogger::workspaceMemberUpdated($record, $member, ['source' => 'manual_sync_edit_page', 'action' => 'reactivated']);
                        }
                    }

                    $added       = count($syncResult['added']);
                    $reactivated = count($syncResult['reactivated']);
                    $skipped     = count($syncResult['skipped']);

                    \Filament\Notifications\Notification::make()
                        ->title('Primary team synced')
                        ->body("Added: {$added} · Reactivated: {$reactivated} · Already active: {$skipped}")
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make()->hidden(),
        ];
    }
}
