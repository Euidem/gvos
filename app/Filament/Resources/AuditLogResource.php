<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon   = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup  = 'Security';
    protected static ?string $navigationLabel  = 'Audit Logs';
    protected static ?int    $navigationSort   = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->searchable(false),

                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Actor')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->formatStateUsing(fn (string $state) => str_replace(['.', '_'], [' › ', ' '], $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('context')
                    ->label('Context')
                    ->getStateUsing(fn ($record) => $record->context ? collect($record->context)
                        ->only(['workspace_code', 'workspace_name', 'status', 'action'])
                        ->filter()
                        ->map(fn ($v, $k) => "{$k}: {$v}")
                        ->join(', ') : null)
                    ->placeholder('—')
                    ->limit(80)
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label('Today Only')
                    ->query(fn ($query) => $query->whereDate('created_at', today())),

                Tables\Filters\SelectFilter::make('action')
                    ->label('Action Contains')
                    ->options(
                        AuditLog::query()
                            ->select('action')
                            ->distinct()
                            ->orderBy('action')
                            ->limit(100)
                            ->pluck('action', 'action')
                            ->toArray()
                    )
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }
}
