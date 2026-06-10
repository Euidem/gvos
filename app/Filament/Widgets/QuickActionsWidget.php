<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 2;
    protected static ?string $pollingInterval = null;

    public function getActions(): array
    {
        return [
            [
                'label' => 'Create Workspace',
                'icon'  => 'heroicon-o-rectangle-stack',
                'url'   => route('filament.admin.resources.workspaces.create'),
                'color' => 'primary',
            ],
            [
                'label' => 'Add User',
                'icon'  => 'heroicon-o-user-plus',
                'url'   => route('filament.admin.resources.users.create'),
                'color' => 'primary',
            ],
            [
                'label' => 'Create Invoice',
                'icon'  => 'heroicon-o-document-plus',
                'url'   => route('filament.admin.resources.invoices.create'),
                'color' => 'warning',
            ],
            [
                'label' => 'Record Payment',
                'icon'  => 'heroicon-o-banknotes',
                'url'   => route('filament.admin.resources.payments.create'),
                'color' => 'success',
            ],
            [
                'label' => 'Overdue Billing',
                'icon'  => 'heroicon-o-exclamation-circle',
                'url'   => route('filament.admin.resources.invoices.index') . '?tableFilters[status][value]=overdue',
                'color' => 'danger',
            ],
            [
                'label' => 'Running Timers',
                'icon'  => 'heroicon-o-play-circle',
                'url'   => route('filament.admin.resources.workspace-time-logs.index') . '?tableFilters[status][value]=running',
                'color' => 'warning',
            ],
            [
                'label' => 'Pending Reports',
                'icon'  => 'heroicon-o-document-text',
                'url'   => route('filament.admin.resources.workspace-weekly-reports.index') . '?tableFilters[status][value]=draft',
                'color' => 'info',
            ],
            [
                'label' => 'Pending Invitations',
                'icon'  => 'heroicon-o-envelope',
                'url'   => route('filament.admin.resources.workspaces.index'),
                'color' => 'info',
            ],
            [
                'label' => 'Vault Items',
                'icon'  => 'heroicon-o-key',
                'url'   => route('filament.admin.resources.workspace-vault-items.index'),
                'color' => 'gray',
            ],
            [
                'label' => 'Mail Test',
                'icon'  => 'heroicon-o-envelope-open',
                'url'   => route('filament.admin.pages.mail-test'),
                'color' => 'gray',
            ],
        ];
    }
}
