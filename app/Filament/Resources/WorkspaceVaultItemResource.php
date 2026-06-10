<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceVaultItemResource\Pages;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceVaultAccessLog;
use App\Models\WorkspaceVaultItem;
use App\Services\AuditLogger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WorkspaceVaultItemResource extends Resource
{
    protected static ?string $model = WorkspaceVaultItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationGroup = 'Security';

    protected static ?string $navigationLabel = 'Password Vault';

    protected static ?string $modelLabel = 'Vault Item';

    protected static ?int $navigationSort = 1;

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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Credential Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('workspace_id')
                        ->label('Workspace')
                        ->relationship('workspace', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('category')
                        ->label('Category')
                        ->options(WorkspaceVaultItem::categoryLabels())
                        ->searchable()
                        ->nullable(),

                    Forms\Components\TextInput::make('username')
                        ->label('Username')
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\TextInput::make('login_url')
                        ->label('Login URL')
                        ->url()
                        ->maxLength(2048)
                        ->nullable()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('secret_value')
                        ->label('Secret')
                        ->password()
                        ->formatStateUsing(fn ($state = null) => null)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(10000)
                        ->helperText('Existing secrets are never prefilled. Leave blank on edit to keep the current encrypted value.')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('notes')
                        ->rows(3)
                        ->nullable()
                        ->helperText('Do not paste additional secrets into notes.')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Access Control')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('visibility')
                        ->options(WorkspaceVaultItem::visibilityLabels())
                        ->default('restricted')
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options(WorkspaceVaultItem::statusLabels())
                        ->default('active')
                        ->required(),

                    Forms\Components\CheckboxList::make('allowed_roles')
                        ->label('Allowed Roles')
                        ->options(WorkspaceVaultItem::allowedRoleOptions())
                        ->columns(2)
                        ->dehydrateStateUsing(fn (?array $state): array => collect($state ?? [])
                            ->filter()
                            ->unique()
                            ->values()
                            ->all())
                        ->helperText('Talent and staff require explicit role or user assignment to reveal secrets.')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('allowed_user_ids')
                        ->label('Allowed Users')
                        ->multiple()
                        ->options(fn (Get $get): array => static::workspaceUserOptions($get('workspace_id')))
                        ->searchable()
                        ->dehydrateStateUsing(fn (?array $state): array => collect($state ?? [])
                            ->map(fn ($id) => (int) $id)
                            ->filter()
                            ->unique()
                            ->values()
                            ->all())
                        ->helperText('Options are limited to active workspace members, primary team, and task assignees.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('workspace.workspace_code')
                    ->label('Workspace')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (WorkspaceVaultItem::categoryLabels()[$state] ?? ucfirst(str_replace('_', ' ', $state)))
                        : 'Other')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('username')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('visibility')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WorkspaceVaultItem::visibilityLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'workspace_admins' => 'info',
                        'assigned_users'   => 'warning',
                        default            => 'gray',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WorkspaceVaultItem::statusLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),

                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_revealed_at')
                    ->label('Last Revealed')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('workspace_id')
                    ->label('Workspace')
                    ->relationship('workspace', 'name')
                    ->searchable(),

                SelectFilter::make('visibility')
                    ->options(WorkspaceVaultItem::visibilityLabels()),

                SelectFilter::make('status')
                    ->options(WorkspaceVaultItem::statusLabels()),

                SelectFilter::make('category')
                    ->options(WorkspaceVaultItem::categoryLabels()),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (WorkspaceVaultItem $record): bool => $record->status === 'active')
                    ->action(function (WorkspaceVaultItem $record): void {
                        $record->update([
                            'status' => 'archived',
                            'updated_by' => auth()->id(),
                        ]);
                        WorkspaceVaultAccessLog::record($record, auth()->user(), 'archived', request(), [
                            'source' => 'filament',
                        ]);
                        AuditLogger::workspaceVaultItemArchived($record, ['source' => 'filament']);
                        Notification::make()->title('Vault item archived.')->success()->send();
                    }),

                Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (WorkspaceVaultItem $record): bool => $record->status === 'archived')
                    ->action(function (WorkspaceVaultItem $record): void {
                        $record->update([
                            'status' => 'active',
                            'updated_by' => auth()->id(),
                        ]);
                        WorkspaceVaultAccessLog::record($record, auth()->user(), 'restored', request(), [
                            'source' => 'filament',
                        ]);
                        AuditLogger::workspaceVaultItemRestored($record, ['source' => 'filament']);
                        Notification::make()->title('Vault item restored.')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function workspaceUserOptions($workspaceId): array
    {
        if (! $workspaceId) {
            return [];
        }

        $workspace = Workspace::find($workspaceId);

        if (! $workspace) {
            return [];
        }

        $memberIds = $workspace->activeMembers()->pluck('user_id');
        $primaryIds = collect([$workspace->primary_manager_id, $workspace->primary_talent_id])->filter();
        $taskAssigneeIds = $workspace->tasks()
            ->whereNotNull('assigned_to_user_id')
            ->pluck('assigned_to_user_id');

        $userIds = $memberIds
            ->merge($primaryIds)
            ->merge($taskAssigneeIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => $user->name . ' (' . $user->email . ')',
            ])
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkspaceVaultItems::route('/'),
            'create' => Pages\CreateWorkspaceVaultItem::route('/create'),
            'edit'   => Pages\EditWorkspaceVaultItem::route('/{record}/edit'),
        ];
    }
}
