<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Company;
use App\Models\Department;
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

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'People';

    protected static ?string $navigationLabel = 'Departments';

    protected static ?string $modelLabel = 'Department';

    protected static ?int $navigationSort = 3;

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

            Forms\Components\Section::make('Department Details')
                ->columns(2)
                ->schema([
                    Select::make('company_id')
                        ->label('Company')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('name')
                        ->label('Department Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(2)
                        ->nullable()
                        ->columnSpanFull(),

                    TextInput::make('manager_name')
                        ->label('Department Manager Name')
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('manager_email')
                        ->label('Department Manager Email')
                        ->email()
                        ->maxLength(255)
                        ->nullable(),

                    Select::make('status')
                        ->label('Status')
                        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                        ->default('active')
                        ->required(),
                ]),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Department')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('manager_name')
                    ->label('Manager')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),

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
                    ->options(['active' => 'Active', 'inactive' => 'Inactive']),

                SelectFilter::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'name'),
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
            'index'  => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit'   => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
