<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceSubscriptionResource\Pages;
use App\Models\BillingPlan;
use App\Models\WorkspaceSubscription;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WorkspaceSubscriptionResource extends Resource
{
    protected static ?string $model = WorkspaceSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Subscriptions';

    protected static ?string $modelLabel = 'Subscription';

    protected static ?int $navigationSort = 2;

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
            Forms\Components\Select::make('workspace_id')
                ->relationship('workspace', 'name')
                ->searchable()->required(),
            Forms\Components\Select::make('billing_plan_id')
                ->relationship('billingPlan', 'name')
                ->searchable()->nullable(),
            Forms\Components\Select::make('client_profile_id')
                ->relationship('clientProfile', 'id')
                ->label('Client Profile')
                ->searchable()->nullable(),
            Forms\Components\Select::make('company_id')
                ->relationship('company', 'name')
                ->searchable()->nullable(),
            Forms\Components\Select::make('currency')
                ->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'NGN' => 'NGN', 'CAD' => 'CAD'])
                ->default('USD')->required(),
            Forms\Components\TextInput::make('amount')
                ->numeric()->minValue(0)->required(),
            Forms\Components\Select::make('billing_cycle')
                ->options(BillingPlan::cycleLabels())
                ->default('bi_weekly')->required(),
            Forms\Components\Select::make('status')
                ->options(WorkspaceSubscription::statusLabels())
                ->default('trial')->required(),
            Forms\Components\DatePicker::make('starts_at'),
            Forms\Components\DatePicker::make('next_billing_date'),
            Forms\Components\DatePicker::make('ends_at'),
            Forms\Components\DateTimePicker::make('grace_ends_at'),
            Forms\Components\Textarea::make('notes')->rows(2),
            // Phase 18 enforcement fields
            Forms\Components\DateTimePicker::make('restricted_at')->nullable(),
            Forms\Components\DateTimePicker::make('suspended_at')->nullable(),
            Forms\Components\DateTimePicker::make('reactivated_at')->nullable(),
            Forms\Components\Textarea::make('restriction_reason')->rows(2)->nullable()->label('Restriction / suspension reason'),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('workspace.name')->searchable()->sortable(),
                TextColumn::make('workspace.workspace_code')
                    ->label('Code')->searchable()->toggleable(),
                TextColumn::make('billingPlan.name')
                    ->label('Plan')->searchable()->toggleable(),
                TextColumn::make('currency'),
                TextColumn::make('amount')->money(fn ($record) => $record->currency)->sortable(),
                TextColumn::make('billing_cycle')
                    ->formatStateUsing(fn ($state) => BillingPlan::cycleLabels()[$state] ?? $state),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active'      => 'success',
                        'trial'       => 'info',
                        'payment_due' => 'warning',
                        'overdue'     => 'danger',
                        'suspended'   => 'gray',
                        default       => 'gray',
                    }),
                TextColumn::make('next_billing_date')->date('d M Y')->sortable(),
                TextColumn::make('last_paid_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
                // Phase 18 — enforcement columns
                IconColumn::make('restricted_at')
                    ->label('Restricted')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isRestricted())
                    ->trueIcon('heroicon-o-lock-closed')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('suspended_at')
                    ->label('Suspended')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isSuspended())
                    ->trueIcon('heroicon-o-no-symbol')
                    ->trueColor('gray')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('grace_ends_at')->label('Grace ends')->date('d M Y')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reactivated_at')->label('Reactivated')->dateTime('d M Y')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('workspace_id')
                    ->label('Workspace')
                    ->relationship('workspace', 'name')
                    ->searchable(),
                SelectFilter::make('status')->options(WorkspaceSubscription::statusLabels()),
                SelectFilter::make('currency')->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'NGN' => 'NGN', 'CAD' => 'CAD']),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),

                // ── Phase 18: Restrict client access ─────────────────────────
                Action::make('restrict')
                    ->label('Restrict')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Restrict client workspace access?')
                    ->modalDescription('This will mark the subscription as restricted and block client access to workspace features. Internal staff are not affected. Send a notification to workspace members?')
                    ->form([
                        Forms\Components\Checkbox::make('notify_members')
                            ->label('Send notification to internal staff')
                            ->default(true),
                    ])
                    ->visible(fn (WorkspaceSubscription $record): bool =>
                        ! $record->isRestricted() && ! $record->isSuspended()
                    )
                    ->action(function (WorkspaceSubscription $record, array $data): void {
                        $record->update([
                            'restricted_at' => now(),
                        ]);
                        AuditLogger::billingSubscriptionRestricted($record->fresh());
                        if ($data['notify_members'] ?? true) {
                            app(NotificationService::class)->notifyWorkspaceRestricted($record->fresh());
                        }
                        FilamentNotification::make()
                            ->title('Client access restricted')
                            ->success()
                            ->send();
                    }),

                // ── Phase 18: Suspend workspace ───────────────────────────────
                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Suspend this workspace?')
                    ->modalDescription('This will manually suspend the workspace. Internal staff and clients lose access. This cannot be auto-cleared by payment — an admin must reactivate manually.')
                    ->form([
                        Forms\Components\Textarea::make('restriction_reason')
                            ->label('Reason / admin note (visible to members)')
                            ->rows(2)
                            ->nullable(),
                        Forms\Components\Checkbox::make('notify_members')
                            ->label('Send suspension notification to workspace members')
                            ->default(true),
                    ])
                    ->visible(fn (WorkspaceSubscription $record): bool =>
                        ! $record->isSuspended()
                    )
                    ->action(function (WorkspaceSubscription $record, array $data): void {
                        $actorId = auth()->id();
                        $record->update([
                            'status'              => 'suspended',
                            'suspended_at'        => now(),
                            'suspended_by'        => $actorId,
                            'restriction_reason'  => $data['restriction_reason'] ?? null,
                        ]);
                        AuditLogger::billingSubscriptionSuspended($record->fresh(), $actorId);
                        if ($data['notify_members'] ?? true) {
                            app(NotificationService::class)->notifyWorkspaceSuspended($record->fresh());
                        }
                        FilamentNotification::make()
                            ->title('Workspace suspended')
                            ->success()
                            ->send();
                    }),

                // ── Phase 18: Reactivate workspace ────────────────────────────
                Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Reactivate workspace access?')
                    ->modalDescription('This will clear any restriction or suspension and restore the subscription to active. Notify workspace members?')
                    ->form([
                        Forms\Components\Checkbox::make('notify_members')
                            ->label('Send reactivation notification to workspace members')
                            ->default(true),
                    ])
                    ->visible(fn (WorkspaceSubscription $record): bool =>
                        $record->isRestricted() || $record->isSuspended()
                    )
                    ->action(function (WorkspaceSubscription $record, array $data): void {
                        $actorId = auth()->id();
                        $record->update([
                            'status'             => 'active',
                            'restricted_at'      => null,
                            'suspended_at'       => null,
                            'suspended_by'       => null,
                            'restriction_reason' => null,
                            'reactivated_at'     => now(),
                            'reactivated_by'     => $actorId,
                        ]);
                        AuditLogger::billingSubscriptionReactivated($record->fresh(), $actorId);
                        if ($data['notify_members'] ?? true) {
                            app(NotificationService::class)->notifyWorkspaceReactivated($record->fresh());
                        }
                        FilamentNotification::make()
                            ->title('Workspace reactivated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkspaceSubscriptions::route('/'),
            'create' => Pages\CreateWorkspaceSubscription::route('/create'),
            'edit'   => Pages\EditWorkspaceSubscription::route('/{record}/edit'),
        ];
    }
}
