<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\WorkspaceSubscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BillingHealthWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'Billing Health';

    protected function getStats(): array
    {
        $activeSubs    = WorkspaceSubscription::where('status', 'active')->count();
        $dueSubs       = WorkspaceSubscription::whereIn('status', ['payment_due', 'overdue'])->count();
        $overdueInv    = Invoice::where('status', 'overdue')->count();
        $restricted    = WorkspaceSubscription::whereNotNull('restricted_at')->whereNull('suspended_at')->count();
        $suspended     = WorkspaceSubscription::where('status', 'suspended')->count();
        $recentPaid    = Payment::where('status', 'confirmed')
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Active Subscriptions', $activeSubs)
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Payment Due / Overdue', $dueSubs)
                ->description('Subscriptions needing payment')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($dueSubs > 0 ? 'warning' : 'success'),

            Stat::make('Overdue Invoices', $overdueInv)
                ->description('Invoices past due date')
                ->descriptionIcon('heroicon-m-document-minus')
                ->color($overdueInv > 0 ? 'danger' : 'success'),

            Stat::make('Restricted Workspaces', $restricted)
                ->description('Client access blocked')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color($restricted > 0 ? 'danger' : 'success'),

            Stat::make('Suspended Workspaces', $suspended)
                ->description('Manually suspended')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color($suspended > 0 ? 'gray' : 'success'),

            Stat::make('Payments Confirmed (7d)', $recentPaid)
                ->description('Last 7 days')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
