<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadRequestResource\Pages;
use App\Models\ClientProfile;
use App\Models\LeadRequest;
use App\Models\Trial;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LeadRequestResource extends Resource
{
    protected static ?string $model = LeadRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Leads & Trials';

    protected static ?string $navigationLabel = 'Lead Requests';

    protected static ?string $modelLabel = 'Lead Request';

    protected static ?int $navigationSort = 1;

    // ── Status helpers ────────────────────────────────────────────────────

    public static function statusOptions(): array
    {
        return [
            'new'             => 'New',
            'price_estimated' => 'Price Estimated',
            'price_accepted'  => 'Price Accepted',
            'under_review'    => 'Under Review',
            'trial_approved'  => 'Trial Approved',
            'trial_active'    => 'Trial Active',
            'trial_completed' => 'Trial Completed',
            'payment_pending' => 'Payment Pending',
            'converted'       => 'Converted',
            'lost'            => 'Lost',
            'disqualified'    => 'Disqualified',
        ];
    }

    public static function statusColor(string $state): string
    {
        return match ($state) {
            'new'             => 'gray',
            'price_estimated' => 'info',
            'price_accepted'  => 'info',
            'under_review'    => 'warning',
            'trial_approved'  => 'success',
            'trial_active'    => 'success',
            'trial_completed' => 'success',
            'payment_pending' => 'warning',
            'converted'       => 'success',
            'lost'            => 'danger',
            'disqualified'    => 'danger',
            default           => 'gray',
        };
    }

    // ── Navigation badge: show count of 'new' leads ───────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = LeadRequest::where('status', 'new')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    // ── Global search ─────────────────────────────────────────────────────

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'email', 'company_name'];
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

            Forms\Components\Section::make('Personal Details')
                ->columns(2)
                ->schema([
                    TextInput::make('first_name')->required()->maxLength(100),
                    TextInput::make('last_name')->required()->maxLength(100),
                    TextInput::make('email')->email()->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('phone')->tel()->maxLength(50)->nullable(),
                    TextInput::make('country')->maxLength(100)->nullable(),
                    TextInput::make('city')->maxLength(100)->nullable(),
                    Select::make('timezone')
                        ->options([
                            'Africa/Lagos'        => 'Africa/Lagos (WAT)',
                            'UTC'                 => 'UTC',
                            'Europe/London'       => 'Europe/London (GMT/BST)',
                            'Europe/Paris'        => 'Europe/Paris (CET/CEST)',
                            'Europe/Berlin'       => 'Europe/Berlin (CET/CEST)',
                            'America/New_York'    => 'America/New_York (EST/EDT)',
                            'America/Chicago'     => 'America/Chicago (CST/CDT)',
                            'America/Denver'      => 'America/Denver (MST/MDT)',
                            'America/Los_Angeles' => 'America/Los_Angeles (PST/PDT)',
                            'America/Toronto'     => 'America/Toronto (EST/EDT)',
                            'America/Vancouver'   => 'America/Vancouver (PST/PDT)',
                        ])
                        ->searchable()
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Account Type')
                ->columns(2)
                ->schema([
                    Select::make('client_type')
                        ->options(['individual' => 'Individual', 'business' => 'Business'])
                        ->default('individual')
                        ->required(),
                    TextInput::make('company_name')->maxLength(255)->nullable(),
                    TextInput::make('company_website')->url()->maxLength(255)->nullable(),
                    TextInput::make('company_email_domain')->maxLength(255)->nullable(),
                    TextInput::make('lead_code')
                        ->label('Lead Code')
                        ->helperText('Optional internal reference, e.g. GVL-001')
                        ->maxLength(50)
                        ->nullable()
                        ->unique(LeadRequest::class, 'lead_code', ignoreRecord: true),
                ]),

            Forms\Components\Section::make('Service Requirements')
                ->columns(2)
                ->schema([
                    Select::make('role_needed')
                        ->options(LeadRequest::roleLabels())
                        ->nullable(),
                    TextInput::make('role_needed_other')
                        ->label('Role (if other)')
                        ->maxLength(255)
                        ->nullable(),
                    TextInput::make('estimated_hours_per_week')
                        ->label('Est. Hours/Week')
                        ->numeric()
                        ->minValue(1)->maxValue(168)
                        ->nullable(),
                    DatePicker::make('preferred_start_date')->nullable(),
                    TextInput::make('preferred_work_schedule')->maxLength(255)->nullable()->columnSpanFull(),
                    Textarea::make('required_skills')->rows(2)->nullable()->columnSpanFull(),
                    Textarea::make('work_description')->rows(4)->nullable()->columnSpanFull(),
                    TextInput::make('budget_range')->maxLength(100)->nullable(),
                    TextInput::make('source')->maxLength(255)->nullable(),
                ]),

            Forms\Components\Section::make('Status & Admin Notes')
                ->columns(1)
                ->schema([
                    Select::make('status')
                        ->options(fn () => static::statusOptions())
                        ->default('new')
                        ->required(),
                    Textarea::make('admin_notes')
                        ->label('Admin Notes (internal, not visible to lead)')
                        ->rows(4)
                        ->nullable(),
                ]),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead_code')
                    ->label('Code')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('first_name')
                    ->label('Lead')
                    ->formatStateUsing(fn ($state, LeadRequest $record): string =>
                        trim("{$record->first_name} {$record->last_name}")
                    )
                    ->description(fn (LeadRequest $record): string =>
                        $record->email . ($record->company_name ? " · {$record->company_name}" : '')
                    )
                    ->searchable(['first_name', 'last_name', 'email', 'company_name'])
                    ->sortable(),

                TextColumn::make('client_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'business' ? 'info' : 'gray'),

                TextColumn::make('role_needed')
                    ->label('Role')
                    ->formatStateUsing(fn (?string $state): string =>
                        $state ? (LeadRequest::roleLabels()[$state] ?? ucwords(str_replace('_', ' ', $state))) : '—'
                    )
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string =>
                        static::statusOptions()[$state] ?? ucwords(str_replace('_', ' ', $state))
                    )
                    ->color(fn (string $state): string => static::statusColor($state)),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(fn () => static::statusOptions()),

                SelectFilter::make('client_type')
                    ->label('Client Type')
                    ->options(['individual' => 'Individual', 'business' => 'Business']),

                SelectFilter::make('role_needed')
                    ->label('Role Needed')
                    ->options(LeadRequest::roleLabels()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_under_review')
                    ->label('Under Review')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (LeadRequest $record): bool => ! in_array($record->status, [
                        'under_review', 'converted', 'lost', 'disqualified',
                        'trial_approved', 'trial_active', 'trial_completed', 'payment_pending',
                    ]))
                    ->action(function (LeadRequest $record): void {
                        $old = $record->status;
                        $record->update(['status' => 'under_review']);
                        AuditLogger::log('lead_request.status_changed', $record, ['from' => $old, 'to' => 'under_review']);
                        Notification::make()->title('Status updated to Under Review')->success()->send();
                    }),

                Tables\Actions\Action::make('mark_price_estimated')
                    ->label('Price Estimated')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (LeadRequest $record): bool => in_array($record->status, ['new', 'under_review']))
                    ->action(function (LeadRequest $record): void {
                        $old = $record->status;
                        $record->update(['status' => 'price_estimated']);
                        AuditLogger::log('lead_request.status_changed', $record, ['from' => $old, 'to' => 'price_estimated']);
                        Notification::make()->title('Status updated to Price Estimated')->success()->send();
                    }),

                Tables\Actions\Action::make('mark_price_accepted')
                    ->label('Price Accepted')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (LeadRequest $record): bool => $record->status === 'price_estimated')
                    ->action(function (LeadRequest $record): void {
                        $old = $record->status;
                        $record->update(['status' => 'price_accepted']);
                        AuditLogger::log('lead_request.status_changed', $record, ['from' => $old, 'to' => 'price_accepted']);
                        Notification::make()->title('Status updated to Price Accepted')->success()->send();
                    }),

                Tables\Actions\Action::make('approve_trial')
                    ->label('Approve Trial')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (LeadRequest $record): bool => ! in_array($record->status, [
                        'trial_approved', 'trial_active', 'trial_completed',
                        'payment_pending', 'converted', 'lost', 'disqualified',
                    ]))
                    ->form([
                        Forms\Components\Select::make('assigned_talent_user_id')
                            ->label('Assign Talent (optional)')
                            ->options(fn (): array => User::whereHas('talentProfile')
                                ->get()
                                ->mapWithKeys(fn ($u): array => [$u->id => "{$u->name} ({$u->email})"])
                                ->toArray()
                            )
                            ->searchable()
                            ->nullable(),

                        Forms\Components\Select::make('assigned_manager_user_id')
                            ->label('Assign Manager (optional)')
                            ->options(fn (): array => User::whereHas('managerProfile')
                                ->get()
                                ->mapWithKeys(fn ($u): array => [$u->id => "{$u->name} ({$u->email})"])
                                ->toArray()
                            )
                            ->searchable()
                            ->nullable(),

                        Forms\Components\Select::make('price_estimate_id')
                            ->label('Link Price Estimate (optional)')
                            ->options(fn (LeadRequest $record): array =>
                                $record->priceEstimates()
                                    ->orderByDesc('created_at')
                                    ->get()
                                    ->mapWithKeys(fn ($e): array => [
                                        $e->id => "{$e->currency} " . number_format((float) $e->estimated_amount, 2) . "/{$e->billing_cycle} ({$e->status})",
                                    ])
                                    ->toArray()
                            )
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Trial Start (leave blank to set later)')
                            ->nullable(),

                        TextInput::make('trial_duration_hours')
                            ->label('Trial Duration (hours)')
                            ->numeric()
                            ->default(24)
                            ->minValue(1),

                        Textarea::make('trial_notes')
                            ->label('Trial Notes (optional)')
                            ->nullable()
                            ->rows(2),
                    ])
                    ->action(function (LeadRequest $record, array $data): void {
                        // 1. Find or create the active_lead user
                        $user = User::where('email', $record->email)->first();
                        $isNew = false;

                        if (! $user) {
                            $isNew = true;
                            $user  = User::create([
                                'name'     => trim("{$record->first_name} {$record->last_name}"),
                                'email'    => $record->email,
                                'password' => Hash::make(Str::random(20)),
                                'status'   => 'active',
                                'timezone' => $record->timezone ?? 'Africa/Lagos',
                            ]);
                            $user->profile()->create([
                                'first_name'         => $record->first_name,
                                'last_name'          => $record->last_name,
                                'phone'              => $record->phone,
                                'country'            => $record->country,
                                'city'               => $record->city,
                                'onboarding_status'  => 'in_progress',
                            ]);
                        }

                        // 2. Assign active_lead role
                        $user->syncRoles(['active_lead']);

                        // 3. Create client profile stub if missing
                        $user->clientProfile()->firstOrCreate(
                            ['user_id' => $user->id],
                            [
                                'client_type' => $record->client_type === 'business' ? 'business_admin' : 'individual',
                                'status'      => 'pending',
                            ]
                        );

                        // 4. Compute trial end from start + duration
                        $startsAt = $data['starts_at'] ? Carbon::parse($data['starts_at']) : null;
                        $endsAt   = $startsAt
                            ? $startsAt->copy()->addHours((int) ($data['trial_duration_hours'] ?? 24))
                            : null;

                        // 5. Create the trial
                        $trial = Trial::create([
                            'lead_request_id'          => $record->id,
                            'active_lead_user_id'      => $user->id,
                            'assigned_talent_user_id'  => $data['assigned_talent_user_id'] ?? null,
                            'assigned_manager_user_id' => $data['assigned_manager_user_id'] ?? null,
                            'price_estimate_id'        => $data['price_estimate_id'] ?? null,
                            'status'                   => 'approved',
                            'starts_at'                => $startsAt,
                            'ends_at'                  => $endsAt,
                            'trial_duration_hours'     => (int) ($data['trial_duration_hours'] ?? 24),
                            'notes'                    => $data['trial_notes'] ?? null,
                        ]);

                        // 6. Update lead status
                        $old = $record->status;
                        $record->update(['status' => 'trial_approved']);

                        // 7. Audit
                        AuditLogger::log('trial.created', $trial, [
                            'lead_request_id' => $record->id,
                            'user_id'         => $user->id,
                            'user_is_new'     => $isNew,
                        ]);
                        AuditLogger::log('lead_request.status_changed', $record, [
                            'from' => $old,
                            'to'   => 'trial_approved',
                        ]);

                        app(NotificationService::class)->notifyTrialApproved($trial, auth()->user());

                        Notification::make()
                            ->title('Trial approved')
                            ->body($isNew
                                ? "Active Lead account created for {$record->email}. They will need to set a password via password reset."
                                : "Existing account for {$record->email} updated to Active Lead."
                            )
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_lost')
                    ->label('Mark Lost')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (LeadRequest $record): bool => ! in_array($record->status, [
                        'lost', 'disqualified', 'converted',
                    ]))
                    ->action(function (LeadRequest $record): void {
                        $old = $record->status;
                        $record->update(['status' => 'lost']);
                        AuditLogger::log('lead_request.status_changed', $record, ['from' => $old, 'to' => 'lost']);
                        Notification::make()->title('Lead marked as Lost')->warning()->send();
                    }),

                Tables\Actions\Action::make('mark_disqualified')
                    ->label('Disqualify')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (LeadRequest $record): bool => ! in_array($record->status, [
                        'lost', 'disqualified', 'converted',
                    ]))
                    ->action(function (LeadRequest $record): void {
                        $old = $record->status;
                        $record->update(['status' => 'disqualified']);
                        AuditLogger::log('lead_request.status_changed', $record, ['from' => $old, 'to' => 'disqualified']);
                        Notification::make()->title('Lead disqualified')->warning()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeadRequests::route('/'),
            'create' => Pages\CreateLeadRequest::route('/create'),
            'edit'   => Pages\EditLeadRequest::route('/{record}/edit'),
        ];
    }
}
