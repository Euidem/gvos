<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceFileResource\Pages;
use App\Models\WorkspaceFile;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WorkspaceFileResource extends Resource
{
    protected static ?string $model = WorkspaceFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Files';

    protected static ?string $modelLabel = 'Workspace File';

    protected static ?int $navigationSort = 5;

    // ── Access control ────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }

    public static function canCreate(): bool
    {
        return false; // Files are uploaded via the portal, not Filament
    }

    public static function canEdit(Model $record): bool
    {
        return false; // No edit from Filament; portal is source of truth
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

                TextColumn::make('original_filename')
                    ->label('Filename')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('—'),

                TextColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn (?string $state): string =>
                        WorkspaceFile::categoryLabels()[$state] ?? ucfirst($state ?? 'general')
                    )
                    ->badge()
                    ->color('gray'),

                TextColumn::make('visibility')
                    ->label('Visibility')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'internal' ? 'info' : 'success'),

                TextColumn::make('uploadedBy.name')
                    ->label('Uploaded By')
                    ->searchable(),

                TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn (?int $state): string =>
                        $state ? ($state < 1048576
                            ? number_format($state / 1024, 1) . ' KB'
                            : number_format($state / 1048576, 2) . ' MB')
                        : '—'
                    )
                    ->sortable(),

                TextColumn::make('downloads_count')
                    ->label('Downloads')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('visibility')
                    ->options(['public' => 'Public', 'internal' => 'Internal']),

                SelectFilter::make('category')
                    ->options(WorkspaceFile::categoryLabels()),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (WorkspaceFile $record) => $record->delete()),
            ])
            ->bulkActions([]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // No create/edit form needed
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkspaceFiles::route('/'),
        ];
    }
}
