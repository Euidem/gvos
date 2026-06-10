<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity';
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 2;
    protected static ?string $pollingInterval = null;

    public function getRecentEvents(): \Illuminate\Support\Collection
    {
        return AuditLog::with('actor')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'action'     => $log->action,
                    'actor'      => $log->actor?->name ?? 'System',
                    'workspace'  => $log->context['workspace_code'] ?? ($log->context['workspace_name'] ?? null),
                    'created_at' => $log->created_at,
                    'context'    => $log->context,
                ];
            });
    }
}
