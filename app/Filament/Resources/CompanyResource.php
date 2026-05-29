<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use App\Services\AuditLogger;
use App\Support\CountryList;
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

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'People & Organizations';

    protected static ?string $navigationLabel = 'Companies';

    protected static ?string $modelLabel = 'Company';

    protected static ?int $navigationSort = 1;

    // ── Status label helpers ──────────────────────────────────────────────

    public static function statusOptions(): array
    {
        return [
            'active'    => 'Active',
            'pending'   => 'Pending',
            'inactive'  => 'Inactive',
            'suspended' => 'Suspended',
        ];
    }

    public static function statusColor(string $state): string
    {
        return match ($state) {
            'active'    => 'success',
            'pending'   => 'warning',
            'inactive'  => 'gray',
            'suspended' => 'danger',
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

            Forms\Components\Section::make('Company Identity')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Company Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('legal_name')
                        ->label('Legal / Registered Name')
                        ->maxLength(255)
                        ->nullable(),

                    Select::make('type')
                        ->label('Account Type')
                        ->options(['individual' => 'Individual', 'business' => 'Business'])
                        ->default('business')
                        ->required(),

                    TextInput::make('industry')
                        ->label('Industry')
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('website')
                        ->label('Website')
                        ->url()
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('company_email_domain')
                        ->label('Email Domain')
                        ->helperText('e.g. acme.com — used for staff invitation matching')
                        ->maxLength(255)
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Location & Timezone')
                ->columns(3)
                ->schema([
                    Select::make('country')
                        ->label('Country')
                        ->options(CountryList::options())
                        ->searchable()
                        ->nullable(),

                    TextInput::make('city')
                        ->label('City')
                        ->maxLength(100)
                        ->nullable(),

                    Select::make('timezone')
                        ->label('Timezone')
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

            Forms\Components\Section::make('Primary Contact')
                ->columns(2)
                ->schema([
                    TextInput::make('primary_contact_name')
                        ->label('Contact Name')
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('primary_contact_email')
                        ->label('Contact Email')
                        ->email()
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('primary_contact_phone')
                        ->label('Contact Phone')
                        ->tel()
                        ->maxLength(50)
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Status & Notes')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(fn () => static::statusOptions())
                        ->default('pending')
                        ->required(),

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
                TextColumn::make('name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'business' ? 'info' : 'gray'),

                TextColumn::make('country')
                    ->label('Country')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('primary_contact_name')
                    ->label('Primary Contact')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => static::statusColor($state)),

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

                SelectFilter::make('type')
                    ->label('Type')
                    ->options(['individual' => 'Individual', 'business' => 'Business']),
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
            'index'  => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit'   => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
