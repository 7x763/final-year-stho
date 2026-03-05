<?php

namespace App\Filament\Resources\Tickets;

use App\Filament\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Resources\Tickets\Pages\EditTicket;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Filament\Resources\Tickets\Pages\ViewTicket;
use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Closure;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Tickets';

    public static function getNavigationGroup(): ?string
    {
        return __('Project Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Tickets');
    }

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('Ticket');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tickets');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['status', 'priority', 'assignees', 'creator', 'epic', 'project']);
        $user = auth()->user();

        if ($user && ! $user->isSuperAdmin()) {
            $userId = $user->id;
            $query->where(function ($query) use ($userId): void {
                $query->whereHas('assignees', function ($query) use ($userId): void {
                    $query->where('users.id', $userId);
                })
                    ->orWhere('created_by', $userId)
                    ->orWhereHas('project.members', function ($query) use ($userId): void {
                        $query->where('users.id', $userId);
                    });
            });
        }

        return $query;
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        $projectId = request()->query('project_id') ?? request()->input('project_id');
        $statusId = request()->query('ticket_status_id') ?? request()->input('ticket_status_id');

        return $schema
            ->components([
                Select::make('project_id')
                    ->label(__('Project'))
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && $user->isSuperAdmin()) {
                            return Project::pluck('name', 'id')->toArray();
                        }

                        return $user ? $user->projects()->pluck('name', 'projects.id')->toArray() : [];
                    })
                    ->default($projectId)
                    ->disabledOn('ticket_on_board')
                    ->dehydrated()
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set): void {
                        $set('ticket_status_id', null);
                        $set('assignees', []);
                        $set('epic_id', null);
                    }),

                Select::make('ticket_status_id')
                    ->label(__('Status'))
                    ->options(function ($get) {
                        $projectId = $get('project_id');
                        if (! $projectId) {
                            return [];
                        }

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->default($statusId)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (! $value) {
                                return;
                            }
                            $projectId = $get('project_id');
                            if (! TicketStatus::where('id', $value)->where('project_id', $projectId)->exists()) {
                                $fail(__("The selected status does not belong to the selected project."));
                            }
                        },
                    ]),

                Select::make('priority_id')
                    ->label(__('Priority'))
                    ->options(TicketPriority::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Select::make('epic_id')
                    ->label(__('Epic'))
                    ->options(function (Get $get) {
                        $projectId = $get('project_id');

                        if (! $projectId) {
                            return [];
                        }

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->hidden(fn (Get $get): bool => ! $get('project_id'))
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (! $value) {
                                return;
                            }
                            $projectId = $get('project_id');
                            if (! Epic::where('id', $value)->where('project_id', $projectId)->exists()) {
                                $fail(__("The selected Epic does not belong to the selected project."));
                            }
                        },
                    ]),

                TextInput::make('name')
                    ->label(__('Ticket Name'))
                    ->required()
                    ->maxLength(255),

                RichEditor::make('description')
                    ->label(__('Description'))
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'video/mp4'])
                    ->columnSpanFull(),

                // // Multi-user assignment
                Select::make('assignees')
                    ->label(__('Assignee'))
                    ->multiple()
                    ->relationship(
                        name: 'assignees',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            $projectId = $get('project_id');
                            if (! $projectId) {
                                return $query->whereRaw('1 = 0');
                            }

                            $project = Project::find($projectId);
                            if (! $project) {
                                return $query->whereRaw('1 = 0');
                            }

                            return $query->whereHas('projects', function ($query) use ($projectId): void {
                                $query->where('projects.id', $projectId);
                            });
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->helperText(__('Choose one or more people to assign this ticket to. Only project members can be assigned.'))
                    ->hidden(fn (Get $get): bool => ! $get('project_id'))
                    ->live()
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (! $value) {
                                return;
                            }
                            $projectId = $get('project_id');
                            if (! $projectId) {
                                return;
                            }
                            // $value is an array of IDs for multiple select
                            $userIds = is_array($value) ? $value : [$value];
                            $project = Project::find($projectId);
                            if (! $project) {
                                return;
                            }
                            $memberIds = $project->members()->pluck('users.id')->map(fn($id) => (string) $id)->toArray();
                            foreach ($userIds as $userId) {
                                if (! in_array((string) $userId, $memberIds)) {
                                    $fail(__("One or more selected people are not members of this project."));
                                    break;
                                }
                            }
                        },
                    ]),

                DatePicker::make('start_date')
                    ->label(__('Start Date'))
                    ->default(now())
                    ->nullable()
                    ->live(),

                DatePicker::make('due_date')
                    ->label(__('Due Date'))
                    ->nullable()
                    ->afterOrEqual('start_date'),
                Select::make('created_by')
                    ->label(__('Creator'))
                    ->relationship('creator', 'name')
                    ->disabled()
                    ->hiddenOn(['create', 'ticket_on_board']),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label(__('Ticket Code'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('project.name')
                    ->label(__('Project'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label(__('Ticket Name'))
                    ->searchable()
                    ->limit(30),

                TextColumn::make('status.name')
                    ->label(__('Status'))
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $color = e($record->status?->color ?? '#6B7280');
                        $name = e(__($record->status?->name ?? 'Unknown'));

                        return new HtmlString(<<<HTML
                            <span class="fi-badge fi-size-sm" style="color: #fff; background-color: {$color};">
                                {$name}
                            </span>
                        HTML);
                    }),

                TextColumn::make('priority.name')
                    ->label(__('Priority'))
                    ->badge()
                    ->color(fn (string $state): string => match (__($state)) {
                        'High' => 'danger',
                        'Medium' => 'warning',
                        'Low' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->default('—')
                    ->placeholder(__('None')),

                // Display multiple assignees
                TextColumn::make('assignees.name')
                    ->label(__('Assignee'))
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),

                TextColumn::make('creator.name')
                    ->label(__('Creator'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label(__('Due Date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('epic.name')
                    ->label(__('Epic'))
                    ->sortable()
                    ->searchable()
                    ->default('—')
                    ->placeholder(__('None')),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label(__('Project'))
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && $user->isSuperAdmin()) {
                            return Project::pluck('name', 'id')->toArray();
                        }

                        return $user ? $user->projects()->pluck('name', 'projects.id')->toArray() : [];
                    })
                    ->searchable(),

                SelectFilter::make('ticket_status_id')
                    ->label(__('Status'))
                    ->options(function () {
                        $projectId = request()->input('tableFilters.project_id');

                        if (! $projectId) {
                            return [];
                        }

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('epic_id')
                    ->label(__('Epic'))
                    ->options(function () {
                        $projectId = request()->input('tableFilters.project_id');

                        if (! $projectId) {
                            return [];
                        }

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('priority_id')
                    ->label(__('Priority'))
                    ->options(TicketPriority::pluck('name', 'id')->toArray())
                    ->searchable(),

                // Filter by assignees
                SelectFilter::make('assignees')
                    ->label(__('Assignee'))
                    ->relationship('assignees', 'name')
                    ->multiple()
                    ->searchable(),

                // Filter by creator
                SelectFilter::make('created_by')
                    ->label(__('Creator'))
                    ->relationship('creator', 'name')
                    ->searchable(),

                Filter::make('due_date')
                    ->label(__('Due Date'))
                    ->schema([
                        DatePicker::make('due_from')->label(__('From date')),
                        DatePicker::make('due_until')->label(__('Until date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('copy')
                    ->label(__('Copy'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function ($record, $livewire) {
                        // Redirect ke halaman create, dengan parameter copy_from
                        return $livewire->redirect(
                            static::getUrl('create', [
                                'copy_from' => $record->id,
                            ])
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected'))
                        ->visible(fn() => auth()->user() && auth()->user()->isSuperAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view' => ViewTicket::route('/{record}'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $cacheKey = 'nav_badge_tickets_' . $user->id;

        return cache()->remember($cacheKey, now()->addMinutes(2), function () use ($user) {
            $query = Ticket::query();

            if (! $user->isSuperAdmin()) {
                $userId = $user->id;
                $query->where(function ($query) use ($userId): void {
                    $query->whereHas('assignees', function ($query) use ($userId): void {
                        $query->where('users.id', $userId);
                    })
                        ->orWhere('created_by', $userId)
                        ->orWhereHas('project.members', function ($query) use ($userId): void {
                            $query->where('users.id', $userId);
                        });
                });
            }

            return (string) $query->count();
        });
    }
}
