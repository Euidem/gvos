<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?string $modelLabel = 'Payment';

    protected static ?int $navigationSort = 4;

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
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin'])
            && $record->status === 'pending';
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    // ── Form ─────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('payment_reference')
                ->label('Payment Reference')
                ->placeholder('Auto-generated if blank')
                ->maxLength(100),
            Forms\Components\Select::make('invoice_id')
                ->relationship('invoice', 'invoice_number')
                ->searchable()->nullable(),
            Forms\Components\Select::make('workspace_id')
                ->relationship('workspace', 'name')
                ->searchable()->nullable(),
            Forms\Components\Select::make('workspace_subscription_id')
                ->relationship('subscription', 'id')
                ->label('Subscription')
                ->searchable()->nullable(),
            Forms\Components\Select::make('provider')
                ->options(Payment::providerLabels())
                ->default('manual')->required(),
            Forms\Components\TextInput::make('provider_reference')
                ->nullable()->maxLength(255),
            Forms\Components\Select::make('currency')
                ->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'NGN' => 'NGN', 'CAD' => 'CAD'])
                ->default('USD')->required(),
            Forms\Components\TextInput::make('amount')
                ->numeric()->minValue(0)->required(),
            Forms\Components\Select::make('status')
                ->options(Payment::statusLabels())
                ->default('pending')->required(),
            Forms\Components\DateTimePicker::make('paid_at')->nullable(),
            Forms\Components\Textarea::make('confirmation_notes')->rows(2)->nullable(),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_reference')
                    ->searchable()->sortable()->fontFamily('mono'),
                TextColumn::make('invoice.invoice_number')
                    ->label('Invoice')->searchable()->toggleable(),
                TextColumn::make('workspace.name')->searchable()->sortable(),
                TextColumn::make('provider')
                    ->formatStateUsing(fn ($state) => Payment::providerLabels()[$state] ?? $state),
                TextColumn::make('currency'),
                TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency)->sortable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'confirmed' => 'success',
                        'pending'   => 'warning',
                        'failed'    => 'danger',
                        'reversed'  => 'info',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),
                TextColumn::make('paid_at')->dateTime('d M Y')->sortable(),
                TextColumn::make('confirmedBy.name')
                    ->label('Confirmed by')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(Payment::statusLabels()),
                SelectFilter::make('provider')->options(Payment::providerLabels()),
                SelectFilter::make('currency')->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'NGN' => 'NGN', 'CAD' => 'CAD']),
                SelectFilter::make('workspace_id')
                    ->label('Workspace')
                    ->relationship('workspace', 'name')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),

                // Confirm payment
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Confirmation notes (optional)')
                            ->rows(2),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $record->confirm(auth()->id(), $data['notes'] ?? '');
                        AuditLogger::paymentConfirmed($record, ['confirmed_by' => auth()->id()]);
                        app(NotificationService::class)->notifyPaymentRecorded($record->fresh(['workspace']), auth()->user());
                        Notification::make()->title('Payment confirmed.')->success()->send();
                    }),

                // Cancel payment
                Action::make('cancelPayment')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        $record->update(['status' => 'cancelled']);
                        AuditLogger::paymentFailedOrCancelled($record, ['actioned_by' => auth()->id()]);
                        Notification::make()->title('Payment cancelled.')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
