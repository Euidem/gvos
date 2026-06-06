<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingPlanResource\Pages;
use App\Models\BillingPlan;
use App\Services\AuditLogger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BillingPlanResource extends Resource
{
    protected static ?string $model = BillingPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Billing Plans';

    protected static ?string $modelLabel = 'Billing Plan';

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
            Forms\Components\TextInput::make('name')
                ->required()->maxLength(255),
            Forms\Components\TextInput::make('code')
                ->maxLength(100)->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description')
                ->rows(2),
            Forms\Components\Select::make('currency')
                ->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'NGN' => 'NGN', 'CAD' => 'CAD'])
                ->default('USD')->required(),
            Forms\Components\TextInput::make('amount')
                ->numeric()->minValue(0)->required(),
            Forms\Components\Select::make('billing_cycle')
                ->options(BillingPlan::cycleLabels())
                ->default('bi_weekly')->required(),
            Forms\Components\TextInput::make('included_talents')
                ->numeric()->minValue(1)->default(1),
            Forms\Components\TextInput::make('included_hours_per_week')
                ->numeric()->minValue(0)->nullable(),
            Forms\Components\Select::make('status')
                ->options(BillingPlan::statusLabels())
                ->default('active')->required(),
            Forms\Components\Textarea::make('notes')->rows(2),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('code')->searchable()->toggleable(),
                TextColumn::make('currency')->sortable(),
                TextColumn::make('amount')->money(fn ($record) => $record->currency)->sortable(),
                TextColumn::make('billing_cycle')
                    ->formatStateUsing(fn ($state) => BillingPlan::cycleLabels()[$state] ?? $state),
                TextColumn::make('included_talents')->sortable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'warning',
                        'archived' => 'gray',
                        default    => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(BillingPlan::statusLabels()),
                SelectFilter::make('currency')->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'NGN' => 'NGN', 'CAD' => 'CAD']),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (BillingPlan $record): bool => $record->status !== 'archived')
                    ->requiresConfirmation()
                    ->action(function (BillingPlan $record): void {
                        $record->update(['status' => 'archived']);
                        AuditLogger::billingPlanUpdated($record, [
                            'action'      => 'archived',
                            'actioned_by' => auth()->id(),
                        ]);
                        Notification::make()->title('Billing plan archived.')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBillingPlans::route('/'),
            'create' => Pages\CreateBillingPlan::route('/create'),
            'edit'   => Pages\EditBillingPlan::route('/{record}/edit'),
        ];
    }
}
