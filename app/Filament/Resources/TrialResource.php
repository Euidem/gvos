<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrialResource\Pages;
use App\Models\Trial;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TrialResource extends Resource
{
    protected static ?string $model = Trial::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Leads & Trials';

    protected static ?string $navigationLabel = 'Trials';

    protected static ?string $modelLabel = 'Trial';

    protected static ?int $navigationSort = 3;

    // ── Status helpers ────────────────────────────────────────────────────

    public static function statusOptions(): array
    {
        return [
            'pending'   => 'Pending',
            'approved'  => 'Approved',
            'active'    => 'Active',
            'completed' => 'Completed',
            'expired'   => 'Expired',
            'cancelled' => 'Cancelled',
            'converted' => 'Converted',
        ];
    }

    public static function statusColor(string $state): string
    {
        return match ($state) {
            'pending'   => 'gray',
            'approved'  => 'info',
            'active'    => 'success',
            'completed' => 'success',
            'expired'   => 'warning',
            'cancelled' => 'danger',
            'converted' => 'success',
            default     => 'gray',
        };
    }

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

            Forms\Components\Section::make('Trial Details')
                ->columns(2)
                ->schema([
                    TextInput::make('trial_code')
                        ->label('Trial Code')
                        ->helperText('Optional internal reference, e.g. TRL-001')
                        ->maxLength(50)
                        ->nullable()
                        ->unique(Trial::class, 'trial_code', ignoreRecord: true),

                    Select::make('lead_request_id')
                        ->label('Lead Request')
                        ->relationship('leadRequest', 'email')
                        ->getOptionLabelFromRecordUsing(fn ($record): string =>
                            "{$record->first_name} {$record->last_name} ({$record->email})"
                        )
                        ->searchable()
                        ->nullable(),

                    Select::make('active_lead_user_id')
                        ->label('Active Lead User')
                        ->relationship('activeLeadUser', 'name')
                        ->getOptionLabelFromRecordUsing(fn ($record): string =>
                            "{$record->name} ({$record->email})"
                        )
                        ->searchable()
                        ->nullable(),

                    Select::make('assigned_talent_user_id')
                        ->label('Assigned Talent')
                        ->relationship('assignedTalent', 'name')
                        ->getOptionLabelFromRecordUsing(fn ($record): string =>
                            "{$record->name} ({$record->email})"
                        )
                        ->searchable()
                        ->nullable(),

                    Select::make('assigned_manager_user_id')
                        ->label('Assigned Manager')
                        ->relationship('assignedManager', 'name')
                        ->getOptionLabelFromRecordUsing(fn ($record): string =>
                            "{$record->name} ({$record->email})"
                        )
                        ->searchable()
                        ->nullable(),

                    Select::make('price_estimate_id')
                        ->label('Price Estimate')
                        ->relationship('priceEstimate', 'id')
                        ->getOptionLabelFromRecordUsing(fn ($record): string =>
                            "{$record->currency} " . number_format((float) $record->estimated_amount, 2) . "/{$record->billing_cycle} ({$record->status})"
                        )
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Schedule & Configuration')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(fn () => static::statusOptions())
                        ->default('pending')
                        ->required(),

                    TextInput::make('trial_duration_hours')
                        ->label('Duration (hours)')
                        ->numeric()
                        ->default(24)
                        ->minValue(1),

                    TextInput::make('trial_task_limit')
                        ->label('Task Limit')
                        ->numeric()
                        ->default(3)
                        ->minValue(1),

                    TextInput::make('trial_file_limit_mb')
                        ->label('File Limit (MB)')
                        ->numeric()
                        ->default(100)
                        ->minValue(1),

                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Starts At')
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Ends At')
                        ->nullable(),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trial_code')
                    ->label('Code')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),

                TextColumn::make('leadRequest.email')
                    ->label('Lead')
                    ->description(fn (Trial $record): string =>
                        $record->leadRequest
                            ? trim("{$record->leadRequest->first_name} {$record->leadRequest->last_name}")
                            : '—'
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('activeLeadUser.name')
                    ->label('Lead User')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('assignedTalent.name')
                    ->label('Talent')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string =>
                        static::statusOptions()[$state] ?? ucfirst($state)
                    )
                    ->color(fn (string $state): string => static::statusColor($state)),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
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
                    ->options(fn () => static::statusOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('start_trial')
                    ->label('Start Trial')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Trial $record): bool => $record->status === 'approved')
                    ->action(function (Trial $record): void {
                        $startsAt = now();
                        $endsAt   = $startsAt->copy()->addHours($record->trial_duration_hours);

                        $record->update([
                            'status'    => 'active',
                            'starts_at' => $startsAt,
                            'ends_at'   => $endsAt,
                        ]);

                        // Update lead status
                        if ($record->leadRequest) {
                            $old = $record->leadRequest->status;
                            $record->leadRequest->update(['status' => 'trial_active']);
                            AuditLogger::log('lead_request.status_changed', $record->leadRequest, [
                                'from' => $old, 'to' => 'trial_active',
                            ]);
                        }

                        AuditLogger::log('trial.started', $record, [
                            'starts_at'            => $startsAt->toDateTimeString(),
                            'ends_at'              => $endsAt->toDateTimeString(),
                            'trial_duration_hours' => $record->trial_duration_hours,
                        ]);
                        Notification::make()->title('Trial started — ends at ' . $endsAt->format('d M Y H:i'))->success()->send();
                    }),

                Tables\Actions\Action::make('complete_trial')
                    ->label('Complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Trial $record): bool => $record->status === 'active')
                    ->action(function (Trial $record): void {
                        $record->update(['status' => 'completed', 'ends_at' => now()]);

                        if ($record->leadRequest) {
                            $old = $record->leadRequest->status;
                            $record->leadRequest->update(['status' => 'trial_completed']);
                            AuditLogger::log('lead_request.status_changed', $record->leadRequest, [
                                'from' => $old, 'to' => 'trial_completed',
                            ]);
                        }

                        AuditLogger::log('trial.completed', $record, ['completed_at' => now()->toDateTimeString()]);
                        Notification::make()->title('Trial marked as Completed')->success()->send();
                    }),

                Tables\Actions\Action::make('expire_trial')
                    ->label('Expire')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Trial $record): bool => in_array($record->status, ['approved', 'active']))
                    ->action(function (Trial $record): void {
                        $record->update(['status' => 'expired']);
                        AuditLogger::log('trial.updated', $record, ['from' => $record->getOriginal('status'), 'to' => 'expired']);
                        Notification::make()->title('Trial marked as Expired')->warning()->send();
                    }),

                Tables\Actions\Action::make('cancel_trial')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Trial $record): bool => in_array($record->status, ['pending', 'approved', 'active']))
                    ->action(function (Trial $record): void {
                        $record->update(['status' => 'cancelled']);
                        AuditLogger::log('trial.cancelled', $record, ['cancelled_at' => now()->toDateTimeString()]);
                        Notification::make()->title('Trial cancelled')->danger()->send();
                    }),

                Tables\Actions\Action::make('payment_pending')
                    ->label('Payment Pending')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Trial $record): bool => $record->status === 'completed')
                    ->action(function (Trial $record): void {
                        if ($record->leadRequest) {
                            $old = $record->leadRequest->status;
                            $record->leadRequest->update(['status' => 'payment_pending']);
                            AuditLogger::log('lead_request.status_changed', $record->leadRequest, [
                                'from' => $old, 'to' => 'payment_pending',
                            ]);
                        }
                        AuditLogger::log('trial.payment_pending', $record, [
                            'lead_request_id' => $record->lead_request_id,
                        ]);
                        Notification::make()->title('Lead status updated to Payment Pending')->success()->send();
                    }),

                Tables\Actions\Action::make('create_workspace')
                    ->label('Create Workspace')
                    ->icon('heroicon-o-rectangle-stack')
                    ->color('indigo')
                    ->requiresConfirmation()
                    ->modalHeading('Create Trial Workspace')
                    ->modalDescription('This will create a workspace for the trial and add the lead, talent, and manager as members.')
                    ->visible(fn (Trial $record): bool =>
                        in_array($record->status, ['approved', 'active', 'completed']) &&
                        ! $record->workspace()->exists()
                    )
                    ->action(function (Trial $record): void {
                        $code = Workspace::generateCode();
                        $leadRequest = $record->leadRequest;

                        $name = $leadRequest
                            ? trim("{$leadRequest->first_name} {$leadRequest->last_name}") . ' Trial Workspace'
                            : "Trial {$record->trial_code} Workspace";

                        $workspace = Workspace::create([
                            'workspace_code'      => $code,
                            'name'                => $name,
                            'lead_request_id'     => $record->lead_request_id,
                            'trial_id'            => $record->id,
                            'primary_manager_id'  => $record->assigned_manager_user_id,
                            'primary_talent_id'   => $record->assigned_talent_user_id,
                            'status'              => $record->status === 'active' ? 'active' : 'pending',
                            'type'                => 'trial',
                            'starts_at'           => $record->starts_at,
                            'ends_at'             => $record->ends_at,
                            'task_limit'          => $record->trial_task_limit,
                            'file_limit_mb'       => $record->trial_file_limit_mb,
                            'notes'               => "Auto-created from trial {$record->trial_code}.",
                        ]);

                        // Add members
                        if ($record->active_lead_user_id) {
                            WorkspaceMember::create([
                                'workspace_id' => $workspace->id,
                                'user_id'      => $record->active_lead_user_id,
                                'role'         => 'client',
                                'status'       => 'active',
                                'joined_at'    => now(),
                            ]);
                        }
                        if ($record->assigned_talent_user_id) {
                            WorkspaceMember::create([
                                'workspace_id' => $workspace->id,
                                'user_id'      => $record->assigned_talent_user_id,
                                'role'         => 'talent',
                                'status'       => 'active',
                                'joined_at'    => now(),
                            ]);
                        }
                        if ($record->assigned_manager_user_id) {
                            WorkspaceMember::create([
                                'workspace_id' => $workspace->id,
                                'user_id'      => $record->assigned_manager_user_id,
                                'role'         => 'manager',
                                'status'       => 'active',
                                'joined_at'    => now(),
                            ]);
                        }

                        AuditLogger::trialWorkspaceCreated($record, $workspace);
                        Notification::make()->title("Workspace {$code} created successfully")->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTrials::route('/'),
            'create' => Pages\CreateTrial::route('/create'),
            'edit'   => Pages\EditTrial::route('/{record}/edit'),
        ];
    }
}
