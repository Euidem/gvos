<?php

namespace App\Filament\Resources\WorkspaceResource\RelationManagers;

use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkspaceInvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'invitations';

    protected static ?string $title = 'Invitations';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255),

            TextInput::make('name')
                ->label('Name')
                ->maxLength(255)
                ->nullable(),

            Select::make('workspace_role')
                ->label('Workspace Role')
                ->options(collect(WorkspaceMember::roleLabels())->except('client')->toArray())
                ->default('client_staff')
                ->required(),

            Select::make('platform_role')
                ->label('Platform Role')
                ->options(WorkspaceInvitation::platformRoleLabels())
                ->nullable(),

            Forms\Components\DateTimePicker::make('expires_at')
                ->label('Expires At')
                ->default(now()->addDays(14))
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('workspace_role')
                    ->label('Workspace Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WorkspaceMember::roleLabels()[$state] ?? ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WorkspaceInvitation::statusLabels()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'revoked', 'expired' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('inviter.name')
                    ->label('Invited By')
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('d M Y')
                    ->toggleable(),

                TextColumn::make('accepted_at')
                    ->label('Accepted At')
                    ->dateTime('d M Y H:i')
                    ->toggleable()
                    ->placeholder('—'),

                TextColumn::make('acceptedBy.name')
                    ->label('Accepted By')
                    ->toggleable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Invite Member')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['email'] = strtolower($data['email']);
                        $data['invited_by'] = auth()->id();
                        $data['status'] = 'pending';
                        $data['expires_at'] = $data['expires_at'] ?? now()->addDays(14);

                        return $data;
                    })
                    ->after(function (WorkspaceInvitation $record): void {
                        AuditLogger::workspaceInvitationCreated($record, ['source' => 'filament']);
                        app(NotificationService::class)->notifyWorkspaceInvitationSent($record, auth()->user());
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (WorkspaceInvitation $record): bool => $record->status === 'pending')
                    ->action(function (WorkspaceInvitation $record): void {
                        $record->update(['expires_at' => now()->addDays(14)]);
                        $record = $record->fresh(['workspace', 'inviter']);
                        AuditLogger::workspaceInvitationResent($record, ['source' => 'filament']);
                        app(NotificationService::class)->notifyWorkspaceInvitationSent($record, auth()->user());
                    }),

                Tables\Actions\Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (WorkspaceInvitation $record): bool => $record->status === 'pending')
                    ->action(function (WorkspaceInvitation $record): void {
                        $record->update(['status' => 'revoked']);
                        AuditLogger::workspaceInvitationRevoked($record->fresh(), ['source' => 'filament']);
                    }),
            ])
            ->bulkActions([]);
    }
}
