<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\AuditLogger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?string $modelLabel = 'Invoice';

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
        return auth()->user()->hasAnyRole(['super_admin', 'operations_admin'])
            && in_array($record->status, ['draft', 'issued', 'partially_paid'], true);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    // ── Form ─────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('invoice_number')
                ->label('Invoice Number')
                ->placeholder('Auto-generated if blank')
                ->maxLength(50),

            Forms\Components\Select::make('workspace_id')
                ->relationship('workspace', 'name')
                ->searchable()->required(),

            Forms\Components\Select::make('workspace_subscription_id')
                ->relationship('subscription', 'id')
                ->label('Subscription')
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

            Forms\Components\TextInput::make('subtotal')
                ->numeric()->minValue(0)->default(0),
            Forms\Components\TextInput::make('discount_amount')
                ->numeric()->minValue(0)->default(0),
            Forms\Components\TextInput::make('tax_amount')
                ->numeric()->minValue(0)->default(0),
            Forms\Components\TextInput::make('total_amount')
                ->numeric()->minValue(0)->required(),
            Forms\Components\TextInput::make('amount_paid')
                ->numeric()->minValue(0)->default(0),
            Forms\Components\TextInput::make('balance_due')
                ->numeric()->minValue(0)->default(0),

            Forms\Components\Select::make('status')
                ->options(Invoice::statusLabels())
                ->default('draft')->required(),

            Forms\Components\DatePicker::make('issue_date')->default(now())->required(),
            Forms\Components\DatePicker::make('due_date')->nullable(),

            Forms\Components\Textarea::make('notes')->rows(2)->nullable(),
            Forms\Components\Textarea::make('internal_notes')
                ->rows(2)->nullable()
                ->helperText('Not visible to clients'),

            // Inline invoice items
            Forms\Components\Repeater::make('items')
                ->relationship('items')
                ->schema([
                    Forms\Components\TextInput::make('description')->required()->columnSpan(2),
                    Forms\Components\Select::make('item_type')
                        ->options(InvoiceItem::typeLabels())
                        ->default('subscription')->required(),
                    Forms\Components\TextInput::make('quantity')
                        ->numeric()->default(1)->minValue(0.0001)->required(),
                    Forms\Components\TextInput::make('unit_amount')
                        ->numeric()->default(0)->required(),
                    Forms\Components\TextInput::make('total_amount')
                        ->numeric()->default(0)->helperText('Auto-calculated if 0'),
                ])
                ->columns(3)
                ->defaultItems(0)
                ->collapsible(),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()->sortable()
                    ->fontFamily('mono'),
                TextColumn::make('workspace.name')->searchable()->sortable(),
                TextColumn::make('workspace.workspace_code')
                    ->label('Code')->searchable()->toggleable(),
                TextColumn::make('currency'),
                TextColumn::make('total_amount')
                    ->money(fn ($record) => $record->currency)->sortable(),
                TextColumn::make('balance_due')
                    ->money(fn ($record) => $record->currency)->sortable()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft'          => 'gray',
                        'issued'         => 'info',
                        'partially_paid' => 'warning',
                        'paid'           => 'success',
                        'overdue'        => 'danger',
                        'cancelled'      => 'gray',
                        'void'           => 'gray',
                        default          => 'gray',
                    }),
                TextColumn::make('issue_date')->date('d M Y')->sortable(),
                TextColumn::make('due_date')->date('d M Y')->sortable()->toggleable(),
                TextColumn::make('paid_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('workspace_id')
                    ->label('Workspace')
                    ->relationship('workspace', 'name')
                    ->searchable(),
                SelectFilter::make('status')->options(Invoice::statusLabels()),
                SelectFilter::make('currency')->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'NGN' => 'NGN', 'CAD' => 'CAD']),
                Filter::make('due_date')
                    ->form([
                        Forms\Components\DatePicker::make('due_from')->label('Due from'),
                        Forms\Components\DatePicker::make('due_until')->label('Due until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['due_from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date))
                            ->when($data['due_until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date));
                    }),
            ])
            ->defaultSort('issue_date', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),

                // Issue invoice
                Action::make('issue')
                    ->label('Issue')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (Invoice $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->recalculateTotals();
                        $record->save();
                        $record->update(['status' => 'issued']);
                        AuditLogger::invoiceIssued($record, ['actioned_by' => auth()->id()]);
                        Notification::make()->title('Invoice issued.')->success()->send();
                    }),

                // Mark as paid
                Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Invoice $record) => in_array($record->status, ['issued', 'partially_paid', 'overdue'], true))
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->update([
                            'status'      => 'paid',
                            'amount_paid' => $record->total_amount,
                            'balance_due' => 0,
                            'paid_at'     => now(),
                        ]);
                        AuditLogger::invoiceMarkedPaid($record, ['actioned_by' => auth()->id()]);
                        Notification::make()->title('Invoice marked as paid.')->success()->send();
                    }),

                // Cancel invoice
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'issued'], true))
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->update(['status' => 'cancelled']);
                        AuditLogger::invoiceCancelled($record, ['actioned_by' => auth()->id()]);
                        Notification::make()->title('Invoice cancelled.')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
