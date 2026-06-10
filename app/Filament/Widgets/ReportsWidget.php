<?php

namespace App\Filament\Widgets;

use App\Models\WorkspaceWeeklyReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportsWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'Weekly Reports';

    protected function getStats(): array
    {
        $draft       = WorkspaceWeeklyReport::where('status', 'draft')->count();
        $submitted   = WorkspaceWeeklyReport::where('status', 'submitted')->count();
        $approved    = WorkspaceWeeklyReport::where('status', 'approved')->count();
        $publishedWk = WorkspaceWeeklyReport::where('status', 'published')
            ->whereBetween('published_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            Stat::make('Draft Reports', $draft)
                ->description('Awaiting manager review')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color($draft > 0 ? 'warning' : 'success'),

            Stat::make('Submitted Reports', $submitted)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color($submitted > 0 ? 'warning' : 'success'),

            Stat::make('Approved Reports', $approved)
                ->description('Ready to publish')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($approved > 0 ? 'info' : 'gray'),

            Stat::make('Published This Week', $publishedWk)
                ->description('Sent to clients this week')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
        ];
    }
}
