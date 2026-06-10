<?php

namespace App\Filament\Widgets;

use App\Models\Workspace;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WorkspaceOperationsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = null;
    protected ?string $heading = 'Workspace Operations';

    protected function getStats(): array
    {
        $active        = Workspace::where('status', 'active')->count();
        $withTimers    = Workspace::where('status', 'active')
            ->whereHas('timeLogs', fn ($q) => $q->where('status', 'running'))
            ->count();
        $withBlocked   = Workspace::where('status', 'active')
            ->whereHas('tasks', fn ($q) => $q->where('status', 'blocked'))
            ->count();
        $withReports   = Workspace::where('status', 'active')
            ->whereHas('weeklyReports', fn ($q) => $q->whereIn('status', ['draft', 'submitted']))
            ->count();
        $noManager     = Workspace::where('status', 'active')->whereNull('primary_manager_id')->count();
        $noTalent      = Workspace::where('status', 'active')->whereNull('primary_talent_id')->count();

        return [
            Stat::make('Active Workspaces', $active)
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Timers Running', $withTimers)
                ->description('Workspaces with live sessions')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color($withTimers > 0 ? 'warning' : 'success'),

            Stat::make('Blocked Tasks', $withBlocked)
                ->description('Workspaces with blocked tasks')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($withBlocked > 0 ? 'danger' : 'success'),

            Stat::make('Pending Reports', $withReports)
                ->description('Draft or submitted reports')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($withReports > 0 ? 'warning' : 'success'),

            Stat::make('No Manager Assigned', $noManager)
                ->description('Active workspaces')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($noManager > 0 ? 'danger' : 'success'),

            Stat::make('No Talent Assigned', $noTalent)
                ->description('Active workspaces')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($noTalent > 0 ? 'danger' : 'success'),
        ];
    }
}
