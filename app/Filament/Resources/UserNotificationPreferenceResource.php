<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserNotificationPreferenceResource\Pages;
use App\Models\UserNotificationPreference;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserNotificationPreferenceResource extends Resource
{
    protected static ?string $model = UserNotificationPreference::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Communications';

    protected static ?string $navigationLabel = 'Notification Preferences';

    protected static ?string $modelLabel = 'Notification Preference';

    protected static ?int $navigationSort = 1;

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

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('notification_key')
                    ->label('Notification')
                    ->formatStateUsing(fn (string $state): string =>
                        UserNotificationPreference::definition($state)['label'] ?? ucfirst(str_replace('_', ' ', $state))
                    )
                    ->searchable()
                    ->sortable(),
                IconColumn::make('in_app_enabled')
                    ->boolean()
                    ->label('In app'),
                IconColumn::make('email_enabled')
                    ->boolean()
                    ->label('Email'),
                TextColumn::make('updated_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('notification_key')
                    ->label('Notification')
                    ->options(collect(UserNotificationPreference::DEFINITIONS)
                        ->mapWithKeys(fn (array $definition, string $key): array => [$key => $definition['label']])
                        ->toArray()),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserNotificationPreferences::route('/'),
        ];
    }
}
