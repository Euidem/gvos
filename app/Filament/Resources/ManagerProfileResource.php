<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManagerProfileResource\Pages;
use App\Models\ManagerProfile;
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

class ManagerProfileResource extends Resource
{
    protected static ?string $model = ManagerProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'People';

    protected static ?string $navigationLabel = 'Manager Profiles';

    protected static ?string $modelLabel = 'Manager Profile';

    protected static ?int $navigationSort = 6;

    // ── Status options ────────────────────────────────────────────────────

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

            Forms\Components\Section::make('Manager Identity')
                ->columns(2)
                ->schema([
                    Select::make('user_id')
                        ->label('User Account')
                        ->relationship('user', 'email')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('manager_code')
                        ->label('Manager Code')
                        ->helperText('Leave blank to assign manually later (e.g. GVM-001)')
                        ->maxLength(50)
                        ->unique(ManagerProfile::class, 'manager_code', ignoreRecord: true)
                        ->nullable(),

                    TextInput::make('department')
                        ->label('Department / Speciality Area')
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('specialization')
                        ->label('Specialization')
                        ->maxLength(255)
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Capacity')
                ->columns(2)
                ->schema([
                    TextInput::make('capacity_limit')
                        ->label('Capacity Limit (clients)')
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->maxValue(999),

                    TextInput::make('current_load')
                        ->label('Current Load (clients)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                ]),

            Forms\Components\Section::make('Status & Notes')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Status')
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

                TextColumn::make('manager_code')
                    ->label('Code')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('department')
                    ->label('Department')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('current_load')
                    ->label('Load')
                    ->description(fn (ManagerProfile $record): string =>
                        "/ {$record->capacity_limit} capacity"
                    )
                    ->sortable(),

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
            'index'  => Pages\ListManagerProfiles::route('/'),
            'create' => Pages\CreateManagerProfile::route('/create'),
            'edit'   => Pages\EditManagerProfile::route('/{record}/edit'),
        ];
    }
}
