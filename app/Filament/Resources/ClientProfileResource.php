<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientProfileResource\Pages;
use App\Models\ClientProfile;
use App\Models\User;
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

class ClientProfileResource extends Resource
{
    protected static ?string $model = ClientProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'People & Organizations';

    protected static ?string $navigationLabel = 'Client Profiles';

    protected static ?string $modelLabel = 'Client Profile';

    protected static ?int $navigationSort = 3;

    // ── Client type labels ────────────────────────────────────────────────

    public static function clientTypeOptions(): array
    {
        return [
            'individual'    => 'Individual',
            'business_admin'  => 'Business Admin',
            'business_staff'  => 'Business Staff',
        ];
    }

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

            Forms\Components\Section::make('User & Type')
                ->columns(2)
                ->schema([
                    Select::make('user_id')
                        ->label('User Account')
                        ->relationship('user', 'email')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    Select::make('client_type')
                        ->label('Client Type')
                        ->options(fn () => static::clientTypeOptions())
                        ->default('individual')
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options(fn () => static::statusOptions())
                        ->default('pending')
                        ->required(),
                ]),

            Forms\Components\Section::make('Company & Department')
                ->columns(2)
                ->schema([
                    Select::make('company_id')
                        ->label('Company')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('department_id')
                        ->label('Department')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    TextInput::make('job_title')
                        ->label('Job Title')
                        ->maxLength(255)
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Service Details')
                ->columns(2)
                ->schema([
                    TextInput::make('preferred_contact_window')
                        ->label('Preferred Contact Window')
                        ->helperText('e.g. Mon–Fri 9am–5pm WAT')
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('service_interest')
                        ->label('Service Interest')
                        ->maxLength(255)
                        ->nullable(),

                    Textarea::make('notes')
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

                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('client_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string =>
                        static::clientTypeOptions()[$state] ?? ucwords(str_replace('_', ' ', $state))
                    )
                    ->color('info'),

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

                SelectFilter::make('client_type')
                    ->label('Client Type')
                    ->options(fn () => static::clientTypeOptions()),
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
            'index'  => Pages\ListClientProfiles::route('/'),
            'create' => Pages\CreateClientProfile::route('/create'),
            'edit'   => Pages\EditClientProfile::route('/{record}/edit'),
        ];
    }
}
