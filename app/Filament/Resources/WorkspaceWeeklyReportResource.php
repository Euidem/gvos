<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceWeeklyReportResource\Pages;
use App\Models\WorkspaceWeeklyReport;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WorkspaceWeeklyReportResource extends Resource
{
    protected static ?string $model = WorkspaceWeeklyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Weekly Reports';

    protected static ?string $modelLabel = 'Weekly Report';

    protected static ?int $navigationSort = 8;

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

                TextColumn::make('week_start_date')
                    ->label('Week Start')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('week_end_date')
                    ->label('Week End')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('total_minutes')
                    ->label('Total Min')
                    ->sortable(),

                TextColumn::make('summary')
                    ->label('Summary')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft'     => 'gray',
                        'submitted' => 'info',
                        'approved'  => 'warning',
                        'published' => 'success',
                        default     => 'gray',
                    }),

                TextColumn::make('preparedBy.name')
                    ->label('Prepared by')
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(WorkspaceWeeklyReport::statusLabels()),
            ])
            ->defaultSort('week_start_date', 'desc')
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
            'index' => Pages\ListWorkspaceWeeklyReports::route('/'),
        ];
    }
}
