<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceEstimateResource\Pages;
use App\Models\PriceEstimate;
use App\Services\AuditLogger;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PriceEstimateResource extends Resource
{
    protected static ?string $model = PriceEstimate::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Leads & Trials';

    protected static ?string $navigationLabel = 'Price Estimates';

    protected static ?string $modelLabel = 'Price Estimate';

    protected static ?int $navigationSort = 2;

    // ── Status helpers ────────────────────────────────────────────────────

    public static function statusOptions(): array
    {
        return [
            'draft'    => 'Draft',
            'sent'     => 'Sent',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'expired'  => 'Expired',
        ];
    }

    public static function statusColor(string $state): string
    {
        return match ($state) {
            'draft'    => 'gray',
            'sent'     => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired'  => 'warning',
            default    => 'gray',
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
        return auth()->user()->hasRole('super_admin');
    }

    // ── Form ─────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Lead Request')
                ->columns(1)
                ->schema([
                    Select::make('lead_request_id')
                        ->label('Lead Request')
                        ->relationship('leadRequest', 'email')
                        ->getOptionLabelFromRecordUsing(fn ($record): string =>
                            "{$record->first_name} {$record->last_name} ({$record->email})"
                        )
                        ->searchable()
                        ->required(),
                ]),

            Forms\Components\Section::make('Estimate Details')
                ->columns(2)
                ->schema([
                    Select::make('currency')
                        ->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'NGN' => 'NGN'])
                        ->default('USD')
                        ->required(),

                    TextInput::make('estimated_amount')
                        ->label('Estimated Amount')
                        ->numeric()
                        ->minValue(0)
                        ->required(),

                    Select::make('billing_cycle')
                        ->options(['bi_weekly' => 'Bi-Weekly', 'monthly' => 'Monthly'])
                        ->default('monthly')
                        ->required(),

                    TextInput::make('estimated_hours_per_week')
                        ->label('Est. Hours / Week')
                        ->numeric()
                        ->minValue(1)->maxValue(168)
                        ->nullable(),

                    Select::make('role_needed')
                        ->options([
                            'virtual_assistant'    => 'Virtual Assistant',
                            'executive_assistant'  => 'Executive Assistant',
                            'social_media_manager' => 'Social Media Manager',
                            'video_editor'         => 'Video Editor',
                            'developer'            => 'Developer',
                            'designer'             => 'Designer',
                            'motion_graphics'      => 'Motion Graphics',
                            'other'                => 'Other',
                        ])
                        ->nullable(),

                    Select::make('status')
                        ->options(fn () => static::statusOptions())
                        ->default('draft')
                        ->required(),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Expires At')
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('accepted_at')
                        ->label('Accepted At')
                        ->nullable(),

                    Textarea::make('notes')
                        ->label('Notes')
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
                TextColumn::make('leadRequest.email')
                    ->label('Lead')
                    ->description(fn (PriceEstimate $record): string =>
                        $record->leadRequest
                            ? trim("{$record->leadRequest->first_name} {$record->leadRequest->last_name}")
                            : '—'
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('currency')
                    ->label('Currency')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('estimated_amount')
                    ->label('Amount')
                    ->formatStateUsing(fn (PriceEstimate $record): string =>
                        $record->currency . ' ' . number_format((float) $record->estimated_amount, 2)
                    )
                    ->sortable(),

                TextColumn::make('billing_cycle')
                    ->label('Cycle')
                    ->formatStateUsing(fn (string $state): string =>
                        $state === 'bi_weekly' ? 'Bi-Weekly' : 'Monthly'
                    ),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string =>
                        static::statusOptions()[$state] ?? ucfirst($state)
                    )
                    ->color(fn (string $state): string => static::statusColor($state)),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(fn () => static::statusOptions()),

                SelectFilter::make('currency')
                    ->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'NGN' => 'NGN']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_sent')
                    ->label('Mark Sent')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (PriceEstimate $record): bool => $record->status === 'draft')
                    ->action(function (PriceEstimate $record): void {
                        $record->update(['status' => 'sent']);
                        AuditLogger::log('price_estimate.updated', $record, [
                            'from' => 'draft', 'to' => 'sent', 'field' => 'status',
                        ]);
                        Notification::make()->title('Estimate marked as Sent')->success()->send();
                    }),

                Tables\Actions\Action::make('mark_accepted')
                    ->label('Mark Accepted')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PriceEstimate $record): bool => $record->status === 'sent')
                    ->action(function (PriceEstimate $record): void {
                        $record->update([
                            'status'      => 'accepted',
                            'accepted_at' => now(),
                        ]);
                        // Update lead status to price_accepted
                        if ($record->leadRequest && $record->leadRequest->status === 'price_estimated') {
                            $old = $record->leadRequest->status;
                            $record->leadRequest->update(['status' => 'price_accepted']);
                            AuditLogger::log('lead_request.status_changed', $record->leadRequest, [
                                'from' => $old, 'to' => 'price_accepted',
                            ]);
                        }
                        AuditLogger::log('price_estimate.accepted', $record, [
                            'lead_request_id' => $record->lead_request_id,
                            'amount'          => $record->estimated_amount,
                            'currency'        => $record->currency,
                        ]);
                        Notification::make()->title('Estimate accepted — lead status updated to Price Accepted')->success()->send();
                    }),

                Tables\Actions\Action::make('mark_rejected')
                    ->label('Mark Rejected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (PriceEstimate $record): bool => in_array($record->status, ['draft', 'sent']))
                    ->action(function (PriceEstimate $record): void {
                        $record->update(['status' => 'rejected']);
                        AuditLogger::log('price_estimate.updated', $record, [
                            'from' => $record->getOriginal('status'), 'to' => 'rejected', 'field' => 'status',
                        ]);
                        Notification::make()->title('Estimate marked as Rejected')->warning()->send();
                    }),

                Tables\Actions\Action::make('mark_expired')
                    ->label('Mark Expired')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (PriceEstimate $record): bool => in_array($record->status, ['draft', 'sent']))
                    ->action(function (PriceEstimate $record): void {
                        $record->update(['status' => 'expired']);
                        AuditLogger::log('price_estimate.updated', $record, [
                            'from' => $record->getOriginal('status'), 'to' => 'expired', 'field' => 'status',
                        ]);
                        Notification::make()->title('Estimate marked as Expired')->warning()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPriceEstimates::route('/'),
            'create' => Pages\CreatePriceEstimate::route('/create'),
            'edit'   => Pages\EditPriceEstimate::route('/{record}/edit'),
        ];
    }
}
