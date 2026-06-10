<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use App\Models\WorkspaceFile;
use App\Models\WorkspaceVaultAccessLog;
use App\Models\WorkspaceVaultItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SecurityVaultWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'Security and Vault';

    protected function getStats(): array
    {
        $vaultItems      = WorkspaceVaultItem::where('status', 'active')->count();
        $revealsToday    = WorkspaceVaultAccessLog::whereDate('created_at', today())
            ->where('action', 'reveal')
            ->count();
        $vaultLogsToday  = WorkspaceVaultAccessLog::whereDate('created_at', today())->count();
        $uploadsToday    = WorkspaceFile::whereDate('created_at', today())->count();
        $auditEventsToday = AuditLog::whereDate('created_at', today())->count();

        return [
            Stat::make('Active Vault Items', $vaultItems)
                ->description('Stored credentials')
                ->descriptionIcon('heroicon-m-key')
                ->color('primary'),

            Stat::make('Secret Reveals Today', $revealsToday)
                ->description('Vault reveals in last 24h')
                ->descriptionIcon('heroicon-m-eye')
                ->color($revealsToday > 20 ? 'danger' : 'gray'),

            Stat::make('Vault Access Logs Today', $vaultLogsToday)
                ->description('All vault activity')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),

            Stat::make('File Uploads Today', $uploadsToday)
                ->description('New workspace files')
                ->descriptionIcon('heroicon-m-paper-clip')
                ->color('gray'),

            Stat::make('Audit Events Today', $auditEventsToday)
                ->description('All tracked actions')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),
        ];
    }
}
