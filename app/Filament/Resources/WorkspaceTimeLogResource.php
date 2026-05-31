<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceTimeLogResource\Pages;
use App\Models\WorkspaceTimeLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WorkspaceTimeLogResource extends Resource
{
    protected static ?string $model = WorkspaceTimeLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Time Logs';

    protected static ?string $modelLabel = 'Time Log';

    protected static ?int $navigationSort = 7;

    // ── Access control ────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }

    public static function canCreate(): bool
    {
        return false; // Created through the portal
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
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
                    ->label('Logged by')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('log_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('work_summary')
                    ->label('Summary')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('duration_minutes')
                    ->label('Duration (min)')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft'     => 'gray',
                        'submitted' => 'info',
                        'reviewed'  => 'warning',
                        'approved'  => 'success',
                        'rejected'  => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('visibility')
                    ->label('Visibility')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'internal'       => 'gray',
                        'client_summary' => 'info',
                        default          => 'gray',
                    }),

                TextColumn::make('reviewedBy.name')
                    ->label('Reviewed by')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(WorkspaceTimeLog::statusLabels()),

                SelectFilter::make('visibility')
                    ->options(WorkspaceTimeLog::visibilityLabels()),
            ])
            ->defaultSort('log_date', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ── Form (for view/edit in admin) ─────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkspaceTimeLogs::route('/'),
        ];
    }
}
