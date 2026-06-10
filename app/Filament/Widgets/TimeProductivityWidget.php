<?php

namespace App\Filament\Widgets;

use App\Models\WorkspaceTimeLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TimeProductivityWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $pollingInterval = null;
    protected ?string $heading = 'Time and Productivity';

    protected function getStats(): array
    {
        $running      = WorkspaceTimeLog::where('status', 'running')->count();
        $submitted    = WorkspaceTimeLog::where('status', 'submitted')->count();
        $approvedWeek = WorkspaceTimeLog::where('status', 'approved')
            ->whereBetween('reviewed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $minutesWeek  = (int) WorkspaceTimeLog::where('status', 'approved')
            ->whereBetween('reviewed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('duration_minutes');
        $hoursWeek    = round($minutesWeek / 60, 1);

        $longRunning = WorkspaceTimeLog::where('status', 'running')
            ->where('started_at', '<', now()->subHours(10))
            ->count();

        return [
            Stat::make('Running Timers', $running)
                ->description('Active sessions right now')
                ->descriptionIcon('heroicon-m-play')
                ->color($running > 0 ? 'warning' : 'gray'),

            Stat::make('Awaiting Review', $submitted)
                ->description('Submitted time logs')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($submitted > 0 ? 'warning' : 'success'),

            Stat::make('Approved This Week', $approvedWeek)
                ->description('Logs reviewed this week')
                ->descriptionIcon('heroicon-m-check')
                ->color('success'),

            Stat::make('Logged Hours (Week)', "{$hoursWeek}h")
                ->description('Approved time this week')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Long-Running Timers', $longRunning)
                ->description('Sessions over 10 hours')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($longRunning > 0 ? 'danger' : 'success'),
        ];
    }
}
