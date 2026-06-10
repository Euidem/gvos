<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkspaceTaskResource\Pages;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceTask;
use App\Services\AuditLogger;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WorkspaceTaskResource extends Resource
{
    protected static ?string $model = WorkspaceTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?string $modelLabel = 'Workspace Task';

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
        return false; // Soft delete via model only; no hard delete from Filament
    }

    // ── Navigation badge (open tasks count) ───────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = WorkspaceTask::whereIn('status', ['pending', 'in_progress', 'blocked', 'submitted', 'revision_requested'])->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // ── Form ─────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Task Identity')
                ->columns(2)
                ->schema([
                    TextInput::make('task_code')
                        ->label('Task Code')
                        ->maxLength(30)
                        ->helperText('Auto-generated if blank')
                        ->nullable(),

                    Select::make('workspace_id')
                        ->label('Workspace')
                        ->relationship('workspace', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('title')
                        ->label('Task Title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(4)
                        ->nullable()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Assignment & Priority')
                ->columns(2)
                ->schema([
                    Select::make('assigned_to_user_id')
                        ->label('Assigned To')
                        ->options(fn () => User::orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->nullable()
                        ->helperText('Preferably a member of the selected workspace'),

                    Select::make('priority')
                        ->label('Priority')
                        ->options(WorkspaceTask::priorityLabels())
                        ->default('normal')
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options(WorkspaceTask::statusLabels())
                        ->default('pending')
                        ->required(),

                    DatePicker::make('due_date')
                        ->label('Due Date')
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Internal Notes')
                ->schema([
                    Textarea::make('internal_notes')
                        ->label('Internal Notes')
                        ->rows(3)
                        ->nullable()
                        ->helperText('Visible to admins and managers only'),
                ]),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                TextColumn::make('title')
                    ->label('Task')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->title),

                TextColumn::make('workspace.name')
                    ->label('Workspace')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WorkspaceTask::statusLabels()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending'            => 'warning',
                        'in_progress'        => 'info',
                        'blocked'            => 'danger',
                        'submitted'          => 'primary',
                        'revision_requested' => 'danger',
                        'approved'           => 'success',
                        'closed'             => 'gray',
                        'cancelled'          => 'gray',
                        default              => 'gray',
                    }),

                TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low'    => 'gray',
                        'normal' => 'info',
                        'high'   => 'warning',
                        'urgent' => 'danger',
                        default  => 'gray',
                    }),

                TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('due_date')
                    ->label('Due')
                    ->date('d M Y')
                    ->sortable()
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
                    ->label('Status')
                    ->options(WorkspaceTask::statusLabels()),

                SelectFilter::make('priority')
                    ->label('Priority')
                    ->options(WorkspaceTask::priorityLabels()),

                SelectFilter::make('workspace_id')
                    ->label('Workspace')
                    ->relationship('workspace', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('soft_delete')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (WorkspaceTask $record): bool => ! $record->trashed())
                    ->action(function (WorkspaceTask $record): void {
                        AuditLogger::workspaceTaskDeleted($record);
                        $record->delete();
                    }),
            ])
            ->bulkActions([]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkspaceTasks::route('/'),
            'create' => Pages\CreateWorkspaceTask::route('/create'),
            'edit'   => Pages\EditWorkspaceTask::route('/{record}/edit'),
        ];
    }
}
