<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceSubscriptionResource\Pages;
use App\Models\BillingPlan;
use App\Models\WorkspaceSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
