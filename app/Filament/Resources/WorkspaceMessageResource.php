<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceMessageResource\Pages;
use App\Models\WorkspaceMessage;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WorkspaceMessageResource extends Resource
{
    protected static ?string $model = WorkspaceMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Messages';

    protected static ?string $modelLabel = 'Workspace Message';

    protected static ?int $navigationSort = 5;

    // ── Access control ────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }

    public static function canCreate(): bool
    {
        return false; // Messages are posted via the portal chat
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('workspace.workspace_code')
                    ->label('Workspace')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable(),

                TextColumn::make('message')
                    ->label('Message')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('visibility')
                    ->label('Visibility')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'internal' ? 'info' : 'success'),

                TextColumn::make('message_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'system' ? 'warning' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('visibility')
                    ->options(['public' => 'Public', 'internal' => 'Internal']),

                SelectFilter::make('message_type')
                    ->label('Type')
                    ->options(['text' => 'Text', 'system' => 'System']),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('moderate')
                    ->label('Remove')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (WorkspaceMessage $record) => $record->delete()),
            ])
            ->bulkActions([]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkspaceMessages::route('/'),
        ];
    }
}
