<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TalentProfileResource\Pages;
use App\Models\TalentProfile;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TalentProfileResource extends Resource
{
    protected static ?string $model = TalentProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'People & Organizations';

    protected static ?string $navigationLabel = 'Talent Profiles';

    protected static ?string $modelLabel = 'Talent Profile';

    protected static ?int $navigationSort = 4;

    // ── Label helpers ─────────────────────────────────────────────────────

    public static function trainingStatusOptions(): array
    {
        return [
            'not_started'  => 'Not Started',
            'in_training'  => 'In Training',
            'prequalified' => 'Pre-qualified',
            'active'       => 'Active',
            'paused'       => 'Paused',
            'suspended'    => 'Suspended',
        ];
    }

    public static function equipmentStatusOptions(): array
    {
        return [
            'not_assigned' => 'Not Assigned',
            'assigned'     => 'Assigned',
            'returned'     => 'Returned',
            'damaged'      => 'Damaged',
            'maintenance'  => 'Maintenance',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'active'    => 'Active',
            'pending'   => 'Pending',
            'inactive'  => 'Inactive',
            'suspended' => 'Suspended',
        ];
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

            Forms\Components\Section::make('Talent Identity')
                ->columns(2)
                ->schema([
                    Select::make('user_id')
                        ->label('User Account')
                        ->relationship('user', 'email')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('talent_code')
                        ->label('Talent Code')
                        ->helperText('Leave blank to assign manually later (e.g. GVT-001)')
                        ->maxLength(50)
                        ->unique(TalentProfile::class, 'talent_code', ignoreRecord: true)
                        ->nullable(),

                    TextInput::make('role_type')
                        ->label('Role / Job Title')
                        ->maxLength(255)
                        ->nullable(),

                    Textarea::make('skill_summary')
                        ->label('Skill Summary')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Availability & Timezone')
                ->columns(3)
                ->schema([
                    Select::make('availability_type')
                        ->label('Availability Type')
                        ->options([
                            'fixed'    => 'Fixed',
                            'flexible' => 'Flexible',
                            'hybrid'   => 'Hybrid',
                        ])
                        ->default('flexible')
                        ->required(),

                    TextInput::make('weekly_capacity_hours')
                        ->label('Weekly Capacity (hours)')
                        ->numeric()
                        ->default(40)
                        ->minValue(1)
                        ->maxValue(168),

                    Select::make('work_timezone')
                        ->label('Work Timezone')
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
                        ->default('Africa/Lagos')
                        ->searchable(),
                ]),

            Forms\Components\Section::make('Status & Equipment')
                ->columns(2)
                ->schema([
                    Select::make('training_status')
                        ->label('Training Status')
                        ->options(fn () => static::trainingStatusOptions())
                        ->default('not_started')
                        ->required(),

                    Select::make('equipment_status')
                        ->label('Equipment Status')
                        ->options(fn () => static::equipmentStatusOptions())
                        ->default('not_assigned')
                        ->required(),

                    Select::make('status')
                        ->label('Record Status')
                        ->options(fn () => static::statusOptions())
                        ->default('pending')
                        ->required(),

                    Textarea::make('internal_notes')
                        ->label('Internal Notes')
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
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('talent_code')
                    ->label('Code')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('role_type')
                    ->label('Role Type')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('training_status')
                    ->label('Training')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string =>
                        static::trainingStatusOptions()[$state] ?? ucwords(str_replace('_', ' ', $state))
                    )
                    ->color(fn (string $state): string => match ($state) {
                        'active'       => 'success',
                        'prequalified' => 'info',
                        'in_training'  => 'warning',
                        'paused'       => 'gray',
                        'suspended'    => 'danger',
                        default        => 'gray',
                    }),

                TextColumn::make('equipment_status')
                    ->label('Equipment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string =>
                        static::equipmentStatusOptions()[$state] ?? ucwords(str_replace('_', ' ', $state))
                    )
                    ->color(fn (string $state): string => match ($state) {
                        'assigned'     => 'success',
                        'not_assigned' => 'gray',
                        'damaged'      => 'danger',
                        'maintenance'  => 'warning',
                        default        => 'gray',
                    }),

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
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(fn () => static::statusOptions()),

                SelectFilter::make('training_status')
                    ->label('Training Status')
                    ->options(fn () => static::trainingStatusOptions()),

                SelectFilter::make('equipment_status')
                    ->label('Equipment Status')
                    ->options(fn () => static::equipmentStatusOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTalentProfiles::route('/'),
            'create' => Pages\CreateTalentProfile::route('/create'),
            'edit'   => Pages\EditTalentProfile::route('/{record}/edit'),
        ];
    }
}
