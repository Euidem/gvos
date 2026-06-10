<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailDeliveryLogResource\Pages;
use App\Models\EmailDeliveryLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailDeliveryLogResource extends Resource
{
    protected static ?string $model = EmailDeliveryLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationLabel = 'Mail Delivery Log';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 98;
    protected static ?string $modelLabel = 'Mail Delivery Log';
    protected static ?string $pluralModelLabel = 'Mail Delivery Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notification_key')
                    ->label('Type')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('channel')
                    ->label('Channel')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'mail' ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'success' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('recipientUser.name')
                    ->label('Recipient')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('workspace_id')
                    ->label('Workspace ID')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('channel')
                    ->options([
                        'mail' => 'Mail',
                        'database' => 'Database',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailDeliveryLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
