<?php

namespace App\Filament\Widgets;

use App\Models\ClientProfile;
use App\Models\ManagerProfile;
use App\Models\TalentProfile;
use App\Models\Trial;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'Platform Overview';

    protected function getStats(): array
    {
        $totalUsers  = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $activeWs    = Workspace::where('status', 'active')->count();
        $totalWs     = Workspace::count();
        $clients     = ClientProfile::whereHas('user', fn ($q) => $q->where('status', 'active'))->count();
        $talent      = TalentProfile::whereHas('user', fn ($q) => $q->where('status', 'active'))->count();
        $managers    = ManagerProfile::whereHas('user', fn ($q) => $q->where('status', 'active'))->count();
        $trials      = Trial::where('status', 'active')->count();
        $invitations = WorkspaceInvitation::where('status', 'pending')->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description("{$activeUsers} active")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Active Workspaces', $activeWs)
                ->description("{$totalWs} total")
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success'),

            Stat::make('Active Clients', $clients)
                ->description('Client accounts')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Talent / Managers', "{$talent} / {$managers}")
                ->description('Active team members')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Active Trials', $trials)
                ->description(Trial::whereIn('status', ['pending', 'approved'])->count() . ' pending or approved')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),

            Stat::make('Pending Invitations', $invitations)
                ->description('Awaiting acceptance')
                ->descriptionIcon('heroicon-m-envelope-open')
                ->color($invitations > 0 ? 'warning' : 'success'),
        ];
    }
}
