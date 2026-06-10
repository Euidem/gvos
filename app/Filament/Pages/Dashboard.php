<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Command Center';

    public function getHeading(): string
    {
        return 'GVOS Command Center';
    }

    public function getSubheading(): ?string
    {
        return 'Monitor clients, workspaces, billing, reports and operations from one place.';
    }
}
