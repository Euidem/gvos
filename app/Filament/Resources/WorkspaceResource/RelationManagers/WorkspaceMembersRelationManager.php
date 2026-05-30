<?php

namespace App\Filament\Resources\WorkspaceResource\RelationManagers;

use App\Models\WorkspaceMember;
use App\Services\AuditLogger;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkspaceMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Members';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('User')
                ->relationship('user', 'email')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})")
                ->searchable()
                ->preload()
                ->required(),

            Select::make('role')
                ->label('Workspace Role')
                ->options(WorkspaceMember::roleLabels())
                ->default('talent')
                ->required(),

            Select::make('status')
                ->label('Status')
                ->options(['active' => 'Active', 'removed' => 'Removed'])
                ->default('active')
                ->required(),

            Forms\Components\DateTimePicker::make('joined_at')
                ->label('Joined At')
                ->default(now())
                ->nullable(),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(2)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.email')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WorkspaceMember::roleLabels()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'workspace_admin' => 'danger',
                        'manager'         => 'info',
                        'talent'          => 'success',
                        'client_admin'    => 'warning',
                        'client_staff'    => 'warning',
                        'client'          => 'warning',  // legacy
                        'observer'        => 'gray',
                        default           => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger'),

                TextColumn::make('joined_at')
                    ->label('Joined')
                    ->dateTime('d M Y')
                    ->toggleable(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Member')
                    ->after(function ($record): void {
                        AuditLogger::workspaceMemberAdded($record->workspace, $record);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($record): void {
                        AuditLogger::workspaceMemberUpdated($record->workspace, $record);
                    }),

                Tables\Actions\Action::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (WorkspaceMember $record): bool => $record->status === 'active')
                    ->action(function (WorkspaceMember $record): void {
                        $record->update([
                            'status'     => 'removed',
                            'removed_at' => now(),
                        ]);
                        AuditLogger::workspaceMemberRemoved($record->workspace, $record);
                    }),
            ])
            ->bulkActions([]);
    }
}
