<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceResource\Pages;
use App\Filament\Resources\WorkspaceResource\RelationManagers\WorkspaceInvitationsRelationManager;
use App\Filament\Resources\WorkspaceResource\RelationManagers\WorkspaceMembersRelationManager;
use App\Models\User;
use App\Models\Workspace;
use App\Services\AuditLogger;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WorkspaceResource extends Resource
{
    protected static ?string $model = Workspace::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Workspaces';

    protected static ?string $modelLabel = 'Workspace';

    protected static ?int $navigationSort = 1;

    // ── Access control ────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    // ── Form ─────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Workspace Identity')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Workspace Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('workspace_code')
                        ->label('Workspace Code')
                        ->maxLength(50)
                        ->helperText('Auto-generated if left blank')
                        ->nullable(),

                    Select::make('type')
                        ->label('Type')
                        ->options(Workspace::typeLabels())
                        ->default('trial')
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options(Workspace::statusLabels())
                        ->default('pending')
                        ->required(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(2)
                        ->nullable()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Linked Records')
                ->columns(2)
                ->schema([
                    Select::make('lead_request_id')
                        ->label('Lead Request')
                        ->relationship('leadRequest', 'email')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('trial_id')
                        ->label('Trial')
                        ->relationship('trial', 'trial_code')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('company_id')
                        ->label('Company')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Team Assignment')
                ->columns(2)
                ->schema([
                    Select::make('primary_manager_id')
                        ->label('Primary Manager')
                        ->options(fn () => User::role('line_manager')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->nullable(),

                    Select::make('primary_talent_id')
                        ->label('Primary Talent')
                        ->options(fn () => User::role('talent')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Dates & Limits')
                ->columns(2)
                ->schema([
                    DateTimePicker::make('starts_at')
                        ->label('Start Date/Time')
                        ->nullable(),

                    DateTimePicker::make('ends_at')
                        ->label('End Date/Time')
                        ->nullable(),

                    TextInput::make('task_limit')
                        ->label('Task Limit')
                        ->numeric()
                        ->default(0)
                        ->helperText('0 = unlimited'),

                    TextInput::make('file_limit_mb')
                        ->label('File Limit (MB)')
                        ->numeric()
                        ->default(0)
                        ->helperText('0 = unlimited'),
                ]),

            Forms\Components\Section::make('Notes')
                ->schema([
                    Textarea::make('notes')
                        ->label('Internal Notes')
                        ->rows(3)
                        ->nullable(),
                ]),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('workspace_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Workspace Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Workspace::typeLabels()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'trial'   => 'warning',
                        'ongoing' => 'success',
                        'project' => 'info',
                        default   => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'pending'   => 'warning',
                        'paused'    => 'info',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('primaryManager.name')
                    ->label('Manager')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('primaryTalent.name')
                    ->label('Talent')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Workspace::statusLabels()),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options(Workspace::typeLabels()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('sync_primary_team')
                    ->label('Sync Team')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->tooltip('Ensure primary manager and talent have active member rows')
                    ->requiresConfirmation()
                    ->modalHeading('Sync Primary Team Members')
                    ->modalDescription('This will create or reactivate workspace member rows for the primary manager and primary talent. Existing active member rows are not affected.')
                    ->modalSubmitActionLabel('Sync Now')
                    ->visible(fn (Workspace $record): bool =>
                        $record->primary_manager_id !== null || $record->primary_talent_id !== null
                    )
                    ->action(function (Workspace $record): void {
                        $syncResult = $record->syncPrimaryTeamToMembers();
                        AuditLogger::workspacePrimaryTeamSynced($record, $syncResult);

                        foreach ($syncResult['added'] as $entry) {
                            $member = $record->members()->where('user_id', $entry['user_id'])->first();
                            if ($member) {
                                AuditLogger::workspaceMemberAdded($record, $member, ['source' => 'manual_sync']);
                            }
                        }
                        foreach ($syncResult['reactivated'] as $entry) {
                            $member = $record->members()->where('user_id', $entry['user_id'])->first();
                            if ($member) {
                                AuditLogger::workspaceMemberUpdated($record, $member, ['source' => 'manual_sync', 'action' => 'reactivated']);
                            }
                        }

                        $added       = count($syncResult['added']);
                        $reactivated = count($syncResult['reactivated']);
                        $skipped     = count($syncResult['skipped']);

                        \Filament\Notifications\Notification::make()
                            ->title('Primary team synced')
                            ->body("Added: {$added} · Reactivated: {$reactivated} · Already active: {$skipped}")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Workspace $record): bool => $record->status === 'pending')
                    ->action(function (Workspace $record): void {
                        $from = $record->status;
                        $record->update([
                            'status'    => 'active',
                            'starts_at' => $record->starts_at ?? now(),
                        ]);
                        AuditLogger::workspaceStatusChanged($record, $from, 'active');
                    }),

                Tables\Actions\Action::make('pause')
                    ->label('Pause')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Workspace $record): bool => $record->status === 'active')
                    ->action(function (Workspace $record): void {
                        $from = $record->status;
                        $record->update(['status' => 'paused']);
                        AuditLogger::workspaceStatusChanged($record, $from, 'paused');
                    }),

                Tables\Actions\Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Workspace $record): bool => in_array($record->status, ['active', 'paused']))
                    ->action(function (Workspace $record): void {
                        $from = $record->status;
                        $record->update([
                            'status' => 'completed',
                            'ends_at' => now(),
                        ]);
                        AuditLogger::workspaceStatusChanged($record, $from, 'completed');
                    }),
            ])
            ->bulkActions([]);
    }

    // ── Relation Managers ─────────────────────────────────────────────────

    public static function getRelationManagers(): array
    {
        return [
            WorkspaceMembersRelationManager::class,
            WorkspaceInvitationsRelationManager::class,
        ];
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkspaces::route('/'),
            'create' => Pages\CreateWorkspace::route('/create'),
            'edit'   => Pages\EditWorkspace::route('/{record}/edit'),
        ];
    }
}
