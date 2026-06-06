<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceVaultAccessLogResource\Pages;
use App\Models\WorkspaceVaultAccessLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WorkspaceVaultAccessLogResource extends Resource
{
    protected static ?string $model = WorkspaceVaultAccessLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Vault Access Logs';

    protected static ?string $modelLabel = 'Vault Access Log';

    protected static ?int $navigationSort = 10;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vaultItem.title')
                    ->label('Vault Item')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('workspace.workspace_code')
                    ->label('Workspace')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->placeholder('System'),

                TextColumn::make('action')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WorkspaceVaultAccessLog::actionLabels()[$state] ?? ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'revealed_secret', 'copied_secret' => 'warning',
                        'archived' => 'gray',
                        'restored' => 'success',
                        default => 'info',
                    }),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('workspace_id')
                    ->label('Workspace')
                    ->relationship('workspace', 'name')
                    ->searchable(),

                SelectFilter::make('action')
                    ->options(WorkspaceVaultAccessLog::actionLabels()),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkspaceVaultAccessLogs::route('/'),
        ];
    }
}
