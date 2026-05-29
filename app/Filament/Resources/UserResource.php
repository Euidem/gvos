<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?int $navigationSort = 1;

    // ── Role label helpers ────────────────────────────────────────────────

    /**
     * Mapping of internal role slugs → human-readable labels.
     * Used in the form dropdown, table column and filters.
     */
    public static function roleLabels(): array
    {
        return [
            'super_admin'           => 'Super Admin',
            'operations_admin'      => 'Operations Admin',
            'line_manager'          => 'Line Manager',
            'talent'                => 'Talent',
            'individual_client'     => 'Individual Client',
            'business_client_admin' => 'Business Client Admin',
            'business_client_staff' => 'Business Client Staff',
            'active_lead'           => 'Active Lead',
        ];
    }

    public static function friendlyRoleName(string $slug): string
    {
        return static::roleLabels()[$slug] ?? ucwords(str_replace('_', ' ', $slug));
    }

    // ── Timezone options ─────────────────────────────────────────────────

    /**
     * Practical timezone list for GVOS users.
     * Default is Africa/Lagos (GetVirtual primary market).
     */
    public static function timezoneOptions(): array
    {
        return [
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
        ];
    }

    // ── Access control ────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function canDelete(Model $record): bool
    {
        // Deletion disabled — use status changes (suspend/deactivate) instead.
        return false;
    }

    // ── Form ─────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Name section ─────────────────────────────────────────────
            Forms\Components\Section::make('Name')
                ->columns(2)
                ->schema([
                    TextInput::make('first_name')
                        ->label('First Name')
                        ->maxLength(100)
                        ->nullable(),

                    TextInput::make('last_name')
                        ->label('Last Name')
                        ->maxLength(100)
                        ->nullable(),

                    TextInput::make('name')
                        ->label('Display Name')
                        ->helperText('Auto-filled from first + last name if left blank.')
                        ->maxLength(255)
                        ->nullable()
                        ->columnSpanFull(),
                ]),

            // ── Account credentials ──────────────────────────────────────
            Forms\Components\Section::make('Account Credentials')
                ->columns(2)
                ->schema([
                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->columnSpanFull(),

                    TextInput::make('password')
                        ->label(fn (string $operation) => $operation === 'create'
                            ? 'Password'
                            : 'New Password (leave blank to keep current)')
                        ->password()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->minLength(8)
                        ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->columnSpanFull(),
                ]),

            // ── Role & status ────────────────────────────────────────────
            Forms\Components\Section::make('Role & Status')
                ->columns(2)
                ->schema([
                    Select::make('role')
                        ->label('Role')
                        ->options(fn () => static::roleLabels())
                        ->afterStateHydrated(function (Select $component, ?User $record) {
                            $component->state($record?->getRoleNames()->first());
                        })
                        ->required()
                        ->searchable(),

                    Select::make('status')
                        ->label('Account Status')
                        ->options([
                            'active'    => 'Active',
                            'pending'   => 'Pending',
                            'inactive'  => 'Inactive',
                            'suspended' => 'Suspended',
                        ])
                        ->default('active')
                        ->required(),
                ]),

            // ── Settings ─────────────────────────────────────────────────
            Forms\Components\Section::make('Settings')
                ->columns(2)
                ->schema([
                    Select::make('timezone')
                        ->label('Timezone')
                        ->options(fn () => static::timezoneOptions())
                        ->default('Africa/Lagos')
                        ->required()
                        ->searchable(),
                ]),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Display Name')
                    ->description(fn (User $record): string =>
                        trim(($record->profile?->first_name ?? '') . ' ' . ($record->profile?->last_name ?? ''))
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(',')
                    ->formatStateUsing(fn (?string $state): string =>
                        static::friendlyRoleName($state ?? '')
                    ),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'pending'   => 'warning',
                        'inactive'  => 'gray',
                        'suspended' => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active'    => 'Active',
                        'pending'   => 'Pending',
                        'inactive'  => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),

                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->options(fn () => static::roleLabels()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
            ])
            ->bulkActions([]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
